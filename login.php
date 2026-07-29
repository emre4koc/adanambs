<?php
// Oturumun sunucuda kalma süresini 30 güne (259200 saniyeye) ayarla
ini_set('session.gc_maxlifetime', 259200);

// Oturum çerezinin ömrünü 30 gün (259200 saniye) olarak ayarla
$cookie_lifetime = 60 * 60 * 24 * 30; // 30 gün
session_set_cookie_params($cookie_lifetime);

session_start();
require_once 'config/db.php';

$mesaj = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $kullanici_girisi = trim($_POST['kullanici_girisi']);
    $sifre = $_POST['sifre'];

    if (empty($kullanici_girisi) || empty($sifre)) {
        $mesaj = 'Lütfen tüm alanları doldurun.';
    } else {
        // Kullanıcının e-posta veya telefon numarası ile giriş yapmasını sağla (aktif kontrolü kaldırıldı)
        $stmt = $pdo->prepare("SELECT * FROM users WHERE (email = :giris OR telefon = :giris)");
        $stmt->execute(['giris' => $kullanici_girisi]);
        $user = $stmt->fetch();

        if ($user && password_verify($sifre, $user->sifre)) {
            // Oturum bilgilerini ayarla
            $_SESSION['user_id'] = $user->id;
            $_SESSION['user_ad'] = $user->ad;
            $_SESSION['user_soyad'] = $user->soyad;
            $_SESSION['user_rol'] = $user->rol;
            $_SESSION['user_aktif'] = $user->aktif; // Aktiflik durumunu da kaydet

            // Hesap pasifse sadece görevlerim sayfasına yönlendir
            if ($user->aktif == 0) {
                $_SESSION['mesaj'] = [
                    'tip' => 'warning', 
                    'icerik' => 'Hesabınız pasif durumdadır. Sadece görevlerinizi ve profilinizi görüntüleyebilirsiniz.'
                ];
                header("Location: gorevlerim.php");
                exit();
            }

            // Aktif kullanıcı - Rolüne göre yönlendir
            if ($user->rol == 1) {
                header("Location: admin/index.php");
            } else {
                header("Location: anasayfa.php");
            }
            exit();
        } else {
            $mesaj = 'E-posta/Telefon veya şifre hatalı.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Giriş Yap - Müsabaka Bilgi Sistemi</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
</head>
<body class="bg-gray-100 flex items-center justify-center h-screen">
    <div class="w-full max-w-md bg-white rounded-lg shadow-md p-8">
        <div class="text-center mb-6">
            <i class="fas fa-whistle text-4xl text-blue-600"></i>
            <h2 class="mt-2 text-2xl font-bold text-gray-800">Müsabaka Bilgi Sistemi</h2>
            <p class="text-gray-500">Lütfen hesabınıza giriş yapın</p>
        </div>

        <?php if (!empty($mesaj)): ?>
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4" role="alert">
                <span class="block sm:inline"><?php echo $mesaj; ?></span>
            </div>
        <?php endif; ?>

        <form method="POST">
            <div class="mb-4">
                <label for="kullanici_girisi" class="block text-sm font-medium text-gray-700">E-posta veya Telefon Numarası</label>
                <input type="text" name="kullanici_girisi" id="kullanici_girisi" class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-blue-500 focus:border-blue-500" placeholder="E-posta veya Telefon (05xx...)" required>
            </div>
            
            <div class="mb-4">
                <div class="flex items-center justify-between">
                    <label for="sifre" class="block text-sm font-medium text-gray-700">Şifre</label>
                    <a href="sifre_unuttum.php" class="text-sm text-blue-600 hover:text-blue-800 transition-colors">Şifremi Unuttum</a>
                </div>
                <input type="password" name="sifre" id="sifre" class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-blue-500 focus:border-blue-500" required>
            </div>

            <div class="mt-6">
                <button type="submit" class="w-full bg-blue-600 text-white py-2 px-4 rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors">
                    Giriş Yap
                </button>
            </div>
        </form>
    </div>
</body>
</html>