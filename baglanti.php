<?php
$servername = "localhost";
$username = "root";
$password = "root";
$dbname = "proje"; // Senin veritabanı adın

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Bağlantı hatası: " . $conn->connect_error);
}
$conn->set_charset("utf8");
?>