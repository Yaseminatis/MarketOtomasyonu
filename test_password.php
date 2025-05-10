<?php
// Girdiğiniz Şifre (Giriş yapmaya çalıştığınız şifre)
$girilen_sifre = "yasemin123"; // Kayıt sırasında kullandığınız şifre

// Veritabanındaki Hashli Şifre (phpMyAdmin'den aldığınız hashli şifre)
$hashli_sifre = '$2y$10$NXpaI4xzr10L/'; // Kendi hashli şifrenizi buraya yapıştırın

// Şifre Doğrulama Testi
if (password_verify($girilen_sifre, $hashli_sifre)) {
    echo "Şifre doğrulama BAŞARILI!";
} else {
    echo "Şifre doğrulama BAŞARISIZ!";
}
?>
