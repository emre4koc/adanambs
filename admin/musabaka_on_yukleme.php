<?php
ob_start();
require_once '../config/session_check_admin.php';
require_once '../config/db.php';

// SimpleXLSX kütüphane kontrolü
$library_path = '../lib/SimpleXLSX.php';
if (file_exists($library_path)) {
    require_once $library_path;
}

if (!class_exists('SimpleXLSX')) {
    if (class_exists('Shuchkin\SimpleXLSX')) {
        class_alias('Shuchkin\SimpleXLSX', 'SimpleXLSX');
    }
}

$sayfa_baslik = "Müsabaka Ön Yükleme";
$mesaj = '';
if (isset($_SESSION['mesaj'])) {
    $mesaj = $_SESSION['mesaj'];
    unset($_SESSION['mesaj']);
}

// Yardımcı fonksiyonlar
function getUserIdAndEmailByName($pdo_stmt, $name) {
    $name = trim($name);
    if (empty($name)) return null;
    try {
        $pdo_stmt->execute([$name]);
        $result = $pdo_stmt->fetch();
        return $result ? (object)[
            'id' => $result->id,
            'email' => $result->email,
            'ad_soyad' => $name,
            'aktif' => $result->aktif
        ] : null;
    } catch (PDOException $e) {
        error_log("getUserIdAndEmailByName hatası: " . $e->getMessage());
        return null;
    }
}

function getIdAndCreateIfNeeded($pdo, &$map, $name, $tableName) {
    $name = trim($name);
    if (empty($name)) return null;
    if (isset($map[$name])) return $map[$name];
    try {
        $stmt = $pdo->prepare("INSERT INTO {$tableName} (ad) VALUES (?)");
        $stmt->execute([$name]);
        $newId = $pdo->lastInsertId();
        $map[$name] = $newId;
        return $newId;
    } catch (PDOException $e) {
        try {
            $stmt = $pdo->prepare("SELECT id FROM {$tableName} WHERE ad = ?");
            $stmt->execute([$name]);
            $existingId = $stmt->fetchColumn();
            if ($existingId) {
                $map[$name] = $existingId;
                return $existingId;
            }
            return null;
        } catch (PDOException $e2) {
            error_log("getIdAndCreateIfNeeded hatası: " . $e2->getMessage());
            return null;
        }
    }
}

function validatePersonel($user_info, $pozisyon) {
    if (!$user_info) {
        return ['durum' => 'yok', 'mesaj' => "{$pozisyon} bulunamadı"];
    }
    if ($user_info->aktif == 0) {
        return ['durum' => 'pasif', 'mesaj' => "{$pozisyon} hesabı pasif"];
    }
    return ['durum' => 'aktif', 'mesaj' => ''];
}

/**
 * Görevlilere yeni atama bildirimi gönderir.
 * musabaka_yonetimi.php dosyasından entegre edilmiştir.
 * @param PDO $pdo PDO veritabanı bağlantısı
 * @param array $gorevli_bilgileri Görevli objelerinden oluşan dizi
 * @param int $musabaka_id Atanan müsabakanın ID'si
 */
function sendDbNotification($pdo, $gorevli_bilgileri, $musabaka_id) {
    // Null olmayan ve id'si olan görevlileri filtrele
    $gorevliler = array_filter($gorevli_bilgileri, function($g) {
        return !empty($g) && is_object($g) && !empty($g->id);
    });
    
    if (empty($gorevliler)) return;

    try {
        $musabaka_stmt = $pdo->prepare("SELECT t1.ad as ev_sahibi, t2.ad as misafir FROM musabakalar m JOIN takimlar t1 ON m.ev_sahibi_id=t1.id JOIN takimlar t2 ON m.misafir_id=t2.id WHERE m.id = ?");
        $musabaka_stmt->execute([$musabaka_id]);
        $musabaka = $musabaka_stmt->fetch();
        if (!$musabaka) return;

        $bildirim_mesaji = "Yeni görev atandı: {$musabaka->ev_sahibi} - {$musabaka->misafir}";
        $bildirim_linki = "/musabaka_detay.php?id={$musabaka_id}";
        $bildirim_stmt = $pdo->prepare("INSERT INTO bildirimler (user_id, mesaj, link) VALUES (?, ?, ?)");

        foreach ($gorevliler as $gorevli) {
            $bildirim_stmt->execute([$gorevli->id, $bildirim_mesaji, $bildirim_linki]);
        }
    } catch (PDOException $e) {
        error_log("sendDbNotification hatası: " . $e->getMessage());
    }
}

