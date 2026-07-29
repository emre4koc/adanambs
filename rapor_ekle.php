<?php
ob_start();
require_once 'config/session_check.php';
require_once 'config/db.php';

// Güvenlik: Sadece Gözlemci (Rol 3) erişebilir
if ($_SESSION['user_rol'] != 3) { header("Location: anasayfa.php"); exit(); }

// Saat dilimi ayarı eklendi
date_default_timezone_set('Europe/Istanbul'); 

/**
 * Resmi yeniden boyutlandırır ve yeni bir JPG olarak kaydeder.
 * GD kütüphanesinin PHP'de etkin olması gerekir.
 * @param string $kaynak_yolu Orijinal dosyanın yolu (tmp_name).
 * @param string $hedef_yolu Yeniden boyutlandırılmış dosyanın kaydedileceği yol.
 * @param int $max_boyut Yeni görüntünün maksimum genişlik veya yüksekliği (piksel).
 * @param int $kalite JPG kalite ayarı (0-100).
 * @return bool Başarı durumunu döndürür.
 */
function yeniden_boyutlandir_ve_kaydet($kaynak_yolu, $hedef_yolu, $max_boyut = 1200, $kalite = 80) {
    if (!extension_loaded('gd')) {
        // GD kütüphanesi yoksa, işlemi atla ve sadece kopyala
        return copy($kaynak_yolu, $hedef_yolu);
    }

    list($orijinal_genislik, $orijinal_yukseklik, $tip) = getimagesize($kaynak_yolu);
    
    // Sadece JPG/JPEG dosyalarını destekler
    if ($tip != IMAGETYPE_JPEG) {
        // JPEG değilse ve copy başarısız olursa
        return false;
    }

    // Yeni boyutları hesapla
    $oran = $orijinal_genislik / $orijinal_yukseklik;
    if ($orijinal_genislik > $orijinal_yukseklik) {
        $yeni_genislik = min($orijinal_genislik, $max_boyut);
        $yeni_yukseklik = round($yeni_genislik / $oran);
    } else {
        $yeni_yukseklik = min($orijinal_yukseklik, $max_boyut);
        $yeni_genislik = round($yeni_yukseklik * $oran);
    }
    
    // Eğer orijinal boyut zaten küçüktürse yeniden boyutlandırma yapma, sadece kopyala
    if ($orijinal_genislik <= $max_boyut && $orijinal_yukseklik <= $max_boyut) {
        return copy($kaynak_yolu, $hedef_yolu);
    }

    $orijinal_resim = imagecreatefromjpeg($kaynak_yolu);
    // imagecreatetruecolor yerine imagecreatefromjpeg kullanıldığı için, resmin kalitesini korumak için gerekli
    $yeni_resim = imagecreatetruecolor($yeni_genislik, $yeni_yukseklik);

    imagecopyresampled($yeni_resim, $orijinal_resim, 0, 0, 0, 0, $yeni_genislik, $yeni_yukseklik, $orijinal_genislik, $orijinal_yukseklik);
    
    // Görüntüyü belirtilen kalite ile kaydet
    $sonuc = imagejpeg($yeni_resim, $hedef_yolu, $kalite);

    imagedestroy($orijinal_resim);
    imagedestroy($yeni_resim);
    
    return $sonuc;
}

$sayfa_baslik = "Gözlemci Raporu Ekle";
$musabaka_id = isset($_GET['musabaka_id']) ? (int)$_GET['musabaka_id'] : 0;
$gozlemci_id = $_SESSION['user_id'];
$mesaj = '';

// Yönlendirme sonrası mesajı al
if (isset($_SESSION['mesaj'])) {
    $mesaj = $_SESSION['mesaj'];
    unset($_SESSION['mesaj']);
}

