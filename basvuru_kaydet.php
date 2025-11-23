<?php
include 'baglanti.php';

// Form gönderildi mi kontrol et
if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $tc = $_POST['tc_kimlik'];
    $ad = $_POST['ad_soyad'];
    $email = $_POST['email'];
    $egitim = $_POST['egitim_durumu'];
    $motivasyon = $_POST['motivasyon_metni'];
    $ip = $_SERVER['REMOTE_ADDR'];

    // --- DOSYA YÜKLEME FONKSİYONU ---
    function dosyaYukle($fileInputName, $tc) {
        $target_dir = "uploads/";
        
        // Dosya ismini benzersiz yap: TC_DosyaTipi_Zaman.uzantı
        // Örnek: 12345678901_cv_169875432.pdf
        $dosyaUzantisi = strtolower(pathinfo($_FILES[$fileInputName]["name"], PATHINFO_EXTENSION));
        $yeniIsim = $tc . "_" . $fileInputName . "_" . time() . "." . $dosyaUzantisi;
        $target_file = $target_dir . $yeniIsim;

        if (move_uploaded_file($_FILES[$fileInputName]["tmp_name"], $target_file)) {
            return $target_file; // Başarılıysa veritabanına yazılacak yolu döndür
        } else {
            return false;
        }
    }

    // Dosyaları Yükle
    $cv_yolu = dosyaYukle("ozgecmis", $tc);
    $belge_yolu = dosyaYukle("belge", $tc); // Formdaki name="belge"

    if ($cv_yolu && $belge_yolu) {
        // SQL Injection önlemi (Prepare Statement)
        $stmt = $conn->prepare("INSERT INTO basvurular (tc_kimlik, ad_soyad, email, egitim_durumu, motivasyon_metni, ozgecmis_path, ogrenci_belgesi_path, ip_adresi, durum) VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'beklemede')");
        
        $stmt->bind_param("ssssssss", $tc, $ad, $email, $egitim, $motivasyon, $cv_yolu, $belge_yolu, $ip);

        if ($stmt->execute()) {
            echo "<h1 style='color:green; text-align:center; margin-top:50px;'>Başvurunuz Başarıyla Alındı! 🎉</h1>";
            echo "<p style='text-align:center;'>Yönlendiriliyorsunuz...</p>";
            header("refresh:3;url=basvuru.php"); // 3 saniye sonra forma dön
        } else {
            echo "Veritabanı Hatası: " . $stmt->error;
        }
        $stmt->close();
    } else {
        echo "Dosya yüklenirken bir hata oluştu. Lütfen klasör izinlerini kontrol edin.";
    }
}
?>