// Excel yükleme işlemi
if (isset($_POST['toplu_musabaka_yukle_excel'])) {
    $mesaj_icerik = 'Dosya yüklenirken bir hata oluştu.';
    $mesaj_tip = 'error';
    if (class_exists('SimpleXLSX') && isset($_FILES['xlsx_dosyasi']) && $_FILES['xlsx_dosyasi']['error'] == 0) {
        set_time_limit(0);
        try {
            $pdo->beginTransaction();
            // Ön yükleme tablosunu temizle
            $pdo->prepare("DELETE FROM musabaka_on_yukleme")->execute();
            $xlsx = SimpleXLSX::parse($_FILES['xlsx_dosyasi']['tmp_name']);
            if ($xlsx) {
                $ligler_map = $pdo->query("SELECT ad, id FROM ligler")->fetchAll(PDO::FETCH_KEY_PAIR);
                $stadyumlar_map = $pdo->query("SELECT ad, id FROM stadyumlar")->fetchAll(PDO::FETCH_KEY_PAIR);
                $takimlar_map = $pdo->query("SELECT ad, id FROM takimlar")->fetchAll(PDO::FETCH_KEY_PAIR);
                $user_stmt = $pdo->prepare("SELECT id, email, aktif FROM users WHERE CONCAT(ad, ' ', soyad) = ? LIMIT 1");

                $eklenen_sayisi = 0;
                $hata_sayisi = 0;
                $satir_no = 0;

                $stmt = $pdo->prepare("INSERT INTO musabaka_on_yukleme (mac_no, hafta_no, tarih, saat, lig_id, stadyum_id, ev_sahibi_id, misafir_id, hakem_id, yardimci_1_id, yardimci_2_id, dorduncu_hakem_id, gozlemci_id, durum, hata_mesaji, olusturulma_tarihi) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())");
                foreach ($xlsx->rows() as $i => $row) {
                    $satir_no++;
                    if ($i === 0) continue; // Başlık satırını atla

                    try {
                        if (count($row) < 8) {
                            error_log("Excel Satır {$satir_no}: Eksik sütun");
                            $hata_sayisi++;
                            continue;
                        }

                        $hata_mesajlari = [];
                        // Tarih kontrolü
                        $tarih_excel = trim($row[2]);
                        $tarih_obj = DateTime::createFromFormat('d.m.Y', $tarih_excel) ?: DateTime::createFromFormat('Y-m-d', $tarih_excel);
                        $formatted_tarih = $tarih_obj ? $tarih_obj->format('Y-m-d') : null;
                        if ($formatted_tarih === null) {
                            $hata_mesajlari[] = "Geçersiz tarih formatı: {$tarih_excel}";
                        }

                        // Temel veriler
                        $lig_id = getIdAndCreateIfNeeded($pdo, $ligler_map, trim($row[4]), 'ligler');
                        $stadyum_id = getIdAndCreateIfNeeded($pdo, $stadyumlar_map, trim($row[5]), 'stadyumlar');
                        $ev_sahibi_id = getIdAndCreateIfNeeded($pdo, $takimlar_map, trim($row[6]), 'takimlar');
                        $misafir_id = getIdAndCreateIfNeeded($pdo, $takimlar_map, trim($row[7]), 'takimlar');
                        if (!$lig_id || !$stadyum_id || !$ev_sahibi_id || !$misafir_id) {
                            $hata_mesajlari[] = "Gerekli ID'ler oluşturulamadı";
                        }

                        if ($ev_sahibi_id == $misafir_id) {
                            $hata_mesajlari[] = "Ev sahibi ve misafir takım aynı olamaz";
                        }

                        // Personel kontrolü
                        $hakem_info = isset($row[8]) ? getUserIdAndEmailByName($user_stmt, trim($row[8])) : null;
                        $yardimci_1_info = isset($row[9]) ? getUserIdAndEmailByName($user_stmt, trim($row[9])) : null;
                        $yardimci_2_info = isset($row[10]) ? getUserIdAndEmailByName($user_stmt, trim($row[10])) : null;
                        $dorduncu_hakem_info = isset($row[11]) ? getUserIdAndEmailByName($user_stmt, trim($row[11])) : null;
                        $gozlemci_info = isset($row[12]) ? getUserIdAndEmailByName($user_stmt, trim($row[12])) : null;
                        // Personel validasyon
                        if (!empty(trim($row[8]))) {
                            $val = validatePersonel($hakem_info, 'Hakem');
                            if ($val['durum'] != 'aktif') $hata_mesajlari[] = $val['mesaj'];
                        }
                        if (!empty(trim($row[9]))) {
                            $val = validatePersonel($yardimci_1_info, '1. Yardımcı');
                            if ($val['durum'] != 'aktif') $hata_mesajlari[] = $val['mesaj'];
                        }
                        if (!empty(trim($row[10]))) {
                            $val = validatePersonel($yardimci_2_info, '2. Yardımcı');
                            if ($val['durum'] != 'aktif') $hata_mesajlari[] = $val['mesaj'];
                        }
                        if (!empty(trim($row[11]))) {
                            $val = validatePersonel($dorduncu_hakem_info, '4. Hakem');
                            if ($val['durum'] != 'aktif') $hata_mesajlari[] = $val['mesaj'];
                        }
                        if (!empty(trim($row[12]))) {
                            $val = validatePersonel($gozlemci_info, 'Gözlemci');
                            if ($val['durum'] != 'aktif') $hata_mesajlari[] = $val['mesaj'];
                        }

                        $durum = empty($hata_mesajlari) ? 'Onay Bekliyor' : 'Hata';
                        $hata_mesaji_str = implode(', ', $hata_mesajlari);

                        if ($stmt->execute([
                            trim($row[0]),
                            (int)trim($row[1]),
                            $formatted_tarih,
                            trim($row[3]),
                            $lig_id,
                            $stadyum_id,
                            $ev_sahibi_id,
                            $misafir_id,
                            $hakem_info ? $hakem_info->id : null,
                            $yardimci_1_info ? $yardimci_1_info->id : null,
                            $yardimci_2_info ? $yardimci_2_info->id : null,
                            $dorduncu_hakem_info ? $dorduncu_hakem_info->id : null,
                            $gozlemci_info ? $gozlemci_info->id : null,
                            $durum,
                            $hata_mesaji_str
                        ])) {
                            $eklenen_sayisi++;
                        } else {
                            $hata_sayisi++;
                        }
                    } catch (Exception $e) {
                        $hata_sayisi++;
                        error_log("Excel Satır {$satir_no} işlenirken hata: " . $e->getMessage());
                    }
                }

                $pdo->commit();
                if ($eklenen_sayisi > 0) {
                    $mesaj_icerik = "{$eklenen_sayisi} adet müsabaka ön yükleme listesine eklendi.";
                    $mesaj_tip = 'success';
                } else {
                    $mesaj_icerik = "Hiçbir müsabaka eklenemedi.";
                    $mesaj_tip = 'error';
                }

            } else {
                $mesaj_icerik = 'Excel dosyası okunamadı. Hata: ' . SimpleXLSX::parseError();
                $mesaj_tip = 'error';
            }
        } catch(PDOException $e) {
            $pdo->rollBack();
            error_log("Excel yükleme genel hatası: " . $e->getMessage());
            $mesaj_icerik = 'Excel yükleme sırasında veritabanı hatası oluştu: ' . $e->getMessage();
            $mesaj_tip = 'error';
        }
    }
    $_SESSION['mesaj'] = ['tip' => $mesaj_tip, 'icerik' => $mesaj_icerik];
    header("Location: musabaka_on_yukleme.php");
    exit();
}

