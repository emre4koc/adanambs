<?php
// Basit, bağımsız SMTP test scripti
// Amaç: sadece mail sunucu bağlantısının çalışıp çalışmadığını görmek
// Anasayfa.php'ye dokunmaz, doğum günü mantığına girmez

error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once __DIR__ . '/lib/PHPMailer/Exception.php';
require_once __DIR__ . '/lib/PHPMailer/PHPMailer.php';
require_once __DIR__ . '/lib/PHPMailer/SMTP.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception as PHPMailerException;

$mail = new PHPMailer(true);

try {
    // Ayrıntılı SMTP konuşmasını ekrana bas (sorunu görmek için)
    $mail->SMTPDebug = 2;

    $mail->isSMTP();
    $mail->Host       = 'mail.adanambs.com';
    $mail->SMTPAuth   = true;
    $mail->Username   = 'ihk@adanambs.com';
    $mail->Password   = 'd1BV%mI!7zYtfDxr';
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS; // Port 465 -> SSL/SMTPS
    $mail->Port       = 465;
    $mail->CharSet    = 'UTF-8';

    $mail->setFrom('ihk@adanambs.com', 'Test Sistemi');
    $mail->addAddress('emre4koc@gmail.com'); // Test alıcısı

    $mail->isHTML(true);
    $mail->Subject = 'SMTP Test Maili';
    $mail->Body    = 'Bu maili görüyorsan SMTP ayarların doğru çalışıyor demektir.';

    $mail->send();
    echo "<h2 style='color:green;'>BAŞARILI: Test maili gönderildi.</h2>";
    echo "Gelen kutunu (ve spam klasörünü) kontrol et.";

} catch (PHPMailerException $e) {
    echo "<h2 style='color:red;'>HATA: Mail gönderilemedi.</h2>";
    echo "Hata detayı: " . $mail->ErrorInfo;
}