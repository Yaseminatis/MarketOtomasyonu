<?php
session_start();
include 'includes/db_connect.php';

// Kullanıcı giriş kontrolü (Yalnızca Admin)
if (!isset($_SESSION['kullanici_adi']) || $_SESSION['rol'] !== 'admin') {
    header("Location: index.php");
    exit();
}

// Kategorileri çekme
$query = "SELECT * FROM kategori";
$result = $conn->query($query);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Kategori Yönetimi</title>
</head>
<body>
    <h2>Kategori Yönetimi</h2>
    <a href="kategori_ekle.php">+ Kategori Ekle</a>
    <table border="1">
        <tr>
            <th>ID</th>
            <th>Kategori Adı</th>
            <th>İşlemler</th>
        </tr>
        <?php while ($row = $result->fetch_assoc()) { ?>
            <tr>
                <td><?php echo $row['id']; ?></td>
                <td><?php echo $row['kategori_adi']; ?></td>
                <td>
                    <a href="kategori_sil.php?id=<?php echo $row['id']; ?>">Sil</a>
                </td>
            </tr>
        <?php } ?>
    </table>
    <a href="dashboard.php">Geri Dön</a>
</body>
</html>