// Manuel müsabaka ekleme
if (isset($_POST['manuel_musabaka_ekle'])) {
    $mac_no = trim($_POST['mac_no']);
    $hafta_no = (int)$_POST['hafta_no'];
    $tarih = $_POST['tarih'];
    $saat = $_POST['saat'];
    $lig_id = (int)$_POST['lig_id'];
    $stadyum_id = (int)$_POST['stadyum_id'];
    $ev_sahibi_id = (int)$_POST['ev_sahibi_id'];
    $misafir_id = (int)$_POST['misafir_id'];
    $hakem_id = !empty($_POST['hakem_id']) ? (int)$_POST['hakem_id'] : null;
    $yardimci_1_id = !empty($_POST['yardimci_1_id']) ? (int)$_POST['yardimci_1_id'] : null;
    $yardimci_2_id = !empty($_POST['yardimci_2_id']) ? (int)$_POST['yardimci_2_id'] : null;
    $dorduncu_hakem_id = !empty($_POST['dorduncu_hakem_id']) ? (int)$_POST['dorduncu_hakem_id'] : null;
    $gozlemci_id = !empty($_POST['gozlemci_id']) ? (int)$_POST['gozlemci_id'] : null;

    $hata_mesajlari = [];
    if ($ev_sahibi_id == $misafir_id) {
        $hata_mesajlari[] = "Ev sahibi ve misafir takım aynı olamaz";
    }

    $durum = empty($hata_mesajlari) ? 'Onay Bekliyor' : 'Hata';
    $hata_mesaji_str = implode(', ', $hata_mesajlari);
    try {
        $stmt = $pdo->prepare("INSERT INTO musabaka_on_yukleme (mac_no, hafta_no, tarih, saat, lig_id, stadyum_id, ev_sahibi_id, misafir_id, hakem_id, yardimci_1_id, yardimci_2_id, dorduncu_hakem_id, gozlemci_id, durum, hata_mesaji, olusturulma_tarihi) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())");
        if ($stmt->execute([$mac_no, $hafta_no, $tarih, $saat, $lig_id, $stadyum_id, $ev_sahibi_id, $misafir_id, $hakem_id, $yardimci_1_id, $yardimci_2_id, $dorduncu_hakem_id, $gozlemci_id, $durum, $hata_mesaji_str])) {
            $_SESSION['mesaj'] = ['tip' => 'success', 'icerik' => 'Müsabaka ön yükleme listesine başarıyla eklendi.'];
        } else {
            $_SESSION['mesaj'] = ['tip' => 'error', 'icerik' => 'Müsabaka eklenirken bir hata oluştu.'];
        }
    } catch (PDOException $e) {
        error_log("Manuel müsabaka ekleme hatası: " . $e->getMessage());
        $_SESSION['mesaj'] = ['tip' => 'error', 'icerik' => 'Müsabaka eklenirken bir veritabanı hatası oluştu: ' . $e->getMessage()];
    }
    header("Location: musabaka_on_yukleme.php");
    exit();
}

// Hakem güncelleme
if (isset($_POST['hakem_guncelle'])) {
    $id = (int)$_POST['id'];
    $hakem_id = !empty($_POST['hakem_id']) ? (int)$_POST['hakem_id'] : null;
    $yardimci_1_id = !empty($_POST['yardimci_1_id']) ? (int)$_POST['yardimci_1_id'] : null;
    $yardimci_2_id = !empty($_POST['yardimci_2_id']) ? (int)$_POST['yardimci_2_id'] : null;
    $dorduncu_hakem_id = !empty($_POST['dorduncu_hakem_id']) ? (int)$_POST['dorduncu_hakem_id'] : null;
    $gozlemci_id = !empty($_POST['gozlemci_id']) ? (int)$_POST['gozlemci_id'] : null;
    
    try {
        $hata_mesajlari = [];
        
        // Mevcut verileri çek
        $current_stmt = $pdo->prepare("SELECT ev_sahibi_id, misafir_id FROM musabaka_on_yukleme WHERE id = ?");
        $current_stmt->execute([$id]);
        $current = $current_stmt->fetch();

        if ($current && $current->ev_sahibi_id == $current->misafir_id) {
            $hata_mesajlari[] = "Ev sahibi ve misafir takım aynı olamaz";
        }
        
        // Atanan kişilerin aktiflik kontrolü
        $user_ids = [];
        $user_id_map = [];

        if ($hakem_id) {
            $user_ids[] = $hakem_id;
            $user_id_map[$hakem_id] = 'Hakem';
        }
        if ($yardimci_1_id) {
            $user_ids[] = $yardimci_1_id;
            $user_id_map[$yardimci_1_id] = '1. Yardımcı';
        }
        if ($yardimci_2_id) {
            $user_ids[] = $yardimci_2_id;
            $user_id_map[$yardimci_2_id] = '2. Yardımcı';
        }
        if ($dorduncu_hakem_id) {
            $user_ids[] = $dorduncu_hakem_id;
            $user_id_map[$dorduncu_hakem_id] = '4. Hakem';
        }
        if ($gozlemci_id) {
            $user_ids[] = $gozlemci_id;
            $user_id_map[$gozlemci_id] = 'Gözlemci';
        }

        if (!empty($user_ids)) {
            $placeholders = implode(',', array_fill(0, count($user_ids), '?'));
            $users_stmt = $pdo->prepare("SELECT id, aktif FROM users WHERE id IN ($placeholders) AND aktif = 0");
            $users_stmt->execute($user_ids);
            $pasif_kullanicilar = $users_stmt->fetchAll(PDO::FETCH_ASSOC);

            foreach ($pasif_kullanicilar as $user) {
                if (isset($user_id_map[$user['id']])) {
                    $hata_mesajlari[] = "{$user_id_map[$user['id']]} hesabı pasif";
                }
            }
        }
        
        $durum = empty($hata_mesajlari) ? 'Onay Bekliyor' : 'Hata';
        $hata_mesaji_str = implode(', ', $hata_mesajlari);

        $stmt = $pdo->prepare("UPDATE musabaka_on_yukleme SET hakem_id = ?, yardimci_1_id = ?, yardimci_2_id = ?, dorduncu_hakem_id = ?, gozlemci_id = ?, durum = ?, hata_mesaji = ? WHERE id = ?");
        if ($stmt->execute([$hakem_id, $yardimci_1_id, $yardimci_2_id, $dorduncu_hakem_id, $gozlemci_id, $durum, $hata_mesaji_str, $id])) {
            $_SESSION['mesaj'] = ['tip' => 'success', 'icerik' => 'Hakem atamaları başarıyla güncellendi.'];
        } else {
            $_SESSION['mesaj'] = ['tip' => 'error', 'icerik' => 'Güncelleme sırasında bir hata oluştu.'];
        }
    } catch (PDOException $e) {
        error_log("Hakem güncelleme hatası: " . $e->getMessage());
        $_SESSION['mesaj'] = ['tip' => 'error', 'icerik' => 'Güncelleme sırasında bir veritabanı hatası oluştu: ' . $e->getMessage()];
    }
    header("Location: musabaka_on_yukleme.php");
    exit();
}

