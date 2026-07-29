<?php
require_once 'config/session_check.php';
require_once 'config/db.php';

date_default_timezone_set('Europe/Istanbul');

$user_id = $_SESSION['user_id'];

// Filtre parametresini kontrol et
$filtre = isset($_GET['filtre']) ? $_GET['filtre'] : 'tumu';

// Session mesajını al ve temizle
$mesaj = '';
if (isset($_SESSION['mesaj'])) {
    $mesaj = $_SESSION['mesaj'];
    unset($_SESSION['mesaj']);
}

// Sorgu ve parametreler için başlangıç değerleri
$sql_base = "
    SELECT m.id, m.tarih, m.saat, m.durum, m.arsiv, l.ad as lig_adi, s.ad as stadyum_adi,
           t1.ad as ev_sahibi, t2.ad as misafir, r.rapor_dosya_yolu,
           CONCAT(h.ad, ' ', h.soyad) as hakem,
           CONCAT(y1.ad, ' ', y1.soyad) as yardimci_1,
           CONCAT(y2.ad, ' ', y2.soyad) as yardimci_2,
           CONCAT(d4.ad, ' ', d4.soyad) as dorduncu_hakem,
           CONCAT(g.ad, ' ', g.soyad) as gozlemci,
           (CASE
                WHEN m.hakem_id = :user_id THEN 'Hakem'
                WHEN m.yardimci_1_id = :user_id THEN '1. Yardımcı Hakem'
                WHEN m.yardimci_2_id = :user_id THEN '2. Yardımcı Hakem'
                WHEN m.dorduncu_hakem_id = :user_id THEN '4. Hakem'
                WHEN m.gozlemci_id = :user_id THEN 'Gözlemci'
                ELSE ''
           END) as gorev,
           (CASE
                WHEN m.hakem_id = :user_id THEN m.hakem_onay
                WHEN m.yardimci_1_id = :user_id THEN m.yardimci_1_onay
                WHEN m.yardimci_2_id = :user_id THEN m.yardimci_2_onay
                WHEN m.dorduncu_hakem_id = :user_id THEN m.dorduncu_hakem_onay
                WHEN m.gozlemci_id = :user_id THEN m.gozlemci_onay
           END) as onay_durumu
    FROM musabakalar m
    JOIN ligler l ON m.lig_id = l.id
    LEFT JOIN stadyumlar s ON m.stadyum_id = s.id
    JOIN takimlar t1 ON m.ev_sahibi_id = t1.id
    JOIN takimlar t2 ON m.misafir_id = t2.id
    LEFT JOIN users h ON m.hakem_id = h.id
    LEFT JOIN users y1 ON m.yardimci_1_id = y1.id
    LEFT JOIN users y2 ON m.yardimci_2_id = y2.id
    LEFT JOIN users d4 ON m.dorduncu_hakem_id = d4.id
    LEFT JOIN users g ON m.gozlemci_id = g.id
    LEFT JOIN raporlar r ON m.id = r.musabaka_id
    WHERE (m.hakem_id = :user_id OR m.yardimci_1_id = :user_id OR m.yardimci_2_id = :user_id OR m.dorduncu_hakem_id = :user_id OR m.gozlemci_id = :user_id)
";
$params = [':user_id' => $user_id];
$sayfa_baslik = "Tüm Görevlerim";

// Filtreye göre sorguyu ve başlığı ayarla
if ($filtre == 'aktif') {
    $sayfa_baslik = "Aktif Görevlerim";
    $sql_base .= " AND m.arsiv = 0";
}

$sql_final = $sql_base . " ORDER BY m.tarih DESC, m.saat DESC";

$stmt = $pdo->prepare($sql_final);
$stmt->execute($params);
$gorevler = $stmt->fetchAll();

// Onay bekleyen maç sayısını hesapla (sadece aktif filtrede göster)
$onay_bekleyen_sayisi = 0;
if ($filtre == 'aktif') {
    foreach ($gorevler as $g) {
        if (!$g->onay_durumu) $onay_bekleyen_sayisi++;
    }
}

include 'templates/header.php';
?>

