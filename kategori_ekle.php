<?php
session_start();
include 'includes/db_connect.php';

// Kullanıcı giriş kontrolü (Yalnızca Admin)
if (!isset($_SESSION['kullanici_adi']) || $_SESSION['rol'] !== 'admin') {
    header("Location: index.php");
    exit();
}

// Kategori ekleme işlemi
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $kategori_adi = trim($_POST['kategori_adi']);
    
    $query = "INSERT INTO kategori (kategori_adi) VALUES (?)";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("s", $kategori_adi);
    $stmt->execute();

    header("Location: kategori_yonetimi.php?success=Kategori başarıyla eklendi.");
    exit();
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Kategori Ekle</title>
</head>
<body>
    <h2>Kategori Ekle</h2>
    <form action="kategori_ekle.php" method="post">
        <input type="text" name="kategori_adi" placeholder="Kategori Adı" required>
        <button type="submit">Kategori Ekle</button>
    </form>
    <a href="kategori_yonetimi.php">Kategori Yönetimi</a>
</body>
</html>
