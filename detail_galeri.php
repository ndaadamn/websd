<?php
// FILE: detail_galeri.php
include 'koneksi.php';

// Pastikan ID diterima
if (!isset($_GET['id']) || empty($_GET['id'])) {
    // Redirect jika ID tidak ada atau kosong
    header("Location: galeri.php");
    exit();
}

$id = intval($_GET['id']);

// Ambil data dari database menggunakan Prepared Statement untuk keamanan
$stmt = $conn->prepare("SELECT * FROM galeri WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    // Redirect jika ID tidak ditemukan
    header("Location: galeri.php");
    exit();
}

$data = $result->fetch_assoc();
$stmt->close();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($data['title']); ?> - Galeri SD Assa'adah</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css"> 
    <style>
        /* --- CSS Global & Navbar (Dipindahkan dari galeri.php untuk konsistensi) --- */
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
        .nav-menu a[id="galeri"] { color: var(--primary-color) !important; font-weight: bold; }

        /* --- DETAIL KONTEN GALERI --- */
        .main-content {
            padding: 20px 5%; 
        }
        
        .detail-container {
            max-width: 900px;
            margin: 0 auto;
            background-color: white;
            box-shadow: 0 0 15px rgba(0, 0, 0, 0.1);
            border-radius: 8px;
            padding: 30px;
        }

        .detail-header {
            border-bottom: 2px solid #eee;
            margin-bottom: 20px;
            padding-bottom: 10px;
        }

        .detail-header h1 {
            font-size: 2.5em;
            color: var(--secondary-color);
            margin-top: 10px; /* Jarak dari tombol kembali */
            margin-bottom: 10px;
        }

        .detail-info {
            font-size: 0.9em;
            color: #777;
            margin-bottom: 20px;
        }
        
        .detail-media {
            width: 100%;
            max-height: 500px;
            object-fit: contain; 
            margin-bottom: 30px;
            border-radius: 4px;
            background-color: #ccc;
        }

        .detail-deskripsi {
            font-size: 1.1em;
            line-height: 1.6;
            color: var(--text-dark);
            white-space: pre-wrap; 
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
            
            <a href="galeri.php" id="galeri">Galeri</a> 
            
            <div class="dropdown">
                <a href="#" class="dropdown-toggle">Ekskul <i class="fas fa-caret-down"></i></a>
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
        <div class="detail-container">
            <div class="detail-header">
                <a href="galeri.php" style="font-size: 0.9em; color: var(--primary-color); text-decoration: none;"><i class="fas fa-arrow-left"></i> Kembali ke Galeri</a>
                <h1><?php echo htmlspecialchars($data['title']); ?></h1>
                <div class="detail-info">
                    <i class="fas fa-calendar-alt"></i> Tanggal Upload: **<?php echo date("d M Y", strtotime($data['tanggal_upload'])); ?>**
                </div>
            </div>

            <?php if ($data['tipe_file'] == 'foto'): ?>
                <img src="<?php echo htmlspecialchars($data['file_path']); ?>" alt="<?php echo htmlspecialchars($data['title']); ?>" class="detail-media">
            <?php else: /* tipe_file == 'video' */ ?>
                <video controls class="detail-media">
                    <source src="<?php echo htmlspecialchars($data['file_path']); ?>" type="video/mp4">
                    Browser Anda tidak mendukung tag video.
                </video>
            <?php endif; ?>

            <div class="detail-deskripsi">
                <?php echo nl2br(htmlspecialchars($data['deskripsi'])); ?>
            </div>
        </div>
    </main>
    
    <footer class="placeholder-footer">
        <p>&copy; 2025 SD ISLAM ASSA'ADAH. Hak Cipta Dilindungi.</p>
    </footer>

</body>
</html>
<?php $conn->close(); ?>