<div class="container mx-auto">
    <div class="bg-white p-6 rounded-lg shadow-md">
        <h2 class="text-xl font-semibold mb-4 text-gray-800"><?php echo $sayfa_baslik; ?></h2>

        <?php if (!empty($mesaj)): ?>
        <div class="mb-4 px-4 py-3 rounded-md <?php echo $mesaj['tip'] === 'success' ? 'bg-green-100 text-green-800 border border-green-300' : ($mesaj['tip'] === 'warning' ? 'bg-yellow-100 text-yellow-800 border border-yellow-300' : 'bg-red-100 text-red-800 border border-red-300'); ?>">
            <?php echo htmlspecialchars($mesaj['icerik']); ?>
        </div>
        <?php endif; ?>

        <?php if ($filtre == 'aktif' && $onay_bekleyen_sayisi > 0): ?>
        <!-- TOPLU ONAY BÖLÜMÜ -->
        <form action="toplu_onayla.php" method="POST" id="toplu-onay-form">
            <div class="flex items-center justify-between bg-blue-50 border border-blue-200 rounded-lg px-4 py-3 mb-4">
                <div class="flex items-center gap-3">
                    <input type="checkbox" id="hepsini-sec" class="w-4 h-4 accent-blue-600 cursor-pointer" title="Tümünü Seç / Kaldır">
                    <label for="hepsini-sec" class="text-sm font-medium text-blue-800 cursor-pointer select-none">Tümünü Seç</label>
                    <span id="secim-sayaci" class="text-xs text-blue-600 hidden">(<span id="secilen-adet">0</span> seçildi)</span>
                </div>
                <button type="submit" id="toplu-onayla-btn"
                    class="hidden items-center gap-2 bg-green-600 hover:bg-green-700 text-white text-sm font-semibold py-2 px-4 rounded-md transition-all"
                    onclick="return confirm('Seçilen müsabakaları onaylamak istediğinizden emin misiniz?')">
                    <i class="fas fa-check-circle"></i>
                    Seçilenleri Onayla (<span id="btn-sayi">0</span>)
                </button>
            </div>
        <?php endif; ?>

        <div class="overflow-x-auto">
            <table class="min-w-full bg-white">
                <thead class="bg-gray-200">
                    <tr>
                        <?php if ($filtre == 'aktif' && $onay_bekleyen_sayisi > 0): ?>
                        <th class="py-1 px-2 text-left text-sm font-semibold text-gray-600 w-8">Seç</th>
                        <?php endif; ?>
                        <th class="py-1 px-2 text-left text-sm font-semibold text-gray-600">Tarih - Saat</th>
                        <th class="py-1 px-2 text-left text-sm font-semibold text-gray-600">Lig</th>
                        <th class="py-1 px-2 text-left text-sm font-semibold text-gray-600">Müsabaka</th>
                        <th class="py-1 px-2 text-left text-sm font-semibold text-gray-600">Stadyum</th>
                        <th class="py-1 px-2 text-left text-sm font-semibold text-gray-600">Göreviniz</th>
                        <th class="py-1 px-2 text-left text-sm font-semibold text-gray-600">Hakem Ekibi / Gözlemci</th>
                        <th class="py-1 px-2 text-left text-sm font-semibold text-gray-600">Durum</th>
                        <th class="py-1 px-2 text-left text-sm font-semibold text-gray-600">Onay Durumu</th>
                        <th class="py-1 px-2 text-left text-sm font-semibold text-gray-600">İşlemler</th>
                    </tr>
                </thead>
                <tbody class="text-gray-700">
                    <?php if (empty($gorevler)): ?>
                        <tr>
                            <td colspan="<?php echo ($filtre == 'aktif' && $onay_bekleyen_sayisi > 0) ? '10' : '9'; ?>" class="text-center py-4">Bu kritere uygun görev bulunmamaktadır.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach($gorevler as $gorev): ?>
                        <?php
                            $musabaka_zamani = new DateTime($gorev->tarih . ' ' . $gorev->saat);
                            $su_an = new DateTime();
                            $mac_basladi_mi = $su_an >= $musabaka_zamani;
                            $is_archived = $gorev->arsiv == 1;
                            $onay_bekliyor = !$gorev->onay_durumu;
                            $show_checkbox = ($filtre == 'aktif' && $onay_bekleyen_sayisi > 0);
                        ?>
                        <tr class="border-b hover:bg-gray-50 <?php if ($is_archived) echo 'bg-gray-100 opacity-70'; ?>">
                            <?php if ($show_checkbox): ?>
                            <td class="py-1 px-2 text-center">
                                <?php if ($onay_bekliyor && !$is_archived): ?>
                                <input type="checkbox"
                                       name="musabaka_ids[]"
                                       value="<?php echo $gorev->id; ?>"
                                       class="onay-checkbox w-4 h-4 accent-green-600 cursor-pointer"
                                       form="toplu-onay-form">
                                <?php else: ?>
                                <!-- Zaten onaylı, checkbox yok -->
                                <span class="text-green-500 text-xs" title="Zaten onaylı"><i class="fas fa-check"></i></span>
                                <?php endif; ?>
                            </td>
                            <?php endif; ?>
                            <td class="py-1 px-2"><?php echo date('d.m.Y', strtotime($gorev->tarih)); ?> - <?php echo date('H:i', strtotime($gorev->saat)); ?></td>
                            <td class="py-1 px-2"><?php echo htmlspecialchars($gorev->lig_adi); ?></td>
                            <td class="py-1 px-2 font-medium"><?php echo htmlspecialchars($gorev->ev_sahibi); ?> - <?php echo htmlspecialchars($gorev->misafir); ?></td>
                            <td class="py-1 px-2"><?php echo htmlspecialchars($gorev->stadyum_adi ?? '-'); ?></td>
                            <td class="py-1 px-2"><span class="bg-blue-100 text-blue-800 text-xs font-medium mr-2 px-2.5 py-0.5 rounded"><?php echo htmlspecialchars($gorev->gorev); ?></span></td>
                            <td class="py-1 px-2 text-xs leading-tight">
                                <div><span class="font-semibold text-gray-800">H:</span> <?php echo htmlspecialchars($gorev->hakem ?? '-'); ?></div>
                                <div><span class="font-semibold text-gray-800">Y1:</span> <?php echo htmlspecialchars($gorev->yardimci_1 ?? '-'); ?></div>
                                <div><span class="font-semibold text-gray-800">Y2:</span> <?php echo htmlspecialchars($gorev->yardimci_2 ?? '-'); ?></div>
                                <div><span class="font-semibold text-gray-800">4:</span> <?php echo htmlspecialchars($gorev->dorduncu_hakem ?? '-'); ?></div>
                                <div class="border-t mt-1 pt-1"><span class="font-semibold text-gray-800">G:</span> <?php echo htmlspecialchars($gorev->gozlemci ?? '-'); ?></div>
                            </td>
                            <td class="py-1 px-2">
                                <?php echo htmlspecialchars($gorev->durum); ?>
                                <?php if ($is_archived): ?>
                                    <span class="bg-yellow-100 text-yellow-800 text-xs font-medium ml-2 px-2.5 py-0.5 rounded">Arşivlendi</span>
                                <?php endif; ?>
                            </td>
                            <td class="py-1 px-2">
                                <?php if ($gorev->onay_durumu): ?>
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">Onaylandı</span>
                                <?php else: ?>
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-yellow-100 text-yellow-800">Onay Bekliyor</span>
                                <?php endif; ?>
                            </td>
                            <td class="py-1 px-2">
                                <a href="musabaka_detay.php?id=<?php echo $gorev->id; ?>" class="text-blue-600 hover:text-blue-800 mr-2" title="Detayları Gör"><i class="fas fa-eye"></i>İncele</a>
                                <?php if (!$is_archived): ?>
                                    <?php if ($gorev->gorev === 'Gözlemci' && $mac_basladi_mi): ?>
                                        <a href="rapor_ekle.php?musabaka_id=<?php echo $gorev->id; ?>" class="text-green-600 hover:text-green-800" title="Rapor Ekle/Düzenle"><i class="fas fa-file-alt"></i>Rapor</a>
                                    <?php elseif ($gorev->gorev === 'Hakem' && $mac_basladi_mi): ?>
                                         <a href="musabaka_detay.php?id=<?php echo $gorev->id; ?>&action=skor_gir" class="text-purple-600 hover:text-purple-800" title="Sonuç Gir"><i class="fas fa-futbol"></i>Durum</a>
                                    <?php endif; ?>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <?php if ($filtre == 'aktif' && $onay_bekleyen_sayisi > 0): ?>
        </form>
        <?php endif; ?>

    </div>
