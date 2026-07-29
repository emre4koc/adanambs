<?php
require_once '../config/session_check_admin.php';
require_once '../config/db.php';

$sayfa_baslik = "Kullanıcı Yeterlilik Yönetimi";
$mesaj = '';

// Sayfa yüklendiğinde session'daki mesajı al ve temizle
if (isset($_SESSION['mesaj'])) {
    $mesaj = $_SESSION['mesaj'];
    unset($_SESSION['mesaj']);
}

// --- İŞLEM BLOKLARI ---

// YENİ: Toplu durum güncelleme ve uyarı kaldırma
if (isset($_POST['toplu_kaydet']) && isset($_POST['user_ids'])) {
    $user_ids = $_POST['user_ids'];
    $pdo->beginTransaction();
    try {
        foreach ($user_ids as $user_id) {
            $user_id = (int)$user_id;

            $egitim_durum   = isset($_POST['egitim'][$user_id]) ? 1 : 0;
            $antreman_durum = isset($_POST['antreman'][$user_id]) ? 1 : 0;
            $ceza_durum     = isset($_POST['ceza'][$user_id]) ? 1 : 0;
            $aktif          = isset($_POST['aktif'][$user_id]) ? 1 : 0;
            $uyari_kaldirildi = isset($_POST['uyari_kaldir'][$user_id]) ? 1 : 0;

            $stmt = $pdo->prepare(
                "UPDATE users SET 
                    egitim_durum = :egitim_durum, 
                    antreman_durum = :antreman_durum, 
                    ceza_durum = :ceza_durum, 
                    aktif = :aktif,
                    uyari_kaldirildi = :uyari_kaldirildi
                WHERE id = :id"
            );

            $stmt->execute([
                ':egitim_durum'      => $egitim_durum,
                ':antreman_durum'    => $antreman_durum,
                ':ceza_durum'        => $ceza_durum,
                ':aktif'             => $aktif,
                ':uyari_kaldirildi'  => $uyari_kaldirildi,
                ':id'                => $user_id
            ]);
        }
        $pdo->commit();
        $_SESSION['mesaj'] = ['tip' => 'success', 'icerik' => 'Tüm değişiklikler başarıyla kaydedildi.'];
    } catch (Exception $e) {
        $pdo->rollBack();
        $_SESSION['mesaj'] = ['tip' => 'error', 'icerik' => 'Hata: ' . $e->getMessage()];
    }

    header("Location: kullanici_yeterlilik.php" . (!empty($_GET) ? '?' . http_build_query($_GET) : ''));
    exit();
}

function getRoleName($roleId) {
    switch ($roleId) {
        case 1: return 'Yönetici';
        case 2: return 'Hakem';
        case 3: return 'Gözlemci';
        default: return 'Bilinmiyor';
    }
}

