<?php
ob_start();
require_once '../config/session_check_admin.php'; 
require_once '../config/db.php';

date_default_timezone_set('Europe/Istanbul'); 

$sayfa_baslik = "Disiplin Raporu Olan Müsabakalar";

// Filtreleme
$secili_lig_id = $_GET['lig_id'] ?? '';
$secili_gun = $_GET['gun'] ?? '';

$gunler = [
    'Pazartesi' => 2, 'Salı' => 3, 'Çarşamba' => 4, 'Perşembe' => 5, 
    'Cuma' => 6, 'Cumartesi' => 7, 'Pazar' => 1
];

// Ligler
$ligler = $pdo->query("SELECT id, ad FROM ligler ORDER BY ad ASC")->fetchAll(PDO::FETCH_OBJ);

// KESİN VE DÜZELTİLMİŞ SORGUNUN BAŞLANGICI
$query = "
    SELECT m.*, l.ad AS lig_adi, t1.ad AS ev_sahibi, t2.ad AS misafir,
           r.rapor_dosya_yolu, r.id as rapor_id,
           CONCAT(g.ad, ' ', g.soyad) AS gozlemci_adi,
           CONCAT(h.ad, ' ', h.soyad) AS hakem_adi
    FROM musabakalar m
    LEFT JOIN ligler l ON m.lig_id = l.id
    LEFT JOIN takimlar t1 ON m.ev_sahibi_id = t1.id
    LEFT JOIN takimlar t2 ON m.misafir_id = t2.id
    INNER JOIN raporlar r ON m.id = r.musabaka_id
    LEFT JOIN users g ON m.gozlemci_id = g.id
    LEFT JOIN users h ON m.hakem_id = h.id
    WHERE EXISTS (SELECT 1 FROM disiplin_raporlari dr WHERE dr.rapor_id = r.id)
";

$params = [];
if (!empty($secili_lig_id)) {
    $query .= " AND m.lig_id = ?";
    $params[] = $secili_lig_id;
}
if (!empty($secili_gun)) {
    $query .= " AND DAYOFWEEK(m.tarih) = ?";
    $params[] = $gunler[$secili_gun];
}

$query .= " ORDER BY m.arsiv ASC, m.tarih DESC, m.saat DESC";

$musabakalar = $pdo->prepare($query);
$musabakalar->execute($params);
$musabakalar = $musabakalar->fetchAll(PDO::FETCH_OBJ);

include '../templates/header.php';
?>

