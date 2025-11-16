<?php
// FILE: ekskul.php
include 'koneksi.php';

// ----------------------------------------------------------------------
// 1. LOGIKA UNTUK MENGAMBIL DAFTAR EKSKUL (untuk NAVBAR)
// ----------------------------------------------------------------------
$daftar_ekskul = []; 
$query_ekskul_list = "SELECT id, nama_ekskul FROM ekstrakurikuler ORDER BY nama_ekskul ASC";
if (isset($conn) && $conn) {
    $result_ekskul_list = $conn->query($query_ekskul_list);
    if ($result_ekskul_list && $result_ekskul_list->num_rows > 0) {
        while($row = $result_ekskul_list->fetch_assoc()) {
            $daftar_ekskul[] = $row;
        }
    }
}

// ----------------------------------------------------------------------
// 2. LOGIKA UNTUK MENGAMBIL DETAIL EKSKUL (untuk KONTEN UTAMA)
// ----------------------------------------------------------------------
$ekskul_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$ekskul_data = null;

if ($ekskul_id > 0) {
    $stmt = $conn->prepare("SELECT * FROM ekstrakurikuler WHERE id = ?");
    $stmt->bind_param("i", $ekskul_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $ekskul_data = $result->fetch_assoc();
    $stmt->close();
}

// LOGIKA JIKA ID TIDAK DITEMUKAN (FALLBACK)
if (!$ekskul_data) {
    $ekskul_data = [
        'nama_ekskul' => 'Ekskul Tidak Ditemukan',
        'deskripsi' => 'Data untuk ekstrakurikuler ini belum tersedia di database atau ID tidak valid.',
        'jadwal' => 'N/A',
        'pembina' => 'N/A',
        'kelompok' => 'N/A',
        'prestasi' => 'N/A',
        'gambar_kegiatan' => null,
        'materi_utama' => "Silakan kembali ke halaman utama ekskul."
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
        /* --- CSS Global & Navbar (Diambil dari detail_galeri.php) --- */
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
        .dropdown:hover .dropdown-content { display: block; } /* KUNCI DROPDOWN */
        .dropdown-content a {
            color: black; padding: 12px 16px; text-decoration: none; display: block; margin-right: 0; font-size: 0.9em;
            transition: background-color 0.3s ease;
        }
        .dropdown-content a:hover { background-color: #e8f5e9; }
        
        /* Highlight menu aktif */
        .dropdown .dropdown-toggle[id="ekskul"] { color: var(--primary-color) !important; font-weight: bold; }
        
        /* --- KONTEN EKSTRAKURIKULER --- */
        .main-content { padding: 40px 5%; max-width: 1200px; margin: 0 auto; }
        .ekskul-title { /* ... CSS konten seperti sebelumnya ... */ }
        .ekskul-layout { display: flex; gap: 30px; }
        .ekskul-content { flex: 3; }
        .ekskul-sidebar { flex: 1; background-color: #e8f5e9; padding: 20px; border-radius: 8px; height: fit-content; }
        /* ... CSS Konten lainnya ... */
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
                    <?php foreach ($daftar_ekskul as $ekskul): ?>
                        <a href="ekskul.php?id=<?php echo $ekskul['id']; ?>">
                            <?php echo htmlspecialchars($ekskul['nama_ekskul']); ?>
                        </a>
                    <?php endforeach; ?>
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
        <p class="ekskul-subtitle">Kegiatan ekstrakurikuler SD ISLAM ASSA'ADAH.</p>
        
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
            </div>
            
            <div class="ekskul-sidebar">
                <div class="sidebar-header">Informasi Kegiatan</div>
                
                <div class="info-item">
                    <strong><i class="fas fa-calendar-alt"></i> Jadwal</strong>
                    <?php echo htmlspecialchars($ekskul_data['jadwal']); ?>
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