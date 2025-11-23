<?php
session_start();       // Oturumu başlat
session_unset();       // Tüm oturum değişkenlerini temizle
session_destroy();     // Oturumu tamamen yok et

// Kullanıcıyı giriş sayfasına yönlendir
header("Location: login.php"); 
exit();
?>