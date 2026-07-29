<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

echo "<div style='font-family: Arial, sans-serif; padding: 20px; line-height: 1.6;'>";
echo "<h2>Anasayfa Birebir Sorgu Testi</h2><hr>";

require_once 'config/db.php';

// 1. Duyuru Okundu Testi
echo "<h3>1. Okunmamış Duyuru Sorgusu</h3>";
try {
    $okunmamis_stmt = $pdo->prepare("
        SELECT d.* FROM duyurular d
        WHERE d.arsiv = 0
        AND d.id NOT IN (
            SELECT duyuru_id FROM duyuru_okundu WHERE user_id = 1
        )
        ORDER BY d.olusturma_tarihi DESC
        LIMIT 1
    ");
    $okunmamis_stmt->execute();
    echo "<p style='color:green;'>✓ Başarılı</p>";
} catch (Exception $e) {
    echo "<p style='color:red;'>X HATA: " . $e->getMessage() . "</p>";
}

// 2. Büyük Müsabaka Sorgusu Testi
echo "<h3>2. Haftalık Müsabaka (Büyük JOIN) Sorgusu</h3>";
try {
    $stmt = $pdo->prepare("
        SELECT m.*, l.ad as lig_adi, s.ad as stadyum_adi, 
               t1.ad as ev_sahibi, t2.ad as misafir,
               CONCAT(h.ad, ' ', h.soyad) as hakem,
               CONCAT(y1.ad, ' ', y1.soyad) as yardimci_1,
               CONCAT(y2.ad, ' ', y2.soyad) as yardimci_2,
               CONCAT(d4.ad, ' ', d4.soyad) as dorduncu_hakem,
               CONCAT(g.ad, ' ', g.soyad) as gozlemci
        FROM musabakalar m
        LEFT JOIN ligler l ON m.lig_id = l.id
        LEFT JOIN stadyumlar s ON m.stadyum_id = s.id
        LEFT JOIN takimlar t1 ON m.ev_sahibi_id = t1.id
        LEFT JOIN takimlar t2 ON m.misafir_id = t2.id
        LEFT JOIN users h ON m.hakem_id = h.id
        LEFT JOIN users y1 ON m.yardimci_1_id = y1.id
        LEFT JOIN users y2 ON m.yardimci_2_id = y2.id
        LEFT JOIN users d4 ON m.dorduncu_hakem_id = d4.id
        LEFT JOIN users g ON m.gozlemci_id = g.id
        WHERE m.arsiv = 0
        ORDER BY CAST(m.mac_no AS UNSIGNED) ASC
        LIMIT 1
    ");
    $stmt->execute();
    echo "<p style='color:green;'>✓ Başarılı</p>";
} catch (Exception $e) {
    echo "<p style='color:red;'>X HATA: " . $e->getMessage() . "</p>";
}

// 3. Görev Sayısı Testi
echo "<h3>3. Aktif Görev Sayısı Sorgusu</h3>";
try {
    $aktif_gorev_sayisi_stmt = $pdo->prepare("SELECT COUNT(*) FROM musabakalar WHERE (hakem_id = 1 OR yardimci_1_id = 1 OR yardimci_2_id = 1 OR dorduncu_hakem_id = 1 OR gozlemci_id = 1) AND arsiv = 0");
    $aktif_gorev_sayisi_stmt->execute();
    echo "<p style='color:green;'>✓ Başarılı</p>";
} catch (Exception $e) {
    echo "<p style='color:red;'>X HATA: " . $e->getMessage() . "</p>";
}

// 4. Doğum Günü Testi
echo "<h3>4. Doğum Günü (Aktif Sütunu) Sorgusu</h3>";
try {
    $bugun = date('m-d');
    $dogum_gunu_olanlar = $pdo->query("
        SELECT id, ad, soyad, email, 
               DATE_FORMAT(dogum_tarihi, '%d.%m.%Y') as dogum_tarihi_format,
               YEAR(CURDATE()) - YEAR(dogum_tarihi) as yas
        FROM users 
        WHERE DATE_FORMAT(dogum_tarihi, '%m-%d') = '$bugun'
        AND aktif = 1
        ORDER BY ad, soyad
    ")->fetchAll();
    echo "<p style='color:green;'>✓ Başarılı</p>";
} catch (Exception $e) {
    echo "<p style='color:red;'>X HATA: " . $e->getMessage() . "</p>";
}

echo "</div>";
?>