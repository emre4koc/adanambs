<?php
require_once '../config/session_check_admin.php';
require_once '../config/db.php';

$sayfa_baslik = "Eğitim İstatistikleri";
$egitim_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if (!$egitim_id) {
    header("Location: egitim_yonetimi.php");
    exit();
}

// Eğitim bilgilerini çek
$stmt = $pdo->prepare("SELECT * FROM egitimler WHERE id = ?");
$stmt->execute([$egitim_id]);
$egitim = $stmt->fetch();

if (!$egitim) {
    header("Location: egitim_yonetimi.php");
    exit();
}

// DÜZELTİLMİŞ SORGU: Sütun adı 'goruntulenme_tarihi' olarak güncellendi.
// Roller: 1:Yönetici, 2:Hakem, 3:Gözlemci
$stmt = $pdo->prepare("
    SELECT 
        eg.*,
        CONCAT(u.ad, ' ', u.soyad) as kullanici_adi,
        u.email,
        CASE 
            WHEN u.rol = 1 THEN 'Yönetici'
            WHEN u.rol = 2 THEN 'Hakem'
            WHEN u.rol = 3 THEN 'Gözlemci'
            ELSE 'Belirsiz'
        END as rol_adi
    FROM egitim_goruntulemeler eg
    JOIN users u ON eg.user_id = u.id
    WHERE eg.egitim_id = ?
    ORDER BY eg.goruntulenme_tarihi DESC
");
$stmt->execute([$egitim_id]);
$izlemeler = $stmt->fetchAll();

// Genel istatistikler
$toplam_izleyen = count($izlemeler);
$tamamlayan_sayisi = count(array_filter($izlemeler, function($i) { return $i->tamamlandi == 1; }));
$ortalama_ilerleme = $toplam_izleyen > 0 ? round(array_sum(array_column($izlemeler, 'ilerleme_yuzdesi')) / $toplam_izleyen) : 0;
$toplam_izlenme_suresi = array_sum(array_column($izlemeler, 'toplam_izlenme_suresi'));

include '../templates/header.php';
?>

<div class="container mx-auto px-4 py-8">
    <div class="mb-6">
        <a href="egitim_yonetimi.php" class="text-blue-600 hover:underline">
            <i class="fas fa-arrow-left mr-2"></i>Eğitim Yönetimine Dön
        </a>
    </div>

    <div class="bg-white rounded-lg shadow-lg overflow-hidden mb-8 border border-gray-200">
        <div class="bg-blue-600 text-white p-6">
            <h1 class="text-2xl font-bold"><?php echo htmlspecialchars($egitim->baslik); ?></h1>
            <p class="text-sm opacity-80 mt-1">Eğitim İstatistik Detayları</p>
        </div>
        
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4 p-6 bg-gray-50">
            <div class="bg-white p-4 rounded shadow-sm border-b-4 border-blue-500 text-center">
                <div class="text-gray-500 text-xs uppercase font-bold tracking-wider">İzleyen Sayısı</div>
                <div class="text-2xl font-bold mt-1"><?php echo $toplam_izleyen; ?></div>
            </div>
            <div class="bg-white p-4 rounded shadow-sm border-b-4 border-green-500 text-center">
                <div class="text-gray-500 text-xs uppercase font-bold tracking-wider">Tamamlayan</div>
                <div class="text-2xl font-bold mt-1"><?php echo $tamamlayan_sayisi; ?></div>
            </div>
            <div class="bg-white p-4 rounded shadow-sm border-b-4 border-orange-500 text-center">
                <div class="text-gray-500 text-xs uppercase font-bold tracking-wider">Ort. İlerleme</div>
                <div class="text-2xl font-bold mt-1">%<?php echo $ortalama_ilerleme; ?></div>
            </div>
            <div class="bg-white p-4 rounded shadow-sm border-b-4 border-purple-500 text-center">
                <div class="text-gray-500 text-xs uppercase font-bold tracking-wider">Top. İzleme</div>
                <div class="text-2xl font-bold mt-1"><?php echo floor($toplam_izlenme_suresi / 60); ?> dk</div>
            </div>
        </div>
    </div>

    <div class="bg-white rounded-lg shadow-md overflow-hidden border border-gray-200">
        <div class="overflow-x-auto">
            <table class="min-w-full">
                <thead>
                    <tr class="bg-gray-100 text-gray-700 text-sm">
                        <th class="py-4 px-6 text-left">Kullanıcı Bilgisi</th>
                        <th class="py-4 px-6 text-left">Rol</th>
                        <th class="py-4 px-6 text-center">İlerleme</th>
                        <th class="py-4 px-6 text-center">Süre (Dk)</th>
                        <th class="py-4 px-6 text-center">Son İşlem Tarihi</th>
                        <th class="py-4 px-6 text-center">Durum</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    <?php if (empty($izlemeler)): ?>
                        <tr><td colspan="6" class="p-12 text-center text-gray-500 italic">Henüz kayıt bulunmuyor.</td></tr>
                    <?php else: ?>
                        <?php foreach($izlemeler as $izleme): ?>
                            <tr class="hover:bg-blue-50/30 transition-colors">
                                <td class="py-4 px-6">
                                    <div class="font-bold text-gray-800"><?php echo htmlspecialchars($izleme->kullanici_adi); ?></div>
                                    <div class="text-xs text-gray-500"><?php echo htmlspecialchars($izleme->email); ?></div>
                                </td>
                                <td class="py-4 px-6">
                                    <span class="px-2 py-1 rounded text-xs font-semibold bg-gray-200 text-gray-700">
                                        <?php echo $izleme->rol_adi; ?>
                                    </span>
                                </td>
                                <td class="py-4 px-6 text-center">
                                    <div class="flex flex-col items-center">
                                        <div class="w-20 bg-gray-200 rounded-full h-2">
                                            <div class="bg-blue-600 h-2 rounded-full" style="width: <?php echo $izleme->ilerleme_yuzdesi; ?>%"></div>
                                        </div>
                                        <span class="text-xs font-bold mt-1">%<?php echo $izleme->ilerleme_yuzdesi; ?></span>
                                    </div>
                                </td>
                                <td class="py-4 px-6 text-center text-sm">
                                    <?php echo round($izleme->toplam_izlenme_suresi / 60, 1); ?> dk
                                </td>
                                <td class="py-4 px-6 text-center text-xs text-gray-600">
                                    <?php echo date('d.m.Y H:i', strtotime($izleme->goruntulenme_tarihi)); ?>
                                </td>
                                <td class="py-4 px-6 text-center">
                                    <?php if ($izleme->tamamlandi): ?>
                                        <span class="bg-green-100 text-green-700 py-1 px-3 rounded-full text-xs font-bold">Tamamlandı</span>
                                    <?php else: ?>
                                        <span class="bg-yellow-100 text-yellow-700 py-1 px-3 rounded-full text-xs font-bold">İşlemde</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php include '../templates/footer.php'; ?>