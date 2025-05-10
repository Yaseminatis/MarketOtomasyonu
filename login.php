<?php
session_start();
include 'includes/db_connect.php'; // Veritabanı bağlantısı

// Kullanıcı giriş işlemi
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Kullanıcı adı ve şifreyi güvenli bir şekilde alıyoruz
    $kullanici_adi = trim(htmlspecialchars($_POST['kullanici_adi']));
    $sifre = trim($_POST['sifre']);

    // Kullanıcıyı veritabanından güvenli bir şekilde çekme
    $query = "SELECT * FROM kullanicilar WHERE kullanici_adi = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("s", $kullanici_adi);
    $stmt->execute();
    $result = $stmt->get_result();

    // Kullanıcı kontrolü
    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();
        // Şifre doğrulama
        if (password_verify($sifre, $user['sifre'])) {
            // Giriş başarılı, oturum başlatılıyor
            $_SESSION['kullanici_adi'] = $user['kullanici_adi'];
            $_SESSION['rol'] = $user['rol']; // Kullanıcı rolü (admin veya user)
            header("Location: dashboard.php");
            exit();
        } else {
            $hata_mesaji = "⚠️ Hatalı kullanıcı adı veya şifre!";
        }
    } else {
        $hata_mesaji = "⚠️ Hatalı kullanıcı adı veya şifre!";
    }

    $stmt->close();
    $conn->close();
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Kullanıcı Giriş</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f9;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
        }
        .login-container {
            background-color: #fff;
            padding: 25px 20px;
            border-radius: 10px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
            width: 350px;
            text-align: center;
        }
        h2 {
            margin-bottom: 15px;
            color: #333;
        }
        input {
            width: 100%;
            padding: 12px;
            margin: 10px 0;
            border: 1px solid #ddd;
            border-radius: 5px;
            box-sizing: border-box;
        }
        button {
            width: 100%;
            padding: 12px;
            background-color: #4CAF50;
            color: #fff;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-weight: bold;
        }
        button:hover {
            background-color: #45a049;
        }
        .error {
            color: red;
            font-size: 14px;
            margin-top: 10px;
        }
        .success {
            color: green;
            font-size: 14px;
            margin-top: 10px;
        }
        .link {
            margin-top: 15px;
            font-size: 14px;
        }
        .link a {
            color: #4CAF50;
            text-decoration: none;
        }
        .link a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <div class="login-container">
        <h2>🚀 Kullanıcı Giriş</h2>
        <form action="login.php" method="post">
            <input type="text" name="kullanici_adi" placeholder="Kullanıcı Adı" required><br>
            <input type="password" name="sifre" placeholder="Şifre" required><br>
            <button type="submit">Giriş Yap</button>
        </form>
        
        <?php if (isset($hata_mesaji)) { ?>
            <p class="error"><?php echo $hata_mesaji; ?></p>
        <?php } ?>

        <div class="link">
            <p>Hesabınız yok mu? <a href="register.php">Kayıt Ol</a></p>
        </div>
    </div>
</body>
</html>