<div class="container mx-auto px-4 py-6">
    <h1 class="text-2xl font-bold mb-6"><?php echo $sayfa_baslik; ?></h1>
    
    <?php if (isset($_SESSION['mesaj'])): ?>
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
            <?php echo $_SESSION['mesaj']; unset($_SESSION['mesaj']); ?>
        </div>
    <?php endif; ?>
    
    <div class="bg-white p-6 rounded-lg shadow-md mb-6">
        <form method="GET" class="grid grid-cols-1 md:grid-cols-3 gap-4 items-end">
            <div>
                <label class="block text-sm font-medium mb-1">Lig:</label>
                <select name="lig_id" class="w-full border rounded p-2">
                    <option value="">Tüm Ligler</option>
                    <?php foreach($ligler as $lig): ?>
                        <option value="<?php echo $lig->id; ?>" <?php echo ($secili_lig_id == $lig->id) ? 'selected' : ''; ?>>
                            <?php echo $lig->ad; ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <div>
                <label class="block text-sm font-medium mb-1">Gün:</label>
                <select name="gun" class="w-full border rounded p-2">
                    <option value="">Tüm Günler</option>
                    <?php foreach($gunler as $gun_adi => $gun_sayi): ?>
                        <option value="<?php echo $gun_adi; ?>" <?php echo ($secili_gun == $gun_adi) ? 'selected' : ''; ?>>
                            <?php echo $gun_adi; ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="flex space-x-2">
                <button type="submit" class="bg-blue-600 text-white py-2 px-4 rounded hover:bg-blue-700 w-full">Filtrele</button>
                <a href="disiplin_raporu_listesi.php" class="bg-gray-200 text-gray-700 py-2 px-4 rounded hover:bg-gray-300 w-full text-center">Temizle</a>
            </div>
        </form>
    </div>
    
    <div class="bg-white rounded-lg shadow-md">
        <h2 class="text-xl font-semibold p-6 border-b">Disiplin Raporlu Müsabakalar (<?php echo count($musabakalar); ?>)</h2>
        
        <div class="overflow-x-auto">
            <table class="min-w-full">
                <thead>
                    <tr class="bg-gray-100">
                        <th class="py-3 px-4 text-left">Durum</th>
                        <th class="py-3 px-4 text-left">Tarih</th>
                        <th class="py-3 px-4 text-left">Müsabaka</th>
                        <th class="py-3 px-4 text-left">Lig</th>
                        <th class="py-3 px-4 text-left">Gözlemci</th>
                        <th class="py-3 px-4 text-left">Hakem</th>
                        <th class="py-3 px-4 text-center">Raporlar</th>
                        <th class="py-3 px-4 text-center">Onay Durumu</th>
                        <th class="py-3 px-4 text-center">İşlem</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($musabakalar)): ?>
                        <tr>
                            <td colspan="9" class="text-center py-4 text-gray-500">Müsabaka bulunamadı.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($musabakalar as $musabaka): ?>
                            <?php
                            // Onay durumu - TÜM raporların durumunu kontrol et
                            $durum_stmt = $pdo->prepare("
                                SELECT COUNT(*) as toplam,
                                       SUM(CASE WHEN durum = 'onaylandi' THEN 1 ELSE 0 END) as onaylanan,
                                       SUM(CASE WHEN durum = 'reddedildi' THEN 1 ELSE 0 END) as reddedilen,
                                       SUM(CASE WHEN durum = 'beklemede' THEN 1 ELSE 0 END) as bekleyen
                                FROM disiplin_raporlari 
                                WHERE rapor_id = ?
                            ");
                            $durum_stmt->execute([$musabaka->rapor_id]);
                            $durum_istatistik = $durum_stmt->fetch(PDO::FETCH_OBJ);
                            
                            // Rapor sayısı ve tipi
                            $rapor_tipleri_stmt = $pdo->prepare("SELECT rapor_tipi, COUNT(*) as sayi FROM disiplin_raporlari WHERE rapor_id = ? GROUP BY rapor_tipi");
                            $rapor_tipleri_stmt->execute([$musabaka->rapor_id]);
                            $rapor_tipleri = $rapor_tipleri_stmt->fetchAll(PDO::FETCH_OBJ);
                            
                            // Arşiv durumuna göre CSS class'ı belirle
                            $row_class = $musabaka->arsiv == 1 ? 'bg-gray-100 text-gray-400' : 'hover:bg-gray-50';
                            $text_class = $musabaka->arsiv == 1 ? 'text-gray-400' : '';
                            ?>
                            
                            <tr class="border-b <?php echo $row_class; ?>">
                                <td class="py-3 px-4 text-center">
                                    <?php if ($musabaka->arsiv == 1): ?>
                                        <span class="bg-gray-500 text-white text-xs px-2 py-1 rounded" title="Arşivlenmiş">ARŞİV</span>
                                    <?php else: ?>
                                        <span class="bg-green-500 text-white text-xs px-2 py-1 rounded" title="Aktif">AKTİF</span>
                                    <?php endif; ?>
                                </td>
                                <td class="py-3 px-4 <?php echo $text_class; ?>">
                                    <div class="font-medium"><?php echo date('d.m.Y', strtotime($musabaka->tarih)); ?></div>
                                    <div class="text-sm"><?php echo date('H:i', strtotime($musabaka->saat)); ?></div>
                                </td>
                                <td class="py-3 px-4 font-medium <?php echo $text_class; ?>"><?php echo $musabaka->ev_sahibi . ' - ' . $musabaka->misafir; ?></td>
                                <td class="py-3 px-4 <?php echo $text_class; ?>"><?php echo $musabaka->lig_adi; ?></td>
                                
                                <td class="py-3 px-4 text-sm <?php echo $text_class; ?>"><?php echo $musabaka->gozlemci_adi ?? '-'; ?></td>
                                <td class="py-3 px-4 text-sm <?php echo $text_class; ?>"><?php echo $musabaka->hakem_adi ?? '-'; ?></td>
                                <td class="py-3 px-4 text-center">
                                    <?php if (!empty($musabaka->rapor_dosya_yolu)): ?>
                                        <a href="../<?php echo $musabaka->rapor_dosya_yolu; ?>" target="_blank" class="text-blue-600 hover:text-blue-800 mx-1 <?php echo $musabaka->arsiv == 1 ? 'opacity-50' : ''; ?>" title="Excel">
                                            <i class="fas fa-file-excel"></i>
                                        </a>
                                    <?php endif; ?>
                                    
                                    <?php foreach ($rapor_tipleri as $tip): ?>
                                        <button onclick="raporlariGoster(<?php echo $musabaka->id; ?>, '<?php echo $tip->rapor_tipi; ?>')" 
                                                class="<?php echo $tip->rapor_tipi == 'gozlemci' ? 'text-green-600 hover:text-green-800' : 'text-red-600 hover:text-red-800'; ?> mx-1 cursor-pointer <?php echo $musabaka->arsiv == 1 ? 'opacity-70' : ''; ?>" 
                                                title="<?php echo ucfirst($tip->rapor_tipi); ?> Raporları (<?php echo $tip->sayi; ?>)">
                                            <i class="fas fa-file-image"></i>
                                            <span class="text-xs <?php echo $tip->rapor_tipi == 'gozlemci' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'; ?> px-1 rounded">
                                                <?php echo $tip->sayi; ?>
                                            </span>
                                        </button>
                                    <?php endforeach; ?>
                                </td>
                                
                                <td class="py-3 px-4 text-center">
                                    <?php 
                                    // NET ve MANTIKLI durum kontrolü
                                    if ($durum_istatistik->bekleyen > 0): 
                                        // Hala bekleyen raporlar var
                                    ?>
                                        <span class="text-yellow-600 font-bold">BEKLİYOR</span>
                                        <?php if ($durum_istatistik->toplam > 1): ?>
                                            <br><span class="text-xs text-gray-500">
                                                (<?php echo $durum_istatistik->bekleyen; ?>/<?php echo $durum_istatistik->toplam; ?> beklemede)
                                            </span>
                                        <?php endif; ?>
                                    <?php elseif ($durum_istatistik->reddedilen > 0): ?>
                                        <span class="text-red-600 font-bold">REDDEDİLDİ</span>
									<?php else: ?>
									    <span class="text-green-600 font-bold">ONAYLANDI</span>
									<?php endif; ?>
                                </td>
                                
                                <td class="py-3 px-4 text-center">
                                    <a href="rapor_onayla.php?id=<?php echo $musabaka->id; ?>&islem=onayla" 
                                       class="text-green-600 hover:text-green-800 mx-1 <?php echo $musabaka->arsiv == 1 ? 'opacity-70' : ''; ?>" 
                                       title="Onayla"
                                       onclick="return confirm('Onaylamak istediğinizden emin misiniz?')">
                                        <i class="fas fa-check-circle"></i>
                                    </a>
                                    <a href="rapor_onayla.php?id=<?php echo $musabaka->id; ?>&islem=reddet" 
                                       class="text-red-600 hover:text-red-800 mx-1 <?php echo $musabaka->arsiv == 1 ? 'opacity-70' : ''; ?>" 
                                       title="Reddet"
                                       onclick="return confirm('Reddetmek istediğinizden emin misiniz?')">
                                        <i class="fas fa-times-circle"></i>
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<div id="disiplinModal" class="hidden fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
    <div class="relative top-10 mx-auto p-5 border w-11/12 md:w-3/4 lg:w-1/2 shadow-lg rounded-md bg-white">
        <div class="flex justify-between items-center mb-4">
            <h3 class="text-xl font-semibold text-gray-900" id="modalBaslik">Disiplin Raporları</h3>
            <button onclick="modalKapat()" class="text-gray-400 hover:text-gray-600 text-2xl font-bold">
                <i class="fas fa-times"></i>
            </button>
        </div>
        
        <div id="modalIcerik" class="mt-4">
            <div class="flex justify-center items-center py-8">
                <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-blue-600"></div>
            </div>
        </div>
        
        <div class="mt-4 flex justify-end">
            <button onclick="modalKapat()" class="bg-gray-500 hover:bg-gray-600 text-white font-bold py-2 px-4 rounded">
                Kapat
            </button>
        </div>
    </div>
</div>

<script>
function raporlariGoster(musabakaId, raporTipi) {
    const modal = document.getElementById('disiplinModal');
    const modalBaslik = document.getElementById('modalBaslik');
    const modalIcerik = document.getElementById('modalIcerik');
    
    // Modal başlığını güncelle
    const tipAdi = raporTipi === 'gozlemci' ? 'Gözlemci' : 'Hakem';
    modalBaslik.textContent = tipAdi + ' Disiplin Raporları';
    
    // Loading göster
    modalIcerik.innerHTML = `
        <div class="flex justify-center items-center py-8">
            <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-blue-600"></div>
        </div>
    `;
    
    // Modalı aç
    modal.classList.remove('hidden');
    
    // AJAX ile raporları yükle
    fetch(`get_disiplin_raporlari.php?musabaka_id=${musabakaId}&rapor_tipi=${raporTipi}`)
        .then(response => {
            if (!response.ok) {
                throw new Error('Network response was not ok');
            }
            return response.text();
        })
        .then(data => {
            modalIcerik.innerHTML = data;
        })
        .catch(error => {
            console.error('Hata:', error);
            modalIcerik.innerHTML = `
                <div class="text-center py-8">
                    <p class="text-red-500 text-lg mb-2">Raporlar yüklenirken bir hata oluştu</p>
                    <p class="text-gray-500 text-sm">Lütfen sayfayı yenileyip tekrar deneyin</p>
                </div>
            `;
        });
}

function modalKapat() {
    const modal = document.getElementById('disiplinModal');
    modal.classList.add('hidden');
}

// Modal dışına tıklandığında kapat
document.getElementById('disiplinModal').addEventListener('click', function(e) {
    if (e.target === this) {
        modalKapat();
    }
});

// ESC tuşu ile kapat
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        modalKapat();
    }
});

// Sayfa yüklendiğinde event listener'ları kontrol et
document.addEventListener('DOMContentLoaded', function() {
    console.log('Sayfa yüklendi - rapor butonları aktif');
});
</script>

<?php include '../templates/footer.php'; ?>
