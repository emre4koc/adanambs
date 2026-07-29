<?php
// PHP Image Converter - Görüntüleri Yeniden Boyutlandırır ve Doğrudan İndirir

// Ayarlar
$MAX_SIZE = 1200; // Maksimum genişlik/yükseklik (piksel)
$JPG_QUALITY = 80; // JPG sıkıştırma kalitesi (0-100)
$message = '';

// GD veya Imagick kütüphanesi yüklü değilse, işlemi atla
if (!extension_loaded('gd')) {
    $message = '<div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative" role="alert">
                <strong class="font-bold">HATA:</strong>
                <span class="block sm:inline">Sunucunuzda GD kütüphanesi yüklü değil. Görüntü işleme yapılamaz. Lütfen barındırma sağlayıcınızla görüşün.</span>
            </div>';
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_FILES['image_file']) && $_FILES['image_file']['error'] == 0) {
        $gecici_yol = $_FILES['image_file']['tmp_name'];
        $dosya_adi = $_FILES['image_file']['name'];
        $uzanti = strtolower(pathinfo($dosya_adi, PATHINFO_EXTENSION));

        // Yalnızca JPG/JPEG kabul et
        if (!in_array($uzanti, ['jpg', 'jpeg'])) {
            $message = '<div class="bg-yellow-100 border border-yellow-400 text-yellow-700 px-4 py-3 rounded relative" role="alert">
                        <strong class="font-bold">UYARI:</strong>
                        <span class="block sm:inline">Sadece JPG veya JPEG dosyaları kabul edilmektedir.</span>
                    </div>';
        } elseif (extension_loaded('gd')) {
            // GD yüklü ise dosya işleme başlar ve indirmeyi zorlar.
            
            try {
                list($orijinal_genislik, $orijinal_yukseklik, $tip) = getimagesize($gecici_yol);
                
                if ($tip == IMAGETYPE_JPEG) {
                    
                    // Yeni boyutları hesapla
                    $oran = $orijinal_genislik / $orijinal_yukseklik;
                    if ($orijinal_genislik > $orijinal_yukseklik) {
                        $yeni_genislik = min($orijinal_genislik, $MAX_SIZE);
                        $yeni_yukseklik = round($yeni_genislik / $oran);
                    } else {
                        $yeni_yukseklik = min($orijinal_yukseklik, $MAX_SIZE);
                        $yeni_genislik = round($yeni_yukseklik * $oran);
                    }
                    
                    // Eğer orijinal boyut zaten küçüktürse yeniden boyutlandırma yapma
                    if ($orijinal_genislik <= $MAX_SIZE && $orijinal_yukseklik <= $MAX_SIZE) {
                        // Küçültme yapılmadı, orijinal dosyayı gönder
                        header('Content-Type: image/jpeg');
                        header('Content-Disposition: attachment; filename="optimized_' . basename($dosya_adi, '.' . $uzanti) . '.jpg"');
                        readfile($gecici_yol);
                        exit;
                    }

                    // GD Kullanarak İşlem
                    $orijinal_resim = imagecreatefromjpeg($gecici_yol);
                    $yeni_resim = imagecreatetruecolor($yeni_genislik, $yeni_yukseklik);

                    imagecopyresampled($yeni_resim, $orijinal_resim, 0, 0, 0, 0, $yeni_genislik, $yeni_yukseklik, $orijinal_genislik, $orijinal_yukseklik);
                    
                    // Çıktı başlıklarını ayarla ve dosyayı doğrudan akıt (download)
                    header('Content-Type: image/jpeg');
                    header('Content-Disposition: attachment; filename="optimized_' . basename($dosya_adi, '.' . $uzanti) . '.jpg"');
                    imagejpeg($yeni_resim, null, $JPG_QUALITY);

                    imagedestroy($orijinal_resim);
                    imagedestroy($yeni_resim);
                    exit;

                }
            } catch (\Exception $e) {
                $message = '<div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative" role="alert">
                            <strong class="font-bold">HATA:</strong>
                            <span class="block sm:inline">Dosya işlenirken bir hata oluştu: ' . htmlspecialchars($e->getMessage()) . '</span>
                        </div>';
            }
        }
    } elseif (isset($_FILES['image_file']) && $_FILES['image_file']['error'] != 0) {
        $error_code = $_FILES['image_file']['error'];
        $error_text = 'Bilinmeyen Hata';
        
        switch ($error_code) {
            case UPLOAD_ERR_INI_SIZE:
                $error_text = 'Yüklenen dosya boyutu, sunucunuzdaki **upload_max_filesize** direktifini aşıyor. (Ana sorununuz bu olabilir!)';
                break;
            case UPLOAD_ERR_FORM_SIZE:
                $error_text = 'Yüklenen dosya boyutu, formdaki maksimum dosya boyutunu aşıyor.';
                break;
            case UPLOAD_ERR_NO_FILE:
                $error_text = 'Hiçbir dosya yüklenmedi.';
                break;
            default:
                $error_text = 'Dosya yüklenirken bir hata oluştu. Hata kodu: ' . $error_code;
                break;
        }

        $message = '<div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative" role="alert">
                    <strong class="font-bold">YÜKLEME HATASI (' . $error_code . '):</strong>
                    <span class="block sm:inline">' . htmlspecialchars($error_text) . '</span>
                </div>';
    }
}
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <title>JPG Boyut Küçültme Aracı</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        .container { max-width: 800px; }
    </style>
