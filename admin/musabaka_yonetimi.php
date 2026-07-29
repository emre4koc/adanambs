<?php
ob_start();
require_once '../config/session_check_admin.php';
require_once '../config/db.php';

// Saat dilimi ayarı eklendi
date_default_timezone_set('Europe/Istanbul'); 

$sayfa_baslik = "Müsabaka Yönetimi";
$mesaj = '';
if (isset($_SESSION['mesaj'])) {
    $mesaj = $_SESSION['mesaj'];
    unset($_SESSION['mesaj']);
}

// Filtreleme parametrelerini al
$secili_tarih = isset($_GET['tarih']) ? $_GET['tarih'] : date('Y-m-d');
$secili_lig_id = isset($_GET['lig_id']) ? $_GET['lig_id'] : '';
$secili_gun = isset($_GET['gun']) ? $_GET['gun'] : '';
$gunler = [
    'Pazartesi' => 2,
    'Salı'      => 3,
    'Çarşamba'  => 4,
    'Perşembe'  => 5,
    'Cuma'      => 6,
    'Cumartesi' => 7,
    'Pazar'     => 1
];

// Durum renkleri için fonksiyon
function getStatusColor($durum) {
    switch ($durum) {
        case 'Atandı':
            return 'bg-yellow-100 text-yellow-800';
        case 'Oynandı':
            return 'bg-green-100 text-green-800';
        case 'İptal':
            return 'bg-red-100 text-red-800';
        case 'Ertelendi':
            return 'bg-blue-100 text-blue-800';
        default:
            return 'bg-gray-100 text-gray-800';
    }
}

// Yardımcı fonksiyonlar
function deleteMatch($pdo, $musabaka_id) {
    try {
        $rapor_stmt = $pdo->prepare("SELECT id FROM raporlar WHERE musabaka_id = ?");
        $rapor_stmt->execute([$musabaka_id]);
        $rapor = $rapor_stmt->fetch();
        if ($rapor) {
            $pdo->prepare("DELETE FROM rapor_detaylari WHERE rapor_id = ?")->execute([$rapor->id]);
            $pdo->prepare("DELETE FROM raporlar WHERE id = ?")->execute([$rapor->id]);
        }
        $pdo->prepare("DELETE FROM musabakalar WHERE id = ?")->execute([$musabaka_id]);
    } catch (PDOException $e) {
        error_log("deleteMatch hatası: " . $e->getMessage());
        throw $e;
    }
}

// MÜSABAKA SİLME İŞLEMİ (Tekli)
if (isset($_GET['action']) && $_GET['action'] == 'sil' && isset($_GET['id'])) {
    $musabaka_id = (int)$_GET['id'];
    try {
        deleteMatch($pdo, $musabaka_id);
        $_SESSION['mesaj'] = ['tip' => 'success', 'icerik' => 'Müsabaka başarıyla silindi.'];
    } catch (Exception $e) {
        $_SESSION['mesaj'] = ['tip' => 'error', 'icerik' => 'Müsabaka silinirken bir hata oluştu.'];
    }
    header("Location: musabaka_yonetimi.php");
    exit();
}

