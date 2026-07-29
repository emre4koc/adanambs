<?php
require_once '../config/session_check_admin.php';
require_once '../config/db.php';

// Saat dilimi ayarı eklendi (Doğru zaman için)
date_default_timezone_set('Europe/Istanbul');

$sayfa_baslik = "Yönetim Paneli";

// İstatistikleri çek
$toplam_kullanici = $pdo->query("SELECT COUNT(*) FROM users WHERE rol != 1")->fetchColumn();
$aktif_musabaka = $pdo->query("SELECT COUNT(*) FROM musabakalar WHERE arsiv = 0")->fetchColumn();
$bekleyen_mazeret = $pdo->query("SELECT COUNT(*) FROM mazeretler WHERE durum = 'Beklemede'")->fetchColumn();

// YENİ SORGULAR
$dusuk_puanli_hakem = $pdo->query("SELECT COUNT(DISTINCT hakem_id) FROM rapor_detaylari WHERE puan <= 7.9 AND listeden_kaldir = 0")->fetchColumn();
$bekleyen_musaitlik = $pdo->query("SELECT COUNT(*) FROM musaitlik_talepleri WHERE durum = 'Beklemede'")->fetchColumn();

// YENİ EKLENEN: Bugün Disiplin Raporu Sayısı (YENİ TABLO İLE)
$bugunun_tarihi = date('Y-m-d');
$disiplin_raporu_sayisi = $pdo->prepare("
    SELECT COUNT(*) 
    FROM disiplin_raporlari 
    WHERE DATE(olusturma_tarihi) = ?
");
$disiplin_raporu_sayisi->execute([$bugunun_tarihi]);
$bugun_disiplin_raporu = $disiplin_raporu_sayisi->fetchColumn();

?>
<?php include '../templates/header.php'; ?>

<div class="container mx-auto">
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-6 gap-6 mb-8">
        <div class="bg-white p-6 rounded-lg shadow-md">
            <h3 class="text-gray-500 text-sm font-medium">Toplam Kullanıcı</h3>
            <p class="text-3xl font-bold text-blue-600 mt-2"><?php echo $toplam_kullanici; ?></p>
        </div>
        <div class="bg-white p-6 rounded-lg shadow-md">
            <h3 class="text-gray-500 text-sm font-medium">Aktif Müsabaka</h3>
            <p class="text-3xl font-bold text-green-600 mt-2"><?php echo $aktif_musabaka; ?></p>
        </div>
        <div class="bg-white p-6 rounded-lg shadow-md">
            <h3 class="text-gray-500 text-sm font-medium">Bekleyen Mazeret Talebi</h3>
            <p class="text-3xl font-bold text-yellow-600 mt-2"><?php echo $bekleyen_mazeret; ?></p>
        </div>
        <div class="bg-white p-6 rounded-lg shadow-md">
            <h3 class="text-gray-500 text-sm font-medium">Düşük Puanlı Hakem Sayısı</h3>
            <p class="text-3xl font-bold text-red-600 mt-2"><?php echo $dusuk_puanli_hakem; ?></p>
        </div>
        <div class="bg-white p-6 rounded-lg shadow-md">
            <h3 class="text-gray-500 text-sm font-medium">Bekleyen Müsaitlik Onayı</h3>
            <p class="text-3xl font-bold text-purple-600 mt-2"><?php echo $bekleyen_musaitlik; ?></p>
        </div>
        <div class="bg-white p-6 rounded-lg shadow-md">
            <h3 class="text-gray-500 text-sm font-medium">Bugün Disiplin Raporu</h3>
            <p class="text-3xl font-bold <?php echo ($bugun_disiplin_raporu > 0) ? 'text-red-600' : 'text-green-600'; ?> mt-2"><?php echo $bugun_disiplin_raporu; ?></p>
        </div>
    </div>
    
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
        <a href="musabaka_yonetimi.php" class="block bg-white p-6 rounded-lg shadow-md hover:shadow-xl transition-shadow">
            <div class="flex items-center">
                <i class="fas fa-futbol fa-2x text-blue-500"></i>
                <div class="ml-4">
                    <h2 class="text-lg font-semibold text-gray-800">Müsabaka Yönetimi</h2>
                    <p class="text-gray-500 text-sm">Müsabakaları arşivle, düzenle ve yönet.</p>
                </div>
            </div>
        </a>
        
        <a href="musabaka_on_yukleme.php" class="block bg-white p-6 rounded-lg shadow-md hover:shadow-xl transition-shadow">
            <div class="flex items-center">
                <i class="fas fa-upload fa-2x text-blue-500"></i>
                <div class="ml-4">
                    <h2 class="text-lg font-semibold text-gray-800">Müsabaka Yükleme ve Yayınlama</h2>
                    <p class="text-gray-500 text-sm">Yeni müsabaka ekle, düzenle ve yönet.</p>
                </div>
            </div>
        </a>
        
        <a href="kullanicilar.php" class="block bg-white p-6 rounded-lg shadow-md hover:shadow-xl transition-shadow">
            <div class="flex items-center">
                <i class="fas fa-users-cog fa-2x text-purple-500"></i>
                <div class="ml-4">
                    <h2 class="text-lg font-semibold text-gray-800">Kullanıcı Yönetimi</h2>
                    <p class="text-gray-500 text-sm">Kullanıcıları yönet ve yetkilerini düzenle.</p>
                </div>
            </div>
        </a>
        
        <a href="kullanici_yeterlilik.php" class="block bg-white p-6 rounded-lg shadow-md hover:shadow-xl transition-shadow">
            <div class="flex items-center">
                <i class="fas fa-award fa-2x text-purple-500"></i>
                <div class="ml-4">
                    <h2 class="text-lg font-semibold text-gray-800">Kullanıcı Yeterlilik Yönetimi</h2>
                    <p class="text-gray-500 text-sm">Eğitim, antrenman ve ceza durumlarını düzenle.</p>
                </div>
            </div>
        </a>

        <a href="raporlar_dusuk_puan.php" class="block bg-white p-6 rounded-lg shadow-md hover:shadow-xl transition-shadow">
            <div class="flex items-center">
                <i class="fas fa-exclamation-triangle fa-2x text-red-500"></i>
                <div class="ml-4">
                    <h2 class="text-lg font-semibold text-gray-800">Düşük Puanlı Hakem Raporları</h2>
                    <p class="text-gray-500 text-sm">7.9 ve altı puan almış hakemleri gör.</p>
                </div>
            </div>
        </a>
		
        <a href="duyurular.php" class="block bg-white p-6 rounded-lg shadow-md hover:shadow-xl transition-shadow">
            <div class="flex items-center">
                <i class="fas fa-bullhorn fa-2x text-yellow-500"></i>
                <div class="ml-4">
                    <h2 class="text-lg font-semibold text-gray-800">Duyuru Yönetimi</h2>
                    <p class="text-gray-500 text-sm">Tüm kullanıcılara duyuru ve bildirim gönder.</p>
                </div>
            </div>
        </a>
        
        <a href="mazeretler.php" class="block bg-white p-6 rounded-lg shadow-md hover:shadow-xl transition-shadow">
            <div class="flex items-center">
                <i class="fas fa-file-medical-alt fa-2x text-red-500"></i>
                <div class="ml-4">
                    <h2 class="text-lg font-semibold text-gray-800">Mazeret Talepleri</h2>
                    <p class="text-gray-500 text-sm">Kullanıcıların mazeret taleplerini onayla/reddet.</p>
                </div>
            </div>
        </a>
        
        <a href="musaitlik_talepleri.php" class="block bg-white p-6 rounded-lg shadow-md hover:shadow-xl transition-shadow">
            <div class="flex items-center">
                <i class="fas fa-calendar-check fa-2x text-indigo-500"></i>
                <div class="ml-4">
                    <h2 class="text-lg font-semibold text-gray-800">Müsaitlik Talepleri</h2>
                    <p class="text-gray-500 text-sm">Müsaitlik değişikliği taleplerini yönet.</p>
                </div>
            </div>
        </a>
        
        <a href="musaitlik_yonetimi.php" class="block bg-white p-6 rounded-lg shadow-md hover:shadow-xl transition-shadow">
            <div class="flex items-center">
                <i class="fas fa-users-viewfinder fa-2x text-indigo-500"></i>
                <div class="ml-4">
                    <h2 class="text-lg font-semibold text-gray-800">Müsaitlik Durumları</h2>
                    <p class="text-gray-500 text-sm">Kullanıcıların müsaitlik durumlarını gör/yönet.</p>
                </div>
            </div>
        </a>
        
        <a href="onay_takip.php" class="block bg-white p-6 rounded-lg shadow-md hover:shadow-xl transition-shadow">
            <div class="flex items-center">
                <i class="fas fa-check-double fa-2x text-green-500"></i>
                <div class="ml-4">
                    <h2 class="text-lg font-semibold text-gray-800">Onay Takip</h2>
                    <p class="text-gray-500 text-sm">Haftalık görev onay durumlarını görüntüle.</p>
                </div>
            </div>
        </a>
        
        <a href="disiplin_raporu_listesi.php" class="block bg-white p-6 rounded-lg shadow-md hover:shadow-xl transition-shadow">
            <div class="flex items-center">
                <i class="fas fa-file-image fa-2x text-red-500"></i>
                <div class="ml-4">
                    <h2 class="text-lg font-semibold text-gray-800">Disiplin Raporları</h2>
                    <p class="text-gray-500 text-sm">Disiplin raporu eklenen müsabakaları listele.</p>
                </div>
            </div>
        </a>
        <a href="klasman_yonetimi.php" class="block bg-white p-6 rounded-lg shadow-md hover:shadow-xl transition-shadow">
            <div class="flex items-center">
                <i class="fas fa-chart-simple fa-2x text-blue-500"></i>
                <div class="ml-4">
                    <h2 class="text-lg font-semibold text-gray-800">Klasman Yönetimi</h2>
                    <p class="text-gray-500 text-sm">Klasmanları görüntüle ve yönet.</p>
                </div>
            </div>
        </a>
        
        <a href="stadyum_yonetimi.php" class="block bg-white p-6 rounded-lg shadow-md hover:shadow-xl transition-shadow">
            <div class="flex items-center">
                <i class="fas fa-building fa-2x text-gray-500"></i>
                <div class="ml-4">
                    <h2 class="text-lg font-semibold text-gray-800">Stadyum Yönetimi</h2>
                    <p class="text-gray-500 text-sm">Stadyumları görüntüle ve yönet.</p>
                </div>
            </div>
        </a>
        
        <a href="takim_yonetimi.php" class="block bg-white p-6 rounded-lg shadow-md hover:shadow-xl transition-shadow">
            <div class="flex items-center">
                <i class="fas fa-people-group fa-2x text-gray-500"></i>
                <div class="ml-4">
                    <h2 class="text-lg font-semibold text-gray-800">Takım Yönetimi</h2>
                    <p class="text-gray-500 text-sm">Takımları görüntüle ve yönet.</p>
                </div>
            </div>
        </a>

		<a href="lig_yonetimi.php" class="block bg-white p-6 rounded-lg shadow-md hover:shadow-xl transition-shadow">
            <div class="flex items-center">
                <i class="fas fa-list-ol fa-2x text-gray-500"></i>
                <div class="ml-4">
                    <h2 class="text-lg font-semibold text-gray-800">Lig Yönetimi</h2>
                    <p class="text-gray-500 text-sm">Ligleri görüntüle ve yönet.</p>
                </div>
            </div>
        </a>
		
        <a href="arsiv_yonetimi.php" class="block bg-white p-6 rounded-lg shadow-md hover:shadow-xl transition-shadow">
            <div class="flex items-center">
                <i class="fas fa-box-archive fa-2x text-orange-500"></i>
                <div class="ml-4">
                    <h2 class="text-lg font-semibold text-gray-800">Müsabaka Arşivi</h2>
                    <p class="text-gray-500 text-sm">Arşivlenmiş müsabakaları görüntüle ve yönet.</p>
                </div>
            </div>
        </a>
        
    </div>
</div>

<?php include '../templates/footer.php'; ?>