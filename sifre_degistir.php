<?php
header('Content-Type: application/json');
session_start();
include 'baglanti.php';

// Giriş yapılmamışsa işlem yapma
if (!isset($_SESSION['admin_logged_in'])) {
    echo json_encode(['success' => false, 'message' => 'Oturum açmanız gerekiyor.']);
    exit;
}

$data = json_decode(file_get_contents("php://input"), true);
$username = $data['username'];
$old_pass = $data['old_pass'];
$new_pass = $data['new_pass'];

// 1. Eski şifre ve kullanıcı adı doğru mu kontrol et
$stmt = $conn->prepare("SELECT * FROM adminler WHERE username = ? AND password = ?");
$stmt->bind_param("ss", $username, $old_pass);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    // 2. Doğruysa yeni şifreyi güncelle
    $update = $conn->prepare("UPDATE adminler SET password = ? WHERE username = ?");
    $update->bind_param("ss", $new_pass, $username);
    
    if ($update->execute()) {
        echo json_encode(['success' => true, 'message' => 'Şifre başarıyla güncellendi!']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Veritabanı hatası.']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Eski şifre veya kullanıcı adı hatalı!']);
}
?>