<?php
// export.php - Verileri Excel/CSV olarak indir
include 'baglanti.php';

// Dosya adını ayarla
$filename = "pia_basvurular_" . date('Y-m-d') . ".csv";

// Headerları gönder (Tarayıcıya bunun bir dosya olduğunu söyle)
header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename="' . $filename . '"');

// Dosya çıktısını aç
$output = fopen('php://output', 'w');

// Excel'in Türkçe karakterleri düzgün okuması için BOM ekle
fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));

// Sütun Başlıkları
fputcsv($output, array('TC Kimlik', 'Ad Soyad', 'E-posta', 'Eğitim', 'Motivasyon', 'Durum', 'Tarih'));

// Verileri Çek
$sql = "SELECT tc_kimlik, ad_soyad, email, egitim_durumu, motivasyon_metni, durum, created_at FROM basvurular ORDER BY created_at DESC";
$result = $conn->query($sql);

while($row = $result->fetch_assoc()) {
    // CSV'ye satır satır yaz
    fputcsv($output, $row);
}

fclose($output);
exit();
?>