</head>
<body class="bg-gray-100 p-8">
    <div class="container mx-auto bg-white p-8 rounded-lg shadow-xl">
        <h1 class="text-3xl font-bold mb-6 text-gray-800">JPG Boyut Küçültme ve Dönüştürme Aracı</h1>
        
        <p class="mb-4 text-gray-600">Bu araç, yüksek boyutlu JPG dosyalarınızı sunucu limitlerini aşmayacak şekilde (<span class="font-bold text-blue-600"><?php echo $MAX_SIZE; ?>px maksimum boyut ve %<?php echo $JPG_QUALITY; ?> kalite</span>) otomatik olarak küçültüp optimize eder ve doğrudan indirme linki sunar.</p>

        <?php echo $message; ?>

        <?php if (!extension_loaded('gd')): ?>
            <div class="mt-4 p-4 border border-red-500 bg-red-50 rounded-lg">
                <p class="text-red-700"><strong>KRİTİK UYARI:</strong> Sunucunuzda görüntü işleme kütüphanesi (GD) bulunmamaktadır. Bu araç **yeniden boyutlandırma yapamaz** ve sadece dosya yükleme limitini aşmadığınız sürece dosyayı olduğu gibi JPEG olarak indirebilirsiniz. **GD kurulumu önceki çözümün çalışması için şarttır.**</p>
            </div>
        <?php endif; ?>

        <form method="POST" enctype="multipart/form-data" class="mt-8 p-6 border-2 border-dashed border-gray-300 rounded-lg bg-gray-50">
            
            <input type="hidden" name="MAX_FILE_SIZE" value="50000000" /> 

            <div class="mb-4">
                <label for="image_file" class="block text-gray-700 text-sm font-bold mb-2">JPG Dosyası Seçiniz</label>
                <input type="file" name="image_file" id="image_file" accept=".jpg,.jpeg" required 
                       class="block w-full text-sm text-gray-900 border border-gray-300 rounded-lg cursor-pointer bg-white focus:outline-none file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100">
            </div>

            <button type="submit" class="w-full bg-green-500 hover:bg-green-600 text-white font-bold py-3 px-4 rounded focus:outline-none focus:shadow-outline transition duration-150">
                Küçült ve İndir
            </button>
            <p class="mt-2 text-xs text-gray-500 text-center">İşlem tamamlandığında optimize edilmiş dosya otomatik olarak inecektir.</p>
        </form>

        <div class="mt-8 p-4 bg-yellow-100 border border-yellow-400 text-yellow-700 rounded relative">
            <h4 class="font-bold mb-2">Çözüm Olmadıysa Lütfen Kontrol Edin:</h4>
            <ul class="list-disc list-inside text-sm space-y-1">
                <li>Eğer bu sayfa dosyanızı yüklemeye çalışırken hata veriyorsa, sunucunuzdaki <code class="bg-yellow-200 p-0.5 rounded">upload_max_filesize</code> ve <code class="bg-yellow-200 p-0.5 rounded">post_max_size</code> ayarlarını artırmanız gerekir. Bu limitler aşılırsa dosya sunucuya hiç ulaşamaz.</li>
                <li>Eğer yükleme tamamlanıyor ancak indirme gerçekleşmiyorsa, sunucunuzda **PHP GD Kütüphanesinin kurulu ve etkin olduğundan** emin olun. Bu kütüphane olmadan PHP görüntüleri işleyemez.</li>
            </ul>
        </div>
    </div>
</body>
</html>