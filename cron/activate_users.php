<?php
require_once 'config/db.php';

// Geçmiş mazeretleri bul ve kullanıcıları aktif hale getir
$stmt = $pdo->prepare("
    SELECT mazeretler.user_id
    FROM mazeretler
    WHERE mazeretler.bitis_tarihi < CURDATE()
    AND mazeretler.durum = 'Onaylandı'
");
$stmt->execute();
$users_to_activate = $stmt->fetchAll(PDO::FETCH_COLUMN);

if (!empty($users_to_activate)) {
    $in_clause = str_repeat('?,', count($users_to_activate) - 1) . '?';
    $update_stmt = $pdo->prepare("UPDATE users SET durum = 1 WHERE id IN ($in_clause)");
    $update_stmt->execute($users_to_activate);

    // Mazeret durumlarını da 'Tamamlandı' gibi bir duruma çekebilirsiniz
    $update_mazeret_stmt = $pdo->prepare("UPDATE mazeretler SET durum = 'Tamamlandı' WHERE bitis_tarihi < CURDATE() AND durum = 'Onaylandı'");
    $update_mazeret_stmt->execute();
}
echo "Pasif hesaplar başarıyla kontrol edilip güncellendi.";
?>