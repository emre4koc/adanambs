<?php
require_once '../config/session_check_admin.php';
require_once '../config/db.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['status' => 'error', 'message' => 'Geçersiz istek methodu.']);
    exit();
}

if (!isset($_POST['user_id']) || !isset($_POST['field']) || !isset($_POST['value'])) {
    echo json_encode(['status' => 'error', 'message' => 'Eksik parametre.']);
    exit();
}

$userId = (int)$_POST['user_id'];
$field = $_POST['field'];
$value = (int)$_POST['value'];

// Güvenlik kontrolü - sadece izin verilen alanlar güncellenebilir
$allowedFields = ['egitim_durum', 'antreman_durum', 'ceza_durum', 'aktif'];
if (!in_array($field, $allowedFields)) {
    echo json_encode(['status' => 'error', 'message' => 'Geçersiz alan.']);
    exit();
}

try {
    $stmt = $pdo->prepare("UPDATE users SET $field = ? WHERE id = ?");
    if ($stmt->execute([$value, $userId])) {
        echo json_encode(['status' => 'success', 'message' => 'Durum güncellendi.']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Güncelleme başarısız.']);
    }
} catch (Exception $e) {
    echo json_encode(['status' => 'error', 'message' => 'Hata: ' . $e->getMessage()]);
}