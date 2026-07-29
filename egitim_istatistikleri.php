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

// Detaylı izleme istatistiklerini çek
$stmt = $pdo->prepare("
    SELECT 
        eg.*,
        CONCAT(u.ad, ' ', u.soyad) as kullanici_adi,
        u.email,
        r.rol_adi
    FROM egitim_goruntulemeler eg
    JOIN users u ON eg.user_id = u.id
    LEFT JOIN roller r ON u.rol = r.id
    WHERE eg.egitim_id = ?
    ORDER BY eg.son_goruntulenme_tarihi DESC
");
$stmt->execute([$egitim_id]);
$izlemeler = $stmt->fetchAll();

// Genel istatistikler
$toplam_izleyen = count($izlemeler);
$tamamlayan_sayisi = count(array_filter($izlemeler, function($i) { return $i->tamamlandi; }));
$ortalama_ilerleme = $toplam_izleyen > 0 ? round(array_sum(array_column($izlemeler, 'ilerleme_yuzdesi')) / $toplam_izleyen) : 0;
$toplam_izlenme_suresi = array_sum(array_column($izlemeler, 'toplam_izlenme_suresi'));

include '../templates/header.php';
?>

<div class="container mx-auto px-4">
    <div class="mb-6">
        <a href="egitim_yonetimi.php" class="text-blue-600 hover:text-blue-800">
            <i class="fas fa-arrow-left mr-2"></i>Eğitim Yönetimine Dön
        </a>
    </div>

    <div class="bg-white rounded-lg shadow-lg overflow-hidden mb-6">
        <div class="bg-gradient-to-r from-blue-600 to-blue-700 text-white p-6">
            <h1 class="text-3xl font-bold mb-2">
                <i class="fas fa-chart-bar mr-2"></i><?php echo htmlspecialchars($egitim->baslik); ?>
            </h1>
            <p class="text-sm opacity-90">
                <?php echo $egitim->tip == 'video' ? 'Video Eğitim' : 'Sunum Eğitimi'; ?> | 
                <?php echo date('d.m.Y', strtotime($egitim->yukleme_tarihi)); ?>
            </p>
        </div>

        <!-- Genel İstatistikler -->
        <div class="p-6 grid grid-cols-1 md:grid-cols-4 gap-4">
            <div class="bg-blue-50 p-4 rounded-lg">
                <div class="text-blue-600 text-2xl font-bold"><?php echo $toplam_izleyen; ?></div>
                <div class="text-sm text-gray-600">Toplam İzleyen</div>
            </div>
            <div class="bg-green-50 p-4 rounded-lg">
                <div class="text-green-600 text-2xl font-bold"><?php echo $tamamlayan_sayisi; ?></div>
                <div class="text-sm text-gray-600">Tamamlayan</div>
            </div>
            <div class="bg-orange-50 p-4 rounded-lg">
                <div class="text-orange-600 text-2xl font-bold">%<?php echo $ortalama_ilerleme; ?></div>
                <div class="text-sm text-gray-600">Ortalama İlerleme</div>
            </div>
            <div class="bg-purple-50 p-4 rounded-lg">
                <div class="text-purple-600 text-2xl font-bold"><?php echo gmdate("H:i", $toplam_izlenme_suresi); ?></div>
                <div class="text-sm text-gray-600">Toplam İzlenme Süresi</div>
            </div>
        </div>
    </div>

    <!-- Detaylı Kullanıcı İstatistikleri -->
    <div class="bg-white rounded-lg shadow-md overflow-hidden">
        <div class="p-6 border-b">
            <h2 class="text-xl font-bold text-gray-800">
                <i class="fas fa-users mr-2"></i>Kullanıcı Bazlı İzleme Detayları
            </h2>
        </div>

        <div class="overflow-x-auto">
            <table class="min-w-full">
                <thead class="bg-gray-100">
                    <tr>
                        <th class="py-3 px-4 text-left text-sm font-semibold text-gray-600">Kullanıcı</th>
                        <th class="py-3 px-4 text-left text-sm font-semibold text-gray-600">Rol</th>
                        <th class="py-3 px-4 text-left text-sm font-semibold text-gray-600">İlk İzleme</th>
                        <th class="py-3 px-4 text-left text-sm font-semibold text-gray-600">Son İzleme</th>
                        <th class="py-3 px-4 text-center text-sm font-semibold text-gray-600">İzleme Sayısı</th>
                        <th class="py-3 px-4 text-center text-sm font-semibold text-gray-600">Toplam Süre</th>
                        <th class="py-3 px-4 text-center text-sm font-semibold text-gray-600">İlerleme</th>
                        <th class="py-3 px-4 text-center text-sm font-semibold text-gray-600">Durum</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    <?php if (empty($izlemeler)): ?>
                        <tr>
                            <td colspan="8" class="py-8 text-center text-gray-500">
                                Henüz kimse bu eğitimi izlememiş.
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach($izlemeler as $izleme): ?>
                            <tr class="hover:bg-gray-50">
                                <td class="py-3 px-4">
                                    <div class="font-medium text-gray-800"><?php echo htmlspecialchars($izleme->kullanici_adi); ?></div>
                                    <div class="text-xs text-gray-500"><?php echo htmlspecialchars($izleme->email); ?></div>
                                </td>
                                <td class="py-3 px-4 text-sm text-gray-600">
                                    <?php echo htmlspecialchars($izleme->rol_adi ?? 'Kullanıcı'); ?>
                                </td>
                                <td class="py-3 px-4 text-sm text-gray-600">
                                    <?php echo date('d.m.Y H:i', strtotime($izleme->ilk_goruntulenme_tarihi)); ?>
                                </td>
                                <td class="py-3 px-4 text-sm text-gray-600">
                                    <?php echo date('d.m.Y H:i', strtotime($izleme->son_goruntulenme_tarihi)); ?>
                                </td>
                                <td class="py-3 px-4 text-center text-sm text-gray-600">
                                    <span class="inline-flex items-center px-2 py-1 rounded-full bg-blue-100 text-blue-800">
                                        <i class="fas fa-eye mr-1"></i><?php echo $izleme->izlenme_sayisi; ?>
                                    </span>
                                </td>
                                <td class="py-3 px-4 text-center text-sm font-medium text-gray-800">
                                    <?php 
                                    $dakika = floor($izleme->toplam_izlenme_suresi / 60);
                                    $saniye = $izleme->toplam_izlenme_suresi % 60;
                                    echo $dakika . 'dk ' . $saniye . 'sn';
                                    ?>
                                </td>
                                <td class="py-3 px-4 text-center">
                                    <div class="flex items-center justify-center">
                                        <div class="w-24 bg-gray-200 rounded-full h-2 mr-2">
                                            <div class="bg-blue-600 h-2 rounded-full" style="width: <?php echo $izleme->ilerleme_yuzdesi; ?>%"></div>
                                        </div>
                                        <span class="text-sm font-medium text-gray-700">%<?php echo $izleme->ilerleme_yuzdesi; ?></span>
                                    </div>
                                </td>
                                <td class="py-3 px-4 text-center">
                                    <?php if ($izleme->tamamlandi): ?>
                                        <span class="inline-flex items-center px-2 py-1 rounded-full text-xs bg-green-100 text-green-800">
                                            <i class="fas fa-check-circle mr-1"></i>Tamamlandı
                                        </span>
                                    <?php else: ?>
                                        <span class="inline-flex items-center px-2 py-1 rounded-full text-xs bg-yellow-100 text-yellow-800">
                                            <i class="fas fa-clock mr-1"></i>Devam Ediyor
                                        </span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Excel Export -->
    <div class="mt-6 text-center">
        <a href="egitim_istatistik_export.php?id=<?php echo $egitim_id; ?>" 
           class="inline-block bg-green-600 text-white px-6 py-3 rounded hover:bg-green-700">
            <i class="fas fa-file-excel mr-2"></i>Excel Olarak İndir
        </a>
    </div>
</div>

<?php include '../templates/footer.php'; ?>
