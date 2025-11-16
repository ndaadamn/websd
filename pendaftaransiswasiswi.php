<?php
// FILE: pendaftaransiswasiswi.php (FORM PUBLIK FINAL dengan Navbar & Footer yang Konsisten)
include 'koneksi.php'; // Sesuaikan path koneksi

$uploadDir = 'uploads/pendaftaran/'; // Folder tujuan upload
if (!is_dir($uploadDir)) {
    mkdir($uploadDir, 0777, true);
}

$message = '';

// ----------------------------------------------------------------------
// LOGIKA AMBIL DATA UNTUK NAVBAR (Diambil dari kontak.php)
// ----------------------------------------------------------------------
$daftar_ekskul = []; 
$query_ekskul = "SELECT nama_ekskul, file_name FROM ekstrakurikuler ORDER BY nama_ekskul ASC";

if (isset($conn) && $conn) {
    // Gunakan try-catch atau pengecekan yang lebih kuat di lingkungan produksi
    $result_ekskul = $conn->query($query_ekskul);
    
    if ($result_ekskul && $result_ekskul->num_rows > 0) {
        while($row = $result_ekskul->fetch_assoc()) {
            $daftar_ekskul[] = $row;
        }
    }
}


/**
 * Fungsi Pembantu untuk Upload File (Tidak Berubah)
 */
