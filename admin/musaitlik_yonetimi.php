<?php
require_once '../config/session_check_admin.php';
require_once '../config/db.php';
@include_once '../lib/SimpleXLSXGen.php'; // Excel oluşturucu kütüphanesi

$sayfa_baslik = "Müsaitlik Yönetimi";
$gunler = ["Cumartesi", "Pazar", "Pazartesi", "Salı", "Çarşamba", "Perşembe", "Cuma"];
$zamanlar = ["Sabah", "Ogle", "Aksam"];

// Filtreleme için klasmanları ve hesap durumunu al
$secili_klasmanlar = isset($_GET['klasmanlar']) && is_array($_GET['klasmanlar']) ? $_GET['klasmanlar'] : [];
$hesap_durumu_filtresi = isset($_GET['hesap_durumu']) ? $_GET['hesap_durumu'] : 'tumu';

// Sıralama için parametreleri al
$siralama_kriteri = isset($_GET['siralama']) ? $_GET['siralama'] : 'ad';
$siralama_yonu = isset($_GET['yon']) && $_GET['yon'] == 'desc' ? 'DESC' : 'ASC';
$siralama_yonu_toggle = $siralama_yonu == 'ASC' ? 'desc' : 'asc';

// Filtre seçeneklerini doğrudan users tablosundaki mevcut klasmanlardan çek
$klasmanlar_listesi = $pdo->query("SELECT DISTINCT klasman FROM users WHERE klasman IS NOT NULL AND klasman != '' ORDER BY klasman ASC")->fetchAll(PDO::FETCH_COLUMN);

