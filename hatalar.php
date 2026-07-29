<?php
// Hiçbir HTML veya boşluk yazdırmadan direkt hataları açıyoruz
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

try {
    // Sizi 500'e düşüren anasayfayı sessizce içine çekiyoruz
    require_once 'anasayfa.php';
} catch (\Throwable $e) {
    // Eğer bir hata varsa şak diye ekrana basacak
    echo "<br><br><div style='background:#f8d7da; padding:15px; border:1px solid #f5c6cb; color: #721c24; border-radius:5px; font-family:sans-serif;'>";
    echo "<h3>🚨 İŞTE BULDUM! SİTEYİ ÇÖKERTEN HATA ŞU:</h3>";
    echo "<strong>Hata Mesajı:</strong> " . $e->getMessage() . "<br><br>";
    echo "<strong>Hatalı Dosya:</strong> " . $e->getFile() . "<br><br>";
    echo "<strong>Hatalı Satır:</strong> " . $e->getLine();
    echo "</div>";
}
?>