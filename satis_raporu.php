<?php
session_start();
include 'includes/db_connect.php';

// Kullanıcı giriş kontrolü (Yalnızca Admin)
if (!isset($_SESSION['kullanici_adi']) || $_SESSION['rol'] !== 'admin') {
    header("Location: index.php");
    exit();
}

// Tarihe göre filtreleme
$baslangic_tarihi = isset($_GET['baslangic']) ? $_GET['baslangic'] : '';
$bitis_tarihi = isset($_GET['bitis']) ? $_GET['bitis'] : '';

$query = "SELECT s.id, s.urun_adi, s.adet, s.toplam_fiyat, s.tarih, u.kullanici_adi 
          FROM satislar s 
          JOIN kullanicilar u ON s.kullanici_id = u.id 
          WHERE 1";

if (!empty($baslangic_tarihi) && !empty($bitis_tarihi)) {
    $query .= " AND s.tarih BETWEEN ? AND ?";
}

$query .= " ORDER BY s.tarih DESC";
$stmt = $conn->prepare($query);

// Filtre uygulanmışsa tarih aralığını sorguya ekle
if (!empty($baslangic_tarihi) && !empty($bitis_tarihi)) {
    $stmt->bind_param("ss", $baslangic_tarihi, $bitis_tarihi);
}
$stmt->execute();
$result = $stmt->get_result();

// Toplam satış ve gelir hesaplama
$toplam_satis = 0;
$toplam_gelir = 0;
?>

<!DOCTYPE html>
<html>
<head>
    <title>📊 Satış Raporu</title>
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
        .filter-form {
            display: flex;
            justify-content: center;
            margin-bottom: 15px;
        }
        .filter-form input {
            padding: 8px;
            margin-right: 10px;
            border: 1px solid #ddd;
            border-radius: 5px;
        }
        .filter-form button {
            padding: 8px 15px;
            background-color: #4CAF50;
            color: #fff;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }
        .filter-form button:hover {
            background-color: #45a049;
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
        .total-info {
            margin-top: 15px;
            font-weight: bold;
            text-align: right;
        }
        .back-link {
            text-align: center;
            margin-top: 15px;
        }
        .back-link a {
            text-decoration: none;
            color: #4CAF50;
        }
    </style>
</head>
<body>
    <div class="container">
        <h2>📊 Satış Raporu</h2>

        <!-- Filtreleme Formu -->
        <form class="filter-form" method="get">
            <input type="date" name="baslangic" value="<?php echo htmlspecialchars($baslangic_tarihi); ?>">
            <input type="date" name="bitis" value="<?php echo htmlspecialchars($bitis_tarihi); ?>">
            <button type="submit">Filtrele</button>
            <a href="satis_raporu.php" style="margin-left: 10px;">Temizle</a>
        </form>

        <table>
            <tr>
                <th>ID</th>
                <th>Kullanıcı</th>
                <th>Ürün Adı</th>
                <th>Adet</th>
                <th>Toplam Fiyat (TL)</th>
                <th>Tarih</th>
            </tr>
            <?php if ($result->num_rows > 0) { 
                while ($row = $result->fetch_assoc()) {
                    $toplam_satis += $row['adet'];
                    $toplam_gelir += $row['toplam_fiyat'];
                    ?>
                    <tr>
                        <td><?php echo $row['id']; ?></td>
                        <td><?php echo htmlspecialchars($row['kullanici_adi']); ?></td>
                        <td><?php echo htmlspecialchars($row['urun_adi']); ?></td>
                        <td><?php echo $row['adet']; ?></td>
                        <td><?php echo number_format($row['toplam_fiyat'], 2); ?> TL</td>
                        <td><?php echo date("d-m-Y H:i", strtotime($row['tarih'])); ?></td>
                    </tr>
            <?php } 
            } else { ?>
                <tr><td colspan="6">Henüz satış yapılmadı.</td></tr>
            <?php } ?>
        </table>

        <!-- Toplam Bilgiler -->
        <div class="total-info">
            <p>Toplam Satış: <?php echo $toplam_satis; ?> adet</p>
            <p>Toplam Gelir: <?php echo number_format($toplam_gelir, 2); ?> TL</p>
        </div>

        <div class="back-link">
            <a href="dashboard.php">⬅️ Geri Dön</a>
        </div>
    </div>
</body>
</html>
