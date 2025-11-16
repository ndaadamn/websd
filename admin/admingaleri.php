<?php
// FILE: admin/admingaleri.php (FINAL - CARD STYLE + FULL AJAX)
include '../koneksi.php'; // Kembali ke root folder untuk koneksi.php

$uploadDir = '../uploads/galeri/'; // Folder tempat menyimpan file
if (!is_dir($uploadDir)) {
    mkdir($uploadDir, 0777, true);
}

// ----------------------------------------------------------------------
// AJAX HANDLER UNTUK TAMBAH DATA (CREATE)
// ----------------------------------------------------------------------
if (isset($_POST['action']) && $_POST['action'] == 'tambah_galeri_ajax') {
    header('Content-Type: application/json');
    $response = ['status' => 'error', 'message' => 'Terjadi kesalahan tidak dikenal.'];

    $title = $conn->real_escape_string($_POST['title']);
    $deskripsi = $conn->real_escape_string($_POST['deskripsi']);
    $tipe_file = $_POST['tipe_file'];
    
    if (isset($_FILES["file"]) && $_FILES["file"]["error"] == 0) {
        $fileName = basename($_FILES["file"]["name"]);
        // Tambahkan timestamp untuk menghindari konflik nama file
        $uniqueFileName = time() . '_' . $fileName; 
        $targetFile = $uploadDir . $uniqueFileName;
        $fileType = strtolower(pathinfo($targetFile, PATHINFO_EXTENSION));

        // Validasi Tipe File (Foto: jpg, jpeg, png | Video: mp4)
        if ($tipe_file == 'foto' && !in_array($fileType, ['jpg', 'jpeg', 'png', 'webp', 'gif'])) {
            $response['message'] = "Gagal: Tipe file foto harus JPG, JPEG, PNG, WEBP, atau GIF.";
            echo json_encode($response);
            exit();
        } elseif ($tipe_file == 'video' && !in_array($fileType, ['mp4', 'mov', 'webm'])) {
            $response['message'] = "Gagal: Tipe file video harus MP4, MOV, atau WEBM.";
            echo json_encode($response);
            exit();
        } 
        
        if (move_uploaded_file($_FILES["file"]["tmp_name"], $targetFile)) {
            // Query Insert ke Database
            $stmt = $conn->prepare("INSERT INTO galeri (title, deskripsi, file_path, tipe_file) VALUES (?, ?, ?, ?)");
            $relativePath = 'uploads/galeri/' . $uniqueFileName;
            
            if ($stmt) {
                $stmt->bind_param("ssss", $title, $deskripsi, $relativePath, $tipe_file);
                
                if ($stmt->execute()) {
                    $last_id = $conn->insert_id;
                    $response['status'] = 'success';
                    $response['message'] = "Sukses: Konten galeri baru berhasil ditambahkan.";
                    $response['new_row'] = [
                        'id' => $last_id,
                        'title' => htmlspecialchars($title),
                        'tipe_file' => strtoupper($tipe_file),
                        'file_path' => $relativePath,
                        'file_name' => $uniqueFileName,
                        'tanggal_upload' => date("d M Y") // Format tanggal saat ini
                    ];
                } else {
                    $response['message'] = "Error database: " . $stmt->error;
                }
                $stmt->close();
            } else {
                 $response['message'] = "Error prepare statement: " . $conn->error;
            }
        } else {
            $response['message'] = "Gagal mengunggah file. Cek izin folder.";
        }
    } else {
        $response['message'] = "Gagal: File tidak terunggah atau terjadi error file.";
    }

    echo json_encode($response);
    exit();
}

