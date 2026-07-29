<?php
require_once '../config/session_check_admin.php';
require_once '../config/db.php';
@include_once '../lib/SimpleXLSXGen.php'; // Excel oluşturucu kütüphanesi

$sayfa_baslik = "Arşiv Yönetimi";
$mesaj = '';

// Sayfa yüklendiğinde session'daki mesajı al ve temizle
if (isset($_SESSION['mesaj'])) {
    $mesaj = $_SESSION['mesaj'];
    unset($_SESSION['mesaj']);
}

// Tarih filtreleri
$baslangic_tarihi = isset($_GET['baslangic_tarihi']) ? $_GET['baslangic_tarihi'] : '';
$bitis_tarihi = isset($_GET['bitis_tarihi']) ? $_GET['bitis_tarihi'] : '';

// Excel çıktısı için POST isteği
if (isset($_POST['excel_cikti']) && !empty($_POST['musabaka_ids'])) {
    // SimpleXLSXGen.php kütüphanesinin mevcut olup olmadığını kontrol et
    if (class_exists('Shuchkin\SimpleXLSXGen')) {
        $ids = $_POST['musabaka_ids'];
        $ids = array_map('intval', $ids);
        $ids = array_filter($ids, function($id) { return $id > 0; });
        
        if (!empty($ids)) {
            try {
                $placeholders = implode(',', array_fill(0, count($ids), '?'));
                $sql = "
                    SELECT m.*, l.ad as lig_adi, t1.ad as ev_sahibi, t2.ad as misafir,
                           s.ad as stadyum_adi, -- YENİ: Stadyum Adı Eklendi
                           h.ad as hakem_ad, h.soyad as hakem_soyad,
                           y1.ad as yrd1_ad, y1.soyad as yrd1_soyad,
                           y2.ad as yrd2_ad, y2.soyad as yrd2_soyad,
                           d.ad as dorduncu_ad, d.soyad as dorduncu_soyad,
                           g.ad as gozlemci_ad, g.soyad as gozlemci_soyad
                    FROM musabakalar m
                    LEFT JOIN ligler l ON m.lig_id = l.id
                    LEFT JOIN takimlar t1 ON m.ev_sahibi_id = t1.id
                    LEFT JOIN takimlar t2 ON m.misafir_id = t2.id
                    LEFT JOIN stadyumlar s ON m.stadyum_id = s.id -- YENİ: stadyumlar tablosu ile birleştirildi
                    LEFT JOIN users h ON m.hakem_id = h.id
                    LEFT JOIN users y1 ON m.yardimci_1_id = y1.id
                    LEFT JOIN users y2 ON m.yardimci_2_id = y2.id
                    LEFT JOIN users d ON m.dorduncu_hakem_id = d.id
                    LEFT JOIN users g ON m.gozlemci_id = g.id
                    WHERE m.arsiv = 1 AND m.id IN ($placeholders)
                    ORDER BY m.mac_no ASC
                ";
                $stmt = $pdo->prepare($sql);
                $stmt->execute($ids);
                $excel_verileri = $stmt->fetchAll();
                
                // Excel verilerini hazırla
                $excel_data = [];
                // YENİ: Stadyum sütunu başlığa eklendi
                $excel_data[] = [
                    'Maç No', 'Tarih', 'Saat', 'Lig', 'Ev Sahibi', 'Misafir', 'Stadyum',
                    'Hakem', '1. Yardımcı', '2. Yardımcı', '4. Hakem', 'Gözlemci', 'Durum'
                ];
                
                foreach ($excel_verileri as $musabaka) {
                    // YENİ: $musabaka->stadyum_adi veriye eklendi
                    $excel_data[] = [
                        $musabaka->mac_no,
                        date('d.m.Y', strtotime($musabaka->tarih)),
                        $musabaka->saat,
                        $musabaka->lig_adi,
                        $musabaka->ev_sahibi,
                        $musabaka->misafir,
                        $musabaka->stadyum_adi, 
                        $musabaka->hakem_ad ? htmlspecialchars($musabaka->hakem_ad . ' ' . $musabaka->hakem_soyad) : '-',
                        $musabaka->yrd1_ad ? htmlspecialchars($musabaka->yrd1_ad . ' ' . $musabaka->yrd1_soyad) : '-',
                        $musabaka->yrd2_ad ? htmlspecialchars($musabaka->yrd2_ad . ' ' . $musabaka->yrd2_soyad) : '-',
                        $musabaka->dorduncu_ad ? htmlspecialchars($musabaka->dorduncu_ad . ' ' . $musabaka->dorduncu_soyad) : '-',
                        $musabaka->gozlemci_ad ? htmlspecialchars($musabaka->gozlemci_ad . ' ' . $musabaka->gozlemci_soyad) : '-',
                        $musabaka->durum
                    ];
                }
                
                // Excel dosyasını oluştur ve indir
                $xlsx = Shuchkin\SimpleXLSXGen::fromArray($excel_data);
                $dosya_adi = 'arsiv_musabakalar_' . date('Y-m-d_His') . '.xlsx';
                $xlsx->downloadAs($dosya_adi);
                exit();
                
            } catch (PDOException $e) {
                error_log("Excel çıktı hatası: " . $e->getMessage());
                $_SESSION['mesaj'] = ['tip' => 'error', 'icerik' => 'Excel çıktısı alınırken bir hata oluştu.'];
                header("Location: arsiv_yonetimi.php?baslangic_tarihi=$baslangic_tarihi&bitis_tarihi=$bitis_tarihi");
                exit();
            }
        }
    } else {
         // Kütüphane bulunamadığında hata mesajı ver
        $_SESSION['mesaj'] = ['tip' => 'error', 'icerik' => 'Excel oluşturma kütüphanesi bulunamadı. Lütfen sistem yöneticisiyle iletişime geçin.'];
        header("Location: arsiv_yonetimi.php?baslangic_tarihi=$baslangic_tarihi&bitis_tarihi=$bitis_tarihi");
        exit();
    }
}

