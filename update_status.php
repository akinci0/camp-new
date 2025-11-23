<?php
// update_status.php - BAŞVURU DURUMU GÜNCELLEME SERVİSİ

header('Content-Type: application/json');

// 1. Veritabanı Bağlantısını Kurma
// baglanti.php dosyanızı dahil eder.
include 'baglanti.php';

// Hata Kontrolü
if ($conn->connect_error) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Veritabanı bağlantısı başarısız.']);
    exit;
}

// 2. Güvenlik ve İstek Metodu Kontrolü
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Geçersiz istek metodu. Sadece POST kabul edilir.']);
    exit;
}

// 3. Gelen JSON Verisini Alma (Front-end'den gelen TC ve Durum)
$data = json_decode(file_get_contents("php://input"), true);
$tc_kimlik = $data['tc_kimlik'] ?? null;
$yeni_durum = $data['yeni_durum'] ?? null;

// Eksik veri kontrolü
if (empty($tc_kimlik) || empty($yeni_durum)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Eksik TC Kimlik veya Durum bilgisi.']);
    exit;
}

// 4. MySQL UPDATE Sorgusu (Güvenli Güncelleme)
// SQL Enjeksiyonunu önlemek için prepare statement kullanıyoruz.
$stmt = $conn->prepare("UPDATE basvurular SET durum = ?, updated_at = NOW() WHERE tc_kimlik = ?");

// "ss" string demektir (yeni_durum ve tc_kimlik her ikisi de string)
$stmt->bind_param("ss", $yeni_durum, $tc_kimlik); 

try {
    $stmt->execute();

    if ($stmt->affected_rows > 0) {
        // Başarılı güncelleme
        echo json_encode([
            'success' => true,
            'message' => 'Başvuru durumu başarıyla güncellendi.',
            'yeni_durum' => $yeni_durum
        ]);
    } else {
        // Kayıt bulunamadı veya durum zaten aynıydı
        echo json_encode([
            'success' => false,
            'message' => 'Güncellenecek kayıt bulunamadı veya durum değişmedi.',
        ]);
    }

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Sorgu yürütülürken hata oluştu.']);

} finally {
    // Bağlantıları kapat
    if (isset($stmt)) $stmt->close();
    $conn->close();
}
?>