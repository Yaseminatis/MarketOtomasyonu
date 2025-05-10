<?php
session_start();
include 'includes/db_connect.php'; // Veritabanı bağlantısı

// Kullanıcı giriş kontrolü
if (!isset($_SESSION['kullanici_adi'])) {
    header("Location: index.php");  
    exit();
}

// Kullanıcı bilgileri
$kullanici_adi = $_SESSION['kullanici_adi'];
$kullanici_rol = $_SESSION['rol'];

// Kategori Filtreleme
$kategori_id = isset($_GET['kategori']) ? intval($_GET['kategori']) : 0;

// Kategorileri çekme
$kategori_query = "SELECT * FROM kategori";
$kategori_result = $conn->query($kategori_query);

// Ürünleri veritabanından çekme (Kategori filtreleme)
$query = "SELECT u.*, k.kategori_adi 
          FROM urunler u 
          LEFT JOIN kategori k ON u.kategori_id = k.id 
          WHERE 1 ";

if ($kategori_id > 0) {
    $query .= "AND u.kategori_id = ?";
}

$query .= " ORDER BY u.tarih DESC";
$stmt = $conn->prepare($query);

// Kategoriye göre filtreleme
if ($kategori_id > 0) {
    $stmt->bind_param("i", $kategori_id);
}
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html>
<head>
    <title>Ürün Listesi</title>
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
        .filter-bar {
            text-align: center;
            margin-bottom: 15px;
        }
        .filter-bar select {
            padding: 8px;
            border-radius: 5px;
            border: 1px solid #ddd;
        }
        .filter-bar button {
            padding: 8px 15px;
            background-color: #4CAF50;
            color: #fff;
            border: none;
            border-radius: 5px;
            cursor: pointer;
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
        td img {
            border-radius: 5px;
            width: 50px;
            height: 50px;
        }
        .actions a {
            text-decoration: none;
            color: #4CAF50;
            font-weight: bold;
            margin-right: 10px;
        }
        .actions a:hover {
            text-decoration: underline;
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
        <h2>📦 Ürün Listesi</h2>

        <!-- Kategori Filtreleme -->
        <div class="filter-bar">
            <form method="get">
                <select name="kategori" onchange="this.form.submit()">
                    <option value="0">Tüm Kategoriler</option>
                    <?php while ($kategori = $kategori_result->fetch_assoc()) { ?>
                        <option value="<?php echo $kategori['id']; ?>" <?php if ($kategori['id'] == $kategori_id) echo 'selected'; ?>>
                            <?php echo htmlspecialchars($kategori['kategori_adi']); ?>
                        </option>
                    <?php } ?>
                </select>
            </form>
        </div>

        <table>
            <tr>
                <th>ID</th>
                <th>Görsel</th>
                <th>Ürün Adı</th>
                <th>Kategori</th>
                <th>Fiyat (TL)</th>
                <th>Stok</th>
                <th>Açıklama</th>
                <th>İşlemler</th>
            </tr>
            <?php if ($result->num_rows > 0) { 
                while ($row = $result->fetch_assoc()) { ?>
                    <tr>
                        <td><?php echo $row['id']; ?></td>
                        <td>
                            <?php if (!empty($row['resim'])) { ?>
                                <img src="uploads/<?php echo htmlspecialchars($row['resim']); ?>" alt="Ürün Görseli">
                            <?php } else { ?>
                                Görsel Yok
                            <?php } ?>
                        </td>
                        <td><?php echo htmlspecialchars($row['urun_adi']); ?></td>
                        <td><?php echo htmlspecialchars($row['kategori_adi']); ?></td>
                        <td><?php echo number_format($row['fiyat'], 2); ?> TL</td>
                        <td><?php echo htmlspecialchars($row['stok']); ?></td>
                        <td><?php echo htmlspecialchars($row['aciklama']); ?></td>
                        <td class="actions">
                            <?php if ($kullanici_rol === 'admin') { ?>
                                <a href="urun_duzenle.php?id=<?php echo $row['id']; ?>">Düzenle</a> | 
                                <a href="urun_sil.php?id=<?php echo $row['id']; ?>" onclick="return confirm('Bu ürünü silmek istediğinizden emin misiniz?');">Sil</a>
                            <?php } else { ?>
                                <a href="sepet_ekle.php?id=<?php echo $row['id']; ?>">Sepete Ekle</a>
                            <?php } ?>
                        </td>
                    </tr>
            <?php } 
            } else { ?>
                <tr><td colspan="8">Aramanıza uygun ürün bulunamadı.</td></tr>
            <?php } ?>
        </table>

        <div class="back-link">
            <a href="dashboard.php">⬅️ Geri Dön</a>
        </div>
    </div>
</body>
</html>
