<?php
include 'baglanti.php';

if ($_POST) {
    $tc = $_POST['tc_kimlik'];
    $ad = $_POST['ad_soyad'];
    $mail = $_POST['email'];
    $egitim = $_POST['egitim_durumu'];
    $motivasyon = $_POST['motivasyon_metni'];
    $ip = $_SERVER['REMOTE_ADDR'];
    $durum = 'beklemede';

    // Dosyaları yükleme kısmı
    $hedef = "dosyalar/";
    
    $cv_ad = basename($_FILES["ozgecmis"]["name"]);
    $cv_yol = $hedef . $cv_ad;
    move_uploaded_file($_FILES["ozgecmis"]["tmp_name"], $cv_yol);

    $belge_ad = basename($_FILES["ogrenci_belgesi"]["name"]);
    $belge_yol = $hedef . $belge_ad;
    move_uploaded_file($_FILES["ogrenci_belgesi"]["tmp_name"], $belge_yol);

    // Veritabanına yazma
    $sql = "INSERT INTO basvurular (tc_kimlik, ad_soyad, email, egitim_durumu, motivasyon_metni, ozgecmis_path, ogrenci_belgesi_path, ip_adresi, durum) 
            VALUES ('$tc', '$ad', '$mail', '$egitim', '$motivasyon', '$cv_yol', '$belge_yol', '$ip', '$durum')";

    if ($conn->query($sql) === TRUE) {
        echo "Kayit Basarili!";
    } else {
        echo "Hata: " . $conn->error;
    }
}
?>