// Müsabaka bilgilerini çek
$stmt = $pdo->prepare("
    SELECT m.*, 
           t1.ad AS ev_sahibi, t2.ad AS misafir,
           CONCAT(h.ad, ' ', h.soyad) as hakem, h.id as hakem_id,
           CONCAT(y1.ad, ' ', y1.soyad) as yardimci_1, y1.id as yardimci_1_id,
           CONCAT(y2.ad, ' ', y2.soyad) as yardimci_2, y2.id as yardimci_2_id,
           CONCAT(d4.ad, ' ', d4.soyad) as dorduncu_hakem, d4.id as dorduncu_hakem_id,
           r.rapor_dosya_yolu,
           r.id as rapor_id,
           r.iade_edildi
    FROM musabakalar m
    LEFT JOIN takimlar t1 ON m.ev_sahibi_id = t1.id
    LEFT JOIN takimlar t2 ON m.misafir_id = t2.id
    LEFT JOIN users h ON m.hakem_id = h.id
    LEFT JOIN users y1 ON m.yardimci_1_id = y1.id
    LEFT JOIN users y2 ON m.yardimci_2_id = y2.id
    LEFT JOIN users d4 ON m.dorduncu_hakem_id = d4.id
    LEFT JOIN raporlar r ON m.id = r.musabaka_id
    WHERE m.id = ? AND m.gozlemci_id = ?
");
$stmt->execute([$musabaka_id, $gozlemci_id]);
$musabaka = $stmt->fetch(PDO::FETCH_OBJ);

if (!$musabaka) {
    die("Bu müsabaka için yetkiniz yok veya müsabaka bulunamadı.");
}

// Disiplin raporlarını çek
$disiplin_raporlari = [];
if (!empty($musabaka->rapor_id)) {
    $stmt = $pdo->prepare("SELECT * FROM disiplin_raporlari WHERE rapor_id = ? AND rapor_tipi = 'gozlemci' ORDER BY rapor_no");
    $stmt->execute([$musabaka->rapor_id]);
    $disiplin_raporlari = $stmt->fetchAll();
}

// Kontrol: Rapor daha önce yüklenmiş mi ve iade edilmiş mi?
$rapor_mevcut = !empty($musabaka->rapor_dosya_yolu);
$iade_edildi = isset($musabaka->iade_edildi) && $musabaka->iade_edildi == 1;
$duzenleme_yapilabilir = !$rapor_mevcut || $iade_edildi; 

// ARŞİV KONTROLÜ
if ($musabaka->arsiv == 1) {
    include 'templates/header.php';
    echo '<div class="container mx-auto"><div class="bg-yellow-100 border-l-4 border-yellow-500 text-yellow-700 p-4 rounded-md" role="alert"><p class="font-bold">Uyarı</p><p>Bu müsabaka arşivlendiği için rapor üzerinde değişiklik yapamazsınız.</p><a href="/mbs/gorevlerim.php" class="mt-2 inline-block text-blue-600 hover:underline">Görevlerime Geri Dön</a></div></div>';
    include 'templates/footer.php';
    exit();
}

// Form gönderildiyse raporu kaydet
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $duzenleme_yapilabilir) {
    
    // HATA KONTROLÜ: Rapor dosyası yüklenmediyse VE mevcut dosya yolu yoksa hata ver
    if (!$rapor_mevcut && (!isset($_FILES['rapor_dosyasi']) || $_FILES['rapor_dosyasi']['error'] != 0)) {
         $_SESSION['mesaj'] = ['tip' => 'error', 'icerik' => 'Lütfen bir rapor dosyası (.xls/.xlsx) seçiniz.'];
         header("Location: rapor_ekle.php?musabaka_id={$musabaka_id}");
         exit();
    }
    
    try {
        $pdo->beginTransaction();
        $rapor_dosya_yolu = $musabaka->rapor_dosya_yolu; 
        
        // 1. Müsabaka Raporu (Excel) Yükleme
        if (isset($_FILES['rapor_dosyasi']) && $_FILES['rapor_dosyasi']['error'] == 0) {
            $hedef_klasor = 'public/uploads/raporlar/';
            if (!is_dir($hedef_klasor)) { mkdir($hedef_klasor, 0777, true); }
            $dosya_adi = time() . '_' . basename($_FILES['rapor_dosyasi']['name']);
            $yeni_rapor_dosya_yolu = $hedef_klasor . $dosya_adi;
            
            if (move_uploaded_file($_FILES['rapor_dosyasi']['tmp_name'], $yeni_rapor_dosya_yolu)) {
                $rapor_dosya_yolu = $yeni_rapor_dosya_yolu;
            }
        }

        // Rapor ID'sini al veya oluştur
        $stmt = $pdo->prepare("SELECT id FROM raporlar WHERE musabaka_id = ?");
        $stmt->execute([$musabaka_id]);
        $rapor = $stmt->fetch();

        if ($rapor) {
            $rapor_id = $rapor->id;
            // Mevcut raporu güncelle
            $stmt = $pdo->prepare("UPDATE raporlar SET rapor_dosya_yolu = ?, iade_edildi = 0, olusturma_tarihi = NOW() WHERE id = ?");
            $stmt->execute([$rapor_dosya_yolu, $rapor_id]);
        } else {
            // Yeni rapor ekleme
            $stmt = $pdo->prepare("INSERT INTO raporlar (musabaka_id, gozlemci_id, rapor_dosya_yolu, iade_edildi) VALUES (?, ?, ?, 0)");
            $stmt->execute([$musabaka_id, $gozlemci_id, $rapor_dosya_yolu]);
            $rapor_id = $pdo->lastInsertId();
        }

        // 2. Disiplin Raporları Yükleme - GÖZLEMCİ (Yeniden Boyutlandırma ve Sıkıştırma Eklendi)
        for ($i = 1; $i <= 5; $i++) {
            if (isset($_FILES["disiplin_raporu_$i"]) && $_FILES["disiplin_raporu_$i"]['error'] == 0) {
                $hedef_klasor_disiplin = 'public/uploads/disiplin_raporlari/';
                if (!is_dir($hedef_klasor_disiplin)) { 
                    mkdir($hedef_klasor_disiplin, 0777, true); 
                }
                
                $uzanti = strtolower(pathinfo($_FILES["disiplin_raporu_$i"]['name'], PATHINFO_EXTENSION));
                $izinli_uzantilar = ['jpg', 'jpeg'];
                
                if (!in_array($uzanti, $izinli_uzantilar)) {
                    $_SESSION['mesaj'] = ['tip' => 'error', 'icerik' => "Disiplin raporu $i için sadece JPG ve JPEG formatları desteklenmektedir."];
                    header("Location: rapor_ekle.php?musabaka_id={$musabaka_id}");
                    exit();
                }

                $dosya_adi_disiplin = time() . "_gozlemci_rapor_{$i}_" . $musabaka_id . '.jpg';
                $yeni_disiplin_raporu_dosya_yolu = $hedef_klasor_disiplin . $dosya_adi_disiplin;
                $gecici_yol = $_FILES["disiplin_raporu_$i"]['tmp_name'];
                
                // YENİ: Yeniden boyutlandırma ve kaydetme
                if (yeniden_boyutlandir_ve_kaydet($gecici_yol, $yeni_disiplin_raporu_dosya_yolu, 1200, 80)) {
                    $stmt = $pdo->prepare("INSERT INTO disiplin_raporlari (rapor_id, rapor_tipi, rapor_no, dosya_yolu) VALUES (?, 'gozlemci', ?, ?)");
                    $stmt->execute([$rapor_id, $i, $yeni_disiplin_raporu_dosya_yolu]);
                } else {
                    $_SESSION['mesaj'] = ['tip' => 'error', 'icerik' => "Disiplin raporu $i yüklenirken veya işlenirken bir hata oluştu. (Lütfen dosya formatını kontrol edin veya GD kütüphanesini etkinleştirin.)"];
                    header("Location: rapor_ekle.php?musabaka_id={$musabaka_id}");
                    exit();
                }
            }
        }

        // Ek rapor yükleme - GÖZLEMCİ (Yeniden Boyutlandırma ve Sıkıştırma Eklendi)
        if (isset($_FILES['ek_rapor']) && $_FILES['ek_rapor']['error'] == 0) {
            $hedef_klasor_disiplin = 'public/uploads/disiplin_raporlari/';
            if (!is_dir($hedef_klasor_disiplin)) { 
                mkdir($hedef_klasor_disiplin, 0777, true); 
            }
            
            $uzanti = strtolower(pathinfo($_FILES['ek_rapor']['name'], PATHINFO_EXTENSION));
            $izinli_uzantilar = ['jpg', 'jpeg'];
            
            if (!in_array($uzanti, $izinli_uzantilar)) {
                $_SESSION['mesaj'] = ['tip' => 'error', 'icerik' => 'Ek rapor için sadece JPG ve JPEG formatları desteklenmektedir.'];
                header("Location: rapor_ekle.php?musabaka_id={$musabaka_id}");
                exit();
            }

            $dosya_adi_disiplin = time() . '_gozlemci_ek_rapor_' . $musabaka_id . '.jpg';
            $yeni_ek_rapor_dosya_yolu = $hedef_klasor_disiplin . $dosya_adi_disiplin;
            $gecici_yol = $_FILES['ek_rapor']['tmp_name'];
            
            // YENİ: Yeniden boyutlandırma ve kaydetme
            if (yeniden_boyutlandir_ve_kaydet($gecici_yol, $yeni_ek_rapor_dosya_yolu, 1200, 80)) {
                $stmt = $pdo->prepare("INSERT INTO disiplin_raporlari (rapor_id, rapor_tipi, rapor_no, dosya_yolu) VALUES (?, 'gozlemci', 5, ?)");
                $stmt->execute([$rapor_id, $yeni_ek_rapor_dosya_yolu]);
            } else {
                $_SESSION['mesaj'] = ['tip' => 'error', 'icerik' => 'Ek rapor yüklenirken veya işlenirken bir hata oluştu.'];
                header("Location: rapor_ekle.php?musabaka_id={$musabaka_id}");
                exit();
            }
        }

        // Hakem puanlama kısmı
        $hakemler_dizisi = [];
        if ($musabaka->hakem_id) $hakemler_dizisi[] = ['id' => $musabaka->hakem_id];
        if ($musabaka->yardimci_1_id) $hakemler_dizisi[] = ['id' => $musabaka->yardimci_1_id];
        if ($musabaka->yardimci_2_id) $hakemler_dizisi[] = ['id' => $musabaka->yardimci_2_id];
        if ($musabaka->dorduncu_hakem_id) $hakemler_dizisi[] = ['id' => $musabaka->dorduncu_hakem_id];

        foreach ($hakemler_dizisi as $hakem) {
            $hakem_id = $hakem['id'];
            
            $puan = str_replace(',', '.', $_POST['puan'][$hakem_id] ?? '0.0');

            if ($puan > 10.0) $puan = 10.0;
            if ($puan < 0) $puan = 0.0;

            $stmt = $pdo->prepare("SELECT id FROM rapor_detaylari WHERE rapor_id = ? AND hakem_id = ?");
            $stmt->execute([$rapor_id, $hakem_id]);
            $detay = $stmt->fetch();

            if ($detay) {
                $stmt = $pdo->prepare("UPDATE rapor_detaylari SET puan = ? WHERE id = ?");
                $stmt->execute([$puan, $detay->id]);
            } else {
                $stmt = $pdo->prepare("INSERT INTO rapor_detaylari (rapor_id, hakem_id, puan) VALUES (?, ?, ?)");
                $stmt->execute([$rapor_id, $hakem_id, $puan]);
            }
        }
        
        $pdo->commit();
        
        $_SESSION['mesaj'] = ['tip' => 'success', 'icerik' => 'Rapor başarıyla kaydedildi.'];
        header("Location: rapor_ekle.php?musabaka_id={$musabaka_id}");
        exit();

    } catch (Exception $e) {
        $pdo->rollBack();
        $_SESSION['mesaj'] = ['tip' => 'error', 'icerik' => 'Hata: ' . $e->getMessage()];
        header("Location: rapor_ekle.php?musabaka_id={$musabaka_id}");
        exit();
    }
}

