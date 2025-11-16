<?php
// FILE: koneksi.php
$host = "localhost";
$user = "root"; // Ganti dengan username database Anda
$pass = "";     // Ganti dengan password database Anda
$db   = "db_assaadah"; // Pastikan Anda sudah membuat database ini

$conn = new mysqli($host, $user, $pass, $db);

if ($conn->connect_error) {
    die("Koneksi gagal: " . $conn->connect_error);
}

// ==============================================================================
// Buat Tabel Galeri (Jalankan query ini satu kali di MySQL/phpMyAdmin Anda)
// ==============================================================================
/*
CREATE TABLE galeri (
    id INT(11) AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    deskripsi TEXT,
    file_path VARCHAR(255) NOT NULL,
    tipe_file ENUM('foto', 'video') NOT NULL,
    tanggal_upload TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
*/
?>