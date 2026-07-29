<?php
ob_start();
require_once '../config/session_check_admin.php';
require_once '../config/db.php';

$sayfa_baslik = "Eğitim Yönetimi";

// Mesaj gösterimi
$mesaj = '';
if (isset($_SESSION['mesaj'])) {
    $mesaj = $_SESSION['mesaj'];
    unset($_SESSION['mesaj']);
}

// SİLME İŞLEMİ
if (isset($_GET['action']) && $_GET['action'] == 'sil' && isset($_GET['id'])) {
    $egitim_id = (int)$_GET['id'];
    try {
        // Önce dosya yolunu al
        $stmt = $pdo->prepare("SELECT dosya_yolu FROM egitimler WHERE id = ?");
        $stmt->execute([$egitim_id]);
        $egitim = $stmt->fetch();
        
        if ($egitim) {
            // Dosyayı sil
            if (file_exists('../' . $egitim->dosya_yolu)) {
                unlink('../' . $egitim->dosya_yolu);
            }
            
            // Veritabanından sil
            $pdo->prepare("DELETE FROM egitimler WHERE id = ?")->execute([$egitim_id]);
            $_SESSION['mesaj'] = ['tip' => 'success', 'icerik' => 'Eğitim başarıyla silindi.'];
        }
    } catch (Exception $e) {
        $_SESSION['mesaj'] = ['tip' => 'error', 'icerik' => 'Eğitim silinirken bir hata oluştu: ' . $e->getMessage()];
    }
    header("Location: egitim_yonetimi.php");
    exit();
}

// AKTİFLİK DURUMU DEĞİŞTİRME
if (isset($_GET['action']) && $_GET['action'] == 'toggle' && isset($_GET['id'])) {
    $egitim_id = (int)$_GET['id'];
    try {
        $pdo->prepare("UPDATE egitimler SET aktif = NOT aktif WHERE id = ?")->execute([$egitim_id]);
        $_SESSION['mesaj'] = ['tip' => 'success', 'icerik' => 'Eğitim durumu güncellendi.'];
    } catch (Exception $e) {
        $_SESSION['mesaj'] = ['tip' => 'error', 'icerik' => 'Durum güncellenirken bir hata oluştu.'];
    }
    header("Location: egitim_yonetimi.php");
    exit();
}

// EĞİTİM YÜKLEME
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['egitim_ekle'])) {
    $baslik = trim($_POST['baslik']);
    $aciklama = trim($_POST['aciklama']);
    $tip = $_POST['tip'];
    $yukleyen_id = $_SESSION['user_id'];
    
    // Dosya yükleme
    if (isset($_FILES['dosya']) && $_FILES['dosya']['error'] == 0) {
        $upload_dir = '../uploads/egitimler/';
        
        // Klasör yoksa oluştur
        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0755, true);
        }
        
        $dosya_adi = $_FILES['dosya']['name'];
        $dosya_boyutu = $_FILES['dosya']['size'];
        $gecici_dosya = $_FILES['dosya']['tmp_name'];
        $dosya_uzantisi = strtolower(pathinfo($dosya_adi, PATHINFO_EXTENSION));
        
        // İzin verilen uzantılar
        $video_uzantilar = ['mp4', 'webm', 'ogg', 'avi', 'mov'];
        $sunum_uzantilar = ['pdf', 'ppt', 'pptx', 'odp'];
        
        $izinli_uzantilar = $tip == 'video' ? $video_uzantilar : $sunum_uzantilar;
        
        if (!in_array($dosya_uzantisi, $izinli_uzantilar)) {
            $_SESSION['mesaj'] = ['tip' => 'error', 'icerik' => 'Geçersiz dosya formatı!'];
        } else {
            // Benzersiz dosya adı oluştur
            $yeni_dosya_adi = uniqid() . '_' . time() . '.' . $dosya_uzantisi;
            $hedef_yol = $upload_dir . $yeni_dosya_adi;
            
            if (move_uploaded_file($gecici_dosya, $hedef_yol)) {
                // Veritabanına kaydet
                $dosya_yolu = 'uploads/egitimler/' . $yeni_dosya_adi;
                
                try {
                    $stmt = $pdo->prepare("INSERT INTO egitimler (baslik, aciklama, tip, dosya_yolu, dosya_boyutu, yukleyen_id) VALUES (?, ?, ?, ?, ?, ?)");
                    $stmt->execute([$baslik, $aciklama, $tip, $dosya_yolu, $dosya_boyutu, $yukleyen_id]);
                    $_SESSION['mesaj'] = ['tip' => 'success', 'icerik' => 'Eğitim başarıyla yüklendi!'];
                } catch (Exception $e) {
                    unlink($hedef_yol); // Hata olursa dosyayı sil
                    $_SESSION['mesaj'] = ['tip' => 'error', 'icerik' => 'Veritabanı hatası: ' . $e->getMessage()];
                }
            } else {
                $_SESSION['mesaj'] = ['tip' => 'error', 'icerik' => 'Dosya yüklenirken bir hata oluştu!'];
            }
        }
        
        header("Location: egitim_yonetimi.php");
        exit();
    } else {
        $_SESSION['mesaj'] = ['tip' => 'error', 'icerik' => 'Lütfen bir dosya seçin!'];
        header("Location: egitim_yonetimi.php");
        exit();
    }
}