// Seçili müsabakaları yayınla
if (isset($_POST['musabakalari_yayinla'])) {
    if (!empty($_POST['secili_musabakalar'])) {
        $ids = array_map('intval', $_POST['secili_musabakalar']);
        $ids = array_filter($ids, function($id) { return $id > 0; });
        if (!empty($ids)) {
            try {
                $pdo->beginTransaction();
                $placeholders = implode(',', array_fill(0, count($ids), '?'));
                $stmt = $pdo->prepare("SELECT * FROM musabaka_on_yukleme WHERE id IN ($placeholders) AND durum = 'Onay Bekliyor'");
                $stmt->execute($ids);
                $yayinlanacak_musabakalar = $stmt->fetchAll();

                $basarili = 0;
                $hatali = 0;
                foreach ($yayinlanacak_musabakalar as $musabaka) {
                    try {
                        $insert_stmt = $pdo->prepare("INSERT INTO musabakalar (mac_no, hafta_no, tarih, saat, lig_id, stadyum_id, ev_sahibi_id, misafir_id, hakem_id, yardimci_1_id, yardimci_2_id, dorduncu_hakem_id, gozlemci_id, bildirim_gonderildi, arsiv, durum, yayin) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 0, 0, 'Atandı', 1)");
                        if ($insert_stmt->execute([
                            $musabaka->mac_no,
                            $musabaka->hafta_no,
                            $musabaka->tarih,
                            $musabaka->saat,
                            $musabaka->lig_id,
                            $musabaka->stadyum_id,
                            $musabaka->ev_sahibi_id,
                            $musabaka->misafir_id,
                            $musabaka->hakem_id,
                            $musabaka->yardimci_1_id,
                            $musabaka->yardimci_2_id,
                            $musabaka->dorduncu_hakem_id,
                            $musabaka->gozlemci_id
                        ])) {
                            $yeni_musabaka_id = $pdo->lastInsertId();
                            
                            // Bildirim gönder
                            $gorevli_bilgileri = array_filter([
                                $musabaka->hakem_id ? (object)['id' => $musabaka->hakem_id] : null,
                                $musabaka->yardimci_1_id ? (object)['id' => $musabaka->yardimci_1_id] : null,
                                $musabaka->yardimci_2_id ? (object)['id' => $musabaka->yardimci_2_id] : null,
                                $musabaka->dorduncu_hakem_id ? (object)['id' => $musabaka->dorduncu_hakem_id] : null,
                                $musabaka->gozlemci_id ? (object)['id' => $musabaka->gozlemci_id] : null
                            ]);
                            sendDbNotification($pdo, $gorevli_bilgileri, $yeni_musabaka_id);

                            $pdo->prepare("DELETE FROM musabaka_on_yukleme WHERE id = ?")->execute([$musabaka->id]);
                            $basarili++;
                        } else {
                            $hatali++;
                        }
                    } catch (Exception $e) {
                        error_log("Müsabaka yayınlama hatası: " . $e->getMessage());
                        $hatali++;
                    }
                }

                $pdo->commit();
                $_SESSION['mesaj'] = ['tip' => 'success', 'icerik' => "{$basarili} adet müsabaka başarıyla yayınlandı." . ($hatali > 0 ? " {$hatali} müsabakada hata oluştu." : "")];
            } catch (Exception $e) {
                $pdo->rollBack();
                error_log("Toplu yayınlama hatası: " . $e->getMessage());
                $_SESSION['mesaj'] = ['tip' => 'error', 'icerik' => 'Yayınlama sırasında bir hata oluştu.'];
            }
        }
    }
    header("Location: musabaka_on_yukleme.php");
    exit();
}

// Seçili müsabakaları silme işlemi
if (isset($_POST['secilenleri_sil'])) {
    if (!empty($_POST['secili_musabakalar'])) {
        $ids = array_map('intval', $_POST['secili_musabakalar']);
        $ids = array_filter($ids, function($id) { return $id > 0; });
        if (!empty($ids)) {
            try {
                $placeholders = implode(',', array_fill(0, count($ids), '?'));
                $stmt = $pdo->prepare("DELETE FROM musabaka_on_yukleme WHERE id IN ($placeholders)");
                if ($stmt->execute($ids)) {
                    $_SESSION['mesaj'] = ['tip' => 'success', 'icerik' => count($ids) . ' adet müsabaka başarıyla silindi.'];
                } else {
                    $_SESSION['mesaj'] = ['tip' => 'error', 'icerik' => 'Seçili müsabakaları silerken bir hata oluştu.'];
                }
            } catch (PDOException $e) {
                error_log("Toplu silme hatası: " . $e->getMessage());
                $_SESSION['mesaj'] = ['tip' => 'error', 'icerik' => 'Silme sırasında bir veritabanı hatası oluştu.'];
            }
        }
    }
    header("Location: musabaka_on_yukleme.php");
    exit();
}