</div>

<?php if ($filtre == 'aktif' && $onay_bekleyen_sayisi > 0): ?>
<script>
(function () {
    const checkboxler   = document.querySelectorAll('.onay-checkbox');
    const hepsiniSecBtn = document.getElementById('hepsini-sec');
    const onayla_btn    = document.getElementById('toplu-onayla-btn');
    const secimSayaci   = document.getElementById('secim-sayaci');
    const secilenAdet   = document.getElementById('secilen-adet');
    const btnSayi       = document.getElementById('btn-sayi');

    function guncelle() {
        const secilen = document.querySelectorAll('.onay-checkbox:checked').length;
        secilenAdet.textContent = secilen;
        btnSayi.textContent     = secilen;

        if (secilen > 0) {
            onayla_btn.classList.remove('hidden');
            onayla_btn.classList.add('inline-flex');
            secimSayaci.classList.remove('hidden');
        } else {
            onayla_btn.classList.add('hidden');
            onayla_btn.classList.remove('inline-flex');
            secimSayaci.classList.add('hidden');
        }

        // "Tümünü Seç" durumu
        hepsiniSecBtn.indeterminate = secilen > 0 && secilen < checkboxler.length;
        hepsiniSecBtn.checked       = secilen === checkboxler.length && checkboxler.length > 0;
    }

    checkboxler.forEach(cb => cb.addEventListener('change', guncelle));

    hepsiniSecBtn.addEventListener('change', function () {
        checkboxler.forEach(cb => { cb.checked = this.checked; });
        guncelle();
    });
})();
</script>
<?php endif; ?>

<?php include 'templates/footer.php'; ?>