// YENİ - RAPOR İADE İŞLEMİ
if (isset($_GET['action']) && $_GET['action'] == 'iade_et' && isset($_GET['id'])) {
    $musabaka_id = (int)$_GET['id'];
    try {
        // 1. Rapor ID ve Gozlemci ID'yi çek
        $stmt = $pdo->prepare("SELECT r.id AS rapor_id, m.gozlemci_id FROM raporlar r JOIN musabakalar m ON r.musabaka_id = m.id WHERE r.musabaka_id = ?");
        $stmt->execute([$musabaka_id]);
        $data = $stmt->fetch(PDO::FETCH_OBJ);

        if ($data && $data->rapor_id) {
            $rapor_id = $data->rapor_id;
            $gozlemci_id = $data->gozlemci_id;
            
            // 2. Raporu iade et (iade_edildi = 1 yap)
            $stmt = $pdo->prepare("UPDATE raporlar SET iade_edildi = 1 WHERE id = ?");
            $stmt->execute([$rapor_id]);
            
            // 3. Gözlemciye bildirim gönder (Basit Bildirim Sistemi Varsayımı)
            // Lütfen 'bildirimler' tablonuzun mevcut alanlarına göre bu kodu düzenleyin.
            $bildirim_mesaj = "Bir müsabaka raporunuz (ID: {$musabaka_id}) yönetici tarafından İADE EDİLDİ. Lütfen kontrol ediniz.";
            $stmt = $pdo->prepare("INSERT INTO bildirimler (user_id, mesaj, okundu) VALUES (?, ?, 0)");
            $stmt->execute([$gozlemci_id, $bildirim_mesaj]);

            $_SESSION['mesaj'] = ['tip' => 'success', 'icerik' => 'Rapor başarıyla iade edildi ve gözlemciye bildirim gönderildi.'];
        } else {
            $_SESSION['mesaj'] = ['tip' => 'error', 'icerik' => 'İade edilecek bir rapor bulunamadı.'];
        }
    } catch (Exception $e) {
        error_log("Rapor iade hatası: " . $e->getMessage());
        $_SESSION['mesaj'] = ['tip' => 'error', 'icerik' => 'Rapor iade edilirken bir hata oluştu: ' . $e->getMessage()];
    }
    header("Location: musabaka_yonetimi.php");
    exit();
}


// YENİ - MÜSABAKA DURUM GÜNCELLEME İŞLEMİ
if (isset($_GET['action']) && $_GET['action'] == 'durum_guncelle' && isset($_GET['id']) && isset($_GET['durum'])) {
    $musabaka_id = (int)$_GET['id'];
    $yeni_durum = $_GET['durum'];
    
    // İzin verilen durumlar
    $izinli_durumlar = ['Atandı', 'Oynandı', 'İptal', 'Ertelendi'];
    
    if (in_array($yeni_durum, $izinli_durumlar)) {
        try {
            $stmt = $pdo->prepare("UPDATE musabakalar SET durum = ? WHERE id = ?");
            $stmt->execute([$yeni_durum, $musabaka_id]);
            $_SESSION['mesaj'] = ['tip' => 'success', 'icerik' => "Müsabaka durumu '{$yeni_durum}' olarak güncellendi."];
        } catch (Exception $e) {
            error_log("Durum güncelleme hatası: " . $e->getMessage());
            $_SESSION['mesaj'] = ['tip' => 'error', 'icerik' => 'Durum güncellenirken bir hata oluştu.'];
        }
    } else {
        $_SESSION['mesaj'] = ['tip' => 'error', 'icerik' => 'Geçersiz durum seçimi.'];
    }
    header("Location: musabaka_yonetimi.php");
    exit();
}



// YENİ - TOPLU İŞLEM İŞLEME
if (isset($_POST['toplu_islem_uygula']) && isset($_POST['secili_musabakalar']) && !empty($_POST['toplu_islem'])) {
    $secili_musabaka_ids = $_POST['secili_musabakalar'];
    $islem = $_POST['toplu_islem'];
    $islem_basarili = true;
    $pdo->beginTransaction();

    try {
        foreach ($secili_musabaka_ids as $id) {
            $id = (int)$id;
            if ($islem == 'arsivle') {
                $stmt = $pdo->prepare("UPDATE musabakalar SET arsiv = 1 WHERE id = ?");
                if (!$stmt->execute([$id])) {
                    $islem_basarili = false;
                    break;
                }
            } elseif ($islem == 'sil') {
                deleteMatch($pdo, $id);
            }
        }
        if ($islem_basarili) {
            $pdo->commit();
            $_SESSION['mesaj'] = ['tip' => 'success', 'icerik' => count($secili_musabaka_ids) . " adet müsabaka başarıyla $islem."];
        } else {
            $pdo->rollBack();
            $_SESSION['mesaj'] = ['tip' => 'error', 'icerik' => 'Toplu işlem sırasında bir hata oluştu.'];
        }
    } catch (Exception $e) {
        $pdo->rollBack();
        $_SESSION['mesaj'] = ['tip' => 'error', 'icerik' => 'Toplu işlem sırasında bir hata oluştu: ' . $e->getMessage()];
    }
    header("Location: musabaka_yonetimi.php");
    exit();
}