// Yardımcı fonksiyon: Müsabakayı ve ilişkili verileri siler
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

// Tekil Arşivden Çıkarma İşlemi
if (isset($_GET['action']) && $_GET['action'] == 'arsivden_cikar' && isset($_GET['id'])) {
    $musabaka_id = (int)$_GET['id'];
    try {
        $stmt = $pdo->prepare("UPDATE musabakalar SET arsiv = 0 WHERE id = ?");
        if ($stmt->execute([$musabaka_id])) {
            $_SESSION['mesaj'] = ['tip' => 'success', 'icerik' => 'Müsabaka başarıyla arşivden çıkarıldı.'];
        } else {
            $_SESSION['mesaj'] = ['tip' => 'error', 'icerik' => 'İşlem sırasında bir hata oluştu.'];
        }
    } catch (PDOException $e) {
        error_log("Arşivden çıkarma hatası: " . $e->getMessage());
        $_SESSION['mesaj'] = ['tip' => 'error', 'icerik' => 'Arşivden çıkarma sırasında bir veritabanı hatası oluştu.'];
    }
    header("Location: arsiv_yonetimi.php?baslangic_tarihi=$baslangic_tarihi&bitis_tarihi=$bitis_tarihi");
    exit();
}

// Tekil Silme İşlemi
if (isset($_GET['action']) && $_GET['action'] == 'sil' && isset($_GET['id'])) {
    $musabaka_id = (int)$_GET['id'];
    try {
        $pdo->beginTransaction();
        deleteMatch($pdo, $musabaka_id);
        $pdo->commit();
        $_SESSION['mesaj'] = ['tip' => 'success', 'icerik' => 'Müsabaka başarıyla silindi.'];
    } catch (Exception $e) {
        $pdo->rollBack();
        $_SESSION['mesaj'] = ['tip' => 'error', 'icerik' => 'Silme işlemi sırasında bir hata oluştu: ' . $e->getMessage()];
    }
    header("Location: arsiv_yonetimi.php?baslangic_tarihi=$baslangic_tarihi&bitis_tarihi=$bitis_tarihi");
    exit();
}

