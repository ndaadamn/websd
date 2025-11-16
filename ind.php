<?php
// ==============================================================================
// FILE: index.php
// ==============================================================================

// Pastikan Anda telah memasukkan 'koneksi.php' di sini
// include 'koneksi.php'; 

$daftar_ekskul = []; 
$query_ekskul = "SELECT id, nama_ekskul, file_name FROM ekstrakurikuler ORDER BY nama_ekskul ASC";

if (isset($conn) && $conn) {
    $result_ekskul = $conn->query($query_ekskul);
    
    if ($result_ekskul && $result_ekskul->num_rows > 0) {
        while($row = $result_ekskul->fetch_assoc()) {
            $daftar_ekskul[] = $row;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SD ISLAM ASSA'ADAH - Beranda</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css"> 
    
    <style>
        /* --- INTERNAL CSS DIMULAI DI SINI --- */

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: sans-serif;
        }

        /* PALET WARNA HIJAU */
        :root {
            --primary-color: #4CAF50; 
            --secondary-color: #1B5E20; 
            --text-light: #ffffff;
            --text-dark: #333333;
            --bg-light: #f1f8e9; 
            --text-shadow-color: rgba(0, 0, 0, 0.7);
            --stat-bg-transparent: rgba(27, 94, 32, 0.6); 
        }

        body {
            background-color: var(--bg-light); 
        }

        /* --- Navbar (Header) --- */
        .navbar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 15px 5%;
            background-color: var(--text-light);
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            position: relative; 
            z-index: 100; 
        }

        .logo {
            font-size: 1.2em;
            font-weight: bold;
            color: var(--secondary-color);
        }

        /* Tambahkan transisi untuk animasi smooth */
        .nav-menu a {
            text-decoration: none;
            color: var(--text-dark);
            margin-right: 20px;
            padding: 5px 0;
            font-size: 0.9em;
            transition: color 0.3s ease-in-out; 
            display: inline-block; 
        }
        
        /* EFEK HOVER ANIMASI (Perubahan warna menjadi hijau) */
        .nav-menu a:hover, .dropdown:hover .dropdown-toggle {
            color: var(--primary-color) !important; 
        }
        .btn-kontak {
            background-color: var(--primary-color);
            color: var(--text-light); 
        }

        .btn-kontak:hover {
            background-color: var(--secondary-color); 
        }

        .nav-actions a {
            text-decoration: none;
            padding: 8px 15px;
            margin-left: 10px;
            border-radius: 5px;
            font-size: 0.9em;
            transition: background-color 0.3s ease-in-out, color 0.3s ease-in-out;
        }
        
        .btn-kontak:hover {
            background-color: var(--secondary-color);
        }

        /* Dropdown CSS */
        .dropdown {
            position: relative;
            /* Penting: Pastikan ini inline-block */
            display: inline-block; 
            cursor: pointer; 
            /* Tambahkan padding di sini agar area hover-nya lebih luas */
            padding: 0; 
            margin-right: 20px; /* Jarak antara dropdown dengan menu setelahnya */
        }
        
        /* PERBAIKAN UTAMA DROPDOWN TOGGLE */
        .dropdown-toggle {
            text-decoration: none;
            color: var(--text-dark);
            /* Hapus margin-right di sini karena sudah dipindahkan ke .dropdown */
            margin-right: 0; 
            /* Tambahkan padding agar bisa di-hover dengan mudah */
            padding: 5px 0; 
            font-size: 0.9em;
            transition: color 0.3s ease-in-out; 
            display: block; /* Buat dia memenuhi .dropdown container secara horizontal */
        }

        .dropdown-content {
            display: none; 
            position: absolute;
            background-color: var(--text-light);
            min-width: 160px;
            box-shadow: 0px 8px 16px 0px rgba(0,0,0,0.2);
            z-index: 101; 
            top: 100%; 
            left: 0;
            padding: 0;
            margin-top: 0; 
        }
        
        /* ATURAN HOVER CSS: KUNCI AGAR DROPDOWN MUNCUL */
        .dropdown:hover .dropdown-content {
            display: block; 
        }

        .dropdown-content a {
            color: black;
            padding: 12px 16px;
            text-decoration: none;
            display: block;
            margin-right: 0;
            font-size: 0.9em;
            transition: background-color 0.3s ease; 
        }

        .dropdown-content a:hover {
            background-color: #e8f5e9; 
        }
        

        /* --- Hero Section dan CSS Statistik (Tidak berubah) --- */
        .hero-section {
            display: flex;
            flex-direction: column;
            justify-content: flex-end;
            padding: 50px 5% 0;
            min-height: 450px;
            position: relative;
            color: var(--text-light); 
            z-index: 1; 

            background-image: url('img/sekolah.jpg'); 
            background-size: cover;
            background-position: center 25%; 
            background-repeat: no-repeat;
            
            background-color: var(--secondary-color); 
        }
        
        .hero-content {
            flex: 1; padding-bottom: 30px; background-color: transparent; 
            margin-bottom: 20px; text-shadow: 1px 1px 3px var(--text-shadow-color); z-index: 2; 
        }

        .welcome-text {
            background-color: rgba(255, 255, 255, 0.2); display: inline-block;
            padding: 5px 15px; margin-bottom: 20px; border-radius: 5px; font-size: 0.9em;
        }

        .hero-content h1 { font-size: 2.2em; margin-bottom: 5px; }
        .hero-content h2 { font-size: 1.2em; font-weight: normal; margin-bottom: 30px; }
        
        /* Statistik Sekolah Grid */
        .stats-grid {
            display: flex; gap: 20px; background-color: transparent; padding: 0; margin-bottom: 30px;
        }

        .stat-item {
            padding: 15px; text-align: left; color: var(--text-light);
            background-color: var(--stat-bg-transparent); border-radius: 5px; flex: 1;
            text-shadow: 1px 1px 2px var(--text-shadow-color); 
        }

        .stat-item .number { font-size: 1.8em; font-weight: bold; margin-bottom: 5px; }
        .stat-item .label { font-size: 0.9em; }

        /* --- Social Media Section (Menggantikan bottom-nav) --- */
        .social-media-section {
            display: flex;
            flex-direction: column;
            align-items: center;
            text-align: center;
            background-color: var(--bg-light); 
            padding: 40px 5%;
        }
        
        .social-media-section h3 {
            font-size: 1.5em;
            color: var(--secondary-color);
            margin-bottom: 20px;
        }

        .social-links {
            display: flex;
            gap: 30px;
        }

        .social-link {
            text-decoration: none;
            color: var(--secondary-color);
            transition: transform 0.3s ease-in-out;
        }

        .social-link i {
            font-size: 3.5em; 
            color: var(--primary-color);
            transition: color 0.3s ease-in-out; 
        }
        
        .social-link:hover i {
            color: var(--secondary-color); 
            transform: scale(1.1); 
        }

        /* --- Placeholder Footer --- */
        .placeholder-footer {
            text-align: center;
            padding: 20px;
            background-color: var(--secondary-color);
            color: #c8e6c9;
            font-size: 0.8em;
        }

        /* Penyesuaian Responsif */
        @media (max-width: 900px) {
            /* ... (CSS Responsif tidak berubah) ... */
        }
        /* --- INTERNAL CSS BERAKHIR DI SINI --- */
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
            
            
            <div class="dropdown">
                <a class="dropdown-toggle" id="ekskul">Ekskul <i class="fas fa-caret-down"></i></a>
                <div class="dropdown-content">
                    <?php foreach ($daftar_ekskul as $ekskul): ?>
                        <a href="<?php echo htmlspecialchars($ekskul['file_name']); ?>">
                            <?php echo htmlspecialchars($ekskul['nama_ekskul']); ?>
                        </a>
                    <?php endforeach; ?>
                </div>
            </div>
            <a href="#">kontak</a>
            
        </nav>
        <div class="nav-actions">
            <a href="#" class="btn-kontak">PENDAFTAAN SISWA/SISWI</a>
        </div>
    </header>
    
    <main class="hero-section">
        <div class="hero-content">
            <p class="welcome-text">Selamat Datang di Website</p>
            <h1>SD ISLAM ASSA'ADAH</h1>
            <h2>JAKARTA TIMUR TANJUNG LENGKONG</h2>
        </div>
        
        <div class="stats-grid">
            <div class="stat-item">
                <div class="number">6</div>
                <div class="label">Jumlah Guru</div>
            </div>
            <div class="stat-item">
                <div class="number">6</div>
                <div class="label">Jumlah Siswa</div>
            </div>
            <div class="stat-item">
                <div class="number">6</div>
                <div class="label">Komite Sekolah</div>
            </div>
            <div class="stat-item">
                <div class="number">4</div>
                <div class="label">Total Pegawai</div>
            </div>
        </div>
    </main>
    
    <section class="social-media-section">
        <h3>Ikuti Kami di Sosial Media</h3>
        <div class="social-links">
            <a href="#" class="social-link" title="Instagram SD Assa'adah">
                <i class="fab fa-instagram"></i>
            </a>
            <a href="#" class="social-link" title="Facebook SD Assa'adah">
                <i class="fab fa-facebook-f"></i>
            </a>
            <a href="#" class="social-link" title="YouTube Channel SD Assa'adah">
                <i class="fab fa-youtube"></i>
            </a>
        </div>
    </section>

    <footer class="placeholder-footer">
        <p>&copy; 2025 SD ISLAM ASSA'ADAH. Hak Cipta Dilindungi.</p>
    </footer>
    
</body>
</html>