// Silme işlemi (tekil)
if (isset($_GET['action']) && $_GET['action'] == 'sil' && isset($_GET['id'])) {
    $id = (int)$_GET['id'];
    try {
        $stmt = $pdo->prepare("DELETE FROM musabaka_on_yukleme WHERE id = ?");
        if ($stmt->execute([$id])) {
            $_SESSION['mesaj'] = ['tip' => 'success', 'icerik' => 'Müsabaka ön yükleme listesinden silindi.'];
        } else {
            $_SESSION['mesaj'] = ['tip' => 'error', 'icerik' => 'Silme işlemi başarısız.'];
        }
    } catch (PDOException $e) {
        $_SESSION['mesaj'] = ['tip' => 'error', 'icerik' => 'Silme sırasında bir hata oluştu.'];
    }
    header("Location: musabaka_on_yukleme.php");
    exit();
}

// Verileri çekme işlemi ve yeni filtreleme özelliği
try {
    $ligler = $pdo->query("SELECT * FROM ligler ORDER BY ad")->fetchAll();
    $stadyumlar = $pdo->query("SELECT * FROM stadyumlar ORDER BY ad")->fetchAll();
    $takimlar = $pdo->query("SELECT * FROM takimlar ORDER BY ad")->fetchAll();
    $hakemler = $pdo->query("SELECT id, ad, soyad, aktif FROM users WHERE rol = 2 ORDER BY ad, soyad")->fetchAll();
    $gozlemciler = $pdo->query("SELECT id, ad, soyad, aktif FROM users WHERE rol = 3 ORDER BY ad, soyad")->fetchAll();
    
    $where_clause = "";
    if (isset($_GET['filtre']) && $_GET['filtre'] === 'hata') {
        $where_clause = " WHERE m.durum = 'Hata'";
    }

    $sql = "SELECT m.*, l.ad as lig_adi, s.ad as stadyum_adi, t1.ad as ev_sahibi, t2.ad as misafir,
                   h.ad as hakem_ad, h.soyad as hakem_soyad, h.aktif as hakem_aktif,
                   y1.ad as yrd1_ad, y1.soyad as yrd1_soyad, y1.aktif as yrd1_aktif,
                   y2.ad as yrd2_ad, y2.soyad as yrd2_soyad, y2.aktif as yrd2_aktif,
                   d.ad as dorduncu_ad, d.soyad as dorduncu_soyad, d.aktif as dorduncu_aktif,
                   g.ad as gozlemci_ad, g.soyad as gozlemci_soyad, g.aktif as gozlemci_aktif
            FROM musabaka_on_yukleme m
            LEFT JOIN ligler l ON m.lig_id = l.id
            LEFT JOIN stadyumlar s ON m.stadyum_id = s.id
            LEFT JOIN takimlar t1 ON m.ev_sahibi_id = t1.id
            LEFT JOIN takimlar t2 ON m.misafir_id = t2.id
            LEFT JOIN users h ON m.hakem_id = h.id
            LEFT JOIN users y1 ON m.yardimci_1_id = y1.id
            LEFT JOIN users y2 ON m.yardimci_2_id = y2.id
            LEFT JOIN users d ON m.dorduncu_hakem_id = d.id
            LEFT JOIN users g ON m.gozlemci_id = g.id
            {$where_clause}
            ORDER BY CAST(m.mac_no AS UNSIGNED) ASC";
            
    $on_yukleme_musabakalari = $pdo->query($sql)->fetchAll();

} catch (PDOException $e) {
    error_log("Veri çekme hatası: " . $e->getMessage());
    $mesaj = ['tip' => 'error', 'icerik' => 'Sayfa yüklenirken bir hata oluştu.'];
    $ligler = $stadyumlar = $takimlar = $hakemler = $gozlemciler = $on_yukleme_musabakalari = [];
}

include '../templates/header.php';
?>