// --- VERİ HAZIRLAMA FONKSİYONU ---
function hazirlaMusaitlikRaporu($pdo, $secili_klasmanlar, $siralama_kriteri, $siralama_yonu, $hesap_durumu_filtresi) {
    // 1. Adım: Filtreye uygun kullanıcıları ve ID'lerini al
    $user_query_sql = "SELECT id, ad, soyad, klasman, lisans_no, aktif FROM users";
    $params = [];
    $where_clauses = ["rol != 1"]; // YÖNETİCİLERİ FİLTRELEMEK İÇİN EKLENDİ

    if (!empty($secili_klasmanlar)) {
        $placeholders = implode(',', array_fill(0, count($secili_klasmanlar), '?'));
        $where_clauses[] = "klasman IN ($placeholders)";
        $params = array_merge($params, $secili_klasmanlar);
    }
    
    if ($hesap_durumu_filtresi == 'aktif') {
        $where_clauses[] = "aktif = 1";
    } elseif ($hesap_durumu_filtresi == 'pasif') {
        $where_clauses[] = "aktif = 0";
    }

    if (!empty($where_clauses)) {
        $user_query_sql .= " WHERE " . implode(' AND ', $where_clauses);
    }

    // Sıralama kriterine göre ORDER BY eklendi.
    if ($siralama_kriteri == 'lisans_no') {
        $user_query_sql .= " ORDER BY lisans_no " . $siralama_yonu;
    } else {
        $user_query_sql .= " ORDER BY ad " . $siralama_yonu . ", soyad " . $siralama_yonu;
    }
    
    $users_stmt = $pdo->prepare($user_query_sql);
    $users_stmt->execute($params);
    $users = $users_stmt->fetchAll(PDO::FETCH_ASSOC);

    if (empty($users)) {
        return []; // Filtreye uygun kullanıcı yoksa boş rapor döndür
    }

    $user_ids = array_column($users, 'id');
    $user_ids_placeholders = implode(',', array_fill(0, count($user_ids), '?'));

    // 2. Adım: Her kullanıcı için EN SON SEZONDAN veri çek
    // Önce her kullanıcının en son hangi sezonda veri girdiğini bul
    $son_sezon_query = "
        SELECT user_id, MAX(sezon) as son_sezon 
        FROM musaitlik 
        WHERE user_id IN ($user_ids_placeholders) 
        GROUP BY user_id
    ";
    $son_sezon_stmt = $pdo->prepare($son_sezon_query);
    $son_sezon_stmt->execute($user_ids);
    $son_sezonlar = $son_sezon_stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Her kullanıcı için son sezon mapping'i
    $kullanici_son_sezon = [];
    foreach ($son_sezonlar as $row) {
        $kullanici_son_sezon[(int)$row['user_id']] = $row['son_sezon'];
    }

    // 3. Adım: Tüm müsaitlik verilerini çek (sonra filtreleyeceğiz)
    $musaitlik_stmt = $pdo->prepare("SELECT user_id, gun, zaman_dilimi, musait, sezon FROM musaitlik WHERE user_id IN ($user_ids_placeholders)");
    $musaitlik_stmt->execute($user_ids);
    $musaitlik_raw = $musaitlik_stmt->fetchAll(PDO::FETCH_ASSOC);

    $notlar_stmt = $pdo->prepare("SELECT user_id, gun, `not`, sezon FROM musaitlik_notlari WHERE user_id IN ($user_ids_placeholders)");
    $notlar_stmt->execute($user_ids);
    $notlar_raw = $notlar_stmt->fetchAll(PDO::FETCH_ASSOC);

    // 4. Adım: Sadece her kullanıcının SON SEZON verisini al
    $musaitlikler = [];
    foreach ($musaitlik_raw as $item) {
        $uid = (int)$item['user_id'];
        // Bu kullanıcının son sezonu mu kontrol et
        if (isset($kullanici_son_sezon[$uid]) && $item['sezon'] == $kullanici_son_sezon[$uid]) {
            $musaitlikler[$uid][$item['gun']][$item['zaman_dilimi']] = (int)$item['musait'];
        }
    }
    
    $notlar = [];
    foreach ($notlar_raw as $item) {
        $uid = (int)$item['user_id'];
        // Bu kullanıcının son sezonu mu kontrol et
        if (isset($kullanici_son_sezon[$uid]) && $item['sezon'] == $kullanici_son_sezon[$uid]) {
            $notlar[$uid][$item['gun']] = $item['not'];
        }
    }
    
    // 5. Adım: Nihai rapor dizisini oluştur
    $rapor = [];
    foreach ($users as $user) {
        $kullanici_adi = $user['ad'] . ' ' . $user['soyad'];
        $uid = $user['id'];
        
        $rapor[$kullanici_adi] = [
            'gunler' => [],
            'klasman' => $user['klasman'],
            'lisans_no' => $user['lisans_no'],
            'aktif' => $user['aktif'],
            'son_sezon' => isset($kullanici_son_sezon[$uid]) ? $kullanici_son_sezon[$uid] : null,
        ];
        
        foreach ($GLOBALS['gunler'] as $gun) {
            $durum_str = '';
            foreach ($GLOBALS['zamanlar'] as $zaman) {
                // Müsaitlik durumunu kontrol et
                $durum = isset($musaitlikler[$uid][$gun][$zaman]) ? (int)$musaitlikler[$uid][$gun][$zaman] : 0;
                $durum_str .= ($durum == 1 ? 'E' : 'H') . '-';
            }
            $durum_str = rtrim($durum_str, '-');
            
            $not = isset($notlar[$uid][$gun]) ? $notlar[$uid][$gun] : '';
            $hucre_icerigi = $durum_str;
            if (!empty($not)) { $hucre_icerigi .= " (" . $not . ")"; }
            $rapor[$kullanici_adi]['gunler'][$gun] = $hucre_icerigi;
        }
    }
    return $rapor;
}

// Excel'e Aktarma
if (isset($_GET['export']) && class_exists('Shuchkin\SimpleXLSXGen')) {
    $rapor = hazirlaMusaitlikRaporu($pdo, $secili_klasmanlar, $siralama_kriteri, $siralama_yonu, $hesap_durumu_filtresi);
    $excelHeaders = ['Kullanıcı', 'Lisans No', 'Klasman', ...$gunler, 'Hesap Durumu', 'Son Güncelleme'];
    $xlsxData = [$excelHeaders];

    foreach ($rapor as $kullanici_adi => $data) {
        $row = [$kullanici_adi, $data['lisans_no'], $data['klasman']];
        foreach ($gunler as $gun) { $row[] = $data['gunler'][$gun] ?? 'H-H-H'; }
        $row[] = $data['aktif'] ? 'Aktif' : 'Pasif';
        $row[] = $data['son_sezon'] ?? 'Veri Yok';
        $xlsxData[] = $row;
    }
    $xlsx = Shuchkin\SimpleXLSXGen::fromArray($xlsxData);
    $xlsx->downloadAs('musaitlik_raporu.xlsx');
    exit();
}

