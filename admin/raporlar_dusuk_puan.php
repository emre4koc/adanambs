<?php
require_once '../config/session_check_admin.php';
require_once '../config/db.php';

$sayfa_baslik = "Düşük Puanlı Hakem Raporları";
$mesaj = '';

// Sayfa yüklendiğinde session'daki mesajı al ve temizle
if (isset($_SESSION['mesaj'])) {
    $mesaj = $_SESSION['mesaj'];
    unset($_SESSION['mesaj']);
}

// YENİ - Düşük puanlı raporu listeden kaldırma işlemi
if (isset($_GET['action']) && $_GET['action'] == 'kaldir' && isset($_GET['id'])) {
    $rapor_id = (int)$_GET['id'];
    try {
        $stmt = $pdo->prepare("UPDATE rapor_detaylari SET listeden_kaldir = 1 WHERE id = ?");
        if ($stmt->execute([$rapor_id])) {
            $_SESSION['mesaj'] = ['tip' => 'success', 'icerik' => 'Rapor listeden başarıyla kaldırıldı.'];
        } else {
            $_SESSION['mesaj'] = ['tip' => 'error', 'icerik' => 'Rapor kaldırılırken bir hata oluştu.'];
        }
    } catch (PDOException $e) {
        error_log("Rapor kaldırma hatası: " . $e->getMessage());
        $_SESSION['mesaj'] = ['tip' => 'error', 'icerik' => 'Rapor kaldırma sırasında veritabanı hatası oluştu.'];
    }
    header("Location: raporlar_dusuk_puan.php");
    exit();
}

// SQL sorgusu ile 7.9 ve altı puan almış, listeden kaldırılmamış hakemleri ve ilgili müsabaka bilgilerini çek
try {
    $sql = "
        SELECT 
            rd.id,
            rd.puan,
            rd.not,
            u.ad AS hakem_ad,
            u.soyad AS hakem_soyad,
            m.tarih,
            m.saat,
            m.mac_no,
            t1.ad AS ev_sahibi,
            t2.ad AS misafir,
            l.ad AS lig_adi,
            g.ad AS gozlemci_ad,
            g.soyad AS gozlemci_soyad
        FROM rapor_detaylari rd
        JOIN users u ON rd.hakem_id = u.id
        JOIN raporlar r ON rd.rapor_id = r.id
        JOIN musabakalar m ON r.musabaka_id = m.id
        LEFT JOIN takimlar t1 ON m.ev_sahibi_id = t1.id
        LEFT JOIN takimlar t2 ON m.misafir_id = t2.id
        LEFT JOIN ligler l ON m.lig_id = l.id
        LEFT JOIN users g ON m.gozlemci_id = g.id
        WHERE rd.puan <= 7.9 AND rd.listeden_kaldir = 0
        ORDER BY rd.puan ASC, m.tarih DESC
    ";
    $dusuk_puanli_raporlar = $pdo->query($sql)->fetchAll();

} catch (PDOException $e) {
    error_log("Düşük puanlı raporlar çekme hatası: " . $e->getMessage());
    $mesaj = ['tip' => 'error', 'icerik' => 'Raporlar listesi yüklenirken bir hata oluştu.'];
    $dusuk_puanli_raporlar = [];
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

    <div class="bg-white p-6 rounded-lg shadow-md">
        <h2 class="text-xl font-semibold mb-4 text-gray-800">7.9 ve Altı Puan Alan Hakemler</h2>
        
        <?php if (empty($dusuk_puanli_raporlar)): ?>
            <p class="text-gray-600">7.9 ve altında puan alan hakem bulunmamaktadır.</p>
        <?php else: ?>
            <div class="overflow-x-auto">
                <table class="min-w-full bg-white border border-gray-200 text-sm">
                    <thead>
                        <tr class="bg-gray-100">
                            <th class="py-2 px-4 border-b text-left">Hakem</th>
                            <th class="py-2 px-4 border-b text-left">Puan</th>
                            <th class="py-2 px-4 border-b text-left">Müsabaka</th>
                            <th class="py-2 px-4 border-b text-left">Tarih</th>
                            <th class="py-2 px-4 border-b text-left">Gözlemci</th>
                            <th class="py-2 px-4 border-b text-left">Açıklama/Not</th>
                            <th class="py-2 px-4 border-b text-center">İşlem</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($dusuk_puanli_raporlar as $rapor): ?>
                            <tr class="hover:bg-gray-50">
                                <td class="py-2 px-4 border-b"><?php echo htmlspecialchars($rapor->hakem_ad . ' ' . $rapor->hakem_soyad); ?></td>
                                <td class="py-2 px-4 border-b font-bold text-red-600"><?php echo number_format($rapor->puan, 1); ?></td>
                                <td class="py-2 px-4 border-b">
                                    <div class="font-medium"><?php echo htmlspecialchars($rapor->ev_sahibi . ' - ' . $rapor->misafir); ?></div>
                                    <div class="text-xs text-gray-500">Lig: <?php echo htmlspecialchars($rapor->lig_adi); ?></div>
                                </td>
                                <td class="py-2 px-4 border-b whitespace-nowrap"><?php echo date('d.m.Y H:i', strtotime($rapor->tarih . ' ' . $rapor->saat)); ?></td>
                                <td class="py-2 px-4 border-b"><?php echo $rapor->gozlemci_ad ? htmlspecialchars($rapor->gozlemci_ad . ' ' . $rapor->gozlemci_soyad) : '-'; ?></td>
                                <td class="py-2 px-4 border-b text-xs"><?php echo !empty($rapor->not) ? htmlspecialchars($rapor->not) : '-'; ?></td>
                                <td class="py-2 px-4 border-b text-center">
                                    <a href="?action=kaldir&id=<?php echo $rapor->id; ?>" class="text-red-500 hover:text-red-700" title="Listeden Kaldır" onclick="return confirm('Bu raporu listeden kaldırmak istediğinize emin misiniz? Bu işlem geri alınamaz.');">
                                        <i class="fas fa-trash-alt"></i>
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php include '../templates/footer.php'; ?>