<?php
// FILE: kontak.php

include 'koneksi.php'; // Path koneksi

// ----------------------------------------------------------------------
// 1. LOGIKA AMBIL DATA UNTUK NAVBAR (MANUAL)
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

// ----------------------------------------------------------------------
// 2. LOGIKA AMBIL DATA KONTAK
// ----------------------------------------------------------------------
$kontak_data = null;
if (isset($conn) && $conn) {
    // Ambil semua data kontak dari tabel dengan ID=1
    $stmt = $conn->prepare("SELECT * FROM kontak WHERE id = 1"); 
    $stmt->execute();
    $result = $stmt->get_result();
    $kontak_data = $result->fetch_assoc();
    $stmt->close();
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kontak Sekolah - SD ISLAM ASSA'ADAH</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css"> 
    
    <style>
        /* --- CSS Global dan Navbar (Diambil dari index.php) --- */
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
        
        /* --- CSS Konten Kontak --- */
        #content-wrapper {
            padding: 40px 5%; 
            min-height: calc(100vh - 75px - 60px); 
        }
        
        .contact-container {
            max-width: 800px;
            margin: 0 auto;
            background: white;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }

        .contact-container h1, .contact-container h2 {
            color: var(--secondary-color);
            border-bottom: 2px solid #a5d6a7;
            padding-bottom: 10px;
            margin-bottom: 25px;
        }

        .contact-item {
            display: flex;
            align-items: center;
            padding: 15px 0;
            border-bottom: 1px solid #eee;
            color: var(--text-dark);
        }

        .contact-item i {
            font-size: 1.2em;
            width: 30px;
            color: var(--primary-color);
            margin-right: 15px;
            text-align: center;
        }

        .social-link-item {
            padding: 10px 0;
        }

        .social-link-item a {
            color: #333;
            text-decoration: none;
            display: flex;
            align-items: center;
        }
        
        .social-link-item a i {
            font-size: 1.2em;
            width: 30px;
            margin-right: 15px;
            text-align: center;
        }
        /* Pewarnaan Icon Sosial Media (Opsional) */
        .fa-facebook { color: #3b5998; }
        .fa-twitter { color: #00acee; }
        .fa-instagram { color: #c32aa3; }
        .fa-youtube { color: #ff0000; }
        .fa-tiktok { color: #000000; }


        /* Footer */
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
            <a href="#" class="dropdown-toggle">Ekskul <i class="fas fa-caret-down"></i></a>
            <div class="dropdown-content">
                <?php foreach ($daftar_ekskul as $ekskul_nav): ?>
                    <a href="ekskul_detail.php?slug=<?php echo htmlspecialchars($ekskul_nav['file_name']); ?>">
                        <?php echo htmlspecialchars($ekskul_nav['nama_ekskul']); ?>
                    </a>
                <?php endforeach; ?>
            </div>
        </div>
        <a href="kontak.php" style="color: var(--primary-color);">Kontak</a>
        
    </nav>
    <div class="nav-actions">
        <a href="pendaftaransiswasiswi.php" class="btn-kontak">PENDAFTAAN SISWA/SISWI</a>
    </div>
</header>

<div id="content-wrapper">
    <div class="contact-container">
        <h1>Kontak</h1>
        <h2>Kontak Sekolah</h2>

        <?php if ($kontak_data): ?>
            
            <div class="contact-item">
                <i class="fas fa-map-marker-alt"></i>
                <span><?php echo htmlspecialchars($kontak_data['alamat']); ?></span>
            </div>
            
            <div class="contact-item">
                <i class="fas fa-phone-alt"></i>
                <span><?php echo htmlspecialchars($kontak_data['telepon_1']); ?></span>
            </div>
            
            <?php if (!empty($kontak_data['telepon_2'])): ?>
            <div class="contact-item">
                <i class="fas fa-phone-alt"></i>
                <span><?php echo htmlspecialchars($kontak_data['telepon_2']); ?></span>
            </div>
            <?php endif; ?>
            
            <div class="contact-item" style="border-bottom: none;">
                <i class="fas fa-envelope"></i>
                <span><?php echo htmlspecialchars($kontak_data['email']); ?></span>
            </div>
            
            <h2>Sosial Media</h2>

            <?php 
            // Array icon sosial media dan field database-nya
            $sosial_media = [
                ['icon' => 'fab fa-facebook-f', 'field' => 'facebook_url', 'name' => 'Facebook'],
                ['icon' => 'fab fa-twitter', 'field' => 'twitter_url', 'name' => 'Twitter'],
                ['icon' => 'fab fa-instagram', 'field' => 'instagram_url', 'name' => 'Instagram'],
                ['icon' => 'fab fa-youtube', 'field' => 'youtube_url', 'name' => 'Youtube'],
                ['icon' => 'fab fa-tiktok', 'field' => 'tiktok_url', 'name' => 'TikTok'],
            ];
            
            foreach ($sosial_media as $sosmed):
                if (!empty($kontak_data[$sosmed['field']])):
            ?>
                <div class="social-link-item">
                    <a href="<?php echo htmlspecialchars($kontak_data[$sosmed['field']]); ?>" target="_blank" rel="noopener noreferrer">
                        <i class="<?php echo $sosmed['icon']; ?>"></i>
                        <span><?php echo htmlspecialchars($kontak_data[$sosmed['field']]); ?></span>
                    </a>
                </div>
            <?php 
                endif;
            endforeach; 
            ?>
        
        <?php else: ?>
            <p>Data kontak belum tersedia.</p>
        <?php endif; ?>

    </div>
</div>

<footer class="placeholder-footer">
    <p>&copy; 2025 SD ISLAM ASSA'ADAH. Hak Cipta Dilindungi.</p>
</footer>

</body>
</html>
<?php 
if (isset($conn)) $conn->close(); 
?>