<div class="container mx-auto px-4 py-6">
    <h1 class="text-2xl font-bold mb-6 text-gray-800"><?php echo $sayfa_baslik; ?></h1>

    <?php if ($mesaj): ?>
        <div class="p-4 mb-6 text-sm rounded-lg <?php echo $mesaj['tip'] === 'success' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'; ?>">
            <?php echo htmlspecialchars($mesaj['icerik']); ?>
        </div>
    <?php endif; ?>

    <div class="bg-white p-6 rounded-lg shadow-md mb-6">
        <h2 class="text-xl font-semibold mb-4 text-gray-800">Toplu Müsabaka Yükle (.xlsx)</h2>
        <p class="text-sm text-gray-600 mb-2">Sütunlar: Maç No, Hafta No, Tarih (GG.AA.YYYY), Saat (SS:DD), Lig Adı, Stadyum Adı, Ev Sahibi, Misafir, Hakem Adı Soyadı, 1. Yrd. Adı Soyadı, 2. Yrd. Adı Soyadı, 4. Hakem Adı Soyadı, Gözlemci Adı Soyadı</p>
        <p class="text-xs text-blue-600 mb-4">Not: Pasif hesaplı hakemler otomatik olarak tespit edilecek ve hata olarak işaretlenecektir.</p>

        <form method="POST" enctype="multipart/form-data">
            <input type="file" name="xlsx_dosyasi" class="block w-full text-sm text-gray-900 border border-gray-300 rounded-lg cursor-pointer bg-gray-50" accept=".xlsx" required>
            <button type="submit" name="toplu_musabaka_yukle_excel" class="mt-4 w-full bg-purple-600 text-white py-2 rounded-md hover:bg-purple-700">Excel ile Yükle</button>
        </form>
    </div>

    <div class="bg-white p-6 rounded-lg shadow-md mb-6">
        <h2 class="text-xl font-semibold mb-4 text-gray-800">Manuel Müsabaka Ekle</h2>
        <form method="POST" class="space-y-4">
            <div class="grid grid-cols-1 md:grid-cols-5 gap-4">
                <input type="text" name="mac_no" placeholder="Maç No" class="border p-2 rounded w-full">
                <input type="number" name="hafta_no" placeholder="Hafta No" class="border p-2 rounded w-full" min="1" required>
                <input type="date" name="tarih" class="border p-2 rounded w-full" required>
                <input type="time" name="saat" class="border p-2 rounded w-full" required>
                <select name="lig_id" class="border p-2 rounded w-full" required>
                    <option value="">Lig Seçin</option>
                    <?php foreach($ligler as $lig): ?>
                        <option value="<?php echo $lig->id; ?>"><?php echo htmlspecialchars($lig->ad); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <select name="stadyum_id" class="border p-2 rounded w-full" required>
                    <option value="">Stadyum Seçin</option>
                    <?php foreach($stadyumlar as $stadyum): ?>
                        <option value="<?php echo $stadyum->id; ?>"><?php echo htmlspecialchars($stadyum->ad); ?></option>
                    <?php endforeach; ?>
                </select>
                <select name="ev_sahibi_id" class="border p-2 rounded w-full" required>
                    <option value="">Ev Sahibi Seçin</option>
                    <?php foreach($takimlar as $takim): ?>
                        <option value="<?php echo $takim->id; ?>"><?php echo htmlspecialchars($takim->ad); ?></option>
                    <?php endforeach; ?>
                </select>
                <select name="misafir_id" class="border p-2 rounded w-full" required>
                    <option value="">Misafir Takım Seçin</option>
                    <?php foreach($takimlar as $takim): ?>
                        <option value="<?php echo $takim->id; ?>"><?php echo htmlspecialchars($takim->ad); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-5 gap-4">
                <select name="hakem_id" class="border p-2 rounded w-full">
                    <option value="">Hakem Seçin</option>
                    <?php foreach($hakemler as $hakem): ?>
                        <option value="<?php echo $hakem->id; ?>" <?php echo $hakem->aktif == 0 ? 'class="text-red-500"' : ''; ?>>
                            <?php echo htmlspecialchars($hakem->ad . ' ' . $hakem->soyad); ?>
                            <?php echo $hakem->aktif == 0 ? ' (Pasif)' : ''; ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <select name="yardimci_1_id" class="border p-2 rounded w-full">
                    <option value="">1. Yardımcı Seçin</option>
                    <?php foreach($hakemler as $hakem): ?>
                        <option value="<?php echo $hakem->id; ?>" <?php echo $hakem->aktif == 0 ? 'class="text-red-500"' : ''; ?>>
                            <?php echo htmlspecialchars($hakem->ad . ' ' . $hakem->soyad); ?>
                            <?php echo $hakem->aktif == 0 ? ' (Pasif)' : ''; ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <select name="yardimci_2_id" class="border p-2 rounded w-full">
                    <option value="">2. Yardımcı Seçin</option>
                    <?php foreach($hakemler as $hakem): ?>
                        <option value="<?php echo $hakem->id; ?>" <?php echo $hakem->aktif == 0 ? 'class="text-red-500"' : ''; ?>>
                            <?php echo htmlspecialchars($hakem->ad . ' ' . $hakem->soyad); ?>
                            <?php echo $hakem->aktif == 0 ? ' (Pasif)' : ''; ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <select name="dorduncu_hakem_id" class="border p-2 rounded w-full">
                    <option value="">4. Hakem Seçin</option>
                    <?php foreach($hakemler as $hakem): ?>
                        <option value="<?php echo $hakem->id; ?>" <?php echo $hakem->aktif == 0 ? 'class="text-red-500"' : ''; ?>>
                            <?php echo htmlspecialchars($hakem->ad . ' ' . $hakem->soyad); ?>
                            <?php echo $hakem->aktif == 0 ? ' (Pasif)' : ''; ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <select name="gozlemci_id" class="border p-2 rounded w-full">
                    <option value="">Gözlemci Seçin</option>
                    <?php foreach($gozlemciler as $gozlemci): ?>
                        <option value="<?php echo $gozlemci->id; ?>" <?php echo $gozlemci->aktif == 0 ? 'class="text-red-500"' : ''; ?>>
                            <?php echo htmlspecialchars($gozlemci->ad . ' ' . $gozlemci->soyad); ?>
                            <?php echo $gozlemci->aktif == 0 ? ' (Pasif)' : ''; ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <button type="submit" name="manuel_musabaka_ekle" class="w-full bg-blue-600 text-white py-2 rounded-md hover:bg-blue-700">Manuel Ekle</button>
        </form>
    </div>

    <div class="bg-white p-4 rounded-lg shadow-md">
        <h2 class="text-xl font-semibold mb-4 text-gray-800">Ön Yükleme Listesi</h2>
        <div class="flex justify-end mb-4">
            <a href="musabaka_on_yukleme.php" class="mr-2 px-4 py-2 text-sm font-semibold rounded-md <?php echo (!isset($_GET['filtre']) || $_GET['filtre'] !== 'hata') ? 'bg-blue-500 text-white' : 'bg-gray-200 text-gray-700 hover:bg-gray-300'; ?>">Tümünü Göster</a>
            <a href="?filtre=hata" class="px-4 py-2 text-sm font-semibold rounded-md <?php echo (isset($_GET['filtre']) && $_GET['filtre'] === 'hata') ? 'bg-red-500 text-white' : 'bg-gray-200 text-gray-700 hover:bg-gray-300'; ?>">Hatalı Olanları Göster</a>
        </div>
        <?php if (empty($on_yukleme_musabakalari)): ?>
            <p class="text-gray-600 text-sm">
                <?php echo (isset($_GET['filtre']) && $_GET['filtre'] === 'hata') ? 'Hiçbir hata bulunamadı.' : 'Henüz ön yükleme listesinde müsabaka bulunmuyor.'; ?>
            </p>
        <?php else: ?>
            <form method="POST" id="musabakaForm">
                <div class="overflow-x-auto">
                    <table class="min-w-full bg-white border border-gray-200 text-sm">
                        <thead>
                            <tr class="bg-gray-100">
                                <th class="py-2 px-3 border-b text-left"><input type="checkbox" id="selectAll"></th>
                                <th class="py-2 px-3 border-b text-left">Maç No</th>
                                <th class="py-2 px-3 border-b text-left">Tarih/Saat</th>
                                <th class="py-2 px-3 border-b text-left">Müsabaka</th>
                                <th class="py-2 px-3 border-b text-left">Hakemler</th>
                                <th class="py-2 px-3 border-b text-left">Durum</th>
                                <th class="py-2 px-3 border-b text-left">İşlemler</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach($on_yukleme_musabakalari as $musabaka): ?>
                                <tr class="<?php echo $musabaka->durum === 'Hata' ? 'bg-red-50' : 'hover:bg-gray-50'; ?>">
                                    <td class="py-2 px-3 border-b">
                                        <input type="checkbox" name="secili_musabakalar[]" value="<?php echo $musabaka->id; ?>" class="musabaka-checkbox">
                                    </td>
                                    <td class="py-2 px-3 border-b"><?php echo htmlspecialchars($musabaka->mac_no); ?></td>
                                    <td class="py-2 px-3 border-b whitespace-nowrap">
                                        <?php echo date('d.m.Y', strtotime($musabaka->tarih)); ?> <br>
                                        <?php echo $musabaka->saat; ?>
                                    </td>
                                    <td class="py-2 px-3 border-b">
                                        <div class="font-semibold"><?php echo htmlspecialchars($musabaka->ev_sahibi); ?> vs. <?php echo htmlspecialchars($musabaka->misafir); ?></div>
                                        <div class="text-xs text-gray-500">Lig: <?php echo htmlspecialchars($musabaka->lig_adi); ?></div>
                                        <div class="text-xs text-gray-500">Stadyum: <?php echo htmlspecialchars($musabaka->stadyum_adi); ?></div>
                                    </td>
                                    <td class="py-2 px-3 border-b">
                                        <ul class="list-disc list-inside">
                                            <li class="text-xs <?php echo $musabaka->hakem_aktif == 0 ? 'text-red-600' : ''; ?>">
                                                <strong>Hk:</strong> <?php echo $musabaka->hakem_ad ? htmlspecialchars($musabaka->hakem_ad . ' ' . $musabaka->hakem_soyad) : '-'; ?>
                                            </li>
                                            <li class="text-xs <?php echo $musabaka->yrd1_aktif == 0 ? 'text-red-600' : ''; ?>">
                                                <strong>Yrd 1:</strong> <?php echo $musabaka->yrd1_ad ? htmlspecialchars($musabaka->yrd1_ad . ' ' . $musabaka->yrd1_soyad) : '-'; ?>
                                            </li>
                                            <li class="text-xs <?php echo $musabaka->yrd2_aktif == 0 ? 'text-red-600' : ''; ?>">
                                                <strong>Yrd 2:</strong> <?php echo $musabaka->yrd2_ad ? htmlspecialchars($musabaka->yrd2_ad . ' ' . $musabaka->yrd2_soyad) : '-'; ?>
                                            </li>
                                            <li class="text-xs <?php echo $musabaka->dorduncu_aktif == 0 ? 'text-red-600' : ''; ?>">
                                                <strong>4. Hk:</strong> <?php echo $musabaka->dorduncu_ad ? htmlspecialchars($musabaka->dorduncu_ad . ' ' . $musabaka->dorduncu_soyad) : '-'; ?>
                                            </li>
                                            <li class="text-xs <?php echo $musabaka->gozlemci_aktif == 0 ? 'text-red-600' : ''; ?>">
                                                <strong>Göz:</strong> <?php echo $musabaka->gozlemci_ad ? htmlspecialchars($musabaka->gozlemci_ad . ' ' . $musabaka->gozlemci_soyad) : '-'; ?>
                                            </li>
                                        </ul>
                                    </td>
                                    <td class="py-2 px-3 border-b">
                                        <span class="px-2 py-1 rounded-full text-xs <?php echo $musabaka->durum === 'Onay Bekliyor' ? 'bg-yellow-100 text-yellow-800' : 'bg-red-100 text-red-800'; ?>">
                                            <?php echo $musabaka->durum; ?>
                                        </span>
                                        <?php if ($musabaka->durum === 'Hata' && !empty($musabaka->hata_mesaji)): ?>
                                            <div class="text-xs text-red-600 mt-1 max-w-xs whitespace-normal"><?php echo htmlspecialchars($musabaka->hata_mesaji); ?></div>
                                        <?php endif; ?>
                                    </td>
                                    <td class="py-2 px-3 border-b whitespace-nowrap">
                                        <div class="flex space-x-2">
                                            <button type="button" onclick="openEditModal(<?php echo $musabaka->id; ?>, <?php echo $musabaka->hakem_id ?: 'null'; ?>, <?php echo $musabaka->yardimci_1_id ?: 'null'; ?>, <?php echo $musabaka->yardimci_2_id ?: 'null'; ?>, <?php echo $musabaka->dorduncu_hakem_id ?: 'null'; ?>, <?php echo $musabaka->gozlemci_id ?: 'null'; ?>)" class="text-blue-600 hover:text-blue-800">Düzenle</button>
                                            <a href="?action=sil&id=<?php echo $musabaka->id; ?>" onclick="return confirm('Bu müsabakayı silmek istediğinize emin misiniz?')" class="text-red-600 hover:text-red-800">Sil</a>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <div class="mt-4 flex space-x-4">
                    <button type="submit" name="musabakalari_yayinla" class="bg-green-600 text-white px-4 py-2 rounded hover:bg-green-700">Seçilen Müsabakaları Yayınla</button>
                    <button type="submit" name="secilenleri_sil" class="bg-red-600 text-white px-4 py-2 rounded hover:bg-red-700">Seçilenleri Sil</button>
                </div>
            </form>
        <?php endif; ?>
    </div>