function handle_file_upload($fileInput, $fileNamePrefix, $uploadDir, $isRequired = true) {
    if ($fileInput['error'] !== 0) {
        if ($isRequired) {
            return ['success' => false, 'path' => '', 'message' => "Gagal upload file $fileNamePrefix: file tidak ditemukan atau error."];
        }
        return ['success' => true, 'path' => '', 'message' => 'File opsional dilewati.'];
    }

    $allowedTypes = ['jpg', 'jpeg', 'png', 'pdf'];
    $fileType = strtolower(pathinfo($fileInput["name"], PATHINFO_EXTENSION));

    if (!in_array($fileType, $allowedTypes)) {
        return ['success' => false, 'path' => '', 'message' => "Tipe file $fileNamePrefix tidak valid. Hanya JPG, PNG, atau PDF yang diizinkan."];
    }
    
    $unique_filename = $fileNamePrefix . '_' . time() . '.' . $fileType;
    $final_target = $uploadDir . $unique_filename;
    
    if (move_uploaded_file($fileInput["tmp_name"], $final_target)) {
        return ['success' => true, 'path' => $final_target, 'message' => 'Upload berhasil.'];
    } else {
        return ['success' => false, 'path' => '', 'message' => "Gagal memindahkan file $fileNamePrefix."];
    }
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['submit_pendaftaran'])) {
    // 1. Ambil Data Teks
    $nama_lengkap = $conn->real_escape_string($_POST['nama_lengkap']);
    $nomor_kontak = $conn->real_escape_string($_POST['nomor_kontak']);
    $tanggal_daftar = date("Y-m-d H:i:s");
    
    // 2. Validasi KTP (Minimal satu harus diunggah)
    $ktp_ayah_uploaded = (isset($_FILES['ktp_ayah']) && $_FILES['ktp_ayah']['error'] == 0);
    $ktp_ibu_uploaded = (isset($_FILES['ktp_ibu']) && $_FILES['ktp_ibu']['error'] == 0);

    if (!$ktp_ayah_uploaded && !$ktp_ibu_uploaded) {
        $message = '<div class="alert error-message">Gagal: Anda wajib mengunggah minimal satu KTP (Ayah atau Ibu).</div>';
    } else {
        // 3. Proses Upload Semua File
        $uploads = [];
        $uploads['akte'] = handle_file_upload($_FILES['akte_kelahiran'], 'akte', $uploadDir, true);
        $uploads['kk'] = handle_file_upload($_FILES['kartu_keluarga'], 'kk', $uploadDir, true);
        $uploads['ijazah_tk'] = handle_file_upload($_FILES['ijazah_tk'], 'ijazah_tk', $uploadDir, false); // Opsional
        $uploads['ktp_ayah'] = handle_file_upload($_FILES['ktp_ayah'], 'ktp_ayah', $uploadDir, false); 
        $uploads['ktp_ibu'] = handle_file_upload($_FILES['ktp_ibu'], 'ktp_ibu', $uploadDir, false); 

        $all_success = true;
        $error_messages = [];

        foreach ($uploads as $key => $upload) {
            if (!$upload['success'] && !in_array($key, ['ktp_ayah', 'ktp_ibu', 'ijazah_tk'])) { 
                $all_success = false;
                $error_messages[] = $upload['message'];
            }
        }
        
        $ktp_ayah_path = $uploads['ktp_ayah']['path'];
        $ktp_ibu_path = $uploads['ktp_ibu']['path'];

        if ($all_success) {
            // 4. Simpan ke Database
            $sql = "INSERT INTO pendaftaran_siswa (
                        nama_lengkap, nomor_kontak, tanggal_daftar, 
                        akte_kelahiran_path, ijazah_tk_path, kartu_keluarga_path, ktp_ayah_path, ktp_ibu_path
                    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
                    
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("ssssssss", 
                $nama_lengkap, $nomor_kontak, $tanggal_daftar, 
                $uploads['akte']['path'], $uploads['ijazah_tk']['path'], $uploads['kk']['path'], $ktp_ayah_path, $ktp_ibu_path
            );
            
            if ($stmt->execute()) {
                $message = '<div class="alert success-message">Pendaftaran Berhasil! Kami akan segera menghubungi Anda di nomor kontak yang Anda berikan.</div>';
                $_POST = [];
            } else {
                $message = '<div class="alert error-message">Gagal menyimpan data ke database: ' . $stmt->error . '</div>';
            }
            $stmt->close();
        } else {
             $message = '<div class="alert error-message">Pendaftaran Gagal. Beberapa file wajib gagal diunggah: ' . implode('<br>', $error_messages) . '</div>';
        }
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Formulir Pendaftaran Siswa Baru - SD ISLAM ASSA'ADAH</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css"> 
    
    <style>
        /* --- CSS Global dan Navbar (Diambil dari kontak.php) --- */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: sans-serif;
        }

        :root {
            --primary-color: #4CAF50; 
            --secondary-color: #1B5E20; 
            --text-light: #ffffff;
            --text-dark: #333333;
            --bg-light: #f1f8e9; 
        }

        body {
            background-color: var(--bg-light); 
        }

        /* Navbar */
        .navbar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 15px 5%;
            background-color: var(--text-light);
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            position: relative; 
            z-index: 100; 
            width: 100%; 
        }

        .logo {
            font-size: 1.2em;
            font-weight: bold;
            color: var(--secondary-color);
        }

        .nav-menu {
            display: flex;
            align-items: center; 
            gap: 20px;
            flex-grow: 1; 
            margin-left: 30px;
        }
        
        .nav-menu a {
            text-decoration: none;
            color: var(--text-dark);
            padding: 5px 0;
            font-size: 0.9em;
            transition: color 0.3s ease-in-out; 
            display: inline-block; 
        }
        
        .nav-menu a:hover, .dropdown:hover .dropdown-toggle {
            color: var(--primary-color) !important; 
        }
        /* Penanda link aktif */
        .nav-menu .active-link {
            color: var(--primary-color) !important;
            font-weight: bold;
        }


        .nav-actions a {
            text-decoration: none;
            padding: 8px 15px;
            margin-left: 10px;
            border-radius: 5px;
            font-size: 0.9em;
            background-color: var(--primary-color);
            color: var(--text-light); 
        }

        .nav-actions a:hover {
            background-color: var(--secondary-color); 
        }
        
        /* Dropdown CSS */
        .dropdown {
            position: relative;
            display: inline-block;
            cursor: pointer; 
            padding: 0; 
        }

        .dropdown-content {
            display: none !important; 
            position: absolute;
            background-color: var(--text-light);
            min-width: 160px;
            box-shadow: 0px 8px 16px 0px rgba(0,0,0,0.2);
            z-index: 101; 
            top: 100%; 
            left: 0;
            padding: 0;
        }
        
        .dropdown:hover .dropdown-content {
            display: block !important; 
        }

        .dropdown-content a {
            color: black;
            padding: 12px 16px;
            text-decoration: none;
            display: block;
            margin-right: 0;
            font-size: 0.9em;
        }

        .dropdown-content a:hover {
            background-color: #e8f5e9; 
        }
        
        /* Footer */
        .placeholder-footer {
            text-align: center;
            padding: 20px;
            background-color: var(--secondary-color); /* Sama dengan kontak.php */
            color: #c8e6c9; /* Warna teks yang lebih terang */
            font-size: 0.8em;
            width: 100%; 
        }

        /* --- CSS Konten Formulir --- */
        #content-wrapper {
            padding: 40px 5%; 
            min-height: calc(100vh - 75px - 60px); 
        }
        .card { 
            background: white; 
            padding: 30px; 
            border-radius: 10px; 
            box-shadow: 0 6px 15px rgba(0,0,0,0.1); 
            max-width: 700px;
            margin: 0 auto; /* Tengah */
        }
        .card h2 { color: var(--text-dark); border-bottom: 2px solid #ddd; padding-bottom: 10px; margin-bottom: 20px; }
        .card h3 { color: var(--primary-color); margin-top: 0; }
        form label { display: block; margin: 15px 0 5px; font-weight: bold; }
        form input[type="text"], form input[type="file"] { 
            width: 100%; 
            padding: 10px; 
            border: 1px solid #ccc; 
            border-radius: 4px; 
            box-sizing: border-box; 
        }
        form button[type="submit"] { /* Targetkan submit button saja */
            background-color: var(--primary-color); 
            color: white; 
            padding: 12px 20px; 
            border: none; 
            border-radius: 4px; 
            cursor: pointer;
            margin-top: 20px; 
            font-size: 1.1em;
        }
        form button[type="submit"]:hover { background-color: var(--secondary-color); }
        
        /* FIX: Perbaikan Spacing untuk Catatan Wajib */
        .required-note { 
            color: red; 
            font-size: 0.9em; 
            margin-top: 5px; /* Dulu: -10px */
            margin-bottom: 15px; /* Diberi jarak yang cukup dari label pertama */
        }
        
        .alert { padding: 15px; border-radius: 4px; margin-bottom: 20px; font-weight: bold; }
        .success-message { background-color: #d4edda; color: #155724; border: 1px solid #c3e6cb; }
        .error-message { background-color: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; }
    </style>
</head>
<body>

<header class="navbar">
    <div class="logo">SD ISLAM ASSA'ADAH</div>
    <nav class="nav-menu">
        <a href="index.php">Home</a>
        <div class="dropdown">
            <a href="#" class="dropdown-toggle">Profil <i class="fas fa-caret-down"></i></a>
            <div class="dropdown-content">
                <a href="profil_sekolah.php">Profil Sekolah</a> 
                <a href="profil_guru.php">Profil Guru</a> 
            </div>
        </div>
        
        <a href="galeri.php">Galeri</a>
        
        <div class="dropdown" id="menu-ekskul">
            <a href="#" class="dropdown-toggle">Ekskul <i class="fas fa-caret-down"></i></a>
            <div class="dropdown-content">
                <?php foreach ($daftar_ekskul as $ekskul_nav): ?>
                    <a href="ekskul_detail.php?slug=<?php echo htmlspecialchars($ekskul_nav['file_name']); ?>">
                        <?php echo htmlspecialchars($ekskul_nav['nama_ekskul']); ?>
                    </a>
                <?php endforeach; ?>
            </div>
        </div>
        <a href="kontak.php">Kontak</a>
        
    </nav>
    <div class="nav-actions">
        <a href="pendaftaransiswasiswi.php" class="btn-kontak" style="background-color: var(--secondary-color);">PENDAFTAAN SISWA/SISWI</a>
    </div>
</header>
<div id="content-wrapper">
    
    <?php echo $message; ?>

    <div class="card">
        <h2><i class="fas fa-user-plus"></i> Formulir Pendaftaran Siswa Baru</h2>
        
        <form method="POST" enctype="multipart/form-data">
            
            <h3>Data Calon Siswa & Kontak</h3>
            <label for="nama_lengkap">Nama Lengkap Anak</label>
            <input type="text" id="nama_lengkap" name="nama_lengkap" required value="<?php echo htmlspecialchars($_POST['nama_lengkap'] ?? ''); ?>">

            <label for="nomor_kontak">Nomor yang Bisa Dihubungi (HP/WA)</label>
            <input type="text" id="nomor_kontak" name="nomor_kontak" required value="<?php echo htmlspecialchars($_POST['nomor_kontak'] ?? ''); ?>">

            <h3 style="margin-top: 30px;"><i class="fas fa-file-upload"></i> Upload Dokumen Pendukung (JPG, PNG, PDF)</h3>
            
            <label for="akte_kelahiran">Foto Akte Kelahiran Anak</label>
            <input type="file" id="akte_kelahiran" name="akte_kelahiran" accept="image/*,.pdf" required>
            
            <label for="ijazah_tk">Foto Ijazah TK (Opsional)</label>
            <input type="file" id="ijazah_tk" name="ijazah_tk" accept="image/*,.pdf">
            
            <label for="kartu_keluarga">Foto Kartu Keluarga (KK)</label>
            <input type="file" id="kartu_keluarga" name="kartu_keluarga" accept="image/*,.pdf" required>

            <h3 style="margin-top: 30px;">Dokumen KTP Orang Tua</h3>
            <p class="required-note"><i class="fas fa-exclamation-circle"></i> Wajib mengunggah minimal satu KTP (Ayah ATAU Ibu)</p>

            <label for="ktp_ayah">Foto KTP Ayah</label>
            <input type="file" id="ktp_ayah" name="ktp_ayah" accept="image/*,.pdf">
            
            <label for="ktp_ibu">Foto KTP Ibu</label>
            <input type="file" id="ktp_ibu" name="ktp_ibu" accept="image/*,.pdf">

            <button type="submit" name="submit_pendaftaran"><i class="fas fa-paper-plane"></i> Kirim Formulir Pendaftaran</button>
        </form>
    </div>

</div>

<footer class="placeholder-footer">
    <p>&copy; <?php echo date("Y"); ?> SD ISLAM ASSA'ADAH. Hak Cipta Dilindungi.</p>
</footer>

</body>
</html>
<?php 
if (isset($conn)) $conn->close(); 
?>