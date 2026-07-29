<?php
require_once 'config/session_check.php';
require_once 'config/db.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit();
}

$data = json_decode(file_get_contents('php://input'), true);

$egitim_id = isset($data['egitim_id']) ? (int)$data['egitim_id'] : 0;
$izlenme_suresi = isset($data['izlenme_suresi']) ? (int)$data['izlenme_suresi'] : 0; // saniye
$ilerleme_yuzdesi = isset($data['ilerleme_yuzdesi']) ? (int)$data['ilerleme_yuzdesi'] : 0;
$user_id = $_SESSION['user_id'];

if (!$egitim_id) {
    echo json_encode(['success' => false, 'message' => 'Eğitim ID gerekli']);
    exit();
}

try {
    // Mevcut kaydı kontrol et
    $stmt = $pdo->prepare("SELECT * FROM egitim_goruntulemeler WHERE egitim_id = ? AND user_id = ?");
    $stmt->execute([$egitim_id, $user_id]);
    $kayit = $stmt->fetch();
    
    if ($kayit) {
        // Güncelle
        $yeni_toplam_sure = $kayit->toplam_izlenme_suresi + $izlenme_suresi;
        $tamamlandi = $ilerleme_yuzdesi >= 90 ? 1 : 0;
        
        $stmt = $pdo->prepare("
            UPDATE egitim_goruntulemeler 
            SET toplam_izlenme_suresi = ?,
                ilerleme_yuzdesi = ?,
                tamamlandi = ?,
                son_goruntulenme_tarihi = NOW(),
                izlenme_sayisi = izlenme_sayisi + 1
            WHERE egitim_id = ? AND user_id = ?
        ");
        $stmt->execute([$yeni_toplam_sure, $ilerleme_yuzdesi, $tamamlandi, $egitim_id, $user_id]);
    } else {
        // Yeni kayıt oluştur
        $tamamlandi = $ilerleme_yuzdesi >= 90 ? 1 : 0;
        
        $stmt = $pdo->prepare("
            INSERT INTO egitim_goruntulemeler 
            (egitim_id, user_id, toplam_izlenme_suresi, ilerleme_yuzdesi, tamamlandi)
            VALUES (?, ?, ?, ?, ?)
        ");
        $stmt->execute([$egitim_id, $user_id, $izlenme_suresi, $ilerleme_yuzdesi, $tamamlandi]);
        
        // Eğitimin görüntülenme sayısını artır
        $pdo->prepare("UPDATE egitimler SET goruntulenme_sayisi = goruntulenme_sayisi + 1 WHERE id = ?")->execute([$egitim_id]);
    }
    
    echo json_encode(['success' => true, 'message' => 'İzleme kaydedildi']);
    
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Hata: ' . $e->getMessage()]);
}