// --- EXCEL RAPOR BLOĞU ---
if (isset($_GET['excel_rapor'])) {
    @include_once '../lib/SimpleXLSXGen.php';
    if (!class_exists('Shuchkin\SimpleXLSXGen')) {
        die("SimpleXLSXGen kütüphanesi bulunamadı!");
    }
    
    $sql = "
        SELECT u.*,
            (SELECT COUNT(m.id) FROM musabakalar m WHERE m.hakem_id = u.id OR m.yardimci_1_id = u.id OR m.yardimci_2_id = u.id OR m.dorduncu_hakem_id = u.id OR m.gozlemci_id = u.id) AS toplam_gorev,
            (SELECT AVG(rd.puan) FROM rapor_detaylari rd WHERE rd.hakem_id = u.id) AS puan_ortalamasi,
            (SELECT COUNT(*) FROM rapor_detaylari rd WHERE rd.hakem_id = u.id AND rd.puan <= 7.9 AND rd.listeden_kaldir = 0) as dusuk_puan_sayisi
        FROM users u
        WHERE u.rol IN (2, 3)
    ";
    
    $params = [];
    $arama_terimi = isset($_GET['arama']) ? trim($_GET['arama']) : '';
    $filtre_klasman = isset($_GET['filtre_klasman']) ? trim($_GET['filtre_klasman']) : '';
    $filtre_rol = isset($_GET['filtre_rol']) ? (int)$_GET['filtre_rol'] : '';

    if (!empty($arama_terimi)) {
        $sql .= " AND CONCAT(u.ad, ' ', u.soyad) LIKE :arama";
        $params[':arama'] = "%$arama_terimi%";
    }
    if (!empty($filtre_klasman)) {
        $sql .= " AND u.klasman = :klasman";
        $params[':klasman'] = $filtre_klasman;
    }
    if (!empty($filtre_rol)) {
        $sql .= " AND u.rol = :rol";
        $params[':rol'] = $filtre_rol;
    }
    
    $sql .= " ORDER BY u.rol, u.ad, u.soyad";
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $rapor_kullanicilar = $stmt->fetchAll();
    
    $excel_data = [
        ['Ad Soyad', 'Klasman', 'Rol', 'Toplam Görev', 'Puan Ortalaması', 'Eğitim Durumu', 'Antreman Durumu', 'Ceza Durumu', 'Hesap Durumu']
    ];
    
    foreach ($rapor_kullanicilar as $kullanici) {
        $excel_data[] = [
            $kullanici->ad . ' ' . $kullanici->soyad,
            $kullanici->klasman,
            getRoleName($kullanici->rol),
            $kullanici->toplam_gorev,
            ($kullanici->puan_ortalamasi ? number_format($kullanici->puan_ortalamasi, 2) : '-'),
            ($kullanici->egitim_durum ? 'Tamamlandı' : 'Beklemede'),
            ($kullanici->antreman_durum ? 'Tamamlandı' : 'Beklemede'),
            ($kullanici->ceza_durum ? 'Ceza Uygulandı' : 'Ceza Yok'),
            ($kullanici->aktif ? 'Aktif' : 'Pasif')
        ];
    }
    
    try {
        $xlsx = Shuchkin\SimpleXLSXGen::fromArray($excel_data);
        $xlsx->downloadAs('kullanici_yeterlilik_raporu_' . date('d-m-Y') . '.xlsx');
    } catch (Exception $e) {
        die("Excel oluşturma hatası: " . $e->getMessage());
    }
    
    exit();
}

if (isset($_SESSION['mesaj'])) {
    $mesaj = $_SESSION['mesaj'];
    unset($_SESSION['mesaj']);
}

// --- FİLTRELEME ve ARAMA ---
$arama_terimi = isset($_GET['arama']) ? trim($_GET['arama']) : '';
$filtre_klasman = isset($_GET['filtre_klasman']) ? trim($_GET['filtre_klasman']) : '';
$filtre_rol = isset($_GET['filtre_rol']) ? (int)$_GET['filtre_rol'] : 2; // Varsayılan olarak Hakemleri filtrele

$klasmanlar = $pdo->query("SELECT ad FROM klasmanlar ORDER BY ad ASC")->fetchAll(PDO::FETCH_COLUMN);

// SQL sorgusu - Hakemler (rol=2) ve Gözlemciler (rol=3)
$sql = "
    SELECT u.*,
        (SELECT COUNT(m.id) FROM musabakalar m WHERE m.hakem_id = u.id OR m.yardimci_1_id = u.id OR m.yardimci_2_id = u.id OR m.dorduncu_hakem_id = u.id OR m.gozlemci_id = u.id) AS toplam_gorev,
        (SELECT AVG(rd.puan) FROM rapor_detaylari rd WHERE rd.hakem_id = u.id) AS puan_ortalamasi,
        (SELECT COUNT(*) FROM rapor_detaylari rd WHERE rd.hakem_id = u.id AND rd.puan <= 7.9 AND rd.listeden_kaldir = 0) as dusuk_puan_sayisi
    FROM users u
    WHERE u.rol IN (2, 3)
";

