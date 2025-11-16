<?php
// FILE: index.php
// Pastikan file koneksi.php sudah di-include
include 'koneksi.php';

// ----------------------------------------------------------------------
// LOGIKA PENGAMBILAN DATA
// ----------------------------------------------------------------------

// 1. Ambil Data Statistik (Guru, Siswa, Pegawai)
$stats = [
    'jumlah_guru' => 0,
    'jumlah_siswa' => 0,
    'total_pegawai' => 0
    // Kolom 'komite_sekolah' telah dihapus
];
// Query diubah: Hanya mengambil 3 kolom yang tersisa
$query_stats = "SELECT jumlah_guru, jumlah_siswa, total_pegawai FROM pengaturan_index WHERE id = 1";
if (isset($conn) && $conn) {
    $result_stats = $conn->query($query_stats);
    if ($result_stats && $result_stats->num_rows > 0) {
        $stats = $result_stats->fetch_assoc();
    }
}

// 2. Ambil Kalender Akademik (Agenda)
$kalender = [];
// Ambil agenda yang akan datang (dari hari ini dan seterusnya), batasi 5 saja
$query_kalender = "SELECT tanggal, acara FROM kalender_akademik WHERE tanggal >= CURDATE() ORDER BY tanggal ASC LIMIT 5";
if (isset($conn) && $conn) {
    $result_kalender = $conn->query($query_kalender);
    if ($result_kalender) {
        while($row = $result_kalender->fetch_assoc()) {
            $kalender[] = $row;
        }
    }
}

