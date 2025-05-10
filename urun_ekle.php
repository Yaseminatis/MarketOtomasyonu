<?php
session_start();
include 'includes/db_connect.php';
include 'includes/stok_hareketi.php'; // Stok hareketi kaydı için fonksiyon

// Kullanıcı giriş kontrolü (Yalnızca Admin)
if (!isset($_SESSION['kullanici_adi']) || $_SESSION['rol'] !== 'admin') {
    header("Location: index.php");
    exit();
}

// Ürün ekleme işlemi
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $urun_adi = trim(htmlspecialchars($_POST['urun_adi']));
    $kategori = trim(htmlspecialchars($_POST['kategori']));
    $fiyat = floatval($_POST['fiyat']);
    $stok = intval($_POST['stok']);
    $aciklama = trim(htmlspecialchars($_POST['aciklama']));
    $resim = '';

    // Resim yükleme ve boyutlandırma işlemi
    if (isset($_FILES['resim']) && $_FILES['resim']['error'] == 0) {
        $resim_adi = uniqid() . "_" . basename($_FILES['resim']['name']);
        $hedef_dosya = "uploads/" . $resim_adi;
        $resim_tipi = strtolower(pathinfo($hedef_dosya, PATHINFO_EXTENSION));
        
        // Sadece JPG, JPEG, PNG dosyalarına izin verelim
        if (in_array($resim_tipi, ['jpg', 'jpeg', 'png'])) {
            if (move_uploaded_file($_FILES['resim']['tmp_name'], $hedef_dosya)) {
                $resim = $resim_adi;
            } else {
                $hata_mesaji = "⚠️ Resim yüklenirken hata oluştu!";
            }
        } else {
            $hata_mesaji = "⚠️ Sadece JPG, JPEG ve PNG dosyalarına izin verilir!";
        }
    }

    // Veritabanına ürün ekleme
    if (empty($hata_mesaji)) {
        $query = "INSERT INTO urunler (urun_adi, kategori, fiyat, stok, aciklama, resim) VALUES (?, ?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("ssdiss", $urun_adi, $kategori, $fiyat, $stok, $aciklama, $resim);
        $stmt->execute();
        $urun_id = $stmt->insert_id;

        if ($stmt->affected_rows > 0) {
            // Stok hareketi kaydı (Yeni ürün eklendi)
            stok_hareketi_kaydet($conn, $urun_id, $stok, "Ekleme");

            $basari_mesaji = "✅ Ürün başarıyla eklendi!";
        } else {
            $hata_mesaji = "⚠️ Ürün eklenemedi. Lütfen tekrar deneyin.";
        }

        $stmt->close();
    }

    $conn->close();
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>🛒 Yeni Ürün Ekle</title>
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
        .form-container {
            background-color: #fff;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 0 20px rgba(0, 0, 0, 0.1);
            width: 350px;
        }
        h2 {
            text-align: center;
        }
        input, textarea {
            width: 100%;
            padding: 10px;
            margin: 10px 0;
            border: 1px solid #ddd;
            border-radius: 5px;
        }
        button {
            width: 100%;
            padding: 12px;
            background-color: #4CAF50;
            color: #fff;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }
        button:hover {
            background-color: #45a049;
        }
        .message {
            text-align: center;
            margin-top: 10px;
        }
        .message.success {
            color: green;
        }
        .message.error {
            color: red;
        }
        .back-link {
            text-align: center;
            margin-top: 10px;
        }
        .back-link a {
            text-decoration: none;
            color: #4CAF50;
        }
    </style>
</head>
<body>
    <div class="form-container">
        <h2>🛒 Yeni Ürün Ekle</h2>
        <form action="urun_ekle.php" method="post" enctype="multipart/form-data">
            <input type="text" name="urun_adi" placeholder="Ürün Adı" required><br>
            <input type="text" name="kategori" placeholder="Kategori" required><br>
            <input type="number" step="0.01" name="fiyat" placeholder="Fiyat" required><br>
            <input type="number" name="stok" placeholder="Stok Miktarı" required><br>
            <textarea name="aciklama" placeholder="Açıklama"></textarea><br>
            <input type="file" name="resim" accept="image/*"><br>
            <button type="submit">➕ Ürün Ekle</button>
        </form>
        
        <?php if (isset($hata_mesaji)) { ?>
            <p class="message error"><?php echo $hata_mesaji; ?></p>
        <?php } elseif (isset($basari_mesaji)) { ?>
            <p class="message success"><?php echo $basari_mesaji; ?></p>
        <?php } ?>

        <div class="back-link">
            <a href="dashboard.php">⬅️ Geri Dön</a>
        </div>
    </div>
</body>
</html>