// Sayfa Görüntüleme için Verileri Çek
$rapor = hazirlaMusaitlikRaporu($pdo, $secili_klasmanlar, $siralama_kriteri, $siralama_yonu, $hesap_durumu_filtresi);
$export_query_string = http_build_query([
    'export' => 'true', 
    'klasmanlar' => $secili_klasmanlar,
    'hesap_durumu' => $hesap_durumu_filtresi,
    'siralama' => $siralama_kriteri,
    'yon' => $siralama_yonu
]);

// Sıralama butonları için URL'leri oluştur
$siralama_isim_url = http_build_query(array_merge($_GET, ['siralama' => 'ad', 'yon' => ($siralama_kriteri == 'ad' ? $siralama_yonu_toggle : 'asc')]));
$siralama_lisans_url = http_build_query(array_merge($_GET, ['siralama' => 'lisans_no', 'yon' => ($siralama_kriteri == 'lisans_no' ? $siralama_yonu_toggle : 'asc')]));

include '../templates/header.php';
?>
<div class="container mx-auto">
    <div class="bg-white p-6 rounded-lg shadow-md">
        <div class="flex flex-col md:flex-row justify-between md:items-center mb-4">
            <div>
                <h2 class="text-xl font-semibold text-gray-800">Tüm Kullanıcı Müsaitlikleri</h2>
                <p class="text-sm text-gray-600 mt-1">Her kullanıcı için en güncel müsaitlik bilgileri gösterilmektedir</p>
            </div>
            <a href="?<?php echo $export_query_string; ?>" class="bg-green-600 text-white px-4 py-2 rounded-md hover:bg-green-700 text-sm self-start md:self-center mt-4 md:mt-0">
                <i class="fas fa-file-excel mr-2"></i>Excel Olarak İndir (.xlsx)
            </a>
        </div>
        
        <div class="bg-gray-50 p-4 rounded-lg mb-6 border">
            <form method="GET" class="flex flex-col md:flex-row md:items-start md:space-x-4">
                <div class="flex-grow">
                    <label for="klasmanlar" class="block text-sm font-medium text-gray-700 mb-2">Klasman Filtresi:</label>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-2">
                        <?php foreach($klasmanlar_listesi as $klasman): ?>
                            <div class="flex items-center">
                                <input type="checkbox" id="<?php echo htmlspecialchars($klasman); ?>" name="klasmanlar[]" value="<?php echo htmlspecialchars($klasman); ?>" <?php echo in_array($klasman, $secili_klasmanlar) ? 'checked' : ''; ?> class="h-4 w-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500">
                                <label for="<?php echo htmlspecialchars($klasman); ?>" class="ml-2 text-sm text-gray-700"><?php echo htmlspecialchars($klasman); ?></label>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
                <div class="flex-shrink-0 mt-4 md:mt-0">
                    <label for="hesap_durumu" class="block text-sm font-medium text-gray-700 mb-2">Hesap Durumu:</label>
                    <select id="hesap_durumu" name="hesap_durumu" class="w-full h-10 border border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-500 focus:ring-opacity-50">
                        <option value="tumu" <?php echo ($hesap_durumu_filtresi == 'tumu') ? 'selected' : ''; ?>>Tümü</option>
                        <option value="aktif" <?php echo ($hesap_durumu_filtresi == 'aktif') ? 'selected' : ''; ?>>Aktif Hesaplar</option>
                        <option value="pasif" <?php echo ($hesap_durumu_filtresi == 'pasif') ? 'selected' : ''; ?>>Pasif Hesaplar</option>
                    </select>
                </div>
                <div class="flex-shrink-0 flex flex-col space-y-2 mt-4 md:mt-8">
                    <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded-md hover:bg-blue-700 text-sm h-10 flex items-center justify-center">Filtrele</button>
                    <a href="musaitlik_yonetimi.php" class="bg-gray-200 text-gray-700 px-4 py-2 rounded-md hover:bg-gray-300 text-sm h-10 flex items-center justify-center">Temizle</a>
                </div>
            </form>
        </div>
        
        <div class="mb-4 text-xs text-gray-500">
            <p><b>Açıklama:</b> <span class="text-green-500 text-base"><i class="fas fa-check"></i></span> Evet (Müsait), <span class="text-red-500 text-base"><i class="fas fa-times"></i></span> Hayır (Müsait Değil) anlamına gelmektedir. Sıralama Sabah-Öğle-Akşam şeklindedir.</p>
        </div>

        <div class="flex space-x-2 mb-4">
            <a href="?<?php echo htmlspecialchars($siralama_isim_url); ?>" class="bg-indigo-600 text-white px-4 py-2 rounded-md hover:bg-indigo-700 text-sm flex items-center">
                <i class="fas fa-sort-alpha-down-alt mr-2"></i>İsme Göre Sırala
                <?php if ($siralama_kriteri == 'ad'): ?>
                    <?php if ($siralama_yonu == 'ASC'): ?>
                        <i class="fas fa-long-arrow-alt-down ml-2"></i>
                    <?php else: ?>
                        <i class="fas fa-long-arrow-alt-up ml-2"></i>
                    <?php endif; ?>
                <?php endif; ?>
            </a>
            <a href="?<?php echo htmlspecialchars($siralama_lisans_url); ?>" class="bg-indigo-600 text-white px-4 py-2 rounded-md hover:bg-indigo-700 text-sm flex items-center">
                <i class="fas fa-sort-numeric-down-alt mr-2"></i>Lisans No'ya Göre Sırala
                <?php if ($siralama_kriteri == 'lisans_no'): ?>
                    <?php if ($siralama_yonu == 'ASC'): ?>
                        <i class="fas fa-long-arrow-alt-down ml-2"></i>
                    <?php else: ?>
                        <i class="fas fa-long-arrow-alt-up ml-2"></i>
                    <?php endif; ?>
                <?php endif; ?>
            </a>
        </div>

        <div class="overflow-x-auto">
            <table class="min-w-full border text-center text-sm">
                <thead class="bg-gray-100">
                    <tr>
                        <th class="py-2 px-4 border">Kullanıcı</th>
                        <th class="py-2 px-4 border">Lisans No</th>
                        <?php foreach ($gunler as $gun): ?>
                            <th class="py-2 px-4 border"><?php echo $gun; ?></th>
                        <?php endforeach; ?>
                        <th class="py-2 px-4 border">Hesap Durumu</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if(empty($rapor)): ?>
                        <tr><td colspan="<?php echo count($gunler) + 3; ?>" class="text-center py-4">Seçilen filtre için girilmiş müsaitlik kaydı bulunmamaktadır.</td></tr>
                    <?php else: ?>
                        <?php foreach ($rapor as $kullanici_adi => $data): ?>
                        <tr class="border-t hover:bg-gray-50">
                            <td class="py-2 px-4 border font-semibold text-left">
                                <?php echo htmlspecialchars($kullanici_adi); ?>
                                <?php if (!empty($data['klasman'])): ?>
                                    <span class="text-xs text-gray-500 font-normal">[<?php echo htmlspecialchars($data['klasman']); ?>]</span>
                                <?php endif; ?>
                                <?php if ($data['son_sezon']): ?>
                                    <span class="text-xs text-blue-500 ml-2" title="En son <?php echo $data['son_sezon']; ?> sezonunda güncellendi">
                                        (<?php echo $data['son_sezon']; ?>)
                                    </span>
                                <?php endif; ?>
                            </td>
                            <td class="py-2 px-4 border"><?php echo htmlspecialchars($data['lisans_no']); ?></td>
                            <?php foreach ($gunler as $gun): ?>
                                <td class="py-2 px-4 border">
                                    <div class="flex justify-center space-x-2">
                                        <?php
                                            $hucre_icerigi = $data['gunler'][$gun] ?? 'H-H-H';
                                            $parts = explode(' (', $hucre_icerigi);
                                            $durumlar = explode('-', $parts[0]);
                                            $not = isset($parts[1]) ? rtrim($parts[1], ')') : '';

                                            foreach ($durumlar as $durum_kodu) {
                                                if (trim($durum_kodu) == 'E') {
                                                    echo '<span class="text-green-500 text-base" title="Müsait"><i class="fas fa-check"></i></span>';
                                                } else {
                                                    echo '<span class="text-red-500 text-base" title="Müsait Değil"><i class="fas fa-times"></i></span>';
                                                }
                                            }
                                        ?>
                                    </div>
                                    <?php if (!empty($not)): ?>
                                        <div class="text-xs text-blue-600 mt-1"><?php echo htmlspecialchars($not); ?></div>
                                    <?php endif; ?>
                                </td>
                            <?php endforeach; ?>
                            <td class="py-2 px-4 border text-center">
                                <?php if ($data['aktif']): ?>
                                    <i class="fas fa-check text-green-500"></i>
                                <?php else: ?>
                                    <i class="fas fa-times text-red-500"></i>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<?php include '../templates/footer.php'; ?>