</div>

<div id="editModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 flex items-center justify-center hidden">
    <div class="bg-white p-6 rounded-lg shadow-lg w-full max-w-4xl">
        <h3 class="text-xl font-semibold mb-4">Hakem Atamalarını Düzenle</h3>
        <form method="POST" id="editForm">
            <input type="hidden" name="id" id="edit_id">
            <div class="grid grid-cols-1 md:grid-cols-5 gap-4 mb-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Hakem</label>
                    <select name="hakem_id" id="edit_hakem_id" class="border p-2 rounded w-full">
                        <option value="">Seçiniz</option>
                        <?php foreach($hakemler as $hakem): ?>
                            <option value="<?php echo $hakem->id; ?>" <?php echo $hakem->aktif == 0 ? 'class="text-red-500"' : ''; ?>>
                                <?php echo htmlspecialchars($hakem->ad . ' ' . $hakem->soyad); ?>
                                <?php echo $hakem->aktif == 0 ? ' (Pasif)' : ''; ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">1. Yardımcı</label>
                    <select name="yardimci_1_id" id="edit_yardimci_1_id" class="border p-2 rounded w-full">
                        <option value="">Seçiniz</option>
                        <?php foreach($hakemler as $hakem): ?>
                            <option value="<?php echo $hakem->id; ?>" <?php echo $hakem->aktif == 0 ? 'class="text-red-500"' : ''; ?>>
                                <?php echo htmlspecialchars($hakem->ad . ' ' . $hakem->soyad); ?>
                                <?php echo $hakem->aktif == 0 ? ' (Pasif)' : ''; ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">2. Yardımcı</label>
                    <select name="yardimci_2_id" id="edit_yardimci_2_id" class="border p-2 rounded w-full">
                        <option value="">Seçiniz</option>
                        <?php foreach($hakemler as $hakem): ?>
                            <option value="<?php echo $hakem->id; ?>" <?php echo $hakem->aktif == 0 ? 'class="text-red-500"' : ''; ?>>
                                <?php echo htmlspecialchars($hakem->ad . ' ' . $hakem->soyad); ?>
                                <?php echo $hakem->aktif == 0 ? ' (Pasif)' : ''; ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">4. Hakem</label>
                    <select name="dorduncu_hakem_id" id="edit_dorduncu_hakem_id" class="border p-2 rounded w-full">
                        <option value="">Seçiniz</option>
                        <?php foreach($hakemler as $hakem): ?>
                            <option value="<?php echo $hakem->id; ?>" <?php echo $hakem->aktif == 0 ? 'class="text-red-500"' : ''; ?>>
                                <?php echo htmlspecialchars($hakem->ad . ' ' . $hakem->soyad); ?>
                                <?php echo $hakem->aktif == 0 ? ' (Pasif)' : ''; ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Gözlemci</label>
                    <select name="gozlemci_id" id="edit_gozlemci_id" class="border p-2 rounded w-full">
                        <option value="">Seçiniz</option>
                        <?php foreach($gozlemciler as $gozlemci): ?>
                            <option value="<?php echo $gozlemci->id; ?>" <?php echo $gozlemci->aktif == 0 ? 'class="text-red-500"' : ''; ?>>
                                <?php echo htmlspecialchars($gozlemci->ad . ' ' . $gozlemci->soyad); ?>
                                <?php echo $gozlemci->aktif == 0 ? ' (Pasif)' : ''; ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
            <div class="flex justify-end space-x-4">
                <button type="button" onclick="closeEditModal()" class="bg-gray-300 text-gray-700 px-4 py-2 rounded hover:bg-gray-400">İptal</button>
                <button type="submit" name="hakem_guncelle" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">Kaydet</button>
            </div>
        </form>
    </div>
