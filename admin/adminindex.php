<?php
// FILE: admin/adminindex.php
// Pastikan path koneksi.php benar (naik satu level ke root)
include '../koneksi.php';

$message = '';
$message_type = '';

// ----------------------------------------------------------------------
// 1. LOGIKA UPDATE DATA STATISTIK
// ----------------------------------------------------------------------
if (isset($_POST['update_stats'])) {
    $guru = (int)$_POST['jumlah_guru'];
    $siswa = (int)$_POST['jumlah_siswa'];
    $pegawai = (int)$_POST['total_pegawai'];
    // $komite dihapus

    // Query diubah: Menghapus kolom 'komite_sekolah'
    $stmt = $conn->prepare("UPDATE pengaturan_index SET jumlah_guru = ?, jumlah_siswa = ?, total_pegawai = ? WHERE id = 1");
    $stmt->bind_param("iii", $guru, $siswa, $pegawai);

    if ($stmt->execute()) {
        $message = "Data statistik berhasil diperbarui!";
        $message_type = 'success';
    } else {
        $message = "Gagal memperbarui data statistik: " . $stmt->error;
        $message_type = 'error';
    }
    $stmt->close();
}

// ----------------------------------------------------------------------
// 2. LOGIKA TAMBAH AGENDA
// ----------------------------------------------------------------------
if (isset($_POST['tambah_agenda'])) {
    $tanggal = $_POST['tanggal_agenda'];
    $acara = $_POST['acara_agenda'];
    $keterangan = $_POST['keterangan_agenda'];

    $stmt = $conn->prepare("INSERT INTO kalender_akademik (tanggal, acara, keterangan) VALUES (?, ?, ?)");
    $stmt->bind_param("sss", $tanggal, $acara, $keterangan);

    if ($stmt->execute()) {
        $message = "Agenda baru berhasil ditambahkan!";
        $message_type = 'success';
    } else {
        $message = "Gagal menambahkan agenda: " . $stmt->error;
        $message_type = 'error';
    }
    $stmt->close();
}

// ----------------------------------------------------------------------
// 3. LOGIKA HAPUS AGENDA
// ----------------------------------------------------------------------
if (isset($_GET['delete_agenda_id'])) {
    $id_agenda = (int)$_GET['delete_agenda_id'];

    $stmt = $conn->prepare("DELETE FROM kalender_akademik WHERE id = ?");
    $stmt->bind_param("i", $id_agenda);

    if ($stmt->execute()) {
        $message = "Agenda berhasil dihapus!";
        $message_type = 'success';
        // Redirect untuk menghilangkan parameter GET agar tidak terhapus lagi
        header("Location: adminindex.php");
        exit();
    } else {
        $message = "Gagal menghapus agenda: " . $stmt->error;
        $message_type = 'error';
    }
    $stmt->close();
}

// ----------------------------------------------------------------------
// 4. PENGAMBILAN DATA UNTUK FORM DAN LIST
// ----------------------------------------------------------------------

// Ambil data statistik saat ini (tanpa kolom komite_sekolah)
$current_stats = $conn->query("SELECT jumlah_guru, jumlah_siswa, total_pegawai FROM pengaturan_index WHERE id = 1")->fetch_assoc();

