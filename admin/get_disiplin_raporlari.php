<?php
require_once '../config/session_check_admin.php';
require_once '../config/db.php';

$musabaka_id = isset($_GET['musabaka_id']) ? (int)$_GET['musabaka_id'] : 0;
$rapor_tipi = isset($_GET['rapor_tipi']) ? $_GET['rapor_tipi'] : '';

if ($musabaka_id <= 0 || empty($rapor_tipi)) {
    die('Geçersiz parametreler.');
}

// Rapor ID'sini al
$stmt = $pdo->prepare("SELECT id FROM raporlar WHERE musabaka_id = ?");
$stmt->execute([$musabaka_id]);
$rapor = $stmt->fetch();

if (!$rapor) {
    echo '<p class="text-gray-500">Rapor bulunamadı.</p>';
    exit;
}

// Disiplin raporlarını çek
$stmt = $pdo->prepare("
    SELECT * FROM disiplin_raporlari 
    WHERE rapor_id = ? AND rapor_tipi = ? 
    ORDER BY rapor_no
");
$stmt->execute([$rapor->id, $rapor_tipi]);
$raporlar = $stmt->fetchAll(PDO::FETCH_OBJ);

if (empty($raporlar)) {
    echo '<p class="text-gray-500">Bu tipte disiplin raporu bulunamadı.</p>';
    exit;
}

echo '<div class="space-y-4">';
foreach ($raporlar as $rapor) {
    $rapor_adi = $rapor->rapor_no == 5 ? 'Ek Rapor' : "Rapor {$rapor->rapor_no}";
    echo '
    <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
        <div class="flex items-center">
            <i class="fas fa-file-image text-2xl ' . ($rapor_tipi == 'gozlemci' ? 'text-green-600' : 'text-red-600') . ' mr-3"></i>
            <div>
                <h4 class="font-semibold text-gray-800">' . htmlspecialchars($rapor_adi) . '</h4>
                <p class="text-sm text-gray-600">' . date('d.m.Y H:i', strtotime($rapor->created_at ?? 'now')) . '</p>
            </div>
        </div>
        <a href="../' . htmlspecialchars($rapor->dosya_yolu) . '" 
           target="_blank" 
           class="bg-blue-600 text-white py-2 px-4 rounded-md hover:bg-blue-700 text-sm">
            <i class="fas fa-download mr-1"></i> İndir
        </a>
    </div>';
}
echo '</div>';
?>