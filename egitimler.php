<?php
require_once 'config/session_check.php';
require_once 'config/db.php';

$sayfa_baslik = "Eğitimler";
$user_id = $_SESSION['user_id'];
$user_rol = $_SESSION['user_rol'];

// Mesaj gösterimi
$mesaj = '';
if (isset($_SESSION['mesaj'])) {
    $mesaj = $_SESSION['mesaj'];
    unset($_SESSION['mesaj']);
}

// Eğitim görüntüleme kaydı
if (isset($_GET['izle']) && !empty($_GET['izle'])) {
    $egitim_id = (int)$_GET['izle'];
    
    // Görüntülenme sayısını artır
    $pdo->prepare("UPDATE egitimler SET goruntulenme_sayisi = goruntulenme_sayisi + 1 WHERE id = ?")->execute([$egitim_id]);
    
    // Kullanıcının izleme kaydını tut
    try {
        $pdo->prepare("INSERT INTO egitim_goruntulemeler (egitim_id, user_id) VALUES (?, ?) ON DUPLICATE KEY UPDATE goruntulenme_tarihi = NOW()")->execute([$egitim_id, $user_id]);
    } catch (Exception $e) {
        // Hata olursa sessizce geç
    }
}

// Aktif eğitimleri çek
$stmt = $pdo->prepare("
    SELECT e.*, 
           CONCAT(u.ad, ' ', u.soyad) as yukleyen,
           (SELECT COUNT(*) FROM egitim_goruntulemeler WHERE egitim_id = e.id AND user_id = ?) as izlendi
    FROM egitimler e
    LEFT JOIN users u ON e.yukleyen_id = u.id
    WHERE e.aktif = 1
    ORDER BY e.yukleme_tarihi DESC
");
$stmt->execute([$user_id]);
$egitimler = $stmt->fetchAll();

// Eğitimleri tipe göre ayır
$videolar = array_filter($egitimler, function($e) { return $e->tip == 'video'; });
$sunumlar = array_filter($egitimler, function($e) { return $e->tip == 'sunum'; });

include 'templates/header.php';
?>

<div class="container mx-auto px-4">
    <!-- Mesaj Gösterimi -->
    <?php if ($mesaj): ?>
        <div class="mb-6 p-4 rounded-lg <?php echo $mesaj['tip'] == 'success' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'; ?>">
            <?php echo htmlspecialchars($mesaj['icerik']); ?>
        </div>
    <?php endif; ?>

    <div class="flex justify-between items-center mb-6">
        <h1 class="text-3xl font-bold text-gray-800">
            <i class="fas fa-graduation-cap mr-2"></i>Eğitimler
        </h1>
        <?php if ($user_rol == 1): ?>
            <a href="admin/egitim_yonetimi.php" class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 transition">
                <i class="fas fa-cog mr-2"></i>Eğitim Yönetimi
            </a>
        <?php endif; ?>
    </div>

    <?php
    $sayfa_basina = 6;
    $video_listesi = array_values($videolar);
    $sunum_listesi = array_values($sunumlar);
    $video_sayfa = isset($_GET['video_sayfa']) ? max(1, (int)$_GET['video_sayfa']) : 1;
    $sunum_sayfa = isset($_GET['sunum_sayfa']) ? max(1, (int)$_GET['sunum_sayfa']) : 1;
    $video_toplam_sayfa = ceil(count($video_listesi) / $sayfa_basina);
    $sunum_toplam_sayfa = ceil(count($sunum_listesi) / $sayfa_basina);
    $video_sayfa_listesi = array_slice($video_listesi, ($video_sayfa - 1) * $sayfa_basina, $sayfa_basina);
    $sunum_sayfa_listesi = array_slice($sunum_listesi, ($sunum_sayfa - 1) * $sayfa_basina, $sayfa_basina);
    ?>

    <!-- Video Eğitimler -->
    <div class="mb-10">
        <h2 class="text-2xl font-semibold text-gray-700 mb-4 flex items-center">
            <i class="fas fa-video text-red-600 mr-2"></i>Video Eğitimler
            <span class="ml-2 text-sm font-normal text-gray-400">(<?php echo count($video_listesi); ?> video)</span>
        </h2>
        
        <?php if (empty($video_listesi)): ?>
            <div class="bg-gray-50 p-8 rounded-lg text-center text-gray-500">
                <i class="fas fa-video text-4xl mb-2"></i>
                <p>Henüz video eğitim yüklenmemiş.</p>
            </div>
        <?php else: ?>
            <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-4">
                <?php foreach($video_sayfa_listesi as $video): ?>
                    <div class="bg-white rounded-lg shadow-md overflow-hidden hover:shadow-xl transition">
                        <div class="relative bg-gradient-to-r from-red-500 to-red-600 h-28 flex items-center justify-center">
                            <i class="fas fa-play-circle text-white text-5xl opacity-80"></i>
                            <?php if ($video->izlendi): ?>
                                <span class="absolute top-1 right-1 bg-green-500 text-white text-xs px-1 py-0.5 rounded">
                                    <i class="fas fa-check"></i>
                                </span>
                            <?php endif; ?>
                        </div>
                        <div class="p-3">
                            <h3 class="font-bold text-sm text-gray-800 mb-2 line-clamp-2"><?php echo htmlspecialchars($video->baslik); ?></h3>
                            <div class="flex items-center justify-between text-xs text-gray-400 mb-2">
                                <span><i class="fas fa-eye mr-1"></i><?php echo $video->goruntulenme_sayisi; ?></span>
                                <span><?php echo date('d.m.Y', strtotime($video->yukleme_tarihi)); ?></span>
                            </div>
                            <a href="egitim_detay.php?id=<?php echo $video->id; ?>" 
                               class="block w-full bg-red-600 text-white text-center py-1.5 rounded text-sm hover:bg-red-700 transition">
                                <i class="fas fa-play mr-1"></i>İzle
                            </a>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>

            <!-- Video Sayfalama -->
            <?php if ($video_toplam_sayfa > 1): ?>
            <div class="flex justify-center items-center gap-2 mt-6">
                <?php if ($video_sayfa > 1): ?>
                    <a href="?video_sayfa=<?php echo $video_sayfa - 1; ?>&sunum_sayfa=<?php echo $sunum_sayfa; ?>" 
                       class="px-3 py-2 bg-white border rounded hover:bg-gray-50 text-sm">
                        <i class="fas fa-chevron-left"></i>
                    </a>
                <?php endif; ?>
                <?php for ($i = 1; $i <= $video_toplam_sayfa; $i++): ?>
                    <a href="?video_sayfa=<?php echo $i; ?>&sunum_sayfa=<?php echo $sunum_sayfa; ?>" 
                       class="px-3 py-2 border rounded text-sm <?php echo $i == $video_sayfa ? 'bg-red-600 text-white border-red-600' : 'bg-white hover:bg-gray-50'; ?>">
                        <?php echo $i; ?>
                    </a>
                <?php endfor; ?>
                <?php if ($video_sayfa < $video_toplam_sayfa): ?>
                    <a href="?video_sayfa=<?php echo $video_sayfa + 1; ?>&sunum_sayfa=<?php echo $sunum_sayfa; ?>" 
                       class="px-3 py-2 bg-white border rounded hover:bg-gray-50 text-sm">
                        <i class="fas fa-chevron-right"></i>
                    </a>
                <?php endif; ?>
            </div>
            <?php endif; ?>
        <?php endif; ?>
    </div>

    <!-- Sunum Eğitimler -->
    <div class="mb-10">
        <h2 class="text-2xl font-semibold text-gray-700 mb-4 flex items-center">
            <i class="fas fa-file-powerpoint text-orange-600 mr-2"></i>Sunum Eğitimler
            <span class="ml-2 text-sm font-normal text-gray-400">(<?php echo count($sunum_listesi); ?> sunum)</span>
        </h2>
        
        <?php if (empty($sunum_listesi)): ?>
            <div class="bg-gray-50 p-8 rounded-lg text-center text-gray-500">
                <i class="fas fa-file-powerpoint text-4xl mb-2"></i>
                <p>Henüz sunum yüklenmemiş.</p>
            </div>
        <?php else: ?>
            <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-4">
                <?php foreach($sunum_sayfa_listesi as $sunum): ?>
                    <div class="bg-white rounded-lg shadow-md overflow-hidden hover:shadow-xl transition">
                        <div class="relative bg-gradient-to-r from-orange-500 to-orange-600 h-28 flex items-center justify-center">
                            <i class="fas fa-file-powerpoint text-white text-5xl opacity-80"></i>
                            <?php if ($sunum->izlendi): ?>
                                <span class="absolute top-1 right-1 bg-green-500 text-white text-xs px-1 py-0.5 rounded">
                                    <i class="fas fa-check"></i>
                                </span>
                            <?php endif; ?>
                        </div>
                        <div class="p-3">
                            <h3 class="font-bold text-sm text-gray-800 mb-2 line-clamp-2"><?php echo htmlspecialchars($sunum->baslik); ?></h3>
                            <div class="flex items-center justify-between text-xs text-gray-400 mb-2">
                                <span><i class="fas fa-eye mr-1"></i><?php echo $sunum->goruntulenme_sayisi; ?></span>
                                <span><?php echo date('d.m.Y', strtotime($sunum->yukleme_tarihi)); ?></span>
                            </div>
                            <a href="egitim_detay.php?id=<?php echo $sunum->id; ?>" 
                               class="block w-full bg-orange-600 text-white text-center py-1.5 rounded text-sm hover:bg-orange-700 transition">
                                <i class="fas fa-eye mr-1"></i>Görüntüle
                            </a>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>

            <!-- Sunum Sayfalama -->
            <?php if ($sunum_toplam_sayfa > 1): ?>
            <div class="flex justify-center items-center gap-2 mt-6">
                <?php if ($sunum_sayfa > 1): ?>
                    <a href="?video_sayfa=<?php echo $video_sayfa; ?>&sunum_sayfa=<?php echo $sunum_sayfa - 1; ?>" 
                       class="px-3 py-2 bg-white border rounded hover:bg-gray-50 text-sm">
                        <i class="fas fa-chevron-left"></i>
                    </a>
                <?php endif; ?>
                <?php for ($i = 1; $i <= $sunum_toplam_sayfa; $i++): ?>
                    <a href="?video_sayfa=<?php echo $video_sayfa; ?>&sunum_sayfa=<?php echo $i; ?>" 
                       class="px-3 py-2 border rounded text-sm <?php echo $i == $sunum_sayfa ? 'bg-orange-600 text-white border-orange-600' : 'bg-white hover:bg-gray-50'; ?>">
                        <?php echo $i; ?>
                    </a>
                <?php endfor; ?>
                <?php if ($sunum_sayfa < $sunum_toplam_sayfa): ?>
                    <a href="?video_sayfa=<?php echo $video_sayfa; ?>&sunum_sayfa=<?php echo $sunum_sayfa + 1; ?>" 
                       class="px-3 py-2 bg-white border rounded hover:bg-gray-50 text-sm">
                        <i class="fas fa-chevron-right"></i>
                    </a>
                <?php endif; ?>
            </div>
            <?php endif; ?>
        <?php endif; ?>
    </div>
</div>

<style>
.line-clamp-2 {
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
}
</style>

<?php include 'templates/footer.php'; ?>