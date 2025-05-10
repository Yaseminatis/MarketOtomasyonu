<?php
session_start();
include 'includes/db_connect.php';

// Kullanıcı giriş kontrolü
if (!isset($_SESSION['kullanici_adi'])) {
    header("Location: index.php");
    exit();
}

// Kullanıcı bilgileri
$kullanici_adi = $_SESSION['kullanici_adi'];

// Kullanıcı verilerini çekme
$query = "SELECT * FROM kullanicilar WHERE kullanici_adi = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("s", $kullanici_adi);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();
?>

<!DOCTYPE html>
<html>
<head>
    <title>Kullanıcı Profilim</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f9;
            display: flex;
            justify-content: center;
            padding: 20px;
        }
        .container {
            width: 90%;
            max-width: 400px;
            background-color: #fff;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
        }
        h2 {
            text-align: center;
        }
        form {
            display: flex;
            flex-direction: column;
        }
        input {
            padding: 10px;
            margin-bottom: 10px;
            border: 1px solid #ddd;
            border-radius: 5px;
        }
        button {
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
        .back-link {
            text-align: center;
            margin-top: 15px;
        }
    </style>
</head>
<body>
    <div class="container">
        <h2>👤 Kullanıcı Profilim</h2>
        <form action="profil_guncelle.php" method="post">
            <input type="text" name="isim" value="<?php echo htmlspecialchars($user['isim']); ?>" placeholder="İsim" required>
            <input type="email" name="email" value="<?php echo htmlspecialchars($user['email']); ?>" placeholder="E-posta" required>
            <input type="password" name="mevcut_sifre" placeholder="Mevcut Şifre (Değiştirmek için gerekli)">
            <input type="password" name="yeni_sifre" placeholder="Yeni Şifre (İsteğe Bağlı)">
            <button type="submit">Profili Güncelle</button>
        </form>
        <div class="back-link">
            <a href="dashboard.php">⬅️ Geri Dön</a>
        </div>
    </div>
</body>
</html>
