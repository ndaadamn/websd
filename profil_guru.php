<?php
// ==============================================================================
// FILE: profil_guru.php (Updated)
// DESKRIPSI: Halaman Profil Guru (Data diambil dari DB)
// ==============================================================================
include 'koneksi.php'; // Path koneksi harus ada

// ----------------------------------------------------------------------
// 1. LOGIKA AMBIL DATA GURU DAN BANNER
// ----------------------------------------------------------------------
$daftar_guru = [];
$banner_guru_url = "img/sekolah.jpg"; // Default fallback image

if (isset($conn) && $conn) {
    // Ambil daftar guru
    $result_guru = $conn->query("SELECT id, nama_guru, jabatan, foto_url FROM profil_guru ORDER BY id ASC");
    if ($result_guru) {
        while($row = $result_guru->fetch_assoc()) {
            $daftar_guru[] = $row;
        }
    }
    
    // Ambil banner (asumsi disimpan di row ID 1)
    $stmt_banner = $conn->prepare("SELECT foto_banner_guru FROM profil_guru WHERE id = 1");
    $stmt_banner->execute();
    $result_banner = $stmt_banner->get_result();
    $banner_data = $result_banner->fetch_assoc();
    $stmt_banner->close();
    
    if ($banner_data && !empty($banner_data['foto_banner_guru'])) {
        // Path disimpan relatif ke root website
        $banner_guru_url = $banner_data['foto_banner_guru'];
    }
}