// ----------------------------------------------------------------------
// AJAX HANDLER UNTUK HAPUS DATA (DELETE)
// ----------------------------------------------------------------------
if (isset($_POST['action']) && $_POST['action'] == 'hapus_galeri_ajax') {
    header('Content-Type: application/json');
    $response = ['status' => 'error', 'message' => 'Gagal menghapus konten.'];
    $id = intval($_POST['id'] ?? 0);
    
    // 1. Ambil path file untuk dihapus
    $stmt = $conn->prepare("SELECT file_path FROM galeri WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    $stmt->close();

    if ($row) {
        $filePath = '../' . $row['file_path'];
        
        // 2. Hapus dari database
        $stmt_del = $conn->prepare("DELETE FROM galeri WHERE id = ?");
        $stmt_del->bind_param("i", $id);
        
        if ($stmt_del->execute()) {
            // 3. Hapus file fisik
            if (file_exists($filePath)) {
                unlink($filePath);
            }
            $response['status'] = 'success';
            $response['message'] = "Sukses: Konten galeri berhasil dihapus.";
        } else {
            $response['message'] = "Error database: " . $stmt_del->error;
        }
        $stmt_del->close();
    } else {
        $response['message'] = "Gagal: ID tidak ditemukan.";
    }
    echo json_encode($response);
    exit();
}


// ----------------------------------------------------------------------
// TAMPILAN UTAMA
// ----------------------------------------------------------------------

// Tampilkan semua data galeri
$data_galeri = $conn->query("SELECT * FROM galeri ORDER BY tanggal_upload DESC");
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Galeri</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css"> 
    <style>
        /* CSS Tambahan untuk Card dan Styling Form */
        .card {
            background: #fff;
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            padding: 20px;
            margin-bottom: 20px;
        }
        .card h1, .card h2 {
            color: #333;
            border-bottom: 2px solid #f2f2f2;
            padding-bottom: 10px;
            margin-bottom: 15px;
        }
        .alert {
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 4px;
            font-weight: bold;
        }
        .alert.success {
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        .alert.error {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        form label {
            display: block;
            margin-top: 10px;
            margin-bottom: 5px;
            font-weight: bold;
        }
        input[type="text"], textarea, select, input[type="file"] {
            width: 100%; padding: 10px; margin: 8px 0; border: 1px solid #ccc; border-radius: 4px; box-sizing: border-box;
        }
        .btn-submit {
            background-color: #4CAF50; color: white; padding: 10px 15px; border: none; border-radius: 4px; cursor: pointer;
            transition: background-color 0.3s; margin-top: 10px;
        }
        .btn-submit:hover { background-color: #1B5E20; }
        
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { padding: 12px; border: 1px solid #ddd; text-align: left; font-size: 0.9em; }
        th { background-color: #f2f2f2; }
        .action-link { margin-right: 10px; text-decoration: none; color: #d9534f; }
        .action-link:hover { text-decoration: underline; }
    </style>
</head>
<body>

<div class="card" id="alert-area" style="display: none;">
    <div class="alert" id="alert-message"></div>
</div>

<div class="card">
    <h1><i class="fas fa-camera"></i> Admin Galeri Konten</h1>
    <a href="../galeri.php" style="text-decoration: none; color: #4CAF50; margin-bottom: 15px; display: inline-block;">
        <i class="fas fa-eye"></i> Lihat Halaman Publik
    </a>
</div>

<div class="card">
    <h2><i class="fas fa-plus-circle"></i> Tambah Konten Baru</h2>
    <form id="tambahGaleriForm" method="POST" enctype="multipart/form-data">
        <label for="title">Judul:</label>
        <input type="text" id="title" name="title" required>

        <label for="tipe_file">Tipe File:</label>
        <select id="tipe_file" name="tipe_file" required>
            <option value="foto">Foto (JPG/JPEG/PNG/WEBP/GIF)</option>
            <option value="video">Video (MP4/MOV/WEBM)</option>
        </select>

        <label for="file">Pilih File (Foto/Video):</label>
        <input type="file" id="file" name="file" required>

        <label for="deskripsi">Deskripsi (Opsional):</label>
        <textarea id="deskripsi" name="deskripsi" rows="4"></textarea>

        <button type="submit" id="submitBtn" class="btn-submit"><i class="fas fa-upload"></i> Unggah Konten</button>
    </form>
</div>

<div class="card">
    <h2><i class="fas fa-list"></i> Daftar Konten Galeri</h2>
    <table id="galeriTable">
        <thead>
            <tr>
                <th>ID</th>
                <th>Tipe</th>
                <th>Judul</th>
                <th>File</th>
                <th>Tanggal Upload</th>
                <th>Aksi</th>
            </tr>
        </thead>
        <tbody>
            <?php if ($data_galeri->num_rows > 0): ?>
                <?php while($row = $data_galeri->fetch_assoc()): ?>
                <tr id="galeri-row-<?php echo $row['id']; ?>">
                    <td><?php echo $row['id']; ?></td>
                    <td><?php echo strtoupper($row['tipe_file']); ?></td>
                    <td><?php echo htmlspecialchars($row['title']); ?></td>
                    <td><a href="../<?php echo htmlspecialchars($row['file_path']); ?>" target="_blank"><?php echo basename($row['file_path']); ?></a></td>
                    <td><?php echo date("d M Y", strtotime($row['tanggal_upload'])); ?></td>
                    <td>
                        <a href="#" class="delete-galeri-ajax action-link" data-id="<?php echo $row['id']; ?>"><i class="fas fa-trash"></i> Hapus</a>
                    </td>
                </tr>
                <?php endwhile; ?>
            <?php else: ?>
                <tr id="no-data-row"><td colspan="6" style="text-align: center;">Belum ada konten galeri.</td></tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<script>
    // Pastikan jQuery sudah dimuat di halaman dashboard utama Anda agar $(document).ready() berfungsi.
    $(document).ready(function() {
        
        // Fungsi untuk menampilkan pesan alert AJAX
        function showAlert(message, type) {
            var alertArea = $('#alert-area');
            var alertMessage = $('#alert-message');
            
            alertMessage.removeClass('success error').addClass(type).html(message);
            
            // Scroll ke atas sedikit agar user melihat notifikasi
            $('html, body').animate({
                scrollTop: alertArea.offset().top - 20
            }, 300); 

            alertArea.fadeIn().delay(5000).fadeOut(); // Tampilkan 5 detik
        }
        
        // --- 1. AJAX Form Tambah Konten ---
        $('#tambahGaleriForm').on('submit', function(e) {
            e.preventDefault();
            var form = $(this);
            var btn = $('#submitBtn');
            var formData = new FormData(this); 

            formData.append('action', 'tambah_galeri_ajax'); // Tanda untuk PHP handler

            btn.attr('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Mengunggah...');

            $.ajax({
                type: 'POST',
                url: 'admingaleri.php', 
                data: formData,
                contentType: false, 
                processData: false, 
                dataType: 'json', 
                success: function(response) {
                    if (response.status === 'success') {
                        // 1. Reset Form
                        form[0].reset();
                        
                        // 2. Tambahkan Baris Baru ke Tabel
                        var rowData = response.new_row;
                        var newRow = '<tr id="galeri-row-' + rowData.id + '">' +
                            '<td>' + rowData.id + '</td>' +
                            '<td>' + rowData.tipe_file + '</td>' +
                            '<td>' + rowData.title + '</td>' +
                            '<td><a href="../' + rowData.file_path + '" target="_blank">' + rowData.file_name + '</a></td>' +
                            '<td>' + rowData.tanggal_upload + '</td>' +
                            '<td>' +
                                '<a href="#" class="delete-galeri-ajax action-link" data-id="' + rowData.id + '"><i class="fas fa-trash"></i> Hapus</a>' +
                            '</td>' +
                            '</tr>';
                            
                        // Hapus baris "Belum ada konten galeri." jika ada
                        $('#no-data-row').remove();
                        
                        // Tambahkan baris baru di paling atas tabel
                        $('#galeriTable tbody').prepend(newRow); 

                        // 3. Tampilkan Notifikasi
                        showAlert(response.message, 'success');

                    } else {
                        showAlert('Gagal mengunggah konten: ' + response.message, 'error');
                    }
                    btn.attr('disabled', false).html('<i class="fas fa-upload"></i> Unggah Konten');
                },
                error: function(xhr, status, error) {
                    showAlert('Gagal mengunggah konten via AJAX. Cek konsol.', 'error');
                    console.error("AJAX Error:", status, error);
                    btn.attr('disabled', false).html('<i class="fas fa-upload"></i> Unggah Konten');
                }
            });
        });
        
        // --- 2. AJAX Hapus Konten ---
        $(document).on('click', '.delete-galeri-ajax', function(e) {
            e.preventDefault();
            var kontenId = $(this).data('id');
            var $row = $('#galeri-row-' + kontenId);

            if (confirm('Yakin ingin menghapus konten galeri ini?')) {
                $.ajax({
                    type: 'POST',
                    url: 'admingaleri.php',
                    data: { action: 'hapus_galeri_ajax', id: kontenId },
                    dataType: 'json', 
                    success: function(response) {
                        if (response.status === 'success') {
                            // Hapus baris dari tabel dengan animasi
                            $row.fadeOut(300, function() {
                                $(this).remove();
                                
                                // Cek jika tabel kosong, tampilkan pesan 'Belum ada data'
                                if ($('#galeriTable tbody tr').length === 0) {
                                    var noDataRow = '<tr id="no-data-row"><td colspan="6" style="text-align: center;">Belum ada konten galeri.</td></tr>';
                                    $('#galeriTable tbody').append(noDataRow);
                                }
                            });
                            showAlert(response.message, 'success');
                        } else {
                            showAlert('Gagal menghapus konten: ' + response.message, 'error');
                        }
                    },
                    error: function() {
                        showAlert('Gagal menghapus konten via AJAX.', 'error');
                    }
                });
            }
        });
    });
</script>
</body>
</html>