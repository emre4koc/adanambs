<?php
require_once '../config/session_check_admin.php';
require_once '../config/db.php';

$sezon = date('Y');

echo "<h2>Debug Raporu - Müsaitlik Verileri</h2>";
echo "<pre>";

// 1. Users tablosundan birkaç kullanıcı al
echo "\n=== USERS TABLOSU (İlk 3 Kullanıcı) ===\n";
$users_debug = $pdo->query("SELECT id, ad, soyad, klasman, rol FROM users WHERE rol != 1 LIMIT 3")->fetchAll(PDO::FETCH_ASSOC);
print_r($users_debug);

if (!empty($users_debug)) {
    $first_user_id = $users_debug[0]['id'];
    
    // 2. Bu kullanıcının müsaitlik verilerini kontrol et
    echo "\n=== MÜSAİTLİK TABLOSU (User ID: $first_user_id) ===\n";
    $stmt = $pdo->prepare("SELECT * FROM musaitlik WHERE user_id = ? AND sezon = ?");
    $stmt->execute([$first_user_id, $sezon]);
    $musaitlik_debug = $stmt->fetchAll(PDO::FETCH_ASSOC);
    print_r($musaitlik_debug);
    
    // 3. Tüm sezonları göster
    echo "\n=== MEVCUT SEZONLAR ===\n";
    $sezonlar = $pdo->query("SELECT DISTINCT sezon FROM musaitlik ORDER BY sezon DESC")->fetchAll(PDO::FETCH_COLUMN);
    print_r($sezonlar);
    
    // 4. Her sezon için kayıt sayısı
    echo "\n=== SEZONLARA GÖRE KAYIT SAYILARI ===\n";
    foreach ($sezonlar as $s) {
        $count = $pdo->query("SELECT COUNT(*) FROM musaitlik WHERE sezon = '$s'")->fetchColumn();
        echo "Sezon $s: $count kayıt\n";
    }
    
    // 5. Müsait olanları say
    echo "\n=== MÜSAİT DURUMU İSTATİSTİKLERİ (Sezon: $sezon) ===\n";
    $musait_count = $pdo->query("SELECT COUNT(*) FROM musaitlik WHERE sezon = '$sezon' AND musait = 1")->fetchColumn();
    $musait_degil_count = $pdo->query("SELECT COUNT(*) FROM musaitlik WHERE sezon = '$sezon' AND musait = 0")->fetchColumn();
    $toplam_count = $pdo->query("SELECT COUNT(*) FROM musaitlik WHERE sezon = '$sezon'")->fetchColumn();
    
    echo "Müsait (musait=1): $musait_count\n";
    echo "Müsait Değil (musait=0): $musait_degil_count\n";
    echo "Toplam Kayıt: $toplam_count\n";
    
    // 6. Musait=1 olan birkaç kayıt
    echo "\n=== MÜSAİT=1 OLAN KAYITLAR (İlk 10) ===\n";
    $musait_ornekler = $pdo->query("SELECT m.*, u.ad, u.soyad FROM musaitlik m 
                                     JOIN users u ON m.user_id = u.id 
                                     WHERE m.sezon = '$sezon' AND m.musait = 1 
                                     LIMIT 10")->fetchAll(PDO::FETCH_ASSOC);
    print_r($musait_ornekler);
    
    // 7. Gun ve zaman_dilimi değerlerini kontrol et
    echo "\n=== GÜN DEĞERLERİ ===\n";
    $gunler_db = $pdo->query("SELECT DISTINCT gun FROM musaitlik")->fetchAll(PDO::FETCH_COLUMN);
    print_r($gunler_db);
    
    echo "\n=== ZAMAN DİLİMİ DEĞERLERİ ===\n";
    $zamanlar_db = $pdo->query("SELECT DISTINCT zaman_dilimi FROM musaitlik")->fetchAll(PDO::FETCH_COLUMN);
    print_r($zamanlar_db);
    
    // 8. Veri tiplerini kontrol et
    echo "\n=== VERİ TİPLERİ KONTROLÜ ===\n";
    if (!empty($musaitlik_debug)) {
        $ornek = $musaitlik_debug[0];
        echo "user_id tipi: " . gettype($ornek['user_id']) . " = " . var_export($ornek['user_id'], true) . "\n";
        echo "musait tipi: " . gettype($ornek['musait']) . " = " . var_export($ornek['musait'], true) . "\n";
        echo "gun tipi: " . gettype($ornek['gun']) . " = " . var_export($ornek['gun'], true) . "\n";
        echo "zaman_dilimi tipi: " . gettype($ornek['zaman_dilimi']) . " = " . var_export($ornek['zaman_dilimi'], true) . "\n";
    }
}

echo "</pre>";
?>