$params = [];
if (!empty($arama_terimi)) {
    $sql .= " AND CONCAT(u.ad, ' ', u.soyad) LIKE :arama";
    $params[':arama'] = "%$arama_terimi%";
}
if (!empty($filtre_klasman)) {
    $sql .= " AND u.klasman = :klasman";
    $params[':klasman'] = $filtre_klasman;
}
if (!empty($filtre_rol)) {
    $sql .= " AND u.rol = :rol";
    $params[':rol'] = $filtre_rol;
}

$sql .= " ORDER BY 
    FIELD(u.klasman, 
        'Üst Klasman Hakemi', 'Üst Klasman Yardımcı Hakemi', 'Klasman Hakemi', 'Klasman Yardımcı Hakemi', 
        'Bölgesel Hakem', 'Bölgesel Yardımcı Hakem', 'İl Hakemi', 'Aday Hakem', 
        'KLASMAN GÖZLEMCİSİ', 'BÖLGESEL GÖZLEMCİ', 'İL GÖZLEMCİSİ'
    ), u.ad, u.soyad";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$kullanicilar = $stmt->fetchAll();


include '../templates/header.php';
?>
<div class="container mx-auto">
    <div class="bg-white p-6 rounded-lg shadow-md">
        <div class="flex flex-col md:flex-row justify-between items-center mb-4 gap-4">
            <h2 class="text-xl font-semibold text-gray-800">Kullanıcı Yeterlilik Yönetimi</h2>
            <div class="flex flex-col sm:flex-row gap-2 w-full md:w-auto">
                <a href="?excel_rapor=1<?php echo !empty($_GET) ? '&' . http_build_query($_GET) : ''; ?>" class="bg-green-600 text-white py-2 px-4 rounded-md hover:bg-green-700 text-center">
                    <i class="fas fa-file-excel mr-2"></i>Excel Raporu
                </a>
            </div>
        </div>
        
        <div class="flex flex-col md:flex-row justify-between items-center mb-4 gap-4">
            <form method="GET" class="flex flex-col sm:flex-row gap-2 w-full md:w-auto">
                <input type="text" name="arama" placeholder="İsme göre ara..." value="<?php echo htmlspecialchars($arama_terimi); ?>" class="border p-2 rounded-md w-full sm:w-auto">
                <select name="filtre_klasman" class="border p-2 rounded-md w-full sm:w-auto">
                    <option value="">Tüm Klasmanlar</option>
                    <?php foreach ($klasmanlar as $klasman): ?>
                        <option value="<?php echo htmlspecialchars($klasman); ?>" <?php if ($filtre_klasman === $klasman) echo 'selected'; ?>>
                            <?php echo htmlspecialchars($klasman); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <select name="filtre_rol" class="border p-2 rounded-md w-full sm:w-auto">
                    <option value="">Tüm Roller</option>
                    <option value="2" <?php if ($filtre_rol === 2) echo 'selected'; ?>>Hakem</option>
                    <option value="3" <?php if ($filtre_rol === 3) echo 'selected'; ?>>Gözlemci</option>
                </select>
                <button type="submit" class="bg-blue-600 text-white py-2 px-4 rounded-md">Filtrele</button>
                <a href="kullanici_yeterlilik.php" class="bg-gray-200 text-gray-800 text-center py-2 px-4 rounded-md">Temizle</a>
            </form>
        </div>
        
        <?php if ($mesaj): ?>
            <div class="p-4 mb-4 text-sm rounded-lg <?php echo $mesaj['tip'] === 'success' ? 'bg-green-100 text-green-800' : ($mesaj['tip'] === 'warning' ? 'bg-yellow-100 text-yellow-800' : 'bg-red-100 text-red-800'); ?>">
                <?php echo htmlspecialchars($mesaj['icerik']); ?>
            </div>
        <?php endif; ?>
        
        <form method="POST">
            <div class="overflow-x-auto">
                <table class="min-w-full bg-white">
                    <thead class="bg-gray-100">
                        <tr>
                            <th class="py-2 px-4 text-left">Ad Soyad / Klasman</th>
                            <th class="py-2 px-4 text-left">Rol</th>
                            <th class="py-2 px-4 text-center">Toplam Görev</th>
                            <th class="py-2 px-4 text-center">Puan Ort.</th>
                            <th class="py-2 px-4 text-center">Eğitim</th>
                            <th class="py-2 px-4 text-center">Antrenman</th>
                            <th class="py-2 px-4 text-center">Ceza</th>
                            <th class="py-2 px-4 text-center">Hesap Aktif</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if(empty($kullanicilar)): ?>
                            <tr><td colspan="8" class="text-center py-4">Filtreye uygun kullanıcı bulunamadı.</td></tr>
                        <?php else: ?>
                            <?php foreach($kullanicilar as $kullanici): ?>
                                <tr class="border-b">
                                    <input type="hidden" name="user_ids[]" value="<?php echo $kullanici->id; ?>">
                                    <input type="hidden" name="uyari_kaldir[<?php echo $kullanici->id; ?>]" value="<?php echo $kullanici->uyari_kaldirildi; ?>">

                                    <td class="py-2 px-4">
                                        <div class="font-medium">
                                            <?php echo htmlspecialchars($kullanici->ad . ' ' . $kullanici->soyad); ?>
                                            <?php if ($kullanici->dusuk_puan_sayisi > 0 && $kullanici->uyari_kaldirildi == 0): ?>
                                                <i class="fas fa-exclamation-circle text-red-500 ml-2" title="<?php echo htmlspecialchars($kullanici->dusuk_puan_sayisi); ?> adet 7.9 ve altı puanı var."></i>
                                            <?php endif; ?>
                                        </div>
                                        <div class="text-xs text-gray-500"><?php echo htmlspecialchars($kullanici->klasman); ?></div>
                                    </td>
                                    <td class="py-2 px-4">
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full <?php echo $kullanici->rol == 2 ? 'bg-blue-100 text-blue-800' : 'bg-purple-100 text-purple-800'; ?>">
                                            <?php echo getRoleName($kullanici->rol); ?>
                                        </span>
                                    </td>
                                    <td class="py-2 px-4 text-center font-bold"><?php echo $kullanici->toplam_gorev; ?></td>
                                    <td class="py-2 px-4 text-center font-bold text-blue-600">
                                        <?php echo $kullanici->puan_ortalamasi ? number_format($kullanici->puan_ortalamasi, 2) : '-'; ?>
                                    </td>
                                    
                                    <td class="py-2 px-4 text-center">
                                        <input type="checkbox" name="egitim[<?php echo $kullanici->id; ?>]" value="1" <?php if ($kullanici->egitim_durum) echo 'checked'; ?> class="h-5 w-5 rounded">
                                    </td>
                                    
                                    <td class="py-2 px-4 text-center">
                                        <input type="checkbox" name="antreman[<?php echo $kullanici->id; ?>]" value="1" <?php if ($kullanici->antreman_durum) echo 'checked'; ?> class="h-5 w-5 rounded">
                                    </td>
                                    
                                    <td class="py-2 px-4 text-center">
                                        <input type="checkbox" name="ceza[<?php echo $kullanici->id; ?>]" value="1" <?php if ($kullanici->ceza_durum) echo 'checked'; ?> class="h-5 w-5 rounded">
                                    </td>
                                    
                                    <td class="py-2 px-4 text-center">
                                        <input type="checkbox" name="aktif[<?php echo $kullanici->id; ?>]" value="1" <?php if ($kullanici->aktif) echo 'checked'; ?> class="h-5 w-5 rounded">
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <?php if(!empty($kullanicilar)): ?>
            <div class="mt-6 flex justify-end">
                <button type="submit" name="toplu_kaydet" class="bg-blue-600 text-white py-2 px-6 rounded-md hover:bg-blue-700">
                    <i class="fas fa-save mr-2"></i>Tüm Değişiklikleri Kaydet
                </button>
            </div>
            <?php endif; ?>
        </form>
    </div>
</div>
<?php include '../templates/footer.php'; ?>