<?php
session_start();
include 'baglanti.php'; // Veritabanı bağlantısı

// Zaten giriş yapıldıysa panele at
if (isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true) {
    header("Location: panel.php");
    exit;
}

$error = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $user = $_POST['username'];
    $pass = $_POST['password'];

    // Veritabanından admini bul
    $stmt = $conn->prepare("SELECT * FROM adminler WHERE username = ? AND password = ?");
    $stmt->bind_param("ss", $user, $pass);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $_SESSION['admin_logged_in'] = true;
        $_SESSION['admin_username'] = $user;
        header("Location: panel.php");
        exit();
    } else {
        $error = "Hatalı kullanıcı adı veya şifre!";
    }
}
?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <title>Admin Giriş - PiA</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <style>
        body { background: #463e66; font-family: 'Segoe UI', sans-serif; display: flex; justify-content: center; align-items: center; height: 100vh; margin: 0; }
        .login-box { background: white; padding: 40px; border-radius: 10px; box-shadow: 0 10px 25px rgba(0,0,0,0.2); width: 300px; text-align: center; }
        .login-box h2 { color: #463e66; margin-bottom: 20px; }
        
        /* Input Alanları */
        .input-group { margin: 15px 0; text-align: left; }
        input { width: 100%; padding: 12px; border: 1px solid #ddd; border-radius: 5px; box-sizing: border-box; outline: none; }
        input:focus { border-color: #00ADB5; }

        /* Şifre Alanı İçin Özel Stil (İkonu İçeri Almak İçin) */
        .password-wrapper { position: relative; }
        .password-wrapper input { padding-right: 40px; } /* İkonun üstüne yazı gelmesin diye sağdan boşluk */
        .toggle-icon {
            position: absolute;
            right: 10px;
            top: 50%;
            transform: translateY(-50%);
            color: #888;
            cursor: pointer;
            font-size: 1.1rem;
        }
        .toggle-icon:hover { color: #00ADB5; }

        /* Buton */
        button { width: 100%; padding: 12px; background: #00ADB5; color: white; border: none; border-radius: 5px; cursor: pointer; font-size: 16px; font-weight: bold; transition: 0.3s; margin-top: 10px; }
        button:hover { background: #008c93; }
        
        .error { color: red; font-size: 0.9rem; margin-bottom: 10px; background: #ffe6e6; padding: 10px; border-radius: 5px; border: 1px solid red; }
    </style>
</head>
<body>
    <div class="login-box">
        <h2>PiA Admin Giriş</h2>
        
        <?php if(!empty($error)) echo "<div class='error'>$error</div>"; ?>
        
        <form method="POST">
            <div class="input-group">
                <input type="text" name="username" placeholder="Kullanıcı Adı" required autocomplete="off">
            </div>
            
            <div class="input-group password-wrapper">
                <input type="password" name="password" id="passwordInput" placeholder="Şifre" required>
                <i class="fas fa-eye toggle-icon" onclick="togglePassword()"></i>
            </div>

            <button type="submit">Giriş Yap</button>
        </form>
    </div>

    <script>
        function togglePassword() {
            const passwordInput = document.getElementById('passwordInput');
            const icon = document.querySelector('.toggle-icon');

            if (passwordInput.type === 'password') {
                passwordInput.type = 'text'; // Şifreyi göster
                icon.classList.remove('fa-eye');
                icon.classList.add('fa-eye-slash'); // İkonu 'çizgili göz' yap
            } else {
                passwordInput.type = 'password'; // Şifreyi gizle
                icon.classList.remove('fa-eye-slash');
                icon.classList.add('fa-eye'); // İkonu normal göz yap
            }
        }
    </script>
</body>
</html>