<?php
// Veritabanı bağlantısını dahil et. Veritabanı dosyası oturumu başlatmayı zaten yönetiyor.
// Bu dosyanın diğer dosyalardan çağrıldığında doğru yolu bulabilmesi için __DIR__ kullanıyoruz.
require_once __DIR__ . '/db.php';

// Eğer kullanıcı ID'si session'da yoksa login sayfasına yönlendir
if (!isset($_SESSION['user_id'])) {
    header("Location: /login.php");
    exit();
}

// Eğer ad, soyad gibi kritik oturum bilgileri eksikse,
// veritabanından bu bilgileri çekerek oturumu tamamla.
if (!isset($_SESSION['user_ad']) || !isset($_SESSION['user_soyad']) || !isset($_SESSION['user_aktif'])) {
    try {
        $stmt = $pdo->prepare("SELECT ad, soyad, rol, aktif FROM users WHERE id = ?");
        $stmt->execute([$_SESSION['user_id']]);
        $user = $stmt->fetch();

        if ($user) {
            // Eksik oturum değişkenlerini doldur
            $_SESSION['user_ad'] = $user->ad;
            $_SESSION['user_soyad'] = $user->soyad;
            $_SESSION['user_rol'] = $user->rol;
            $_SESSION['user_aktif'] = $user->aktif;
        } else {
            // Eğer session'daki kullanıcı ID'si veritabanında yoksa (geçersizse),
            // oturumu sonlandır ve giriş sayfasına yönlendir.
            session_unset();
            session_destroy();
            header("Location: /login.php");
            exit();
        }
    } catch (PDOException $e) {
        // Olası bir veritabanı hatasında işlemi durdur ve hata mesajı göster.
        die("Oturum bilgileri doğrulanırken bir hata oluştu: " . $e->getMessage());
    }
}

// PASİF KULLANICI KONTROLÜ - Sadece görevlerim ve profil sayfalarına erişebilir
if (isset($_SESSION['user_aktif']) && $_SESSION['user_aktif'] == 0) {
    // İzin verilen sayfalar
    $allowed_pages = ['gorevlerim.php', 'profil.php', 'logout.php'];
    $current_page = basename($_SERVER['PHP_SELF']);
    
    if (!in_array($current_page, $allowed_pages)) {
        $_SESSION['mesaj'] = [
            'tip' => 'warning',
            'icerik' => 'Hesabınız pasif durumdadır. Sadece görevlerinizi ve profilinizi görüntüleyebilirsiniz.'
        ];
        header("Location: /gorevlerim.php");
        exit();
    }
}
?>