</div>

<script>
function openEditModal(id, hakem_id, yrd1_id, yrd2_id, dorduncu_id, gozlemci_id) {
    document.getElementById('edit_id').value = id;
    
    // Select elementlerini sıfırla
    document.getElementById('edit_hakem_id').value = '';
    document.getElementById('edit_yardimci_1_id').value = '';
    document.getElementById('edit_yardimci_2_id').value = '';
    document.getElementById('edit_dorduncu_hakem_id').value = '';
    document.getElementById('edit_gozlemci_id').value = '';
    
    // Mevcut değerleri ata
    if (hakem_id) document.getElementById('edit_hakem_id').value = hakem_id;
    if (yrd1_id) document.getElementById('edit_yardimci_1_id').value = yrd1_id;
    if (yrd2_id) document.getElementById('edit_yardimci_2_id').value = yrd2_id;
    if (dorduncu_id) document.getElementById('edit_dorduncu_hakem_id').value = dorduncu_id;
    if (gozlemci_id) document.getElementById('edit_gozlemci_id').value = gozlemci_id;
    
    document.getElementById('editModal').classList.remove('hidden');
}

function closeEditModal() {
    document.getElementById('editModal').classList.add('hidden');
}

// Tümünü seç
document.getElementById('selectAll').addEventListener('change', function() {
    const checkboxes = document.querySelectorAll('.musabaka-checkbox');
    checkboxes.forEach(checkbox => {
        checkbox.checked = this.checked;
    });
});

// Modal dışına tıklandığında kapat
document.getElementById('editModal').addEventListener('click', function(e) {
    if (e.target === this) {
        closeEditModal();
    }
});
</script>

<?php include '../templates/footer.php'; ?>