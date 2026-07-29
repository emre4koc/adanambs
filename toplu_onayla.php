<?php
require_once 'config/session_check.php';
require_once 'config/db.php';

// Sadece POST isteği kabul et
if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_POST['musabaka_ids']) || !is_array($_POST['musabaka_ids'])) {
    header("Location: gorevlerim.php?filtre=aktif");
    exit();
}

$user_id   = $_SESSION['user_id'];
$ids       = array_map('intval', $_POST['musabaka_ids']);
$basarili  = 0;
$hatali    = 0;

foreach ($ids as $musabaka_id) {
    if ($musabaka_id <= 0) { $hatali++; continue; }

    // Kullanıcının bu maçtaki rolünü bul
    $stmt = $pdo->prepare("
        SELECT hakem_id, yardimci_1_id, yardimci_2_id, dorduncu_hakem_id, gozlemci_id
        FROM musabakalar
        WHERE id = ? AND arsiv = 0
    ");
    $stmt->execute([$musabaka_id]);
    $gorevliler = $stmt->fetch();

    if (!$gorevliler) { $hatali++; continue; }

    $rol_kolonu = '';
    if     ($gorevliler->hakem_id          == $user_id) $rol_kolonu = 'hakem_onay';
    elseif ($gorevliler->yardimci_1_id     == $user_id) $rol_kolonu = 'yardimci_1_onay';
    elseif ($gorevliler->yardimci_2_id     == $user_id) $rol_kolonu = 'yardimci_2_onay';
    elseif ($gorevliler->dorduncu_hakem_id == $user_id) $rol_kolonu = 'dorduncu_hakem_onay';
    elseif ($gorevliler->gozlemci_id       == $user_id) $rol_kolonu = 'gozlemci_onay';

    if (empty($rol_kolonu)) { $hatali++; continue; }

    // Onay durumunu güncelle
    $update = $pdo->prepare("UPDATE musabakalar SET {$rol_kolonu} = 1 WHERE id = ?");
    if ($update->execute([$musabaka_id])) {
        $basarili++;

        // Yöneticilere bildirim gönder
        $mac_stmt = $pdo->prepare("
            SELECT t1.ad as ev_sahibi, t2.ad as misafir
            FROM musabakalar m
            JOIN takimlar t1 ON m.ev_sahibi_id = t1.id
            JOIN takimlar t2 ON m.misafir_id = t2.id
            WHERE m.id = ?
        ");
        $mac_stmt->execute([$musabaka_id]);
        $mac = $mac_stmt->fetch();
        $mac_adi = $mac ? "{$mac->ev_sahibi} - {$mac->misafir}" : "Müsabaka #{$musabaka_id}";

        $user_stmt = $pdo->prepare("SELECT CONCAT(ad, ' ', soyad) as isim FROM users WHERE id = ?");
        $user_stmt->execute([$user_id]);
        $user_bilgi = $user_stmt->fetch();
        $user_isim  = $user_bilgi ? $user_bilgi->isim : 'Kullanıcı';

        $bildirim_linki = "/musabaka_detay.php?id={$musabaka_id}";
        $bildirim_mesaj = "{$user_isim} görevi onayladı: {$mac_adi}";

        $yonetici_stmt = $pdo->query("SELECT id FROM users WHERE rol = 1");
        $yoneticiler   = $yonetici_stmt->fetchAll();
        foreach ($yoneticiler as $y) {
            $notif = $pdo->prepare("INSERT INTO bildirimler (user_id, mesaj, link) VALUES (?, ?, ?)");
            $notif->execute([$y->id, $bildirim_mesaj, $bildirim_linki]);
        }
    } else {
        $hatali++;
    }
}

// Sonuç mesajı
if ($basarili > 0 && $hatali == 0) {
    $_SESSION['mesaj'] = ['tip' => 'success', 'icerik' => "{$basarili} müsabaka başarıyla onaylandı."];
} elseif ($basarili > 0 && $hatali > 0) {
    $_SESSION['mesaj'] = ['tip' => 'warning', 'icerik' => "{$basarili} müsabaka onaylandı, {$hatali} müsabakada hata oluştu."];
} else {
    $_SESSION['mesaj'] = ['tip' => 'error', 'icerik' => "Onaylama işlemi sırasında hata oluştu."];
}

header("Location: gorevlerim.php?filtre=aktif");
exit();