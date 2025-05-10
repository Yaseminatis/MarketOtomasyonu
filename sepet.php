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

// Kullanıcı ID'sini veritabanından çekme
$query = "SELECT id FROM kullanicilar WHERE kullanici_adi = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("s", $kullanici_adi);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();
$kullanici_id = $user['id'];

// Kullanıcının sepetindeki ürünleri çekme
$query = "SELECT s.id AS sepet_id, u.urun_adi, u.fiyat, u.resim, s.adet 
          FROM sepet s 
          JOIN urunler u ON s.urun_id = u.id 
          WHERE s.kullanici_id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $kullanici_id);
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html>
<head>
    <title>🛒 Alışveriş Sepetiniz</title>
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
            max-width: 800px;
            background-color: #fff;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
        }
        h2 {
            text-align: center;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        th, td {
            border: 1px solid #ddd;
            padding: 12px;
            text-align: center;
        }
        th {
            background-color: #4CAF50;
            color: white;
        }
        .actions a {
            text-decoration: none;
            color: #4CAF50;
            margin-right: 10px;
        }
        .actions a:hover {
            text-decoration: underline;
        }
        .total {
            text-align: right;
            font-weight: bold;
            margin-top: 15px;
        }
        .back-link {
            text-align: center;
            margin-top: 15px;
        }
        button {
            padding: 10px 20px;
            background-color: #4CAF50;
            color: #fff;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }
        button:hover {
            background-color: #45a049;
        }
    </style>
</head>
<body>
    <div class="container">
        <h2>🛒 Alışveriş Sepetiniz</h2>

        <table>
            <tr>
                <th>Görsel</th>
                <th>Ürün Adı</th>
                <th>Adet</th>
                <th>Fiyat</th>
                <th>Toplam</th>
                <th>İşlemler</th>
            </tr>
            <?php 
            $toplam_fiyat = 0;
            if ($result->num_rows > 0) {
                while ($row = $result->fetch_assoc()) {
                    $urun_toplam = $row['fiyat'] * $row['adet'];
                    $toplam_fiyat += $urun_toplam;
                    ?>
                    <tr>
                        <td><img src="uploads/<?php echo htmlspecialchars($row['resim']); ?>" width="50" height="50"></td>
                        <td><?php echo htmlspecialchars($row['urun_adi']); ?></td>
                        <td><?php echo $row['adet']; ?></td>
                        <td><?php echo number_format($row['fiyat'], 2); ?> TL</td>
                        <td><?php echo number_format($urun_toplam, 2); ?> TL</td>
                        <td class="actions">
                            <a href="sepet_sil.php?id=<?php echo $row['sepet_id']; ?>">❌ Kaldır</a>
                        </td>
                    </tr>
            <?php }
            } else {
                echo "<tr><td colspan='6'>🛒 Sepetinizde ürün yok.</td></tr>";
            }
            ?>
        </table>

        <div class="total">
            <p>Toplam Fiyat: <strong><?php echo number_format($toplam_fiyat, 2); ?> TL</strong></p>
        </div>

        <?php if ($toplam_fiyat > 0) { ?>
            <form action="satin_al.php" method="post">
                <button type="submit">✅ Satın Al</button>
            </form>
        <?php } ?>

        <div class="back-link">
            <a href="urun_listele.php">⬅️ Alışverişe Devam Et</a>
        </div>
    </div>
</body>
</html>