// Toplu İşlemler (Arşivden Çıkarma ve Silme)
if (isset($_POST['toplu_islem_uygula']) && !empty($_POST['musabaka_ids'])) {
    $ids = $_POST['musabaka_ids'];
    $islem = $_POST['toplu_islem'];

    $ids = array_map('intval', $ids);
    $ids = array_filter($ids, function($id) { return $id > 0; });
    if (empty($ids)) {
        $_SESSION['mesaj'] = ['tip' => 'error', 'icerik' => 'Geçerli müsabaka seçilmedi.'];
        header("Location: arsiv_yonetimi.php?baslangic_tarihi=$baslangic_tarihi&bitis_tarihi=$bitis_tarihi");
        exit();
    }

    if ($islem == 'arsivden_cikar') {
        try {
            $placeholders = implode(',', array_fill(0, count($ids), '?'));
            $stmt = $pdo->prepare("UPDATE musabakalar SET arsiv = 0 WHERE id IN ($placeholders)");
            if ($stmt->execute($ids)) {
                $_SESSION['mesaj'] = ['tip' => 'success', 'icerik' => count($ids) . ' adet müsabaka başarıyla arşivden çıkarıldı.'];
            } else {
                $_SESSION['mesaj'] = ['tip' => 'error', 'icerik' => 'Arşivden çıkarma sırasında bir hata oluştu.'];
            }
        } catch (PDOException $e) {
            error_log("Toplu arşivden çıkarma hatası: " . $e->getMessage());
            $_SESSION['mesaj'] = ['tip' => 'error', 'icerik' => 'Arşivden çıkarma sırasında bir hata oluştu.'];
        }
    } elseif ($islem == 'sil') {
        try {
            $pdo->beginTransaction();
            foreach($ids as $id) {
                deleteMatch($pdo, (int)$id);
            }
            $pdo->commit();
            $_SESSION['mesaj'] = ['tip' => 'success', 'icerik' => count($ids) . ' adet müsabaka başarıyla silindi.'];
        } catch (Exception $e) {
            $pdo->rollBack();
            error_log("Toplu silme hatası: " . $e->getMessage());
            $_SESSION['mesaj'] = ['tip' => 'error', 'icerik' => 'Toplu silme işlemi sırasında bir hata oluştu.'];
        }
    }
    header("Location: arsiv_yonetimi.php?baslangic_tarihi=$baslangic_tarihi&bitis_tarihi=$bitis_tarihi");
    exit();
}

// Arşivlenmiş müsabakaları listele
try {
    $sql = "
        SELECT m.*, l.ad as lig_adi, t1.ad as ev_sahibi, t2.ad as misafir,
               s.ad as stadyum_adi, -- YENİ: Stadyum Adı Eklendi
               h.ad as hakem_ad, h.soyad as hakem_soyad,
               y1.ad as yrd1_ad, y1.soyad as yrd1_soyad,
               y2.ad as yrd2_ad, y2.soyad as yrd2_soyad,
               d.ad as dorduncu_ad, d.soyad as dorduncu_soyad,
               g.ad as gozlemci_ad, g.soyad as gozlemci_soyad
        FROM musabakalar m
        LEFT JOIN ligler l ON m.lig_id = l.id
        LEFT JOIN takimlar t1 ON m.ev_sahibi_id = t1.id
        LEFT JOIN takimlar t2 ON m.misafir_id = t2.id
        LEFT JOIN stadyumlar s ON m.stadyum_id = s.id -- YENİ: stadyumlar tablosu ile birleştirildi
        LEFT JOIN users h ON m.hakem_id = h.id
        LEFT JOIN users y1 ON m.yardimci_1_id = y1.id
        LEFT JOIN users y2 ON m.yardimci_2_id = y2.id
        LEFT JOIN users d ON m.dorduncu_hakem_id = d.id
        LEFT JOIN users g ON m.gozlemci_id = g.id
        WHERE m.arsiv = 1
    ";
    
    $params = [];
    
    // Tarih filtrelerini ekle
    if (!empty($baslangic_tarihi)) {
        $sql .= " AND m.tarih >= ?";
        $params[] = $baslangic_tarihi;
    }
    
    if (!empty($bitis_tarihi)) {
        $sql .= " AND m.tarih <= ?";
        $params[] = $bitis_tarihi;
    }
    
    $sql .= " ORDER BY m.mac_no ASC";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $arsivlenmis_musabakalar = $stmt->fetchAll();
    
} catch (PDOException $e) {
    error_log("Arşiv verisi çekme hatası: " . $e->getMessage());
    $mesaj = ['tip' => 'error', 'icerik' => 'Arşiv listesi yüklenirken bir hata oluştu.'];
    $arsivlenmis_musabakalar = [];
}