// 3. Ambil Data Ekskul untuk Navbar (Logika Lama)
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
        
        /* EFEK HOVER ANIMASI */
        .nav-menu a:hover, .dropdown:hover .dropdown-toggle {
            color: var(--primary-color) !important; 
        }

        .nav-actions a {
            text-decoration: none;
            padding: 8px 15px;
            margin-left: 10px;
            border-radius: 5px;
            font-size: 0.9em;
            transition: background-color 0.3s ease-in-out, color 0.3s ease-in-out;
        }

        .btn-kontak {
            background-color: var(--primary-color);
            color: var(--text-light); 
        }

        .btn-kontak:hover {
            background-color: var(--secondary-color); 
        }
        
        /* Dropdown CSS */
        .dropdown {
            position: relative;
            display: inline-block;
            cursor: pointer; 
            padding: 0; 
            margin-right: 0; 
        }
        
        .dropdown-toggle {
            text-decoration: none;
            color: var(--text-dark);
            margin-right: 0; 
            padding: 5px 0; 
            font-size: 0.9em;
            transition: color 0.3s ease-in-out; 
            display: inline-block; 
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
            margin-top: 0; 
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
            transition: background-color 0.3s ease; 
        }

        .dropdown-content a:hover {
            background-color: #e8f5e9; 
        }
        
        /* --- Hero Section dan CSS Statistik --- */
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
            
            /* (1) INITIAL STATE untuk fade-in */
            opacity: 0;
            transition: opacity 2s ease-in-out;
        }
        /* CLASS AKTIF UNTUK ANIMASI HERO CONTENT */
        .hero-content.active {
            opacity: 1;
        }

        .welcome-text {
            background-color: rgba(255, 255, 255, 0.2); display: inline-block;
            padding: 5px 15px; margin-bottom: 20px; border-radius: 5px; font-size: 0.9em;
        }

        .hero-content h1 { font-size: 2.2em; margin-bottom: 5px; }
        .hero-content h2 { font-size: 1.2em; font-weight: normal; margin-bottom: 30px; }
        
        /* Statistik Sekolah Grid */
        .stats-grid {
            display: grid; 
            grid-template-columns: repeat(3, 1fr); 
            gap: 20px; 
            background-color: transparent; padding: 0; margin-bottom: 30px;
        }

        .stat-item {
            padding: 15px; text-align: left; color: var(--text-light);
            background-color: var(--stat-bg-transparent); border-radius: 5px; flex: 1;
            text-shadow: 1px 1px 2px var(--text-shadow-color); 
        }

        .stat-item .number { font-size: 1.8em; font-weight: bold; margin-bottom: 5px; }
        .stat-item .label { font-size: 0.9em; }

        /* --- Kalender Akademik Section (Fade-In dari Bawah) --- */
        .kalender-akademik-section {
            padding: 40px 5%;
            background-color: white; 
            border-bottom: 1px solid #ddd;
            overflow: hidden; 
            
            /* INITIAL STATE: Transparan dan sedikit di bawah posisi akhir */
            opacity: 0; 
            transform: translateY(50px); /* Geser 50px ke bawah */
            transition: transform 1s ease-out, opacity 1s ease-out; /* Durasi 1 detik */
        }
        /* CLASS AKTIF UNTUK ANIMASI KALENDER */
        .kalender-akademik-section.active { 
            opacity: 1; /* Muncul penuh */
            transform: translateY(0); /* Kembali ke posisi aslinya */
        }
        
        .kalender-akademik-section h3 {
            font-size: 1.8em;
            color: var(--secondary-color);
            margin-bottom: 25px;
            text-align: center;
        }
        
        .agenda-list {
            max-width: 800px;
            margin: 0 auto;
            list-style: none;
            padding: 0;
        }
        
        .agenda-item {
            display: flex;
            align-items: center;
            margin-bottom: 15px;
            padding: 15px;
            border-left: 4px solid var(--primary-color);
            background-color: #f7f7f7;
            border-radius: 4px;
        }
        
        .agenda-item .date-box {
            background-color: var(--secondary-color);
            color: var(--text-light);
            padding: 10px;
            border-radius: 4px;
            text-align: center;
            margin-right: 15px;
            min-width: 70px;
            font-size: 0.9em;
        }
        
        .agenda-item .date-box .day {
            font-size: 1.2em;
            font-weight: bold;
            display: block;
        }
        
        .agenda-item .event-title {
            font-weight: bold;
            color: var(--text-dark);
        }

        /* --- Social Media Section --- */
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

        /* --- Footer (Copyright) --- */
        .placeholder-footer {
            text-align: center;
            padding: 20px;
            background-color: var(--secondary-color);
            color: #c8e6c9;
            font-size: 0.8em;
            width: 100%; 
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
            
            <div class="dropdown" id="menu-ekskul">
                <a href="#" class="dropdown-toggle">Ekskul <i class="fas fa-caret-down"></i></a>
                <div class="dropdown-content">
                    <?php foreach ($daftar_ekskul as $ekskul): ?>
                        <a href="ekskul_detail.php?slug=<?php echo htmlspecialchars($ekskul['file_name']); ?>">
                            <?php echo htmlspecialchars($ekskul['nama_ekskul']); ?>
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
    
    <main class="hero-section">
        <div class="hero-content" id="hero-content">
            <p class="welcome-text">Selamat Datang di Website</p>
            <h1>SD ISLAM ASSA'ADAH</h1>
            <h2>JAKARTA TIMUR TANJUNG LENGKONG</h2>
        </div>
        
        <div class="stats-grid">
            <div class="stat-item">
                <div class="number" data-target="<?php echo htmlspecialchars($stats['jumlah_guru']); ?>" id="counter-guru">0</div>
                <div class="label">Jumlah Guru</div>
            </div>
            <div class="stat-item">
                <div class="number" data-target="<?php echo htmlspecialchars($stats['jumlah_siswa']); ?>" id="counter-siswa">0</div>
                <div class="label">Jumlah Siswa</div>
            </div>
            <div class="stat-item">
                <div class="number" data-target="<?php echo htmlspecialchars($stats['total_pegawai']); ?>" id="counter-pegawai">0</div>
                <div class="label">Total Pegawai</div>
            </div>
        </div>
    </main>
    
    <section class="kalender-akademik-section" id="kalender-akademik">
        <h3>📅 Kalender Akademik & Agenda Sekolah</h3>
        <?php if (!empty($kalender)): ?>
            <ul class="agenda-list">
                <?php 
                // Karena IntlDateFormatter sudah dipastikan bekerja (berdasarkan informasi Anda), 
                // kita biarkan kode ini berjalan tanpa fallback yang kompleks.
                $formatter = new IntlDateFormatter('id_ID', IntlDateFormatter::LONG, IntlDateFormatter::NONE);
                $formatter->setPattern("d MMMM yyyy");
                
                $hari_indonesia = [
                    'Sun' => 'Min', 'Mon' => 'Sen', 'Tue' => 'Sel', 
                    'Wed' => 'Rab', 'Thu' => 'Kam', 'Fri' => 'Jum', 'Sat' => 'Sab'
                ];
                
                foreach ($kalender as $agenda): 
                    $timestamp = strtotime($agenda['tanggal']);
                    $hari = date('D', $timestamp);
                ?>
                <li class="agenda-item">
                    <div class="date-box">
                        <span class="day"><?php echo htmlspecialchars($hari_indonesia[$hari] ?? date('D', $timestamp)); ?></span>
                        <?php echo date('d M', $timestamp); ?>
                    </div>
                    <span class="event-title"><?php echo htmlspecialchars($agenda['acara']); ?></span>
                </li>
                <?php endforeach; ?>
            </ul>
        <?php else: ?>
            <p style="text-align: center; color: #666;">Belum ada agenda akademik yang akan datang.</p>
        <?php endif; ?>
    </section>
    
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

    <script>
        document.addEventListener("DOMContentLoaded", function() {
            
            // --- 1. Animasi Fade-In Hero Content ---
            const heroContent = document.getElementById('hero-content');
            
            // Menambahkan kelas 'active' setelah waktu singkat (untuk memulai transisi CSS)
            setTimeout(() => {
                heroContent.classList.add('active');
            }, 100); 


            // --- 2. Animasi Counter Statistik ---
            const counters = document.querySelectorAll('.number');

            const animateCounter = (el) => {
                const target = parseInt(el.getAttribute('data-target'));
                let current = 0;
                const duration = 1500; // Durasi animasi dalam milidetik (1.5 detik)
                // Hitung kenaikan per frame (sekitar 60fps)
                const increment = target / (duration / 16); 

                const updateCounter = () => {
                    current += increment;
                    if (current < target) {
                        // Gunakan Math.ceil agar angka naik secara bertahap dan terlihat utuh
                        el.innerText = Math.ceil(current);
                        requestAnimationFrame(updateCounter);
                    } else {
                        el.innerText = target;
                    }
                };
                requestAnimationFrame(updateCounter);
            };

            // Mulai counter setelah hero content muncul/setelah jeda singkat
            setTimeout(() => {
                counters.forEach(animateCounter);
            }, 500);


            // --- 3. Animasi Fade-In dari Bawah Kalender Akademik (Menggunakan Intersection Observer) ---
            const kalenderSection = document.getElementById('kalender-akademik');

            // Cek apakah browser mendukung Intersection Observer
            if ('IntersectionObserver' in window) {
                const kalenderObserver = new IntersectionObserver((entries, observer) => {
                    entries.forEach(entry => {
                        // Jika elemen Kalender Akademik masuk viewport (terlihat)
                        if (entry.isIntersecting) {
                            entry.target.classList.add('active');
                            // Hentikan pengamatan setelah animasi berjalan
                            observer.unobserve(entry.target);
                        }
                    });
                }, {
                    rootMargin: '0px',
                    threshold: 0.1 // Mulai animasi ketika 10% elemen terlihat
                });

                kalenderObserver.observe(kalenderSection);
            } else {
                // Fallback jika IntersectionObserver tidak didukung (langsung tampilkan)
                kalenderSection.classList.add('active');
            }
        });
    </script>
    
</body>
</html>