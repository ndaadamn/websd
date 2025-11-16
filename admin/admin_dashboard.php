<?php
session_start();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - SD ISLAM ASSA'ADAH</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
    
    <style>
        :root {
            --primary: #1B5E20;
            --secondary: #4CAF50;
            --bg-light: #f4f7f6;
            --text-light: #ffffff;
            --header-bg: #fff;
        }
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: sans-serif;
        }
        
        html, body {
            height: 100%;
            overflow-x: hidden;
        }
        
        body {
            background-color: var(--bg-light);
            display: flex;
        }
        
        /* SIDEBAR */
        .sidebar {
            width: 250px;
            background-color: var(--primary);
            color: var(--text-light);
            padding: 20px 0;
            display: flex;
            flex-direction: column;
            position: fixed;
            height: 100%;
            box-shadow: 2px 0 5px rgba(0, 0, 0, 0.2);
            z-index: 100;
        }

        .sidebar h2 {
            text-align: center;
            margin-bottom: 30px;
            font-size: 1.3em;
            color: #c8e6c9;
        }

        .sidebar a {
            padding: 15px 20px;
            text-decoration: none;
            color: var(--text-light);
            font-size: 0.95em;
            display: block;
            transition: background-color 0.3s, font-weight 0.3s;
        }

        .sidebar a:hover, 
        .sidebar a.active {
            background-color: var(--secondary);
            font-weight: bold;
        }

        .sidebar a i {
            margin-right: 10px;
            width: 20px;
        }

        /* MAIN CONTENT */
        .main-content {
            margin-left: 250px;
            flex-grow: 1;
            width: calc(100% - 250px);
            display: flex;
            flex-direction: column;
            min-height: 100vh;
        }
        
        /* HEADER */
        .header-admin {
            background-color: var(--header-bg);
            padding: 15px 30px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            display: flex;
            justify-content: space-between;
            align-items: center;
            position: sticky;
            top: 0;
            z-index: 50;
        }

        .header-admin h1 {
            font-size: 1.5em;
            color: var(--primary);
        }

        .header-admin .user-info {
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .header-admin .logout-btn {
            background-color: #f44336;
            color: white;
            padding: 8px 15px;
            text-decoration: none;
            border-radius: 4px;
            font-size: 0.9em;
            transition: background-color 0.3s;
        }

        .header-admin .logout-btn:hover {
            background-color: #d32f2f;
        }

        /* CONTENT AREA FIXED (HILANGKAN GAP!!) */
        .content-area {
            padding: 0 !important;
            margin: 0 !important;
            flex-grow: 1;
            width: 100%;
        }

        /* NORMALISASI SEMUA HALAMAN YANG DI-LOAD */
        #content-area html,
        #content-area body {
            margin: 0 !important;
            padding: 0 !important;
            background: none !important;
        }

        #content-area * {
            box-sizing: border-box !important;
        }

        #content-area > * {
            margin-top: 0 !important;
            padding-top: 0 !important;
        }

        /* WRAPPER AGAR SEMUA HALAMAN RAPI */
        .fix-content {
            margin: 0 !important;
            padding: 20px !important;
            width: 100% !important;
        }

    </style>
</head>
<body>

    <div class="sidebar">
        <h2><i class="fas fa-screwdriver-wrench"></i> ADMIN SD ASSA'ADAH</h2>

        <a href="#" class="nav-link active" data-page="adminindex.php" id="default-load">
            <i class="fas fa-home"></i> Admin Home (Index)
        </a>
        <a href="#" class="nav-link" data-page="adminprofilsekolah.php"><i class="fas fa-school"></i> Profil Sekolah</a>
        <a href="#" class="nav-link" data-page="adminprofilguru.php"><i class="fas fa-chalkboard-teacher"></i> Profil Guru</a>
        <a href="#" class="nav-link" data-page="admingaleri.php"><i class="fas fa-images"></i> Galeri</a>
        <a href="#" class="nav-link" data-page="adminekskul.php"><i class="fas fa-football"></i> Ekskul</a>
        <a href="#" class="nav-link" data-page="adminkontak.php"><i class="fas fa-envelope-open-text"></i> Pesan Kontak</a>
        <a href="#" class="nav-link" data-page="adminpendaftaransiswasiswi.php"><i class="fas fa-user-plus"></i> Pendaftaran Siswa</a>
    </div>

    <div class="main-content">
        
        <div class="header-admin">
            <h1 id="page-title">Admin Home (Index)</h1>
            <div class="user-info">
                <span>Admin User</span>
                <a href="logout.php" class="logout-btn"><i class="fas fa-sign-out-alt"></i> Logout</a>
            </div>
        </div>
        
        <div class="content-area" id="content-area"></div>
    </div>

    <script>
        $(document).ready(function() {

            const contentArea = $('#content-area');
            const pageTitle = $('#page-title');

            function loadPage(pageUrl, linkElement) {
                
                contentArea.html(`
                    <div style="text-align:center; padding-top:50px;">
                        <i class="fas fa-spinner fa-spin fa-3x" style="color: var(--primary);"></i>
                        <p style="margin-top:15px;">Memuat...</p>
                    </div>
                `);

                const newTitle = linkElement.clone().children().remove().end().text().trim();

                contentArea.load(pageUrl, function(response, status) {
                    if (status === "error") {
                        contentArea.html(`
                            <div class="fix-content" style="border-left:5px solid #f44336;">
                                <h3 style="color:#f44336;">Error Memuat Halaman!</h3>
                                <p>Halaman ${pageUrl} tidak ditemukan.</p>
                            </div>
                        `);
                        pageTitle.text("Error");
                    } else {

                        /* BUNGKUS SEMUA HALAMAN AGAR RAPI */
                        if (!contentArea.children().first().hasClass("fix-content")) {
                            contentArea.children().wrapAll('<div class="fix-content"></div>');
                        }

                        pageTitle.text(newTitle);
                    }
                });
            }

            $('.nav-link').on('click', function(e) {
                e.preventDefault();
                $('.nav-link').removeClass('active');
                $(this).addClass('active');

                loadPage($(this).data('page'), $(this));
            });

            const defaultLink = $('#default-load');
            loadPage(defaultLink.data('page'), defaultLink);

        });
    </script>
</body>
</html>