include '../templates/header.php';
?>
<div class="container mx-auto px-4 py-6">
    <h1 class="text-2xl font-bold mb-6 text-gray-800"><?php echo $sayfa_baslik; ?></h1>
    
    <div class="bg-white p-4 rounded-lg shadow-md mb-6">
        <h2 class="text-lg font-semibold mb-3 text-gray-800">Tarih Filtresi</h2>
        <form method="GET" class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Başlangıç Tarihi</label>
                <input type="date" name="baslangic_tarihi" value="<?php echo htmlspecialchars($baslangic_tarihi); ?>" class="w-full p-2 border rounded">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Bitiş Tarihi</label>
                <input type="date" name="bitis_tarihi" value="<?php echo htmlspecialchars($bitis_tarihi); ?>" class="w-full p-2 border rounded">
            </div>
            <div class="flex items-end">
                <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded-md hover:bg-blue-700 mr-2">Filtrele</button>
                <a href="arsiv_yonetimi.php" class="bg-gray-300 text-gray-800 px-4 py-2 rounded-md hover:bg-gray-400">Temizle</a>
            </div>
        </form>
    </div>

    <div class="bg-white p-6 rounded-lg shadow-md">
        <h2 class="text-xl font-semibold mb-4 text-gray-800">Arşivlenmiş Müsabakalar</h2>
        
        <?php if ($mesaj): ?>
            <div class="p-4 mb-4 text-sm rounded-lg <?php echo $mesaj['tip'] === 'success' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'; ?>">
                <?php echo htmlspecialchars($mesaj['icerik']); ?>
            </div>
        <?php endif; ?>
        
        <?php if (empty($arsivlenmis_musabakalar)): ?>
            <p class="text-gray-600">Arşivde müsabaka bulunmamaktadır.</p>
        <?php else: ?>
            <form method="POST" id="toplu_islem_form">
                <div class="flex flex-wrap gap-2 mb-4">
                    <select name="toplu_islem" class="border p-2 rounded">
                        <option value="arsivden_cikar">Seçilenleri Arşivden Çıkar</option>
                        <option value="sil">Seçilenleri Sil</option>
                    </select>
                    <button type="submit" name="toplu_islem_uygula" class="bg-gray-600 text-white px-4 py-2 rounded-md hover:bg-gray-700">Uygula</button>
                    <button type="submit" name="excel_cikti" class="bg-green-600 text-white px-4 py-2 rounded-md hover:bg-green-700">
                        <i class="fas fa-file-excel mr-1"></i> Excel/CSV Çıktısı
                    </button>
                </div>
                <div class="overflow-x-auto">
                    <table class="min-w-full bg-white border border-gray-200 text-sm">
                        <thead>
                            <tr class="bg-gray-100">
                                <th class="py-2 px-2 border-b text-left w-6"><input type="checkbox" id="selectAll"></th>
                                <th class="py-2 px-4 border-b text-left">Maç No</th>
                                <th class="py-2 px-2 border-b text-left">Tarih</th>
                                <th class="py-2 px-2 border-b text-left">Müsabaka</th>
                                <th class="py-2 px-2 border-b text-left">Lig</th>
                                <th class="py-2 px-2 border-b text-left">Stadyum</th> <th class="py-2 px-2 border-b text-left">Hakem Ekibi</th>
                                <th class="py-2 px-2 border-b text-left">Durum</th>
                                <th class="py-2 px-2 border-b text-left w-20">İşlemler</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach($arsivlenmis_musabakalar as $musabaka): ?>
                                <tr class="border-b hover:bg-gray-50">
                                    <td class="py-2 px-2"><input type="checkbox" name="musabaka_ids[]" value="<?php echo $musabaka->id; ?>" class="musabaka-checkbox"></td>
                                    <td class="py-2 px-4 border-b text-center font-medium"><?php echo htmlspecialchars($musabaka->mac_no); ?></td>
                                    <td class="py-2 px-2 whitespace-nowrap"><?php echo date('d.m.Y H:i', strtotime("$musabaka->tarih $musabaka->saat")); ?></td>
                                    <td class="py-2 px-2 font-medium"><?php echo htmlspecialchars($musabaka->ev_sahibi . ' - ' . $musabaka->misafir); ?></td>
                                    <td class="py-2 px-2"><?php echo htmlspecialchars($musabaka->lig_adi); ?></td>
                                    <td class="py-2 px-2"><?php echo htmlspecialchars($musabaka->stadyum_adi); ?></td> <td class="py-2 px-2">
                                        <ul class="list-disc list-inside space-y-1 text-xs">
                                            <li><strong class="font-normal">Hk:</strong> <?php echo $musabaka->hakem_ad ? htmlspecialchars($musabaka->hakem_ad . ' ' . $musabaka->hakem_soyad) : '-'; ?></li>
                                            <li><strong class="font-normal">Yrd 1:</strong> <?php echo $musabaka->yrd1_ad ? htmlspecialchars($musabaka->yrd1_ad . ' ' . $musabaka->yrd1_soyad) : '-'; ?></li>
                                            <li><strong class="font-normal">Yrd 2:</strong> <?php echo $musabaka->yrd2_ad ? htmlspecialchars($musabaka->yrd2_ad . ' ' . $musabaka->yrd2_soyad) : '-'; ?></li>
                                            <li><strong class="font-normal">4. Hk:</strong> <?php echo $musabaka->dorduncu_ad ? htmlspecialchars($musabaka->dorduncu_ad . ' ' . $musabaka->dorduncu_soyad) : '-'; ?></li>
                                            <li><strong class="font-normal">Göz:</strong> <?php echo $musabaka->gozlemci_ad ? htmlspecialchars($musabaka->gozlemci_ad . ' ' . $musabaka->gozlemci_soyad) : '-'; ?></li>
                                        </ul>
                                    </td>
                                    <td class="py-2 px-2"><?php echo htmlspecialchars($musabaka->durum); ?></td>
                                    <td class="py-2 px-2 text-center">
                                        <div class="flex flex-col space-y-2">
                                            <a href="../musabaka_detay.php?id=<?php echo $musabaka->id; ?>" class="text-blue-600 hover:text-blue-800" title="Detay"><i class="fas fa-eye"></i></a>
                                            <a href="?action=arsivden_cikar&id=<?php echo $musabaka->id; ?>&baslangic_tarihi=<?php echo urlencode($baslangic_tarihi); ?>&bitis_tarihi=<?php echo urlencode($bitis_tarihi); ?>" class="text-green-600 hover:text-green-800" title="Arşivden Çıkar" onclick="return confirm('Bu müsabakayı arşivden çıkarmak istediğinizden emin misiniz?')"><i class="fas fa-box-open"></i></a>
                                            <a href="?action=sil&id=<?php echo $musabaka->id; ?>&baslangic_tarihi=<?php echo urlencode($baslangic_tarihi); ?>&bitis_tarihi=<?php echo urlencode($bitis_tarihi); ?>" class="text-red-600 hover:text-red-800" title="Sil" onclick="return confirm('Bu müsabakayı ve ilişkili tüm verileri (raporlar vb.) kalıcı olarak silmek istediğinizden emin misiniz?')"><i class="fas fa-trash"></i></a>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </form>
        <?php endif; ?>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const selectAllCheckbox = document.getElementById('selectAll');
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
    });

    // Toplu işlem formu gönderim kontrolü
    if (topluIslemForm) {
        topluIslemForm.addEventListener('submit', function(e) {
            var checkedBoxes = document.querySelectorAll('.musabaka-checkbox:checked');
            
            // Excel çıktısı butonuna basıldıysa
            if (e.submitter && e.submitter.name === 'excel_cikti') {
                if (checkedBoxes.length === 0) {
                    e.preventDefault();
                    alert('Lütfen çıktı almak için en az bir müsabaka seçin.');
                }
                return;
            }
            
            // Diğer toplu işlemler için
            if (checkedBoxes.length === 0) {
                e.preventDefault();
                alert('Lütfen işlem yapmak için en az bir müsabaka seçin.');
            } else {
                var islem = document.querySelector('select[name=\"toplu_islem\"]').value;
                if (islem === 'sil' && !confirm('Seçili ' + checkedBoxes.length + ' müsabakayı silmek istediğinize emin misiniz? Bu işlem geri alınamaz.')) {
                    e.preventDefault();
                }
            }
        });
    }
});
</script>
<?php include '../templates/footer.php'; ?>