// --- LİG VE STADYUM BİLGİLERİ İÇİN VERİ ÇEKME ---
$ligler = $pdo->query("SELECT id, ad FROM ligler ORDER BY ad ASC")->fetchAll(PDO::FETCH_OBJ);

// --- MÜSABAKALARI LİSTELEME (Sorgu Güncellendi) ---
$query = "
    SELECT 
        m.*, 
        l.ad AS lig_adi, 
        t1.ad AS ev_sahibi, 
        t2.ad AS misafir,
        s.ad AS stadyum_adi, -- YENİ: Stadyum Adı Eklendi
        u1.ad AS hakem_ad, u1.soyad AS hakem_soyad,
        u2.ad AS yrd1_ad, u2.soyad AS yrd1_soyad,
        u3.ad AS yrd2_ad, u3.soyad AS yrd2_soyad,
        u4.ad AS dorduncu_ad, u4.soyad AS dorduncu_soyad,
        u5.ad AS gozlemci_ad, u5.soyad AS gozlemci_soyad,
        r.rapor_dosya_yolu,
        r.olusturma_tarihi AS rapor_yuklenme_tarihi,
        r.id AS rapor_id,
        r.iade_edildi
    FROM musabakalar m
    LEFT JOIN ligler l ON m.lig_id = l.id
    LEFT JOIN takimlar t1 ON m.ev_sahibi_id = t1.id
    LEFT JOIN takimlar t2 ON m.misafir_id = t2.id
    LEFT JOIN stadyumlar s ON m.stadyum_id = s.id -- YENİ: stadyumlar tablosu ile birleştirildi
    LEFT JOIN users u1 ON m.hakem_id = u1.id
    LEFT JOIN users u2 ON m.yardimci_1_id = u2.id
    LEFT JOIN users u3 ON m.yardimci_2_id = u3.id
    LEFT JOIN users u4 ON m.dorduncu_hakem_id = u4.id
    LEFT JOIN users u5 ON m.gozlemci_id = u5.id
    LEFT JOIN raporlar r ON m.id = r.musabaka_id
    WHERE m.arsiv = 0
";

$params = [];
if (!empty($secili_lig_id)) {
    $query .= " AND m.lig_id = ?";
    $params[] = $secili_lig_id;
}
if (!empty($secili_gun)) {
    $gun_sayi = $gunler[$secili_gun];
    // DAYOFWEEK() fonksiyonu Pazar'ı 1, Cumartesi'yi 7 olarak döner.
    $query .= " AND DAYOFWEEK(m.tarih) = ?";
    $params[] = $gun_sayi;
}

$query .= " ORDER BY m.mac_no ASC";

