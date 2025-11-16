<?php
// FILE: ekskul_detail.php
// Halaman tunggal untuk menampilkan detail SEMUA ekskul

include 'koneksi.php'; // Pastikan path koneksi benar

// 1. Ambil data Ekskul untuk Navbar Dropdown (Sama seperti di index.php)
$daftar_ekskul = []; 
$query_ekskul_nav = "SELECT id, nama_ekskul, file_name FROM ekstrakurikuler ORDER BY nama_ekskul ASC";

if (isset($conn) && $conn) {
    $result_ekskul_nav = $conn->query($query_ekskul_nav);
    
    if ($result_ekskul_nav && $result_ekskul_nav->num_rows > 0) {
        while($row = $result_ekskul_nav->fetch_assoc()) {
            $daftar_ekskul[] = $row;
        }
    }
}

// 2. Dapatkan nama file (slug) yang diminta dari URL (Data Konten Utama)
$file_name_slug = $_GET['slug'] ?? ''; 
$ekskul_data = null;

if ($file_name_slug && isset($conn) && $conn) {
    // Cari ekskul berdasarkan file_name yang sama persis dengan slug dari URL
    $stmt = $conn->prepare("SELECT * FROM ekstrakurikuler WHERE file_name = ?");
    $stmt->bind_param("s", $file_name_slug); 
    $stmt->execute();
    $result = $stmt->get_result();
    $ekskul_data = $result->fetch_assoc();
    $stmt->close();
}

// 3. Penanganan jika data tidak ditemukan
if (!$ekskul_data) {
    http_response_code(404);
    echo "<!DOCTYPE html><title>404 Not Found</title><h1>404 Not Found</h1><p>Detail Ekskul tidak ditemukan.</p>";
    exit();
}

// 4. Proses Materi Utama (untuk ditampilkan dalam bentuk list)
$materi_list = explode("\n", $ekskul_data['materi_utama'] ?? ''); 
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detail Ekskul: <?php echo htmlspecialchars($ekskul_data['nama_ekskul']); ?></title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css"> 
    
    <style>
        /* --- CSS Global dan Navbar (Sama seperti index.php) --- */
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

        /* --- CSS Konten Ekskul Detail --- */
        
        /* Hapus padding pada body yang sudah digantikan oleh padding di div#content-wrapper */
        body { padding: 0; } 
        
        /* Wrapper untuk konten utama (agar tidak terlalu lebar dan ada padding atas/bawah) */
        #content-wrapper {
            padding: 40px 5%; /* Padding di luar konten detail */
            min-height: calc(100vh - 75px - 60px); /* Ketinggian minimal dikurangi navbar & footer */
        }
        
        .container { 
            max-width: 900px; margin: auto; background: white; 
            padding: 30px; border-radius: 8px; box-shadow: 0 0 10px rgba(0,0,0,0.1); 
        }
        .header-ekskul h1 { color: #1B5E20; margin-bottom: 5px; border-bottom: 2px solid #a5d6a7; padding-bottom: 10px; }
        .header-ekskul p { color: #666; font-style: italic; margin-bottom: 20px; }
        .info-card { background: #e8f5e9; padding: 20px; border-radius: 8px; }
        .info-card h3 { color: #4CAF50; margin-bottom: 15px; }
        .info-card strong { font-weight: bold; color: #1B5E20; }
        .materi-list { margin-top: 20px; }
        .materi-list h2 { color: #1B5E20; margin-bottom: 10px; }
        .materi-list ul { list-style: none; padding-left: 0; }
        .materi-list li { margin-bottom: 10px; padding-left: 20px; position: relative; }
        .materi-list li::before { content: "•"; position: absolute; left: 0; color: #4CAF50; font-size: 1.2em; line-height: 1; }
        .ekskul-image { max-width: 100%; height: auto; border-radius: 8px; margin-top: 20px; }
        .content-grid { display: grid; grid-template-columns: 2fr 1fr; gap: 30px; }
        .daftar-button { display: block; text-align: center; margin-top: 15px; padding: 10px; background-color: #4CAF50; color: white; border-radius: 5px; text-decoration: none; transition: background-color 0.3s; }
        .daftar-button:hover { background-color: #1B5E20; }
        @media (max-width: 768px) {
            .content-grid { grid-template-columns: 1fr; }
        }
        
        /* --- Footer (Copyright) --- */
        .placeholder-footer {
            text-align: center;
            padding: 20px;
            background-color: var(--secondary-color);
            color: #c8e6c9;
            font-size: 0.8em;
            width: 100%; 
        }
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
            <a href="#" class="dropdown-toggle" style="color: var(--primary-color);">Ekskul <i class="fas fa-caret-down"></i></a>
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
        <a href="pendaftaransiswasiswi.php" class="btn-kontak">PENDAFTAAN SISWA/SISWI</a>
    </div>
</header>

<div id="content-wrapper">
    <div class="container">
        <div class="header-ekskul">
            <h1><?php echo htmlspecialchars($ekskul_data['nama_ekskul']); ?></h1>
            <p><?php echo htmlspecialchars($ekskul_data['deskripsi']); ?></p>
        </div>
        
        <div class="content-grid">
            <div class="main-content">
                <?php if ($ekskul_data['gambar_kegiatan']): ?>
                    <img src="<?php echo htmlspecialchars($ekskul_data['gambar_kegiatan']); ?>" alt="Gambar Kegiatan Ekskul" class="ekskul-image">
                <?php endif; ?>

                <div class="materi-list">
                    <h2>Materi Pembelajaran Utama</h2>
                    <ul>
                        <?php 
                        foreach ($materi_list as $materi): 
                            $materi = trim($materi);
                            if (!empty($materi)):
                        ?>
                            <li><?php echo htmlspecialchars($materi); ?></li>
                        <?php 
                            endif;
                        endforeach; 
                        ?>
                    </ul>
                </div>
            </div>
            
            <div class="sidebar">
                <div class="info-card">
                    <h3>Informasi Kegiatan</h3>
                    <p><strong>Jadwal:</strong> <?php echo htmlspecialchars($ekskul_data['jadwal']); ?></p>
                    <p><strong>Pembina:</strong> <?php echo htmlspecialchars($ekskul_data['pembina']); ?></p>
                    <p><strong>Kelompok:</strong> <?php echo htmlspecialchars($ekskul_data['kelompok']); ?></p>
                    <p><strong>Prestasi:</strong> <?php echo htmlspecialchars($ekskul_data['prestasi']); ?></p>
                    <a href="#" class="daftar-button">Daftar Sekarang</a>
                </div>
            </div>
        </div>
    </div>
</div>

<footer class="placeholder-footer">
    <p>&copy; 2025 SD ISLAM ASSA'ADAH. Hak Cipta Dilindungi.</p>
</footer>

</body>
</html>