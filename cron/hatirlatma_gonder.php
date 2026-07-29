<?php
// Hata loglama
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/cron_errors.log');

require_once __DIR__ . '/../config/db.php'; 
require_once __DIR__ . '/../lib/PHPMailer/Exception.php';
require_once __DIR__ . '/../lib/PHPMailer/PHPMailer.php';
require_once __DIR__ . '/../lib/PHPMailer/SMTP.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

date_default_timezone_set('Europe/Istanbul');

try {
    // 3 saat içinde başlayacak aktif maçlar (185 dakika tolerans ile)
    $sorgu = "SELECT m.*, t1.ad as ev_sahibi, t2.ad as misafir, s.ad as stadyum 
              FROM musabakalar m
              JOIN takimlar t1 ON m.ev_sahibi_id = t1.id
              JOIN takimlar t2 ON m.misafir_id = t2.id
              JOIN stadyumlar s ON m.stadyum_id = s.id
              WHERE m.durum = 'Atandı'
              AND TIMESTAMP(m.tarih, m.saat) BETWEEN NOW() AND NOW() + INTERVAL 185 MINUTE";

    $stmt = $pdo->query($sorgu);
    $maclar = $stmt->fetchAll(PDO::FETCH_OBJ);

	$mail = new PHPMailer(true);
	$mail->SMTPDebug = 2; // Bu, arka planda dönen tüm konuşmayı ekrana basar
    $mail->isSMTP();
    $mail->Host       = 'mail.adanambs.com'; 
    $mail->SMTPAuth   = true;
    $mail->Username   = 'ihk@adanambs.com'; // Kendi mailin
    $mail->Password   = 'd1BV%mI!7zYtfDxr';                // Mail şifren
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
    $mail->Port       = 465;
	$mail->SMTPOptions = array(
        'ssl' => array(
            'verify_peer' => false,
            'verify_peer_name' => false,
            'allow_self_signed' => true
        )
    );
    $mail->CharSet    = 'UTF-8';
    $mail->setFrom('ihk@adanambs.com', 'ADANA İHK');

    foreach ($maclar as $mac) {
        $gorevli_ids = array_filter([$mac->hakem_id, $mac->yardimci_1_id, $mac->yardimci_2_id, $mac->dorduncu_hakem_id, $mac->gozlemci_id]);
        
        foreach ($gorevli_ids as $uid) {
            $check = $pdo->prepare("SELECT id FROM musabaka_hatirlatmalar WHERE musabaka_id = ? AND user_id = ?");
            $check->execute([$mac->id, $uid]);
            
            if ($check->rowCount() == 0) {
                $u_stmt = $pdo->prepare("SELECT email, ad, soyad FROM users WHERE id = ?");
                $u_stmt->execute([$uid]);
                $u = $u_stmt->fetch(PDO::FETCH_OBJ);

                if ($u && filter_var($u->email, FILTER_VALIDATE_EMAIL)) {
                    try {
                        $mail->clearAddresses();
                        $mail->addAddress($u->email);
                        $mail->isHTML(true);
                        $mail->Subject = "GÖREV HATIRLATMASI: {$mac->ev_sahibi} - {$mac->misafir}";

                        // HTML TASARIMI
                        $mail->Body = "
                        <div style='font-family: Arial, sans-serif; background-color: #f4f4f4; padding: 20px;'>
                            <div style='max-width: 600px; margin: 0 auto; background-color: #ffffff; border-radius: 10px; overflow: hidden; box-shadow: 0 4px 10px rgba(0,0,0,0.1); border: 1px solid #ddd;'>
                                <div style='background-color: #1e3a8a; color: #ffffff; padding: 20px; text-align: center;'>
                                    <h2 style='margin: 0; font-size: 20px; letter-spacing: 1px;'>MÜSABAKA GÖREV HATIRLATMASI</h2>
                                </div>
                                <div style='padding: 25px;'>
                                    <p style='font-size: 16px; color: #333;'>Sayın <b>{$u->ad} {$u->soyad}</b>,</p>
                                    <p style='font-size: 14px; color: #555;'>Görevli olduğunuz müsabakanın başlamasına yaklaşık <b>3 saat</b> kalmıştır. Detaylar aşağıda yer almaktadır:</p>
                                    
                                    <table style='width: 100%; border-collapse: collapse; margin-top: 20px; background-color: #f9fafb;'>
                                        <tr>
                                            <td style='padding: 12px; border: 1px solid #e5e7eb; font-weight: bold; color: #4b5563; width: 30%;'>Müsabaka</td>
                                            <td style='padding: 12px; border: 1px solid #e5e7eb; color: #111827;'>{$mac->ev_sahibi} vs {$mac->misafir}</td>
                                        </tr>
                                        <tr>
                                            <td style='padding: 12px; border: 1px solid #e5e7eb; font-weight: bold; color: #4b5563;'>Stadyum / Saha</td>
                                            <td style='padding: 12px; border: 1px solid #e5e7eb; color: #111827;'>{$mac->stadyum}</td>
                                        </tr>
                                        <tr>
                                            <td style='padding: 12px; border: 1px solid #e5e7eb; font-weight: bold; color: #4b5563;'>Tarih / Saat</td>
                                            <td style='padding: 12px; border: 1px solid #e5e7eb; color: #ef4444; font-weight: bold;'>".date('d.m.Y', strtotime($mac->tarih))." - ".substr($mac->saat, 0, 5)."</td>
                                        </tr>
                                    </table>

                                    <div style='margin-top: 25px; padding: 15px; background-color: #eff6ff; border-left: 4px solid #3b82f6; font-size: 13px; color: #1e40af;'>
                                        <b>Önemli Hatırlatma:</b> Lütfen müsabaka saatinden en az 60 dakika önce görev yerinizde hazır bulununuz.
                                    </div>
                                </div>
                                <div style='background-color: #f3f4f6; color: #9ca3af; padding: 15px; text-align: center; font-size: 11px;'>
                                    Bu e-posta sistem tarafından otomatik olarak gönderilmiştir. Lütfen yanıtlamayınız. <br>
                                    © ".date('Y')." Müsabaka Bilgi Sistemi
                                </div>
                            </div>
                        </div>";

                        if($mail->send()){
                            $ins = $pdo->prepare("INSERT INTO musabaka_hatirlatmalar (musabaka_id, user_id) VALUES (?, ?)");
                            $ins->execute([$mac->id, $uid]);
                        }
                    } catch (Exception $e) {
                        error_log("Gönderim hatası ({$u->email}): " . $mail->ErrorInfo);
                    }
                }
            }
        }
    }
} catch (Exception $e) {
    error_log("Genel Cron Hatası: " . $e->getMessage());
}