$musabakalar = $pdo->prepare($query);
$musabakalar->execute($params);
$musabakalar = $musabakalar->fetchAll(PDO::FETCH_OBJ);

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
        <form method="GET" action="musabaka_yonetimi.php" class="flex flex-col space-y-4 md:space-y-0 md:flex-row md:space-x-4 items-center">
            
            <div class="flex-1 w-full">
                <label for="lig_id" class="block text-sm font-medium text-gray-700 mb-1">Lig Seçimi:</label>
                <select id="lig_id" name="lig_id" class="w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500">
                    <option value="">Tüm Ligler</option>
                    <?php foreach($ligler as $lig): ?>
                        <option value="<?php echo htmlspecialchars($lig->id); ?>" <?php echo ($secili_lig_id == $lig->id) ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($lig->ad); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <div class="flex-1 w-full">
                <label for="gun" class="block text-sm font-medium text-gray-700 mb-1">Gün Seçimi:</label>
                <select id="gun" name="gun" class="w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500">
                    <option value="">Tüm Günler</option>
                    <?php foreach($gunler as $gun_adi => $gun_sayi): ?>
                        <option value="<?php echo htmlspecialchars($gun_adi); ?>" <?php echo ($secili_gun == $gun_adi) ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($gun_adi); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="flex items-center space-x-2 w-full md:w-auto mt-4 md:mt-0">
                <button type="submit" class="bg-blue-600 text-white py-2 px-4 rounded-md hover:bg-blue-700 w-full md:w-auto">Filtrele</button>
                <a href="musabaka_yonetimi.php" class="bg-gray-200 text-gray-700 py-2 px-4 rounded-md hover:bg-gray-300 w-full md:w-auto flex items-center justify-center">Temizle</a>
            </div>
        </form>
    </div>

    <div class="bg-white p-6 rounded-lg shadow-md">
        <h2 class="text-xl font-semibold mb-4 text-gray-800">Müsabaka Listesi</h2>
        <form id="toplu_islem_form" method="POST" action="musabaka_yonetimi.php">
            <div class="flex justify-between items-center mb-4">
                <div class="flex items-center space-x-2">
                    <input type="checkbox" id="select-all" class="rounded text-blue-600 focus:ring-blue-500 h-4 w-4">
                    <label for="select-all" class="text-sm text-gray-700">Tümünü Seç</label>
                </div>
                <div class="flex items-center">
                    <select name="toplu_islem" class="border-gray-300 rounded-l-md text-sm">
                        <option value="">Toplu İşlem Seç</option>
                        <option value="arsivle">Seçilenleri Arşivle</option>
                        <option value="sil">Seçilenleri Sil</option>
                    </select>
                    <button type="submit" name="toplu_islem_uygula" class="bg-gray-800 text-white py-2 px-4 rounded-r-md hover:bg-gray-700" onclick="return confirm('Seçili işlem uygulanacaktır. Emin misiniz?');">Uygula</button>
                </div>
            </div>
            
            <div class="overflow-x-auto" style="max-width: 100%;">
                <table class="w-full bg-white border border-gray-200 table-fixed" style="font-size: 10.5px;">
                    <thead class="sticky top-0 z-10 bg-gray-100">
                        <tr>
                            <th class="py-1 px-1 border-b text-center" style="width: 30px;">
                                <input type="checkbox" id="select-all" class="rounded text-blue-600 focus:ring-blue-500 h-3 w-3" title="Tümünü Seç">
                            </th>
                            <th class="py-1 px-1 border-b text-center" style="width: 45px;">Maç</th>
                            <th class="py-1 px-1 border-b text-center" style="width: 40px;">Hft</th>
                            <th class="py-1 px-1 border-b text-left" style="width: 70px;">Tarih</th>
                            <th class="py-1 px-1 border-b text-left" style="width: 38px;">Saat</th>
                            <th class="py-1 px-1 border-b text-left" style="width: 85px;">Lig</th>
                            <th class="py-1 px-1 border-b text-left" style="width: 95px;">Stadyum</th>
                            <th class="py-1 px-1 border-b text-left" style="width: 85px;">Ev Sahibi</th>
                            <th class="py-1 px-1 border-b text-left" style="width: 85px;">Misafir</th>
                            <th class="py-1 px-1 border-b text-left" style="width: 80px;">Hakem</th>
                            <th class="py-1 px-1 border-b text-left" style="width: 70px;">1.Yrd</th>
                            <th class="py-1 px-1 border-b text-left" style="width: 70px;">2.Yrd</th>
                            <th class="py-1 px-1 border-b text-left" style="width: 70px;">4.Hkm</th>
                            <th class="py-1 px-1 border-b text-left" style="width: 80px;">Gözlemci</th>
                            <th class="py-1 px-1 border-b text-center" style="width: 65px;">Rapor</th>
                            <th class="py-1 px-1 border-b text-center" style="width: 95px;">İşlem</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($musabakalar)): ?>
                            <tr>
                                <td colspan="16" class="text-center py-4 text-gray-500"> <?php echo !empty($secili_lig_id) || !empty($secili_gun) ? 'Seçilen filtreye uygun müsabaka bulunamadı.' : 'Müsabaka bulunmamaktadır.'; ?>
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($musabakalar as $musabaka): ?>
                                <?php
                                    // Rapor Kontrol ve Uyarı Mantığı
                                    $rapor_gecikmis = false;
                                    $gec_yuklendi = false; 

                                    try {
                                        // Müsabakanın tarihini DateTime nesnesi olarak oluştur
                                        $mac_tarihi = new DateTime($musabaka->tarih);

                                        // Son teslim tarihini hesapla (Maçın ertesi günü 12:00)
                                        $son_teslim_tarihi = clone $mac_tarihi;
                                        $son_teslim_tarihi->modify('+1 day')->setTime(12, 0, 0);

                                        $simdi = new DateTime();
                                        
                                        // Gecikme Kontrolü: 
                                        // 1. Süresi dolmuş ve rapor yoksa gecikmiş uyarı göster
                                        $rapor_gecikmis = $simdi > $son_teslim_tarihi && empty($musabaka->rapor_dosya_yolu);

                                        // 2. Rapor yüklenmiş VE yüklenme tarihi son teslim tarihinden büyükse geç yüklenmiş
                                        if (!empty($musabaka->rapor_yuklenme_tarihi)) {
                                            $yuklenme_tarihi = new DateTime($musabaka->rapor_yuklenme_tarihi);
                                            $gec_yuklendi = $yuklenme_tarihi > $son_teslim_tarihi;
                                        }

                                    } catch (Exception $e) {
                                        $rapor_gecikmis = false; 
                                        $gec_yuklendi = false; 
                                    }
                                ?>
                                <tr class="hover:bg-gray-50">
                                    <td class="py-1 px-1 border-b text-center">
                                        <input type="checkbox" name="secili_musabakalar[]" value="<?php echo $musabaka->id; ?>" class="musabaka-checkbox rounded text-blue-600 focus:ring-blue-500 h-3 w-3">
                                    </td>
                                    <td class="py-1 px-1 border-b text-center font-medium"><?php echo htmlspecialchars($musabaka->mac_no); ?></td>
                                    <td class="py-1 px-1 border-b text-center"><?php echo htmlspecialchars($musabaka->hafta_no ?? '-'); ?></td>
                                    <td class="py-1 px-1 border-b whitespace-nowrap"><?php echo date('d.m.Y', strtotime($musabaka->tarih)); ?></td>
                                    <td class="py-1 px-1 border-b whitespace-nowrap"><?php echo date('H:i', strtotime($musabaka->saat)); ?></td>
                                    <td class="py-1 px-1 border-b" style="word-break: break-word; line-height: 1.3;"><?php echo htmlspecialchars($musabaka->lig_adi); ?></td>
                                    <td class="py-1 px-1 border-b" style="word-break: break-word; line-height: 1.3;"><?php echo htmlspecialchars($musabaka->stadyum_adi); ?></td>
                                    <td class="py-1 px-1 border-b" style="word-break: break-word; line-height: 1.3;"><?php echo htmlspecialchars($musabaka->ev_sahibi); ?></td>
                                    <td class="py-1 px-1 border-b" style="word-break: break-word; line-height: 1.3;"><?php echo htmlspecialchars($musabaka->misafir); ?></td>
                                    <td class="py-1 px-1 border-b" style="word-break: break-word; line-height: 1.3;"><?php echo htmlspecialchars($musabaka->hakem_ad . ' ' . $musabaka->hakem_soyad); ?></td>
                                    <td class="py-1 px-1 border-b" style="word-break: break-word; line-height: 1.3;"><?php echo !empty($musabaka->yrd1_ad) ? htmlspecialchars($musabaka->yrd1_ad . ' ' . $musabaka->yrd1_soyad) : '-'; ?></td>
                                    <td class="py-1 px-1 border-b" style="word-break: break-word; line-height: 1.3;"><?php echo !empty($musabaka->yrd2_ad) ? htmlspecialchars($musabaka->yrd2_ad . ' ' . $musabaka->yrd2_soyad) : '-'; ?></td>
                                    <td class="py-1 px-1 border-b" style="word-break: break-word; line-height: 1.3;"><?php echo !empty($musabaka->dorduncu_ad) ? htmlspecialchars($musabaka->dorduncu_ad . ' ' . $musabaka->dorduncu_soyad) : '-'; ?></td>
                                    <td class="py-1 px-1 border-b" style="word-break: break-word; line-height: 1.3;"><?php echo !empty($musabaka->gozlemci_ad) ? htmlspecialchars($musabaka->gozlemci_ad . ' ' . $musabaka->gozlemci_soyad) : '-'; ?></td>
                                    <td class="py-1 px-1 border-b text-center">
                                        <?php if (!empty($musabaka->rapor_dosya_yolu)): ?>
                                            <a href="../<?php echo htmlspecialchars($musabaka->rapor_dosya_yolu); ?>" 
                                               class="text-green-600 hover:text-green-800 block text-sm" 
                                               title="Raporu İndir"
                                               target="_blank">
                                                <i class="fas fa-file-excel"></i>
                                            </a>
                                            <?php if (!empty($musabaka->rapor_yuklenme_tarihi)): ?>
                                                <div class="text-xs text-gray-500 mt-0.5" style="font-size: 9px;">
                                                    <?php echo date('d.m H:i', strtotime($musabaka->rapor_yuklenme_tarihi)); ?>
                                                </div>
                                            <?php endif; ?>
                                            <?php if ($gec_yuklendi): ?>
                                                <div class="mt-0.5 text-red-600 font-semibold" style="font-size: 9px;">
                                                    <i class="fas fa-exclamation-triangle"></i> GEÇ
                                                </div>
                                            <?php endif; ?>
                                        <?php else: ?>
                                            <?php if ($rapor_gecikmis): ?>
                                                <div class="text-red-600 font-semibold" style="font-size: 9px;" title="Rapor yükleme süresi doldu">
                                                    <i class="fas fa-exclamation-triangle"></i> GEÇİK
                                                </div>
                                            <?php else: ?>
                                                <span class="text-gray-400">-</span>
                                            <?php endif; ?>
                                        <?php endif; ?>
                                    </td>
                                    <td class="py-1 px-1 border-b text-center">
                                        <div class="flex flex-wrap gap-0.5 justify-center items-center">
                                            <!-- Durum Güncelleme Dropdown -->
                                            <select onchange="if(this.value) { if(confirm('Müsabaka durumunu değiştirmek istediğinize emin misiniz?')) { window.location.href='?action=durum_guncelle&id=<?php echo $musabaka->id; ?>&durum=' + this.value; } else { this.value=''; } }" 
                                                    class="py-0.5 px-1 rounded border cursor-pointer focus:outline-none focus:ring-1 focus:ring-blue-500 <?php 
                                                        if ($musabaka->durum == 'Atandı') echo 'bg-yellow-100 border-yellow-300 text-yellow-800';
                                                        elseif ($musabaka->durum == 'Oynandı') echo 'bg-green-100 border-green-300 text-green-800';
                                                        elseif ($musabaka->durum == 'İptal') echo 'bg-red-100 border-red-300 text-red-800';
                                                        elseif ($musabaka->durum == 'Ertelendi') echo 'bg-blue-100 border-blue-300 text-blue-800';
                                                        else echo 'bg-gray-100 border-gray-300 text-gray-800';
                                                    ?>" 
                                                    style="font-size: 10px;"
                                                    title="Durum Değiştir">
                                                <option value="">Durum</option>
                                                <option value="Atandı" <?php echo ($musabaka->durum == 'Atandı') ? 'selected' : ''; ?>>Atandı</option>
                                                <option value="Oynandı" <?php echo ($musabaka->durum == 'Oynandı') ? 'selected' : ''; ?>>Oynandı</option>
                                                <option value="İptal" <?php echo ($musabaka->durum == 'İptal') ? 'selected' : ''; ?>>İptal</option>
                                                <option value="Ertelendi" <?php echo ($musabaka->durum == 'Ertelendi') ? 'selected' : ''; ?>>Ertelendi</option>
                                            </select>
                                            
                                            <?php if (!empty($musabaka->rapor_dosya_yolu) && empty($musabaka->iade_edildi)): ?>
                                                <a href="?action=iade_et&id=<?php echo $musabaka->id; ?>" 
                                                   class="inline-block text-orange-600 hover:text-orange-800 text-xs" 
                                                   title="Raporu İade Et" 
                                                   onclick="return confirm('Raporu gözlemciye iade etmek istediğinizden emin misiniz?');">
                                                    <i class="fas fa-undo-alt"></i>
                                                </a>
                                            <?php endif; ?>

                                            <a href="../musabaka_detay.php?id=<?php echo $musabaka->id; ?>" 
                                               class="inline-block text-blue-600 hover:text-blue-800 text-xs" 
                                               title="Detay">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <a href="?action=sil&id=<?php echo $musabaka->id; ?>" 
                                               class="inline-block text-red-600 hover:text-red-800 text-xs" 
                                               title="Sil" 
                                               onclick="return confirm('Bu müsabakayı silmek istediğinizden emin misiniz?');">
                                                <i class="fas fa-trash-alt"></i>
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </form>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const selectAllCheckbox = document.getElementById('select-all');
    const musabakaCheckboxes = document.querySelectorAll('.musabaka-checkbox');
    const topluIslemForm = document.getElementById('toplu_islem_form');
    
    // "Tümünü Seç" kutusunun tıklanma olayı
    if (selectAllCheckbox) {
        selectAllCheckbox.addEventListener('change', function() {
            musabakaCheckboxes.forEach(function(checkbox) {
                checkbox.checked = selectAllCheckbox.checked;
            });
        });
    }

    // Bireysel müsabaka kutularının tıklanma olayı
    musabakaCheckboxes.forEach(function(checkbox) {
        checkbox.addEventListener('change', function() {
            const allChecked = Array.from(musabakaCheckboxes).every(cb => cb.checked);
            if (selectAllCheckbox) {
                selectAllCheckbox.checked = allChecked;
            }
        });
    }); // HATA DÜZELTİLDİ: Eksik parantez ve noktalı virgül eklendi

    // Toplu işlem formu gönderim kontrolü
    if (topluIslemForm) {
        topluIslemForm.addEventListener('submit', function(e) {
            var checkedBoxes = document.querySelectorAll('.musabaka-checkbox:checked');
            if (checkedBoxes.length === 0) {
                e.preventDefault();
                alert('Lütfen işlem yapmak için en az bir müsabaka seçin.');
            } else {
                var islem = document.querySelector('select[name="toplu_islem"]').value;
                if (islem === 'sil' && !confirm('Seçili ' + checkedBoxes.length + ' müsabakayı silmek istediğinize emin misiniz? Bu işlem geri alınamaz.')) {
                    e.preventDefault();
                }
            }
        });
    }
});
</script>

<?php include '../templates/footer.php'; ?>