<?php
session_start();
require_once 'config/db.php';

if (!isset($_SESSION['reset_user_id'])) {
    header("Location: login.php");
    exit();
}

$mesaj = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $kod = $_POST['kod'];
    $yeni_sifre = $_POST['yeni_sifre'];
    $user_id = $_SESSION['reset_user_id'];

    // Kodu kontrol et (Son 30 dakika içinde mi?)
    $stmt = $pdo->prepare("SELECT * FROM sifre_sifirlama WHERE user_id = ? AND kod = ? AND olusturma_tarihi > DATE_SUB(NOW(), INTERVAL 30 MINUTE)");
    $stmt->execute([$user_id, $kod]);
    
    if ($stmt->fetch()) {
        $hashli_sifre = password_hash($yeni_sifre, PASSWORD_DEFAULT);
        
        // Şifreyi güncelle
        $update = $pdo->prepare("UPDATE users SET sifre = ? WHERE id = ?");
        $update->execute([$hashli_sifre, $user_id]);
        
        // Kodları temizle
        $pdo->prepare("DELETE FROM sifre_sifirlama WHERE user_id = ?")->execute([$user_id]);
        
        unset($_SESSION['reset_user_id']);
        die("<script>alert('Şifreniz başarıyla güncellendi.'); window.location.href='login.php';</script>");
    } else {
        $mesaj = "Geçersiz veya süresi dolmuş kod.";
    }
}
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <title>Kodu Onayla</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 flex items-center justify-center h-screen">
    <div class="w-full max-w-md bg-white rounded-lg shadow-md p-8">
        <h2 class="text-2xl font-bold text-center mb-6">Yeni Şifre Belirle</h2>
        <?php if($mesaj): ?>
            <div class="bg-red-100 text-red-700 p-3 rounded mb-4"><?php echo $mesaj; ?></div>
        <?php endif; ?>
        <form method="POST">
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700">6 Haneli Onay Kodu</label>
                <input type="text" name="kod" maxlength="6" class="mt-1 block w-full border border-gray-300 rounded-md p-2 text-center text-2xl tracking-widest" required>
            </div>
            <div class="mb-6">
                <label class="block text-sm font-medium text-gray-700">Yeni Şifre</label>
                <input type="password" name="yeni_sifre" class="mt-1 block w-full border border-gray-300 rounded-md p-2" required minlength="6">
            </div>
            <button type="submit" class="w-full bg-green-600 text-white py-2 rounded-md hover:bg-green-700">Şifreyi Güncelle</button>
        </form>
    </div>
</body>
</html>