// Hakemleri ve mevcut puanları çek
$hakemler = [];
if ($musabaka->hakem_id) $hakemler[] = ['id' => $musabaka->hakem_id, 'ad' => $musabaka->hakem, 'gorev' => 'Hakem'];
if ($musabaka->yardimci_1_id) $hakemler[] = ['id' => $musabaka->yardimci_1_id, 'ad' => $musabaka->yardimci_1, 'gorev' => '1. Yardımcı Hakem'];
if ($musabaka->yardimci_2_id) $hakemler[] = ['id' => $musabaka->yardimci_2_id, 'ad' => $musabaka->yardimci_2, 'gorev' => '2. Yardımcı Hakem'];
if ($musabaka->dorduncu_hakem_id) $hakemler[] = ['id' => $musabaka->dorduncu_hakem_id, 'ad' => $musabaka->dorduncu_hakem, 'gorev' => '4. Hakem'];

$mevcut_puanlar = [];
$stmt = $pdo->prepare("SELECT rd.* FROM rapor_detaylari rd JOIN raporlar r ON rd.rapor_id = r.id WHERE r.musabaka_id = ?");
$stmt->execute([$musabaka_id]);
$detaylar = $stmt->fetchAll();
foreach($detaylar as $detay) {
    $mevcut_puanlar[$detay->hakem_id] = ['puan' => number_format((float)$detay->puan, 1, '.', '')]; 
}

