<?php
require_once '../config/session_check_admin.php';
require_once '../config/db.php';

$musabaka_id = isset($_GET['musabaka_id']) ? (int)$_GET['musabaka_id'] : 0;

if ($musabaka_id <= 0) {
    die('Geçersiz müsabaka ID.');
}

// Müsabaka bilgilerini al
$stmt = $pdo->prepare("
    SELECT m.*, t1.ad as ev_sahibi, t2.ad as misafir, r.id as rapor_id
    FROM musabakalar m
    LEFT JOIN takimlar t1 ON m.ev_sahibi_id = t1.id
    LEFT JOIN takimlar t2 ON m.misafir_id = t2.id
    LEFT JOIN raporlar r ON m.id = r.musabaka_id
    WHERE m.id = ?
");
$stmt->execute([$musabaka_id]);
$musabaka = $stmt->fetch(PDO::FETCH_OBJ);

if (!$musabaka) {
    echo '<p class="text-red-600">Müsabaka bulunamadı.</p>';
    exit;
}

// Onay bekleyen raporları al
$stmt = $pdo->prepare("
    SELECT dr.* 
    FROM disiplin_raporlari dr 
    WHERE dr.rapor_id = ? AND dr.durum = 'beklemede'
    ORDER BY dr.rapor_tipi, dr.rapor_no
");
$stmt->execute([$musabaka->rapor_id]);
$raporlar = $stmt->fetchAll(PDO::FETCH_OBJ);

if (empty($raporlar)) {
    echo '<div class="bg-green-50 border border-green-200 rounded-md p-4">';
    echo '<p class="text-green-800">Bu müsabakadaki tüm raporlar zaten onaylanmış.</p>';
    echo '</div>';
    exit;
}

echo '<div class="space-y-4">';
echo '<div class="bg-blue-50 border border-blue-200 rounded-md p-4">';
echo '<h4 class="font-semibold text-blue-800">' . htmlspecialchars($musabaka->ev_sahibi) . ' - ' . htmlspecialchars($musabaka->misafir) . '</h4>';
echo '<p class="text-sm text-blue-600">' . date('d.m.Y', strtotime($musabaka->tarih)) . ' ' . date('H:i', strtotime($musabaka->saat)) . '</p>';
echo '</div>';

foreach ($raporlar as $rapor) {
    $rapor_adi = $rapor->rapor_no == 5 ? 'Ek Rapor' : $rapor->rapor_tipi . ' Raporu ' . $rapor->rapor_no;
    
    echo '<div id="rapor-row-' . $rapor->id . '" class="border rounded-md p-4 bg-white">';
    echo '<div class="flex justify-between items-start mb-3">';
    echo '<div>';
    echo '<h5 class="font-semibold text-gray-800">' . htmlspecialchars($rapor_adi) . '</h5>';
    echo '<p class="text-sm text-gray-600">Yükleme: ' . date('d.m.Y H:i', strtotime($rapor->created_at ?? 'now')) . '</p>';
    echo '</div>';
    echo '<span id="rapor-durum-' . $rapor->id . '" class="px-2 py-1 bg-yellow-100 text-yellow-800 text-xs rounded-full">Onay Bekliyor</span>';
    echo '</div>';
    
    echo '<div class="flex space-x-2 mb-3">';
    echo '<a href="../' . htmlspecialchars($rapor->dosya_yolu) . '" target="_blank" class="bg-blue-600 text-white py-1 px-3 rounded text-sm hover:bg-blue-700">';
    echo '<i class="fas fa-eye mr-1"></i> Görüntüle';
    echo '</a>';
    echo '<a href="../' . htmlspecialchars($rapor->dosya_yolu) . '" download class="bg-green-600 text-white py-1 px-3 rounded text-sm hover:bg-green-700">';
    echo '<i class="fas fa-download mr-1"></i> İndir';
    echo '</a>';
    echo '</div>';
    
    echo '<div class="border-t pt-3">';
    echo '<div class="flex space-x-2">';
    echo '<button onclick="onaylaRapor(' . $rapor->id . ', \'onaylandi\')" class="bg-green-600 text-white py-2 px-4 rounded-md hover:bg-green-700 flex-1">';
    echo '<i class="fas fa-check mr-1"></i> Onayla';
    echo '</button>';
    echo '<button onclick="document.getElementById(\'red-notu-' . $rapor->id . '\').classList.toggle(\'hidden\')" class="bg-red-600 text-white py-2 px-4 rounded-md hover:bg-red-700 flex-1">';
    echo '<i class="fas fa-times mr-1"></i> Reddet';
    echo '</button>';
    echo '</div>';
    
    echo '<div id="red-notu-' . $rapor->id . '" class="hidden mt-2">';
    echo '<textarea id="red_notu_' . $rapor->id . '" placeholder="Reddetme nedeni..." class="w-full border rounded-md p-2 text-sm" rows="2"></textarea>';
    echo '<button onclick="onaylaRapor(' . $rapor->id . ', \'reddedildi\')" class="bg-red-700 text-white py-1 px-3 rounded text-sm mt-1 hover:bg-red-800">';
    echo 'Reddetmeyi Onayla';
    echo '</button>';
    echo '</div>';
    echo '</div>';
    
    echo '</div>';
}

echo '</div>';
?>