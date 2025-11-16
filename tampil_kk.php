<?php
// FILE: tampil_kk.php

include 'koneksi.php'; // Pastikan path koneksi Anda benar

if (!isset($_GET['id']) || empty($_GET['id'])) {
    header('HTTP/1.0 400 Bad Request');
    exit('ID pendaftaran tidak ditemukan.');
}

$pendaftaran_id = $_GET['id'];

if (isset($conn) && $conn) {
    // Siapkan query untuk mengambil file_kk (BLOB) dan mime_type
    $stmt = $conn->prepare("SELECT file_kk, mime_type FROM pendaftaran WHERE id = ?");
    $stmt->bind_param("i", $pendaftaran_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $data = $result->fetch_assoc();
    $stmt->close();
    $conn->close();
    
    // Jika ada konten, tampilkan sebagai gambar dibungkus HTML
    if ($data && $data['file_kk'] && $data['mime_type']) {
        
        $image_base64 = base64_encode($data['file_kk']);
        $mime_type = $data['mime_type'];
        
        ?>
        <!DOCTYPE html>
        <html lang="id">
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title>Foto Kartu Keluarga #<?php echo $pendaftaran_id; ?></title>
            <style>
                body {
                    margin: 0;
                    padding: 20px;
                    background-color: #f0f0f0;
                    font-family: sans-serif;
                    text-align: center;
                }
                .header-exit {
                    margin: 0 auto 20px auto;
                    max-width: 800px;
                    display: flex;
                    justify-content: space-between;
                    align-items: center;
                }
                .btn-exit {
                    background-color: #4CAF50;
                    color: white;
                    padding: 10px 15px;
                    text-decoration: none;
                    border-radius: 5px;
                    font-weight: bold;
                    transition: background-color 0.3s;
                }
                .btn-exit:hover {
                    background-color: #1B5E20;
                }
                .kk-image {
                    max-width: 800px;
                    width: 90%;
                    height: auto;
                    border: 1px solid #ccc;
                    box-shadow: 0 0 8px rgba(0,0,0,0.2);
                    display: block;
                    margin: 0 auto;
                }
            </style>
        </head>
        <body>
            <div class="header-exit">
                <h2>Kartu Keluarga - ID #<?php echo $pendaftaran_id; ?></h2>
                <a href="admin/adminpendaftaransiswasiswi.php" class="btn-exit">Keluar (Kembali ke Admin)</a>

            </div>
            <img src="data:<?php echo $mime_type; ?>;base64,<?php echo $image_base64; ?>" alt="Foto Kartu Keluarga" class="kk-image">
        </body>
        </html>
        <?php
    } else {
        header('HTTP/1.0 404 Not Found');
        echo 'Data atau Foto Kartu Keluarga (KK) tidak ditemukan.';
    }

} else {
    header('HTTP/1.0 500 Internal Server Error');
    echo 'Koneksi database gagal.';
}
?>