// ----------------------------------------------------------------------
// 2. LOGIKA AMBIL DATA EKSTRAKURIKULER UNTUK NAVBAR
// ----------------------------------------------------------------------
$daftar_ekskul = []; 
$query_ekskul = "SELECT nama_ekskul, file_name FROM ekstrakurikuler ORDER BY nama_ekskul ASC";

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
    <title>Profil Guru - SD ISLAM ASSA'ADAH</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css"> 
    
    <style>
        /* CSS Global (diulang agar file berdiri sendiri) */
        * { margin: 0; padding: 0; box-sizing: border-box; font-family: sans-serif; }
        :root {
            --primary-color: #4CAF50; --secondary-color: #1B5E20; --text-light: #ffffff;
            --text-dark: #333333; --bg-light: #f1f8e9; 
        }
        body { background-color: var(--bg-light); color: var(--text-dark); }
        
        /* --- Navbar (Header) --- */
        .navbar { 
            display: flex; justify-content: space-between; align-items: center; padding: 15px 5%;
            background-color: var(--text-light); box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            position: relative; z-index: 100; 
        }
        .logo { font-size: 1.2em; font-weight: bold; color: var(--secondary-color); }
        .nav-menu { display: flex; align-items: center; gap: 20px; flex-grow: 1; margin-left: 30px; }
        .nav-menu a {
            text-decoration: none; color: var(--text-dark); padding: 5px 0; font-size: 0.9em;
            transition: color 0.3s ease-in-out; 
        }
        .nav-menu a:hover, .dropdown:hover .dropdown-toggle {
            color: var(--primary-color) !important; 
        }
        .nav-actions a {
            text-decoration: none; padding: 8px 15px; margin-left: 10px; border-radius: 5px; font-size: 0.9em;
            background-color: var(--primary-color); color: var(--text-light); 
        }
        .nav-actions a:hover { background-color: var(--secondary-color); }

        /* Dropdown CSS */
        .dropdown { position: relative; display: inline-block; cursor: pointer; }
        /* Style untuk Profil yang aktif */
        .profil-active .dropdown-toggle {
             color: var(--primary-color) !important; font-weight: bold;
        }
        .dropdown-content {
            display: none; position: absolute; background-color: var(--text-light); min-width: 160px;
            box-shadow: 0px 8px 16px 0px rgba(0,0,0,0.2); z-index: 101; top: 100%; left: 0; padding: 0; margin-top: 0; 
        }
        .dropdown:hover .dropdown-content { display: block; }
        .dropdown-content a {
            color: black; padding: 12px 16px; text-decoration: none; display: block; margin-right: 0; font-size: 0.9em;
        }
        .dropdown-content a:hover { background-color: #e8f5e9; }
        
        /* --- KONTEN PROFIL GURU --- */
        .main-content {
            padding: 40px 5%; max-width: 1200px; margin: 0 auto;
            min-height: calc(100vh - 75px - 60px); 
        }

        .page-header h1 {
            font-size: 2.2em; color: var(--secondary-color); border-bottom: 3px solid var(--primary-color);
            padding-bottom: 10px; margin-bottom: 30px;
        }
        
        /* Layout Gambar Besar (Foto Bersama Guru) - Dibuat dinamis via style inline */
        .guru-banner {
            width: 100%; height: 350px; 
            background-size: cover; background-position: center;
            border-radius: 8px; box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
            margin-bottom: 40px; display: flex; align-items: center; justify-content: center;
        }

        .guru-banner h2 {
            color: var(--text-light); background-color: rgba(0, 0, 0, 0.5);
            padding: 10px 20px; border-radius: 5px; font-size: 2em;
        }

        /* Daftar Guru Grid */
        .guru-list {
            display: grid; 
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 30px; 
            justify-content: center;
            margin-top: 20px;
        }

        .guru-item {
            background-color: var(--text-light); 
            border-left: 4px solid var(--primary-color); 
            border-radius: 5px;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.05);
            overflow: hidden;
            text-align: center;
        }
        
        .guru-photo {
            width: 100%;
            height: 250px; 
            overflow: hidden;
            margin-bottom: 10px;
        }
        .guru-photo img {
            width: 100%;
            height: 100%;
            object-fit: cover; 
            transition: transform 0.3s ease;
        }
        .guru-item:hover .guru-photo img {
             transform: scale(1.05);
        }

        .guru-info {
            padding: 15px 15px 20px;
        }
        .guru-info h3 { font-size: 1.2em; color: var(--secondary-color); margin-bottom: 5px; }
        .guru-info p { font-size: 0.9em; color: #666; font-style: italic; }

        /* --- Placeholder Footer --- */
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
            <div class="dropdown profil-active"> 
                <a href="#" class="dropdown-toggle">Profil <i class="fas fa-caret-down"></i></a>
                <div class="dropdown-content">
                    <a href="profil_sekolah.php">Profil Sekolah</a> 
                    <a href="profil_guru.php" style="font-weight: bold; background-color: #e8f5e9;">Profil Guru</a> 
                </div>
            </div>
            
            <a href="galeri.php">Galeri</a>
            
            <div class="dropdown" id="menu-ekskul">
                <a href="#" class="dropdown-toggle">Ekskul <i class="fas fa-caret-down"></i></a>
                <div class="dropdown-content">
                    <?php if (!empty($daftar_ekskul)): ?>
                        <?php foreach ($daftar_ekskul as $ekskul_nav): ?>
                            <a href="ekskul_detail.php?slug=<?php echo htmlspecialchars($ekskul_nav['file_name']); ?>">
                                <?php echo htmlspecialchars($ekskul_nav['nama_ekskul']); ?>
                            </a>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <a href="#">Belum Ada Ekskul</a>
                    <?php endif; ?>
                </div>
            </div>
            <a href="kontak.php">Kontak</a> 
            
        </nav>
        <div class="nav-actions">
            <a href="pendaftaransiswasiswi.php" class="btn-kontak">PENDAFTAAN SISWA/SISWI</a>
        </div>
    </header>
    
    <main class="main-content">
        <div class="page-header">
            <h1>Profil Tenaga Pendidik dan Kependidikan</h1>
        </div>

        <div class="guru-banner" style="background-image: url('<?php echo htmlspecialchars($banner_guru_url); ?>');">
            
        </div>

        <div class="guru-list">
            <?php if (!empty($daftar_guru)): ?>
                <?php foreach ($daftar_guru as $guru): ?>
                    <div class="guru-item">
                        <div class="guru-photo">
                            <?php 
                            // Pastikan foto_url tidak kosong dan file ada, jika tidak, gunakan placeholder
                            $foto_path = !empty($guru['foto_url']) ? htmlspecialchars($guru['foto_url']) : 'img/default_guru.png'; 
                            ?>
                            <img src="<?php echo $foto_path; ?>" alt="Foto <?php echo htmlspecialchars($guru['nama_guru']); ?>">
                        </div>
                        <div class="guru-info">
                            <h3><?php echo htmlspecialchars($guru['nama_guru']); ?></h3>
                            <p><?php echo htmlspecialchars($guru['jabatan']); ?></p>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                 <p style="width: 100%; text-align: center;">Data guru belum tersedia. Silakan isi melalui halaman admin.</p>
            <?php endif; ?>
        </div>
        
    </main>
    
    <footer class="placeholder-footer">
        <p>&copy; <?php echo date("Y"); ?> SD ISLAM ASSA'ADAH. Hak Cipta Dilindungi.</p>
    </footer>

</body>
</html>
<?php 
if (isset($conn)) $conn->close(); 
?>