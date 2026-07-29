<?php
require_once 'config/session_check.php';
require_once 'config/db.php';

$sayfa_baslik = "Eğitim Detayı";
$user_id = $_SESSION['user_id'];
$egitim_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if (!$egitim_id) {
    header("Location: egitimler.php");
    exit();
}

// Eğitim bilgilerini çek
$stmt = $pdo->prepare("
    SELECT e.*, CONCAT(u.ad, ' ', u.soyad) as yukleyen
    FROM egitimler e
    LEFT JOIN users u ON e.yukleyen_id = u.id
    WHERE e.id = ? AND e.aktif = 1
");
$stmt->execute([$egitim_id]);
$egitim = $stmt->fetch();

if (!$egitim) {
    $_SESSION['mesaj'] = ['tip' => 'error', 'icerik' => 'Eğitim bulunamadı.'];
    header("Location: egitimler.php");
    exit();
}

// Görüntülenme kaydı
$pdo->prepare("UPDATE egitimler SET goruntulenme_sayisi = goruntulenme_sayisi + 1 WHERE id = ?")->execute([$egitim_id]);
try {
    $pdo->prepare("INSERT INTO egitim_goruntulemeler (egitim_id, user_id) VALUES (?, ?) ON DUPLICATE KEY UPDATE goruntulenme_tarihi = NOW()")->execute([$egitim_id, $user_id]);
} catch (Exception $e) {
    // Hata olursa sessizce geç
}

// Dosya uzantısını belirle
$dosya_uzantisi = strtolower(pathinfo($egitim->dosya_yolu, PATHINFO_EXTENSION));

include 'templates/header.php';
?>

<div class="container mx-auto px-4 py-6">
    <div class="mb-4">
        <a href="egitimler.php" class="text-blue-600 hover:text-blue-800">
            <i class="fas fa-arrow-left mr-2"></i>Eğitimlere Dön
        </a>
    </div>

    <div class="bg-white rounded-lg shadow-lg overflow-hidden">
        <!-- Başlık -->
        <div class="bg-gradient-to-r from-blue-600 to-blue-700 text-white p-6">
            <h1 class="text-3xl font-bold mb-2">
                <i class="fas fa-<?php echo $egitim->tip == 'video' ? 'video' : 'file-powerpoint'; ?> mr-2"></i>
                <?php echo htmlspecialchars($egitim->baslik); ?>
            </h1>
            <div class="flex items-center gap-4 text-sm">
                <span><i class="fas fa-user mr-1"></i><?php echo htmlspecialchars($egitim->yukleyen ?? 'Bilinmiyor'); ?></span>
                <span><i class="fas fa-calendar mr-1"></i><?php echo date('d.m.Y H:i', strtotime($egitim->yukleme_tarihi)); ?></span>
                <span><i class="fas fa-eye mr-1"></i><?php echo $egitim->goruntulenme_sayisi; ?> görüntülenme</span>
            </div>
        </div>

        <!-- İçerik -->
        <div class="p-6">
            <?php if (!empty($egitim->aciklama)): ?>
                <div class="mb-6 p-4 bg-gray-50 rounded-lg">
                    <h3 class="font-semibold text-gray-700 mb-2">Açıklama:</h3>
                    <p class="text-gray-600"><?php echo nl2br(htmlspecialchars($egitim->aciklama)); ?></p>
                </div>
            <?php endif; ?>

            <!-- Video Oynatıcı veya Sunum Görüntüleyici -->
            <div class="bg-gray-100 rounded-lg overflow-hidden">
                <?php if ($egitim->tip == 'video'): ?>
                    <?php if (in_array($dosya_uzantisi, ['mp4', 'webm', 'ogg'])): ?>
                        <!-- HTML5 Video Player -->
                        <video controls class="w-full" controlsList="nodownload">
                            <source src="<?php echo htmlspecialchars($egitim->dosya_yolu); ?>" type="video/<?php echo $dosya_uzantisi; ?>">
                            Tarayıcınız video oynatmayı desteklemiyor.
                        </video>
                    <?php else: ?>
                        <!-- Diğer video formatları için uyarı -->
                        <div class="p-8 text-center bg-white">
                            <i class="fas fa-video text-6xl text-gray-400 mb-4"></i>
                            <p class="text-gray-600">Bu video formatı desteklenmiyor.</p>
                        </div>
                    <?php endif; ?>
                <?php else: ?>
                    <?php if (in_array($dosya_uzantisi, ['pdf'])): ?>
                        <!-- PDF Görüntüleyici -->
                        <iframe src="<?php echo htmlspecialchars($egitim->dosya_yolu); ?>" 
                                class="w-full border-0" 
                                style="height: 80vh;">
                        </iframe>
                    <?php elseif (in_array($dosya_uzantisi, ['ppt', 'pptx'])): ?>
                        <!-- PowerPoint Görüntüleme - Google Docs Viewer -->
                        <?php
                        $dosya_url = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST'] . '/' . $egitim->dosya_yolu;
                        ?>
                        <div id="viewer-container" class="relative">
                            <!-- Google Docs Viewer -->
                            <iframe id="google-viewer"
                                    src="https://docs.google.com/viewer?url=<?php echo urlencode($dosya_url); ?>&embedded=true" 
                                    class="w-full border-0" 
                                    style="height: 80vh;"
                                    onload="hideLoadingMessage()">
                            </iframe>
                            
                            <!-- Yükleme Mesajı -->
                            <div id="loading-message" class="absolute inset-0 flex items-center justify-center bg-white">
                                <div class="text-center">
                                    <div class="animate-spin rounded-full h-16 w-16 border-b-2 border-blue-600 mx-auto mb-4"></div>
                                    <p class="text-gray-600">Sunum yükleniyor...</p>
                                    <p class="text-sm text-gray-500 mt-2">Büyük dosyalar için bu işlem 1-2 dakika sürebilir</p>
                                </div>
                            </div>
                            
                            <!-- Hata Mesajı -->
                            <div id="error-message" class="hidden p-8 bg-white text-center">
                                <i class="fas fa-exclamation-triangle text-6xl text-yellow-500 mb-4"></i>
                                <h3 class="text-xl font-bold text-gray-800 mb-2">Görüntüleme Sorunu</h3>
                                <p class="text-gray-600 mb-4">Sunum yüklenemedi. Lütfen tekrar deneyin.</p>
                                <div class="space-y-2 text-sm text-gray-500 mb-4">
                                    <p>• Dosya boyutu: <strong><?php echo round($egitim->dosya_boyutu / 1048576, 2); ?> MB</strong></p>
                                </div>
                                <button onclick="retryViewer()" 
                                        class="mt-2 bg-blue-600 text-white px-6 py-2 rounded hover:bg-blue-700">
                                    <i class="fas fa-redo mr-2"></i>Tekrar Dene
                                </button>
                            </div>
                        </div>
                        
                        <script>
                        function hideLoadingMessage() {
                            setTimeout(function() {
                                document.getElementById('loading-message').classList.add('hidden');
                            }, 1000);
                        }
                        
                        function retryViewer() {
                            document.getElementById('error-message').classList.add('hidden');
                            document.getElementById('loading-message').classList.remove('hidden');
                            const googleViewer = document.getElementById('google-viewer');
                            googleViewer.src = googleViewer.src;
                        }
                        
                        // Timeout: 120 saniye sonra hala yüklenmediyse hata göster
                        setTimeout(function() {
                            const loadingVisible = !document.getElementById('loading-message').classList.contains('hidden');
                            if (loadingVisible) {
                                document.getElementById('loading-message').classList.add('hidden');
                                document.getElementById('error-message').classList.remove('hidden');
                            }
                        }, 120000);
                        </script>
                        
                    <?php elseif (in_array($dosya_uzantisi, ['odp'])): ?>
                        <!-- ODP Görüntüleme -->
                        <iframe src="https://docs.google.com/viewer?url=<?php echo urlencode((isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST'] . '/' . $egitim->dosya_yolu); ?>&embedded=true" 
                                class="w-full border-0" 
                                style="height: 80vh;">
                        </iframe>
                    <?php else: ?>
                        <!-- Desteklenmeyen dosya formatları -->
                        <div class="p-8 bg-white text-center">
                            <i class="fas fa-file text-6xl text-gray-400 mb-4"></i>
                            <p class="text-gray-600">Bu dosya formatı desteklenmiyor.</p>
                        </div>
                    <?php endif; ?>
                <?php endif; ?>
            </div>

            <!-- Dosya Bilgileri -->
            <div class="mt-4 p-4 bg-gray-50 rounded-lg flex items-center justify-between">
                <div class="text-sm text-gray-600">
                    <i class="fas fa-info-circle mr-2"></i>
                    Dosya Boyutu: <strong><?php echo round($egitim->dosya_boyutu / 1048576, 2); ?> MB</strong>
                </div>
                <div class="flex items-center gap-4">
                    <div class="text-sm text-gray-600">
                        Format: <strong><?php echo strtoupper($dosya_uzantisi); ?></strong>
                    </div>
                    <a href="<?php echo htmlspecialchars($egitim->dosya_yolu); ?>" 
                       download
                       class="inline-flex items-center bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 transition text-sm">
                        <i class="fas fa-download mr-2"></i>İndir
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- İzleme Tracking Script -->
<script>
let egitimId = <?php echo $egitim_id; ?>;
let izlemeBaslangic = Date.now();
let sonKayitZamani = Date.now();
let toplamIzlemeSuresi = 0;

// Her 30 saniyede bir kaydet
setInterval(function() {
    let gecenSure = Math.floor((Date.now() - sonKayitZamani) / 1000); // saniye
    sonKayitZamani = Date.now();
    
    if (gecenSure > 0 && gecenSure < 60) { // Anormal değerleri filtrele
        toplamIzlemeSuresi += gecenSure;
        kaydetIzleme(gecenSure);
    }
}, 30000); // 30 saniye

// Sayfa kapanırken kaydet
window.addEventListener('beforeunload', function() {
    let gecenSure = Math.floor((Date.now() - sonKayitZamani) / 1000);
    if (gecenSure > 0) {
        toplamIzlemeSuresi += gecenSure;
        // Senkron istek (sayfa kapanırken)
        navigator.sendBeacon('egitim_izleme_kaydet.php', JSON.stringify({
            egitim_id: egitimId,
            izlenme_suresi: gecenSure,
            ilerleme_yuzdesi: hesaplaIlerleme()
        }));
    }
});

// Video için ilerleme takibi
<?php if ($egitim->tip == 'video' && in_array($dosya_uzantisi, ['mp4', 'webm', 'ogg'])): ?>
const video = document.querySelector('video');
if (video) {
    video.addEventListener('timeupdate', function() {
        // İlerleme her güncellendiğinde hesapla
    });
    
    video.addEventListener('ended', function() {
        // Video bittiğinde %100 kaydet
        kaydetIzleme(0, 100);
    });
}
<?php endif; ?>

function hesaplaIlerleme() {
    <?php if ($egitim->tip == 'video' && in_array($dosya_uzantisi, ['mp4', 'webm', 'ogg'])): ?>
    const video = document.querySelector('video');
    if (video && video.duration > 0) {
        return Math.floor((video.currentTime / video.duration) * 100);
    }
    <?php else: ?>
    // Sunum için: 5 dakika üzerinde izlediyse %100
    if (toplamIzlemeSuresi > 300) {
        return 100;
    }
    // Her dakika için %20
    return Math.min(Math.floor(toplamIzlemeSuresi / 60) * 20, 100);
    <?php endif; ?>
    return 0;
}

function kaydetIzleme(sure, ilerlemePaketi = null) {
    let ilerleme = ilerlemePaketi !== null ? ilerlemePaketi : hesaplaIlerleme();
    
    fetch('egitim_izleme_kaydet.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({
            egitim_id: egitimId,
            izlenme_suresi: sure,
            ilerleme_yuzdesi: ilerleme
        })
    })
    .then(response => response.json())
    .then(data => {
        if (!data.success) {
            console.error('İzleme kaydedilemedi:', data.message);
        }
    })
    .catch(error => {
        console.error('İzleme hatası:', error);
    });
}
</script>

<?php include 'templates/footer.php'; ?>