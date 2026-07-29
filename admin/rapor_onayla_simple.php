<?php
// rapor_onayla_simple.php
require_once '../config/session_check_admin.php';
require_once '../config/db.php';

if (isset($_GET['rapor_id'])) {
    // Tekil rapor onaylama
    $rapor_id = $_GET['rapor_id'];
    $durum = $_GET['durum'];
    
    $update = $pdo->prepare("UPDATE disiplin_raporlari SET durum = ?, onaylayan_id = ?, onay_tarihi = NOW() WHERE id = ?");
    $update->execute([$durum, $_SESSION['user_id'], $rapor_id]);
    
    $_SESSION['mesaj'] = ['tip' => 'success', 'icerik' => 'Rapor başarıyla ' . ($durum == 'onaylandi' ? 'onaylandı' : 'reddedildi')];
    
} elseif (isset($_GET['musabaka_id'])) {
    // Müsabakadaki tüm raporları onaylama
    $musabaka_id = $_GET['musabaka_id'];
    
    // Müsabakanın rapor ID'sini bul
    $stmt = $pdo->prepare("SELECT id FROM raporlar WHERE musabaka_id = ?");
    $stmt->execute([$musabaka_id]);
    $rapor = $stmt->fetch(PDO::FETCH_OBJ);
    
    if ($rapor) {
        // Tüm bekleyen raporları onayla
        $update = $pdo->prepare("UPDATE disiplin_raporlari SET durum = 'onaylandi', onaylayan_id = ?, onay_tarihi = NOW() WHERE rapor_id = ? AND durum = 'beklemede'");
        $update->execute([$_SESSION['user_id'], $rapor->id]);
        
        $affected_rows = $update->rowCount();
        $_SESSION['mesaj'] = ['tip' => 'success', 'icerik' => $affected_rows . ' rapor başarıyla onaylandı'];
    }
}

// Listeye geri dön
header("Location: disiplin_raporu_listesi.php");
exit;
?>