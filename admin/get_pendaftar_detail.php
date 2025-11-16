<?php
// FILE: admin/get_pendaftar_detail.php

// --------------------------------------------------------------------------------
// CATATAN: HAPUS KOMENTAR pada 3 baris di bawah ini jika error masih muncul! 
// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
// error_reporting(E_ALL); 
// --------------------------------------------------------------------------------

// 1. Tentukan header respons sebagai JSON
header('Content-Type: application/json');

// 2. Sertakan file koneksi (path sudah pasti benar jika koneksi.php ada di luar admin/)
include '../koneksi.php'; 

// 3. Cek Koneksi Database
if (!isset($conn) || $conn->connect_error) {
    echo json_encode(['error' => 'Koneksi database gagal.']);
    exit;
}

// 4. Validasi ID
if (!isset($_GET['id']) || empty($_GET['id'])) {
    echo json_encode(['error' => 'ID pendaftar tidak disediakan.']);
    exit;
}

$id = intval($_GET['id']);

// --- QUERY PENGAMBILAN DATA SESUAI KOLOM YANG TERSEDIA DI TABEL ANDA ---
$query = "SELECT 
    id, nama_lengkap, nomor_kontak, tanggal_daftar, 
    
    -- Dokumen Paths --
    akte_kelahiran_path, ijazah_tk_path, kartu_keluarga_path, ktp_ayah_path, ktp_ibu_path 
    
    FROM pendaftaran_siswa 
    WHERE id = ?";

$stmt = $conn->prepare($query);

// 5. Cek jika prepare query gagal
if ($stmt === false) {
    echo json_encode(['error' => 'SQL prepare error. Cek apakah tabel pendaftaran_siswa ada. Error: ' . $conn->error]);
    $conn->close();
    exit;
}

$stmt->bind_param("i", $id);

// 6. Cek jika execute query gagal
if (!$stmt->execute()) {
    echo json_encode(['error' => 'SQL execution error: ' . $stmt->error]);
    $stmt->close();
    $conn->close();
    exit;
}

$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $data = $result->fetch_assoc();
    
    // Ubah format tanggal (opsional)
    if (isset($data['tanggal_daftar'])) {
        $data['tanggal_daftar'] = date("d M Y H:i:s", strtotime($data['tanggal_daftar']));
    }

    // Kirim data sebagai JSON yang bersih
    echo json_encode($data);
} else {
    echo json_encode(['error' => 'Data pendaftar tidak ditemukan.']);
}

$stmt->close();
$conn->close();
?>