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

// Form verilerini alma
$isim = trim($_POST['isim']);
$email = trim($_POST['email']);
$mevcut_sifre = trim($_POST['mevcut_sifre']);
$yeni_sifre = trim($_POST['yeni_sifre']);

// Kullanıcıyı veritabanından çekme
$query = "SELECT * FROM kullanicilar WHERE kullanici_adi = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("s", $kullanici_adi);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

// Şifre kontrolü
if (!empty($mevcut_sifre)) {
    if (password_verify($mevcut_sifre, $user['sifre'])) {
        if (!empty($yeni_sifre)) {
            // Yeni şifreyi hashleyerek kaydetme
            $hashed_sifre = password_hash($yeni_sifre, PASSWORD_BCRYPT);
            $query = "UPDATE kullanicilar SET isim = ?, email = ?, sifre = ? WHERE kullanici_adi = ?";
            $stmt = $conn->prepare($query);
            $stmt->bind_param("ssss", $isim, $email, $hashed_sifre, $kullanici_adi);
        } else {
            // Şifre değişmeden sadece isim ve e-posta güncellenir
            $query = "UPDATE kullanicilar SET isim = ?, email = ? WHERE kullanici_adi = ?";
            $stmt = $conn->prepare($query);
            $stmt->bind_param("sss", $isim, $email, $kullanici_adi);
        }
    } else {
        header("Location: profil.php?error=Mevcut şifre yanlış.");
        exit();
    }
} else {
    // Şifreyi değiştirmek istemiyorsa
    $query = "UPDATE kullanicilar SET isim = ?, email = ? WHERE kullanici_adi = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("sss", $isim, $email, $kullanici_adi);
}

$stmt->execute();

// Profil güncelleme başarılıysa
header("Location: profil.php?success=Profil başarıyla güncellendi.");
exit();
