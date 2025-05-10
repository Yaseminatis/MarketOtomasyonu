<!DOCTYPE html>
<html>
<head>
    <title>Market Stok Takibi - Giriş</title>
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
            padding: 25px;
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
        <h2>🚀 Market Stok Takibi - Giriş Yap</h2>
        
        <!-- Hata Mesajı -->
        <?php if (isset($_GET['error'])): ?>
            <p class="error"><?php echo htmlspecialchars($_GET['error']); ?></p>
        <?php endif; ?>

        <form action="login.php" method="post">
            <input type="text" name="kullanici_adi" placeholder="Kullanıcı Adı" required><br>
            <input type="password" name="sifre" placeholder="Şifre" required><br>
            <button type="submit">Giriş Yap</button>
        </form>

        <div class="link">
            <p>Hesabınız yok mu? <a href="register.php">Kayıt Ol</a></p>
        </div>
    </div>
</body>
</html>