include 'templates/header.php';
?>
<div class="container mx-auto">
    <div class="bg-white p-6 rounded-lg shadow-md">
        <h2 class="text-xl font-semibold mb-4 text-gray-800">Gözlemci Raporu</h2>
        <p class="text-gray-600 mb-4">Müsabaka: **<?php echo htmlspecialchars($musabaka->ev_sahibi . ' - ' . $musabaka->misafir); ?>**</p>
        
        <?php if ($rapor_mevcut && !$iade_edildi): ?>
             <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 rounded-md mb-4" role="alert">
                <p class="font-bold">Rapor Tamamlandı</p>
                <p>Bu müsabaka için raporunuz başarıyla yüklenmiştir. Yönetici tarafından iade edilmediği sürece yeni giriş/düzenleme yapamazsınız.</p>
                <a href="<?php echo htmlspecialchars($musabaka->rapor_dosya_yolu); ?>" target="_blank" class="mt-2 inline-block text-blue-600 hover:underline">Yüklenen Raporu Gör</a>
            </div>
        <?php endif; ?>

        <?php if ($iade_edildi): ?>
            <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 rounded-md mb-4" role="alert">
                <p class="font-bold">Önemli Uyarı</p>
                <p>Yönetici tarafından bu raporunuz **İADE EDİLMİŞTİR**. Lütfen puanlarınızı kontrol ederek/düzelterek rapor dosyanızı (veya disiplin raporunuzu) tekrar yükleyiniz.</p>
            </div>
        <?php endif; ?>

        <?php if ($mesaj): ?>
            <div class="p-4 mb-4 text-sm rounded-lg <?php echo $mesaj['tip'] === 'success' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'; ?>">
                <?php echo htmlspecialchars($mesaj['icerik']); ?>
            </div>
        <?php endif; ?>

        <form id="raporForm" action="rapor_ekle.php?musabaka_id=<?php echo $musabaka_id; ?>" method="POST" enctype="multipart/form-data">
            
            <?php if ($duzenleme_yapilabilir): ?>
                
                <div class="mb-6 border p-4 rounded-md">
                    <label for="rapor_dosyasi" class="block text-gray-700 text-sm font-bold mb-2">Müsabaka Raporu Dosyası (Excel)</label>
                    <input type="file" name="rapor_dosyasi" id="rapor_dosyasi" class="block w-full text-sm text-gray-900 border border-gray-300 rounded-lg cursor-pointer bg-gray-50 focus:outline-none">
                    <p class="mt-1 text-sm text-gray-500">Sadece .xls veya .xlsx dosyaları yükleyebilirsiniz. <?php echo !empty($musabaka->rapor_dosya_yolu) ? '<a href="' . htmlspecialchars($musabaka->rapor_dosya_yolu) . '" target="_blank" class="text-blue-600 hover:underline">Mevcut Raporu Gör</a>' : ''; ?></p>
                </div>

                <div class="mb-6 border p-4 rounded-md">
                    <h4 class="text-md font-semibold text-gray-700 mb-3">Disiplin Raporları (Gözlemci)</h4>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <?php for ($i = 1; $i <= 5; $i++): 
                            $mevcut_rapor = array_filter($disiplin_raporlari, fn($r) => $r->rapor_no == $i);
                            $mevcut_rapor = reset($mevcut_rapor);
                        ?>
                        <div>
                            <label for="disiplin_raporu_<?php echo $i; ?>" class="block text-gray-700 text-sm font-bold mb-2">Disiplin Raporu <?php echo $i; ?></label>
                            <input type="file" name="disiplin_raporu_<?php echo $i; ?>" id="disiplin_raporu_<?php echo $i; ?>" 
                                   class="block w-full text-sm text-gray-900 border border-gray-300 rounded-lg cursor-pointer bg-gray-50 focus:outline-none" 
                                   accept=".jpg,.jpeg">
                            <p class="mt-1 text-xs text-gray-500">Sadece JPG ve JPEG formatında dosya yükleyebilirsiniz. (Otomatik küçültülecektir.)</p>
                            <?php if ($mevcut_rapor): ?>
                                <p class="mt-1 text-xs text-green-600">
                                    Mevcut: <a href="<?php echo htmlspecialchars($mevcut_rapor->dosya_yolu); ?>" target="_blank" class="underline">Görüntüle</a>
                                </p>
                            <?php endif; ?>
                        </div>
                        <?php endfor; ?>
                    </div>
                    
                    <?php 
                    $mevcut_ek_rapor = array_filter($disiplin_raporlari, fn($r) => $r->rapor_no == 5);
                    $mevcut_ek_rapor = reset($mevcut_ek_rapor);
                    ?>
                    <div class="mt-4">
                        <label for="ek_rapor" class="block text-gray-700 text-sm font-bold mb-2">Ek Rapor</label>
                        <input type="file" name="ek_rapor" id="ek_rapor" 
                               class="block w-full text-sm text-gray-900 border border-gray-300 rounded-lg cursor-pointer bg-gray-50 focus:outline-none" 
                               accept=".jpg,.jpeg">
                        <p class="mt-1 text-xs text-gray-500">Ek rapor için sadece JPG ve JPEG formatında dosya yükleyebilirsiniz. (Otomatik küçültülecektir.)</p>
                        <?php if ($mevcut_ek_rapor): ?>
                            <p class="mt-1 text-xs text-green-600">
                                Mevcut: <a href="<?php echo htmlspecialchars($mevcut_ek_rapor->dosya_yolu); ?>" target="_blank" class="underline">Görüntüle</a>
                            </p>
                        <?php endif; ?>
                    </div>
                </div>

                <h3 class="text-lg font-semibold mb-4 text-gray-700">Hakem Değerlendirmesi</h3>
                <div class="space-y-4">
                    <?php foreach ($hakemler as $hakem): ?>
                    <div class="grid grid-cols-1 md:grid-cols-12 gap-4 items-center border p-4 rounded-md">
                        <div class="md:col-span-6">
                            <p class="font-semibold text-gray-800"><?php echo htmlspecialchars($hakem['ad']); ?></p>
                            <p class="text-sm text-gray-500"><?php echo htmlspecialchars($hakem['gorev']); ?></p>
                        </div>
                        <div class="md:col-span-6">
                            <label class="block text-gray-700 text-sm font-bold mb-1">Puan (0.0 - 10.0)</label>
                            <input type="text" 
                                    inputmode="decimal" 
                                    maxlength="4" 
                                    name="puan[<?php echo $hakem['id']; ?>]" 
                                    value="<?php echo htmlspecialchars($mevcut_puanlar[$hakem['id']]['puan'] ?? '8.4'); ?>" 
                                    class="puan-input shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" 
                                    required>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>

                <div class="mt-6">
                    <button type="button" id="raporKaydetBtn" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline">
                        Raporu Kaydet
                    </button>
                </div>
            
            <?php else: ?>
                
                <h3 class="text-lg font-semibold mb-4 text-gray-700">Rapor Durumu (Tamamlandı)</h3>
                <div class="mb-6">
                    <p class="text-gray-700 font-semibold">Müsabaka Raporu: 
                        <?php echo !empty($musabaka->rapor_dosya_yolu) ? '<a href="' . htmlspecialchars($musabaka->rapor_dosya_yolu) . '" target="_blank" class="text-blue-600 hover:underline">Görüntüle</a>' : 'Yüklenmedi'; ?>
                    </p>
                </div>

                <?php if (!empty($disiplin_raporlari)): ?>
                <div class="mb-6">
                    <h4 class="font-semibold text-gray-700 mb-2">Disiplin Raporları</h4>
                    <div class="space-y-2">
                        <?php foreach ($disiplin_raporlari as $rapor): ?>
                        <div class="flex items-center justify-between p-2 bg-green-50 rounded">
                            <span class="text-sm">
                                <?php 
                                $rapor_adi = $rapor->rapor_no == 5 ? 'Ek Rapor' : "Rapor {$rapor->rapor_no}";
                                echo htmlspecialchars($rapor_adi);
                                ?>
                            </span>
                            <a href="<?php echo htmlspecialchars($rapor->dosya_yolu); ?>" target="_blank" class="text-green-600 hover:text-green-800">
                                <i class="fas fa-download"></i>
                            </a>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
                <?php endif; ?>
                
                <h3 class="text-lg font-semibold mb-4 text-gray-700">Hakem Değerlendirmesi</h3>
                <div class="space-y-4">
                    <?php foreach ($hakemler as $hakem): ?>
                    <div class="grid grid-cols-1 md:grid-cols-12 gap-4 items-center border p-4 rounded-md bg-gray-50">
                        <div class="md:col-span-6">
                            <p class="font-semibold text-gray-800"><?php echo htmlspecialchars($hakem['ad']); ?></p>
                            <p class="text-sm text-gray-500"><?php echo htmlspecialchars($hakem['gorev']); ?></p>
                        </div>
                        <div class="md:col-span-6">
                            <label class="block text-gray-700 text-sm font-bold mb-1">Puan (0.0 - 10.0)</label>
                            <p class="text-lg font-bold text-blue-700"><?php echo htmlspecialchars($mevcut_puanlar[$hakem['id']]['puan'] ?? 'Girilmedi'); ?></p>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>

            <?php endif; ?>

        </form>
    </div>
