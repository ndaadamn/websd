<?php
// ==============================================================================
// FILE: profil_sekolah.php
// DESKRIPSI: Halaman Profil Sekolah (Data diambil dari DB)
// ==============================================================================
include 'koneksi.php'; // Path koneksi

// ----------------------------------------------------------------------
// 1. LOGIKA AMBIL DATA PROFIL SEKOLAH
// ----------------------------------------------------------------------
$profil_sekolah_data = null;
if (isset($conn) && $conn) {
    $stmt = $conn->prepare("SELECT visi, misi, sejarah_singkat FROM profil_sekolah WHERE id = 1"); 
    $stmt->execute();
    $result = $stmt->get_result();
    $profil_sekolah_data = $result->fetch_assoc();
    $stmt->close();
}

// ----------------------------------------------------------------------
// 2. LOGIKA AMBIL DATA EKSTRAKURIKULER UNTUK NAVBAR (MANUAL)
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
    <title>Profil Sekolah - SD ISLAM ASSA'ADAH</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css"> 
    
    <style>
        /* CSS Global */
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
        /* Style umum saat hover */
        .nav-menu a:hover, .dropdown:hover .dropdown-toggle {
            color: var(--primary-color) !important; 
        }
        /* Style untuk tombol aksi */
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
        
        /* --- KONTEN PROFIL SEKOLAH --- */
        .main-content {
            padding: 40px 5%; max-width: 1000px; margin: 0 auto;
            min-height: calc(100vh - 75px - 60px); 
        }

        .page-header h1 {
            font-size: 2.2em; color: var(--secondary-color); border-bottom: 3px solid var(--primary-color);
            padding-bottom: 10px; margin-bottom: 30px;
        }

        .section { margin-bottom: 40px; }
        .section h2 { font-size: 1.8em; color: var(--primary-color); margin-bottom: 15px; }
        .section p, .section ul { white-space: pre-wrap; line-height: 1.6; margin-bottom: 15px; font-size: 1.0em; }
        .section ul { list-style: none; padding-left: 20px; }
        .section ul li::before {
            content: "\f00c"; font-family: "Font Awesome 6 Free"; font-weight: 900;
            color: var(--primary-color); margin-right: 10px;
        }
        
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
            <div class="dropdown profil-active"> <a href="#" class="dropdown-toggle">Profil <i class="fas fa-caret-down"></i></a>
                <div class="dropdown-content">
                    <a href="profil_sekolah.php" style="font-weight: bold; background-color: #e8f5e9;">Profil Sekolah</a> 
                    <a href="profil_guru.php">Profil Guru</a> 
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
            <h1>Profil Sekolah</h1>
        </div>

        <?php if ($profil_sekolah_data): ?>
            
            <div class="section">
                <h2>Visi</h2>
                <p><?php echo nl2br(htmlspecialchars($profil_sekolah_data['visi'])); ?></p>
            </div>

            <div class="section">
                <h2>Misi</h2>
                <p><?php echo nl2br(htmlspecialchars($profil_sekolah_data['misi'])); ?></p>
            </div>

            <div class="section">
                <h2>Sejarah Singkat</h2>
                <p><?php echo nl2br(htmlspecialchars($profil_sekolah_data['sejarah_singkat'])); ?></p>
            </div>

        <?php else: ?>
            <div class="section">
                <p>Data profil sekolah belum tersedia. Silakan hubungi admin.</p>
            </div>
        <?php endif; ?>
    </main>
    
    <footer class="placeholder-footer">
        <p>&copy; <?php echo date("Y"); ?> SD ISLAM ASSA'ADAH. Hak Cipta Dilindungi.</p>
    </footer>

</body>
</html>
<?php 
if (isset($conn)) $conn->close(); 
?>