// Ambil semua agenda
$all_agenda = [];
$result_agenda = $conn->query("SELECT id, tanggal, acara, keterangan FROM kalender_akademik ORDER BY tanggal ASC");
if ($result_agenda) {
    while($row = $result_agenda->fetch_assoc()) {
        $all_agenda[] = $row;
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Index</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <style>
        /* CSS Admin Baru (Bukan CSS Index.php) */
        html, body { margin: 0; padding: 0; height: 100%; font-size: 16px; }
        body { background-color: #f4f4f4; font-family: sans-serif; }
        * { margin: 0; padding: 0; box-sizing: border-box; }
        
        .admin-container {
            padding: 20px;
            max-width: 1200px;
            margin: 20px auto;
            background: white;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
            border-radius: 8px;
        }

        h1 { color: #1B5E20; border-bottom: 2px solid #4CAF50; padding-bottom: 10px; margin-bottom: 30px; }
        h2 { color: #4CAF50; margin-top: 30px; margin-bottom: 15px; }

        /* Message Box */
        .message { padding: 15px; margin-bottom: 20px; border-radius: 4px; font-weight: bold;}
        .message.success { background-color: #d4edda; color: #155724; border: 1px solid #c3e6cb; }
        .message.error { background-color: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; }

        /* Form Styling */
        .form-group { margin-bottom: 15px; }
        .form-group label { display: block; margin-bottom: 5px; font-weight: bold; }
        .form-group input[type="text"], 
        .form-group input[type="number"], 
        .form-group input[type="date"], 
        .form-group textarea {
            width: 100%;
            padding: 10px;
            border: 1px solid #ccc;
            border-radius: 4px;
            box-sizing: border-box;
        }
        
        .btn-submit {
            background-color: #4CAF50;
            color: white;
            padding: 10px 15px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-weight: bold;
            transition: background-color 0.3s;
        }
        .btn-submit:hover { background-color: #1B5E20; }
        
        /* Layout Grid untuk Statistik (Diubah menjadi 3 kolom) */
        .stats-form-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 20px;
        }
        
        /* Table Agenda */
        .agenda-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
            font-size: 0.9em;
        }
        .agenda-table th, .agenda-table td {
            border: 1px solid #ddd;
            padding: 10px;
            text-align: left;
        }
        .agenda-table th {
            background-color: #e8f5e9;
            color: #1B5E20;
        }
        .agenda-table .delete-link {
            color: red;
            text-decoration: none;
            font-weight: bold;
        }
    </style>
</head>
<body>

<div class="admin-container">
    <h1>Dashboard Admin - Pengaturan Index</h1>

    <?php if (!empty($message)): ?>
        <div class="message <?php echo $message_type; ?>">
            <?php echo $message; ?>
        </div>
    <?php endif; ?>
    
    <div id="stats-update">
        <h2>📊 Kelola Statistik Sekolah</h2>
        <form method="POST">
            <div class="stats-form-grid">
                <div class="form-group">
                    <label for="jumlah_guru">Jumlah Guru:</label>
                    <input type="number" id="jumlah_guru" name="jumlah_guru" required value="<?php echo htmlspecialchars($current_stats['jumlah_guru'] ?? 0); ?>">
                </div>
                <div class="form-group">
                    <label for="jumlah_siswa">Jumlah Siswa:</label>
                    <input type="number" id="jumlah_siswa" name="jumlah_siswa" required value="<?php echo htmlspecialchars($current_stats['jumlah_siswa'] ?? 0); ?>">
                </div>
                <div class="form-group">
                    <label for="total_pegawai">Total Pegawai:</label>
                    <input type="number" id="total_pegawai" name="total_pegawai" required value="<?php echo htmlspecialchars($current_stats['total_pegawai'] ?? 0); ?>">
                </div>
            </div>
            <button type="submit" name="update_stats" class="btn-submit">Update Statistik</button>
        </form>
    </div>

    <hr style="margin: 40px 0; border-top: 1px dashed #ddd;">

    <div id="agenda-management">
        <h2>📅 Tambah Agenda Kalender Akademik</h2>
        <form method="POST">
            <div class="form-group">
                <label for="tanggal_agenda">Tanggal:</label>
                <input type="date" id="tanggal_agenda" name="tanggal_agenda" required>
            </div>
            <div class="form-group">
                <label for="acara_agenda">Nama Acara:</label>
                <input type="text" id="acara_agenda" name="acara_agenda" required>
            </div>
            <div class="form-group">
                <label for="keterangan_agenda">Keterangan (Opsional):</label>
                <textarea id="keterangan_agenda" name="keterangan_agenda"></textarea>
            </div>
            <button type="submit" name="tambah_agenda" class="btn-submit">Tambahkan Agenda</button>
        </form>

        <h2 style="margin-top: 40px;">Daftar Agenda Saat Ini</h2>
        <?php if (!empty($all_agenda)): ?>
            <table class="agenda-table">
                <thead>
                    <tr>
                        <th>Tanggal</th>
                        <th>Acara</th>
                        <th>Keterangan</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    $formatter = new IntlDateFormatter('id_ID', IntlDateFormatter::LONG, IntlDateFormatter::NONE);
                    $formatter->setPattern("d MMMM yyyy");
                    
                    foreach ($all_agenda as $agenda): 
                        $tanggal_formatted = $formatter->format(strtotime($agenda['tanggal']));
                    ?>
                    <tr>
                        <td><?php echo $tanggal_formatted; ?></td>
                        <td><?php echo htmlspecialchars($agenda['acara']); ?></td>
                        <td><?php echo htmlspecialchars($agenda['keterangan']); ?></td>
                        <td>
                            <a href="adminindex.php?delete_agenda_id=<?php echo $agenda['id']; ?>" class="delete-link" onclick="return confirm('Apakah Anda yakin ingin menghapus agenda ini?');">Hapus</a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php else: ?>
            <p>Tidak ada agenda yang tersimpan saat ini.</p>
        <?php endif; ?>
    </div>

</div>

</body>
</html>