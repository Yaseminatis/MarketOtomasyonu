<?php
session_start();
include 'includes/db_connect.php';

// Kullanıcı kayıt işlemi
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Kullanıcı adı ve şifreyi güvenli bir şekilde alıyoruz
    $kullanici_adi = trim(htmlspecialchars($_POST['kullanici_adi']));
    $sifre = trim($_POST['sifre']);
    $hata_mesaji = "";

    // Kullanıcı adı ve şifre uzunluk kontrolü
    if (strlen($kullanici_adi) < 3) {
        $hata_mesaji = "Kullanıcı adı en az 3 karakter olmalıdır!";
    } elseif (strlen($sifre) < 6) {
        $hata_mesaji = "Şifre en az 6 karakter olmalıdır!";
    } else {
        // Şifreyi Bcrypt ile güvenli hale getirme
        $hashed_sifre = password_hash($sifre, PASSWORD_BCRYPT);

        // Kullanıcı adı kontrolü (aynı kullanıcı adı varsa hata verir)
        $query = "SELECT * FROM kullanicilar WHERE kullanici_adi = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("s", $kullanici_adi);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $hata_mesaji = "Bu kullanıcı adı zaten mevcut!";
        } else {
            // Kullanıcıyı kaydetme (otomatik olarak 'user' rolü)
            $query = "INSERT INTO kullanicilar (kullanici_adi, sifre, rol) VALUES (?, ?, 'user')";
            $stmt = $conn->prepare($query);
            $stmt->bind_param("ss", $kullanici_adi, $hashed_sifre);
            $stmt->execute();

            if ($stmt->affected_rows > 0) {
                header("Location: login.php?success=1");
                exit();
            } else {
                $hata_mesaji = "Kayıt başarısız. Lütfen tekrar deneyin.";
            }
        }
    }

    $stmt->close();
    $conn->close();
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Kullanıcı Kayıt</title>
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
        .register-container {
            background-color: #fff;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 0 20px rgba(0, 0, 0, 0.1);
            width: 300px;
        }
        h2 {
            text-align: center;
        }
        input {
            width: 100%;
            padding: 10px;
            margin: 10px 0;
            border: 1px solid #ddd;
            border-radius: 5px;
        }
        button {
            width: 100%;
            padding: 10px;
            background-color: #4CAF50;
            color: #fff;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }
        button:hover {
            background-color: #45a049;
        }
        .error {
            color: red;
            text-align: center;
        }
        .success {
            color: green;
            text-align: center;
        }
    </style>
</head>
<body>
    <div class="register-container">
        <h2>Kullanıcı Kayıt</h2>
        <form action="register.php" method="post">
            <input type="text" name="kullanici_adi" placeholder="Kullanıcı Adı" required><br>
            <input type="password" name="sifre" placeholder="Şifre" required><br>
            <button type="submit">Kayıt Ol</button>
        </form>
        <?php if (!empty($hata_mesaji)) { ?>
            <p class="error"><?php echo $hata_mesaji; ?></p>
        <?php } ?>
        <p><a href="login.php">Giriş Yap</a></p>
    </div>
</body>
</html>
