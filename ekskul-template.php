<?php
// FILE: ekskul-template.php

// Ambil data Ekskul berdasarkan ID/Nama unik yang disupply oleh file pemanggil
$ekskul_id = $ekskul_id ?? 0;
$ekskul_data = null;

if ($ekskul_id > 0) {
    $stmt = $conn->prepare("SELECT * FROM ekstrakurikuler WHERE id = ?");
    $stmt->bind_param("i", $ekskul_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $ekskul_data = $result->fetch_assoc();
    $stmt->close();
}

// Jika data tidak ditemukan, gunakan data placeholder atau redirect
if (!$ekskul_data) {
    $ekskul_data = [
        'nama_ekskul' => $page_title ?? 'Ekstrakurikuler',
        'deskripsi' => 'Konten untuk ekstrakurikuler ini belum tersedia di database.',
        'jadwal' => 'Belum ditentukan',
        'pembina' => 'Belum ditentukan',
        'kelompok' => 'Belum ditentukan',
        'prestasi' => 'Belum ada prestasi',
        'gambar_kegiatan' => null,
        'materi_utama' => "Tidak ada materi khusus\nSilakan hubungi Pembina"
    ];
}

$page_title = $ekskul_data['nama_ekskul'];
$materi_array = explode("\n", $ekskul_data['materi_utama']);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($page_title); ?> - SD ISLAM ASSA'ADAH</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css"> 
    <style>
        /* --- CSS Global & Navbar (Konsisten) --- */
        * { margin: 0; padding: 0; box-sizing: border-box; font-family: sans-serif; }
        :root {
            --primary-color: #4CAF50; --secondary-color: #1B5E20; --text-light: #ffffff;
            --text-dark: #333333; --bg-light: #f1f8e9; 
        }

        /* STICKY FOOTER */
        html, body { height: 100%; }
        body { 
            background-color: var(--bg-light); color: var(--text-dark); 
            display: flex; flex-direction: column; 
        }
        main { flex: 1; }
        
        /* --- Navbar Styles --- */
        .navbar { 
            display: flex; justify-content: space-between; align-items: center; padding: 15px 5%;
            background-color: var(--text-light); box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            position: relative; z-index: 100; 
        }
        .logo { font-size: 1.2em; font-weight: bold; color: var(--secondary-color); }
        .nav-menu a, .dropdown-toggle {
            text-decoration: none; color: var(--text-dark); margin-right: 20px;
            padding: 5px 0; font-size: 0.9em; transition: color 0.3s ease-in-out; display: inline-block; 
        }
        .nav-menu a:hover, .dropdown:hover .dropdown-toggle { color: var(--primary-color) !important; }
        .nav-actions a {
            text-decoration: none; padding: 8px 15px; margin-left: 10px; border-radius: 5px;
            font-size: 0.9em; transition: background-color 0.3s ease-in-out, color 0.3s ease-in-out, border-color 0.3s ease-in-out;
        }
        .btn-kontak { background-color: var(--primary-color); color: var(--text-light); border: 1px solid var(--primary-color); }
        .btn-kontak:hover { background-color: var(--text-light); color: var(--primary-color); border: 1px solid var(--primary-color); }
        .dropdown { position: relative; display: inline-block; cursor: pointer; }
        .dropdown-content {
            display: none; position: absolute; background-color: var(--text-light); min-width: 160px;
            box-shadow: 0px 8px 16px 0px rgba(0,0,0,0.2); z-index: 101; top: 100%; left: 0; padding: 0; margin-top: 0; 
        }
        .dropdown:hover .dropdown-content { display: block; }
        .dropdown-content a {
            color: black; padding: 12px 16px; text-decoration: none; display: block; margin-right: 0; font-size: 0.9em;
            transition: background-color 0.3s ease;
        }
        .dropdown-content a:hover { background-color: #e8f5e9; }
        
        /* Highlight menu aktif (Disesuaikan berdasarkan file yang memanggil) */
        .dropdown .dropdown-toggle[id="ekskul"] { color: var(--primary-color) !important; font-weight: bold; }
        .dropdown-content a[href="<?php echo basename($_SERVER['PHP_SELF']); ?>"] { background-color: #e8f5e9; font-weight: bold; }


        /* --- KONTEN EKSTRAKURIKULER SESUAI LAYOUT PRAMUKA --- */
        .main-content {
            padding: 40px 5%;
            max-width: 1200px;
            margin: 0 auto;
        }
        .ekskul-title {
            font-size: 2.5em;
            color: var(--secondary-color);
            border-bottom: 3px solid var(--primary-color);
            padding-bottom: 10px;
            margin-bottom: 20px;
        }
        .ekskul-subtitle {
            font-size: 1.1em;
            color: #666;
            margin-top: -10px;
            margin-bottom: 30px;
        }

        .ekskul-layout {
            display: flex;
            gap: 30px;
        }
        .ekskul-content {
            flex: 3; /* Ambil 3/4 ruang */
        }
        .ekskul-sidebar {
            flex: 1; /* Ambil 1/4 ruang */
            background-color: #e8f5e9;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
            height: fit-content;
        }

        /* Konten */
        .materi-section h2, .galeri-section h2 {
            font-size: 1.8em;
            color: var(--secondary-color);
            margin-top: 30px;
            margin-bottom: 15px;
            border-bottom: 1px dashed #ccc;
            padding-bottom: 5px;
        }
        .materi-list {
            list-style: none;
            padding-left: 0;
        }
        .materi-list li {
            padding: 8px 0;
            border-bottom: 1px dotted #eee;
            font-size: 1em;
            color: var(--text-dark);
        }
        .materi-list li:last-child {
            border-bottom: none;
        }

        /* Sidebar Info */
        .sidebar-header {
            font-size: 1.4em;
            font-weight: bold;
            color: var(--secondary-color);
            margin-bottom: 15px;
        }
        .info-item {
            margin-bottom: 15px;
            font-size: 0.95em;
        }
        .info-item strong {
            display: block;
            margin-bottom: 3px;
            color: #333;
            font-size: 1em;
        }
        .info-item i {
            width: 20px;
            color: var(--primary-color);
        }
        .sidebar-button {
            display: block;
            text-align: center;
            padding: 10px;
            background-color: var(--primary-color);
            color: var(--text-light);
            text-decoration: none;
            border-radius: 5px;
            margin-top: 20px;
            transition: background-color 0.3s;
        }
        .sidebar-button:hover {
            background-color: var(--secondary-color);
        }
        
        .ekskul-img {
            width: 100%;
            max-height: 400px;
            object-fit: cover;
            border-radius: 8px;
            margin-bottom: 20px;
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
        }

        /* Responsif */
        @media (max-width: 768px) {
            .ekskul-layout {
                flex-direction: column;
            }
            .ekskul-sidebar {
                flex: none;
                width: 100%;
                order: -1; /* Pindahkan sidebar ke atas pada ponsel */
            }
            .ekskul-content {
                flex: none;
                width: 100%;
            }
        }

        /* Placeholder Footer */
        .placeholder-footer {
            text-align: center; padding: 20px; background-color: var(--secondary-color);
            color: #c8e6c9; font-size: 0.8em; 
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
            
            <div class="dropdown">
                <a href="#" class="dropdown-toggle" id="ekskul">Ekskul <i class="fas fa-caret-down"></i></a>
                <div class="dropdown-content">
                    <a href="pramuka.php">Pramuka</a> 
                    <a href="seni-tari.php">Seni Tari</a>
                    <a href="futsal.php">Futsal</a>
                    </div>
            </div>
            <a href="#">kontak</a>
            
        </nav>
        <div class="nav-actions">
            <a href="pendaftaransiswasiswi.php" class="btn-kontak">PENDAFTAAN SISWA/SISWI</a>
        </div>
    </header>
    
    <main class="main-content">
        <h1 class="ekskul-title"><?php echo htmlspecialchars($ekskul_data['nama_ekskul']); ?></h1>
        <p class="ekskul-subtitle">Kegiatan wajib yang membangun karakter, kedisiplinan, dan cinta alam.</p>
        
        <div class="ekskul-layout">
            
            <div class="ekskul-content">
                <?php if ($ekskul_data['gambar_kegiatan']): ?>
                    <img src="<?php echo htmlspecialchars($ekskul_data['gambar_kegiatan']); ?>" alt="Gambar Kegiatan <?php echo htmlspecialchars($ekskul_data['nama_ekskul']); ?>" class="ekskul-img">
                <?php endif; ?>
                
                <p><?php echo nl2br(htmlspecialchars($ekskul_data['deskripsi'])); ?></p>
                
                <div class="materi-section">
                    <h2>Materi Pembelajaran Utama</h2>
                    <ul class="materi-list">
                        <?php foreach ($materi_array as $materi): ?>
                            <?php if (trim($materi)): ?>
                                <li><i class="fas fa-check-circle" style="color: var(--primary-color); margin-right: 8px;"></i> <?php echo htmlspecialchars(trim($materi)); ?></li>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    </ul>
                </div>
                
                <div class="galeri-section">
                    <h2>Galeri Kegiatan</h2>
                    <p>Contoh Gambar:  </p>
                </div>
            </div>
            
            <div class="ekskul-sidebar">
                <div class="sidebar-header">Informasi Kegiatan</div>
                
                <div class="info-item">
                    <strong><i class="fas fa-calendar-alt"></i> Jadwal</strong>
                    <?php echo htmlspecialchars($ekskul_data['jadwal']); ?>
                </div>
                
                <div class="info-item">
                    <strong><i class="fas fa-user-tie"></i> Pembina</strong>
                    <?php echo htmlspecialchars($ekskul_data['pembina']); ?>
                </div>
                
                <div class="info-item">
                    <strong><i class="fas fa-users"></i> Kelompok</strong>
                    <?php echo htmlspecialchars($ekskul_data['kelompok']); ?>
                </div>
                
                <div class="info-item">
                    <strong><i class="fas fa-trophy"></i> Prestasi</strong>
                    <?php echo htmlspecialchars($ekskul_data['prestasi']); ?>
                </div>
                
                <a href="#" class="sidebar-button">Daftar Sekarang</a>
            </div>
        </div>
    </main>
    
    <footer class="placeholder-footer">
        <p>&copy; 2025 SD ISLAM ASSA'ADAH. Hak Cipta Dilindungi.</p>
    </footer>

</body>
</html>
<?php 
if (isset($conn)) $conn->close();
?>