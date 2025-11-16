<?php
// FILE: galeri.php
include 'koneksi.php';

// ----------------------------------------------------------------------
// 1. LOGIKA AMBIL DATA EKSKUL UNTUK NAVBAR DROPDOWN
// ----------------------------------------------------------------------
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

// ----------------------------------------------------------------------
// 2. LOGIKA AMBIL DATA GALERI UNTUK KONTEN UTAMA
// ----------------------------------------------------------------------
$query = "SELECT * FROM galeri ORDER BY tanggal_upload DESC";
$result = $conn->query($query);

?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Galeri - SD ISLAM ASSA'ADAH</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css"> 
    
    <style>
        /* --- CSS Global dan Navbar (Diambil dari kode yang benar) --- */
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
            min-height: 100vh;
            display: flex;
            flex-direction: column;
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
        
        /* Menjaga link Galeri tetap aktif */
        .nav-menu a:hover, .dropdown:hover .dropdown-toggle, a.active-link {
            color: var(--primary-color) !important; 
        }

        /* Tombol Aksi PENDAFTAAN SISWA/SISWI */
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
            display: none; 
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
            display: block; 
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

        /* --- KONTEN GALERI --- */
        
        #content-wrapper {
             padding: 40px 5%; 
             flex: 1; 
        }
        
        .page-header {
            max-width: 1200px; margin: 0 auto;
            border-bottom: 3px solid var(--primary-color); 
            margin-bottom: 30px; 
        }
        .page-header h1 {
            font-size: 2.2em; 
            color: var(--secondary-color); 
            padding-bottom: 10px;
        }

        /* Layout 3 Kolom - INI ADALAH TATA LETAK UTAMA */
        .galeri-grid {
            max-width: 1200px; margin: 0 auto;
            display: grid;
            /* Memastikan minimal 300px per kolom, menyesuaikan hingga 3 kolom */
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); 
            gap: 20px;
        }
        
        /* Kartu Galeri */
        .galeri-card {
            display: block; 
            background-color: var(--text-light); 
            border-radius: 8px; 
            overflow: hidden;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
            text-decoration: none; 
            color: inherit; 
            transition: transform 0.3s, box-shadow 0.3s;
        }
        .galeri-card:hover {
            transform: translateY(-5px); 
            box-shadow: 0 8px 15px rgba(0, 0, 0, 0.15);
        }

        .galeri-media {
            width: 100%; height: 200px; object-fit: cover; background-color: #ccc;
        }

        .galeri-content {
            padding: 15px;
        }

        .galeri-content h3 {
            font-size: 1.2em; color: var(--primary-color); margin-bottom: 5px;
        }

        .galeri-content p {
            font-size: 0.9em; color: #666; margin-top: 10px;
        }

        /* --- Footer (Copyright) --- */
        .placeholder-footer {
            text-align: center;
            padding: 20px;
            background-color: var(--secondary-color);
            color: #c8e6c9;
            font-size: 0.8em;
            width: 100%; 
            margin-top: 40px;
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
        
        <a href="galeri.php" class="active-link">Galeri</a>
        
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

<div id="content-wrapper">
    <div class="page-header">
        <h1>Galeri Kegiatan Sekolah</h1>
    </div>

    <div class="galeri-grid">
        <?php 
        if ($result->num_rows > 0): 
            while($row = $result->fetch_assoc()):
        ?>
        
        <a href="detail_galeri.php?id=<?php echo $row['id']; ?>" class="galeri-card">
            <?php 
            // Cek ekstensi file untuk menentukan apakah itu gambar atau video
            $file_extension = pathinfo($row['file_path'], PATHINFO_EXTENSION);
            $is_video = in_array(strtolower($file_extension), ['mp4', 'webm', 'ogg']);
            ?>

            <?php if (!$is_video): ?>
                <img src="<?php echo htmlspecialchars($row['file_path']); ?>" alt="<?php echo htmlspecialchars($row['title']); ?>" class="galeri-media">
            <?php else: ?>
                <video controls class="galeri-media">
                    <source src="<?php echo htmlspecialchars($row['file_path']); ?>" type="video/<?php echo $file_extension; ?>">
                    Browser Anda tidak mendukung tag video.
                </video>
            <?php endif; ?>

            <div class="galeri-content">
                <h3><?php echo htmlspecialchars($row['title']); ?></h3>
                <p><?php echo substr(htmlspecialchars($row['deskripsi']), 0, 80) . '...'; ?></p>
                <small style="color: #999; display: block; margin-top: 10px;"><i class="fas fa-calendar-alt"></i> <?php echo date("d M Y", strtotime($row['tanggal_upload'])); ?></small>
            </div>
        </a>
        
        <?php 
            endwhile; 
        else: 
        ?>
        <p style="grid-column: 1 / -1; text-align: center; color: #666; padding: 20px;">Belum ada konten galeri yang diunggah.</p>
        <?php endif; ?>
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