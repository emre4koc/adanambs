<?php
session_start();
require_once 'config/db.php';
require_once 'lib/PHPMailer/Exception.php';
require_once 'lib/PHPMailer/PHPMailer.php';
require_once 'lib/PHPMailer/SMTP.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

$mesaj = '';
$mesaj_turu = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email']);
    
    $stmt = $pdo->prepare("SELECT id, ad, soyad FROM users WHERE email = ? AND aktif = 1");
    $stmt->execute([$email]);
    $user = $stmt->fetch(PDO::FETCH_OBJ);

    if ($user) {
        $kod = rand(100000, 999999);
        
        // Önceki kodları temizle ve yenisini ekle
        $pdo->prepare("DELETE FROM sifre_sifirlama WHERE user_id = ?")->execute([$user->id]);
        $pdo->prepare("INSERT INTO sifre_sifirlama (user_id, kod) VALUES (?, ?)")->execute([$user->id, $kod]);

        $mail = new PHPMailer(true);
        try {
            // SMTP Ayarları (Çalışan hatirlatma_gonder.php'den alınmıştır)
            $mail->isSMTP();
            $mail->Host       = 'smtp.turkticaret.net'; 
            $mail->SMTPAuth   = true;
            $mail->Username   = 'ihk@mbsadana.com'; 
            $mail->Password   = 'Mbsadana.01+*';                
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port       = 587;
            $mail->CharSet    = 'UTF-8';
            $mail->setFrom('ihk@mbsadana.com', 'ADANA İHK');
            $mail->addAddress($email);

            $mail->SMTPOptions = array(
                'ssl' => array(
                    'verify_peer' => false,
                    'verify_peer_name' => false,
                    'allow_self_signed' => true
                )
            );

            $mail->isHTML(true);
            $mail->Subject = "Şifre Sıfırlama Onay Kodu";
            $mail->Body = "
                <div style='font-family: Arial, sans-serif; padding: 20px; background-color: #f4f4f4;'>
                    <div style='max-width: 500px; margin: auto; background: white; padding: 20px; border-radius: 10px; border: 1px solid #ddd;'>
                        <h2 style='color: #1e3a8a; text-align: center;'>Şifre Sıfırlama</h2>
                        <p>Sayın <b>{$user->ad} {$user->soyad}</b>,</p>
                        <p>Hesabınız için şifre sıfırlama talebinde bulunulmuştur. Onay kodunuz:</p>
                        <div style='background: #eff6ff; padding: 15px; text-align: center; font-size: 24px; font-weight: bold; letter-spacing: 5px; color: #1e40af; border: 1px dashed #3b82f6;'>
                            {$kod}
                        </div>
                        <p style='font-size: 12px; color: #666; margin-top: 20px;'>Bu kod 30 dakika süreyle geçerlidir. Eğer bu talebi siz yapmadıysanız lütfen bu e-postayı dikkate almayınız.</p>
                    </div>
                </div>";

            $mail->send();
            $_SESSION['reset_user_id'] = $user->id;
            header("Location: sifre_onayla.php");
            exit();

        } catch (Exception $e) {
            $mesaj = "E-posta gönderilemedi. Hata: {$mail->ErrorInfo}";
            $mesaj_turu = "red";
        }
    } else {
        $mesaj = "Bu e-posta adresi sistemde kayıtlı değil.";
        $mesaj_turu = "red";
    }
}
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <title>Şifremi Unuttum</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 flex items-center justify-center h-screen">
    <div class="w-full max-w-md bg-white rounded-lg shadow-md p-8">
        <h2 class="text-2xl font-bold text-center text-gray-800 mb-6">Şifremi Unuttum</h2>
        <?php if($mesaj): ?>
            <div class="bg-<?php echo $mesaj_turu; ?>-100 text-<?php echo $mesaj_turu; ?>-700 p-3 rounded mb-4"><?php echo $mesaj; ?></div>
        <?php endif; ?>
        <form method="POST">
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700">E-posta Adresiniz</label>
                <input type="email" name="email" class="mt-1 block w-full border border-gray-300 rounded-md p-2 focus:ring-blue-500 focus:border-blue-500" required>
            </div>
            <button type="submit" class="w-full bg-blue-600 text-white py-2 rounded-md hover:bg-blue-700 transition">Kod Gönder</button>
            <div class="mt-4 text-center">
                <a href="login.php" class="text-sm text-gray-500 hover:underline">Giriş Ekranına Dön</a>
            </div>
        </form>
    </div>
</body>
</html>