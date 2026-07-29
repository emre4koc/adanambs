<?php
require_once '../config/session_check_admin.php';
require_once '../config/db.php';

$musabaka_id = $_GET['id'];
$islem = $_GET['islem'];

// Rapor ID'sini bul
$stmt = $pdo->prepare("SELECT id FROM raporlar WHERE musabaka_id = ?");
$stmt->execute([$musabaka_id]);
$rapor = $stmt->fetch(PDO::FETCH_ASSOC);

if ($rapor) {
    $durum = ($islem == 'onayla') ? 'onaylandi' : 'reddedildi';
    
    $update = $pdo->prepare("UPDATE disiplin_raporlari SET durum = ?, onaylayan_id = ?, onay_tarihi = NOW() WHERE rapor_id = ?");
    $update->execute([$durum, $_SESSION['user_id'], $rapor['id']]);
    
    // YENİ: Rapor onaylandı/reddedildiğinde müsabakayı ARŞİVLE
    $arsivle = $pdo->prepare("UPDATE musabakalar SET arsiv = 1 WHERE id = ?");
    $arsivle->execute([$musabaka_id]);
    
    $_SESSION['mesaj'] = 'Raporlar ' . ($islem == 'onayla' ? 'onaylandı' : 'reddedildi') . ' ve müsabaka arşivlendi';
}

header("Location: disiplin_raporu_listesi.php");
exit;
?>
