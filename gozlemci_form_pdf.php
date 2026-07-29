<?php
// gozlemci_form_pdf.php
require_once 'config/session_check.php';
require_once 'config/db.php';

$musabaka_id = isset($_GET['musabaka_id']) ? (int)$_GET['musabaka_id'] : 0;
$user_id = $_SESSION['user_id'];

// Müsabaka ve görevli bilgilerini çek
$stmt = $pdo->prepare("
    SELECT 
        m.*, 
        l.ad as lig_adi, s.ad as stadyum_adi,
        t1.ad as ev_sahibi, t2.ad as misafir,
        CONCAT(h.ad, ' ', h.soyad) as hakem, h.telefon as hakem_tel, h.lisans_no as hakem_lisans, h.klasman as hakem_klasman,
        CONCAT(y1.ad, ' ', y1.soyad) as yardimci_1, y1.telefon as yardimci_1_tel, y1.lisans_no as yardimci_1_lisans, y1.klasman as yardimci_1_klasman,
        CONCAT(y2.ad, ' ', y2.soyad) as yardimci_2, y2.telefon as yardimci_2_tel, y2.lisans_no as yardimci_2_lisans, y2.klasman as yardimci_2_klasman,
        CONCAT(d4.ad, ' ', d4.soyad) as dorduncu_hakem, d4.telefon as dorduncu_hakem_tel, d4.lisans_no as dorduncu_hakem_lisans, d4.klasman as dorduncu_hakem_klasman,
        CONCAT(g.ad, ' ', g.soyad) as gozlemci, g.telefon as gozlemci_tel, g.lisans_no as gozlemci_lisans, g.klasman as gozlemci_klasman
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
    WHERE m.id = ?
");

$stmt->execute([$musabaka_id]);
$musabaka = $stmt->fetch();

if (!$musabaka) {
    die("Müsabaka bulunamadı.");
}

// HTML olarak PDF benzeri bir çıktı oluştur
header('Content-Type: text/html; charset=utf-8');
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <title>Müsabaka Bilgi Formu - <?php echo htmlspecialchars($musabaka->ev_sahibi); ?> vs <?php echo htmlspecialchars($musabaka->misafir); ?></title>
    <style>
        @page {
            size: A4;
            margin: 5mm;
        }
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            color: #000;
            font-size: 12px;
            line-height: 1.3;
            background: white;
            height: 100vh;
        }
        .container {
            width: 200mm;
            height: 287mm;
            margin: 0 auto;
            padding: 5mm;
            box-sizing: border-box;
            display: flex;
            flex-direction: column;
        }
        .header {
            text-align: center;
            border-bottom: 2px solid #000;
            padding-bottom: 6px;
            margin-bottom: 8px;
            flex-shrink: 0;
        }
        .header h1 {
            margin: 0;
            font-size: 18px;
            color: #000;
            font-weight: bold;
        }
        .content {
            flex: 1;
            display: flex;
            flex-direction: column;
            gap: 8px;
        }
        .section {
            flex-shrink: 0;
        }
        .section-title {
            background-color: #f0f0f0;
            padding: 5px;
            font-weight: bold;
            border: 1px solid #000;
            margin-bottom: 4px;
            text-align: center;
            font-size: 13px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 6px;
            font-size: 12px;
        }
        th, td {
            border: 1px solid #000;
            padding: 5px 3px;
            text-align: left;
            vertical-align: top;
        }
        th {
            background-color: #f0f0f0;
            font-weight: bold;
            font-size: 12px;
        }
        td {
            font-size: 12px;
        }
        .notes-section {
            flex: 1;
            display: flex;
            flex-direction: column;
            gap: 6px;
        }
        .note-row {
            display: flex;
            gap: 8px;
        }
        .note-box {
            border: 1px solid #000;
            padding: 6px;
            flex: 1;
            display: flex;
            flex-direction: column;
        }
        .note-title {
            font-weight: bold;
            margin-bottom: 4px;
            font-size: 13px;
            border-bottom: 1px solid #000;
            padding-bottom: 3px;
            flex-shrink: 0;
        }
        .note-content {
            flex: 1;
            min-height: 0;
        }
        .hakem-notes {
            flex: 2; /* Hakem notları daha yüksek */
        }
        .diger-notes {
            flex: 1; /* Diğer notlar daha kısa */
        }
        .info-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 6px;
            margin-bottom: 8px;
        }
        .info-item {
            border: 1px solid #000;
            padding: 5px;
        }
        .info-label {
            font-weight: bold;
            font-size: 12px;
        }
        .info-value {
            font-size: 12px;
        }
        .cards-section {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 10px;
            margin-top: 8px;
        }
        .team-cards {
            border: 1px solid #000;
            padding: 8px;
        }
        .team-title {
            font-weight: bold;
            font-size: 12px;
            text-align: center;
            margin-bottom: 8px;
            border-bottom: 1px solid #000;
            padding-bottom: 4px;
        }
        .card-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 8px;
            margin-bottom: 8px;
        }
        .card-box {
            border: 1px solid #000;
            padding: 6px;
            text-align: center;
            min-height: 45px;
            display: flex;
            flex-direction: column;
        }
        .card-title {
            font-weight: bold;
            font-size: 10px;
            margin-bottom: 4px;
        }
        .card-content {
            flex: 1;
            display: flex;
            flex-direction: column;
            gap: 2px;
        }
        .small-card-grid {
            display: grid;
            grid-template-columns: 1fr 1fr 1fr 1fr;
            gap: 2px;
        }
        .small-card {
            border: 1px solid #666;
            height: 20px;
            background-color: white;
        }
        .yellow-card {
            background-color: #fff9c4;
        }
        .red-card {
            background-color: #ffcdd2;
        }
        @media print {
            body { 
                margin: 0; 
                padding: 0;
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
                height: 287mm;
            }
            .container { 
                width: 200mm;
                height: 287mm;
                margin: 0;
                padding: 5mm;
                border: none;
            }
            .no-print { 
                display: none; 
            }
            .yellow-card {
                background-color: #fff9c4 !important;
            }
            .red-card {
                background-color: #ffcdd2 !important;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>MÜSABAKA BİLGİ FORMU</h1>
        </div>

        <div class="content">
            <div class="section">
                <div class="section-title">MÜSABAKA BİLGİLERİ</div>
                <div class="info-grid">
                    <div class="info-item">
                        <div class="info-label">Lig:</div>
                        <div class="info-value"><?php echo htmlspecialchars($musabaka->lig_adi); ?></div>
                    </div>
                    <div class="info-item">
                        <div class="info-label">Hafta:</div>
                        <div class="info-value"><?php echo htmlspecialchars($musabaka->hafta_no); ?>. Hafta</div>
                    </div>
                    <div class="info-item">
                        <div class="info-label">Maç No:</div>
                        <div class="info-value"><?php echo htmlspecialchars($musabaka->mac_no ?? '-'); ?></div>
                    </div>
                    <div class="info-item">
                        <div class="info-label">Müsabaka:</div>
                        <div class="info-value"><?php echo htmlspecialchars($musabaka->ev_sahibi); ?> - <?php echo htmlspecialchars($musabaka->misafir); ?></div>
                    </div>
                    <div class="info-item">
                        <div class="info-label">Tarih & Saat:</div>
                        <div class="info-value"><?php echo date('d.m.Y', strtotime($musabaka->tarih)); ?> - <?php echo date('H:i', strtotime($musabaka->saat)); ?></div>
                    </div>
                    <div class="info-item">
                        <div class="info-label">Stadyum:</div>
                        <div class="info-value"><?php echo htmlspecialchars($musabaka->stadyum_adi); ?></div>
                    </div>
                </div>
            </div>

            <div class="section">
                <div class="section-title">HAKEM EKİBİ BİLGİLERİ</div>
                <table>
                    <tr>
                        <th width="15%">Görev</th>
                        <th width="28%">Ad Soyad</th>
                        <th width="17%">Lisans No</th>
                        <th width="12%">Klasman</th>
                        <th width="18%">Telefon</th>
                    </tr>
                    <tr>
                        <td><strong>Hakem</strong></td>
                        <td><?php echo htmlspecialchars($musabaka->hakem ?? '-'); ?></td>
                        <td><?php echo htmlspecialchars($musabaka->hakem_lisans ?? '-'); ?></td>
                        <td><?php echo htmlspecialchars($musabaka->hakem_klasman ?? '-'); ?></td>
                        <td><?php echo htmlspecialchars($musabaka->hakem_tel ?? '-'); ?></td>
                    </tr>
                    <tr>
                        <td><strong>1. Yardımcı</strong></td>
                        <td><?php echo htmlspecialchars($musabaka->yardimci_1 ?? '-'); ?></td>
                        <td><?php echo htmlspecialchars($musabaka->yardimci_1_lisans ?? '-'); ?></td>
                        <td><?php echo htmlspecialchars($musabaka->yardimci_1_klasman ?? '-'); ?></td>
                        <td><?php echo htmlspecialchars($musabaka->yardimci_1_tel ?? '-'); ?></td>
                    </tr>
                    <tr>
                        <td><strong>2. Yardımcı</strong></td>
                        <td><?php echo htmlspecialchars($musabaka->yardimci_2 ?? '-'); ?></td>
                        <td><?php echo htmlspecialchars($musabaka->yardimci_2_lisans ?? '-'); ?></td>
                        <td><?php echo htmlspecialchars($musabaka->yardimci_2_klasman ?? '-'); ?></td>
                        <td><?php echo htmlspecialchars($musabaka->yardimci_2_tel ?? '-'); ?></td>
                    </tr>
                    <tr>
                        <td><strong>4. Hakem</strong></td>
                        <td><?php echo htmlspecialchars($musabaka->dorduncu_hakem ?? '-'); ?></td>
                        <td><?php echo htmlspecialchars($musabaka->dorduncu_hakem_lisans ?? '-'); ?></td>
                        <td><?php echo htmlspecialchars($musabaka->dorduncu_hakem_klasman ?? '-'); ?></td>
                        <td><?php echo htmlspecialchars($musabaka->dorduncu_hakem_tel ?? '-'); ?></td>
                    </tr>
                    <tr>
                        <td><strong>Gözlemci</strong></td>
                        <td><?php echo htmlspecialchars($musabaka->gozlemci ?? '-'); ?></td>
                        <td><?php echo htmlspecialchars($musabaka->gozlemci_lisans ?? '-'); ?></td>
                        <td><?php echo htmlspecialchars($musabaka->gozlemci_klasman ?? '-'); ?></td>
                        <td><?php echo htmlspecialchars($musabaka->gozlemci_tel ?? '-'); ?></td>
                    </tr>
                </table>

                <div class="cards-section">
                    <div class="team-cards">
                        <div class="team-title"><?php echo htmlspecialchars($musabaka->ev_sahibi); ?></div>
                        <div class="card-row">
                            <div class="card-box yellow-card">
                                <div class="card-title">SARI KARTLAR</div>
                                <div class="card-content">
                                    <div class="small-card-grid">
                                        <div class="small-card"></div>
                                        <div class="small-card"></div>
                                        <div class="small-card"></div>
                                        <div class="small-card"></div>
                                        <div class="small-card"></div>
                                        <div class="small-card"></div>
                                        <div class="small-card"></div>
                                        <div class="small-card"></div>
                                    </div>
                                </div>
                            </div>
                            <div class="card-box red-card">
                                <div class="card-title">KIRMIZI KARTLAR</div>
                                <div class="card-content">
                                    <div class="small-card-grid">
                                        <div class="small-card"></div>
                                        <div class="small-card"></div>
                                        <div class="small-card"></div>
                                        <div class="small-card"></div>
                                        <div class="small-card"></div>
                                        <div class="small-card"></div>
                                        <div class="small-card"></div>
                                        <div class="small-card"></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="team-cards">
                        <div class="team-title"><?php echo htmlspecialchars($musabaka->misafir); ?></div>
                        <div class="card-row">
                            <div class="card-box yellow-card">
                                <div class="card-title">SARI KARTLAR</div>
                                <div class="card-content">
                                    <div class="small-card-grid">
                                        <div class="small-card"></div>
                                        <div class="small-card"></div>
                                        <div class="small-card"></div>
                                        <div class="small-card"></div>
                                        <div class="small-card"></div>
                                        <div class="small-card"></div>
                                        <div class="small-card"></div>
                                        <div class="small-card"></div>
                                    </div>
                                </div>
                            </div>
                            <div class="card-box red-card">
                                <div class="card-title">KIRMIZI KARTLAR</div>
                                <div class="card-content">
                                    <div class="small-card-grid">
                                        <div class="small-card"></div>
                                        <div class="small-card"></div>
                                        <div class="small-card"></div>
                                        <div class="small-card"></div>
                                        <div class="small-card"></div>
                                        <div class="small-card"></div>
                                        <div class="small-card"></div>
                                        <div class="small-card"></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="notes-section">
                <div class="section-title">NOTLAR</div>
                
                <div class="note-row" style="flex: 2;">
                    <div class="note-box hakem-notes">
                        <div class="note-title">HAKEM NOTLARI:</div>
                        <div class="note-content">
                            <!-- Boş alan - elle yazılacak -->
                        </div>
                    </div>
                    <div class="note-box hakem-notes">
                        <div class="note-title">1. YARDIMCI HAKEM NOTLARI:</div>
                        <div class="note-content">
                            <!-- Boş alan - elle yazılacak -->
                        </div>
                    </div>
                </div>
                
                <div class="note-row" style="flex: 2;">
                    <div class="note-box hakem-notes">
                        <div class="note-title">2. YARDIMCI HAKEM NOTLARI:</div>
                        <div class="note-content">
                            <!-- Boş alan - elle yazılacak -->
                        </div>
                    </div>
                    <div class="note-box hakem-notes">
                        <div class="note-title">4. HAKEM NOTLARI:</div>
                        <div class="note-content">
                            <!-- Boş alan - elle yazılacak -->
                        </div>
                    </div>
                </div>
                
                <div class="note-row" style="flex: 1;">
                    <div class="note-box diger-notes">
                        <div class="note-title">DİĞER NOTLAR:</div>
                        <div class="note-content">
                            <!-- Boş alan - elle yazılacak -->
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="no-print" style="position: fixed; top: 10px; right: 10px; z-index: 1000;">
        <button onclick="window.print()" style="padding: 8px 16px; background: #007bff; color: white; border: none; border-radius: 4px; cursor: pointer; font-size: 12px;">
            Yazdır
        </button>
        <button onclick="window.close()" style="padding: 8px 16px; background: #6c757d; color: white; border: none; border-radius: 4px; cursor: pointer; margin-left: 8px; font-size: 12px;">
            Kapat
        </button>
    </div>

    <script>
        // Sayfa yüklendiğinde otomatik yazdırma dialogunu aç
        window.onload = function() {
            setTimeout(function() {
                window.print();
            }, 500);
        }
    </script>
</body>
</html>