// Tüm eğitimleri çek
$stmt = $pdo->query("
    SELECT e.*, 
           CONCAT(u.ad, ' ', u.soyad) as yukleyen
    FROM egitimler e
    LEFT JOIN users u ON e.yukleyen_id = u.id
    ORDER BY e.yukleme_tarihi DESC
");
$egitimler = $stmt->fetchAll();

include '../templates/header.php';
?>

<div class="container mx-auto px-4">
    <!-- Mesaj -->
    <?php if ($mesaj): ?>
        <div class="mb-6 p-4 rounded-lg <?php echo $mesaj['tip'] == 'success' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'; ?>">
            <?php echo htmlspecialchars($mesaj['icerik']); ?>
        </div>
    <?php endif; ?>

    <div class="flex justify-between items-center mb-6">
        <h1 class="text-3xl font-bold text-gray-800">
            <i class="fas fa-graduation-cap mr-2"></i>Eğitim Yönetimi
        </h1>
        <button onclick="document.getElementById('yukle_modal').classList.remove('hidden')" 
                class="bg-green-600 text-white px-4 py-2 rounded-lg hover:bg-green-700 transition">
            <i class="fas fa-plus mr-2"></i>Yeni Eğitim Ekle
        </button>
    </div>

    <!-- Eğitim Listesi -->
    <div class="bg-white rounded-lg shadow-md overflow-hidden">
        <table class="min-w-full">
            <thead class="bg-gray-100">
                <tr>
                    <th class="py-3 px-4 text-left text-sm font-semibold text-gray-600">Başlık</th>
                    <th class="py-3 px-4 text-left text-sm font-semibold text-gray-600">Tip</th>
                    <th class="py-3 px-4 text-left text-sm font-semibold text-gray-600">Yükleyen</th>
                    <th class="py-3 px-4 text-left text-sm font-semibold text-gray-600">Tarih</th>
                    <th class="py-3 px-4 text-left text-sm font-semibold text-gray-600">Görüntülenme</th>
                    <th class="py-3 px-4 text-left text-sm font-semibold text-gray-600">Boyut</th>
                    <th class="py-3 px-4 text-center text-sm font-semibold text-gray-600">Durum</th>
                    <th class="py-3 px-4 text-center text-sm font-semibold text-gray-600">İşlemler</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
                <?php if (empty($egitimler)): ?>
                    <tr>
                        <td colspan="8" class="py-8 text-center text-gray-500">
                            Henüz eğitim yüklenmemiş.
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach($egitimler as $egitim): ?>
                        <tr class="hover:bg-gray-50">
                            <td class="py-3 px-4">
                                <div class="font-medium text-gray-800"><?php echo htmlspecialchars($egitim->baslik); ?></div>
                                <?php if (!empty($egitim->aciklama)): ?>
                                    <div class="text-sm text-gray-500 truncate max-w-md"><?php echo htmlspecialchars($egitim->aciklama); ?></div>
                                <?php endif; ?>
                            </td>
                            <td class="py-3 px-4">
                                <?php if ($egitim->tip == 'video'): ?>
                                    <span class="inline-flex items-center px-2 py-1 rounded-full text-xs bg-red-100 text-red-800">
                                        <i class="fas fa-video mr-1"></i>Video
                                    </span>
                                <?php else: ?>
                                    <span class="inline-flex items-center px-2 py-1 rounded-full text-xs bg-orange-100 text-orange-800">
                                        <i class="fas fa-file-powerpoint mr-1"></i>Sunum
                                    </span>
                                <?php endif; ?>
                            </td>
                            <td class="py-3 px-4 text-sm text-gray-600"><?php echo htmlspecialchars($egitim->yukleyen ?? '-'); ?></td>
                            <td class="py-3 px-4 text-sm text-gray-600"><?php echo date('d.m.Y H:i', strtotime($egitim->yukleme_tarihi)); ?></td>
                            <td class="py-3 px-4 text-sm text-gray-600">
                                <i class="fas fa-eye mr-1"></i><?php echo $egitim->goruntulenme_sayisi; ?>
                            </td>
                            <td class="py-3 px-4 text-sm text-gray-600">
                                <?php echo round($egitim->dosya_boyutu / 1048576, 2); ?> MB
                            </td>
                            <td class="py-3 px-4 text-center">
                                <a href="?action=toggle&id=<?php echo $egitim->id; ?>" 
                                   class="inline-block">
                                    <?php if ($egitim->aktif): ?>
                                        <span class="px-2 py-1 rounded-full text-xs bg-green-100 text-green-800">
                                            <i class="fas fa-check-circle"></i> Aktif
                                        </span>
                                    <?php else: ?>
                                        <span class="px-2 py-1 rounded-full text-xs bg-gray-100 text-gray-800">
                                            <i class="fas fa-times-circle"></i> Pasif
                                        </span>
                                    <?php endif; ?>
                                </a>
                            </td>
                            <td class="py-3 px-4 text-center">
                                <a href="egitim_istatistikleri.php?id=<?php echo $egitim->id; ?>" 
                                   class="text-purple-600 hover:text-purple-800 mr-3" 
                                   title="İstatistikler">
                                    <i class="fas fa-chart-bar"></i>
                                </a>
                                <a href="../egitim_detay.php?id=<?php echo $egitim->id; ?>" 
                                   class="text-blue-600 hover:text-blue-800 mr-3" 
                                   title="Görüntüle" 
                                   target="_blank">
                                    <i class="fas fa-eye"></i>
                                </a>
                                <a href="?action=sil&id=<?php echo $egitim->id; ?>" 
                                   class="text-red-600 hover:text-red-800" 
                                   title="Sil" 
                                   onclick="return confirm('Bu eğitimi silmek istediğinizden emin misiniz?');">
                                    <i class="fas fa-trash-alt"></i>
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Eğitim Yükleme Modal -->
<div id="yukle_modal" class="fixed inset-0 bg-black bg-opacity-50 z-50 hidden flex items-center justify-center p-4">
    <div class="bg-white rounded-lg shadow-xl w-full max-w-2xl">
        <div class="flex justify-between items-center p-6 border-b">
            <h3 class="text-2xl font-bold text-gray-800">
                <i class="fas fa-plus-circle mr-2"></i>Yeni Eğitim Ekle
            </h3>
            <button onclick="document.getElementById('yukle_modal').classList.add('hidden')" 
                    class="text-gray-500 hover:text-gray-800 text-2xl">
                &times;
            </button>
        </div>
        
        <form method="POST" enctype="multipart/form-data" class="p-6">
            <div class="mb-4">
                <label class="block text-gray-700 font-semibold mb-2">Başlık *</label>
                <input type="text" name="baslik" required 
                       class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>

            <div class="mb-4">
                <label class="block text-gray-700 font-semibold mb-2">Açıklama</label>
                <textarea name="aciklama" rows="3" 
                          class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500"></textarea>
            </div>

            <div class="mb-4">
                <label class="block text-gray-700 font-semibold mb-2">Eğitim Tipi *</label>
                <select name="tip" id="egitim_tipi" required 
                        class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <option value="">Seçiniz</option>
                    <option value="video">Video</option>
                    <option value="sunum">Sunum</option>
                </select>
            </div>

            <div class="mb-4">
                <label class="block text-gray-700 font-semibold mb-2">Dosya *</label>
                <input type="file" name="dosya" id="dosya_input" required 
                       class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                <p class="text-xs text-gray-500 mt-1" id="format_bilgi">
                    Video: MP4, WEBM, OGG, AVI, MOV | Sunum: PDF, PPT, PPTX, ODP
                </p>
            </div>

            <div class="flex justify-end gap-2">
                <button type="button" 
                        onclick="document.getElementById('yukle_modal').classList.add('hidden')" 
                        class="px-6 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300">
                    İptal
                </button>
                <button type="submit" name="egitim_ekle" 
                        class="px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
                    <i class="fas fa-upload mr-2"></i>Yükle
                </button>
            </div>
        </form>
    </div>
</div>

<script>
// Dosya formatı kontrolü için bilgilendirme
document.getElementById('egitim_tipi').addEventListener('change', function() {
    const tip = this.value;
    const formatBilgi = document.getElementById('format_bilgi');
    
    if (tip === 'video') {
        formatBilgi.textContent = 'İzin verilen formatlar: MP4, WEBM, OGG, AVI, MOV';
    } else if (tip === 'sunum') {
        formatBilgi.textContent = 'İzin verilen formatlar: PDF, PPT, PPTX, ODP';
    } else {
        formatBilgi.textContent = 'Video: MP4, WEBM, OGG, AVI, MOV | Sunum: PDF, PPT, PPTX, ODP';
    }
});
</script>

<?php include '../templates/footer.php'; ?>