</div>

<div id="onayModal" class="hidden fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
    <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
        <div class="mt-3 text-center">
            <div class="mx-auto flex items-center justify-center h-12 w-12 rounded-full bg-blue-100">
                <svg class="h-6 w-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                </svg>
            </div>
            <h3 class="text-lg leading-6 font-medium text-gray-900 mt-4">Raporu Kaydetmek İstediğinizden Emin Misiniz?</h3>
            <p class="text-sm text-gray-500 mt-2">Rapor kaydedildikten sonra yönetici tarafından iade edilmediği sürece değişiklik yapamazsınız.</p>
            <div class="flex gap-4 mt-6 justify-center">
                <button id="onayEvetBtn" type="button" class="px-4 py-2 bg-blue-600 text-white text-base font-medium rounded-md shadow-sm hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500">
                    Evet, Kaydet
                </button>
                <button id="onayHayirBtn" type="button" class="px-4 py-2 bg-gray-300 text-gray-700 text-base font-medium rounded-md shadow-sm hover:bg-gray-400 focus:outline-none focus:ring-2 focus:ring-gray-500">
                    Hayır, İptal
                </button>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const raporForm = document.getElementById('raporForm');
    const raporKaydetBtn = document.getElementById('raporKaydetBtn');
    const onayModal = document.getElementById('onayModal');
    const onayEvetBtn = document.getElementById('onayEvetBtn');
    const onayHayirBtn = document.getElementById('onayHayirBtn');

    // Raporu Kaydet butonuna tıklandığında modalı göster
    if (raporKaydetBtn) {
        raporKaydetBtn.addEventListener('click', function(e) {
            e.preventDefault();
            onayModal.classList.remove('hidden');
        });
    }

    // Evet butonuna tıklandığında formu gönder
    if (onayEvetBtn) {
        onayEvetBtn.addEventListener('click', function() {
            onayModal.classList.add('hidden');
            raporForm.submit();
        });
    }

    // Hayır butonuna tıklandığında modalı kapat
    if (onayHayirBtn) {
        onayHayirBtn.addEventListener('click', function() {
            onayModal.classList.add('hidden');
        });
    }

    // Modal dışına tıklandığında kapat
    onayModal.addEventListener('click', function(e) {
        if (e.target === onayModal) {
            onayModal.classList.add('hidden');
        }
    });
});
</script>

<?php include 'templates/footer.php'; ?>