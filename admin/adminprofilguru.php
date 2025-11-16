<?php
// FILE: admin/adminprofilguru.php (FINAL - CARD STYLE DI DALAM DASHBOARD + FULL AJAX)
include '../koneksi.php'; // Sesuaikan path koneksi

// Folder tujuan upload foto guru individu
$target_dir_guru = "../uploads/guru/"; 
if (!is_dir($target_dir_guru)) {
    mkdir($target_dir_guru, 0777, true);
}

// Folder tujuan upload foto banner
$target_dir_banner = "../uploads/banner/"; 
if (!is_dir($target_dir_banner)) {
    mkdir($target_dir_banner, 0777, true);
}

// Data Banner Saat Ini
$current_banner = '';
$stmt_banner = $conn->prepare("SELECT foto_banner_guru FROM profil_guru WHERE id = 1");
$stmt_banner->execute();
$result_banner = $stmt_banner->get_result();
$banner_data = $result_banner->fetch_assoc();
$stmt_banner->close();
if ($banner_data) {
    $current_banner = $banner_data['foto_banner_guru'];
}


// ----------------------------------------------------------------------
// AJAX HANDLERS (Untuk dipanggil oleh jQuery/JavaScript)
// ----------------------------------------------------------------------

// Logika Tambah Guru
if (isset($_POST['action']) && $_POST['action'] == 'tambah_guru_ajax') {
    header('Content-Type: application/json');
    $response = ['status' => 'error', 'message' => 'Terjadi kesalahan tidak dikenal.'];

    $nama = $_POST['nama_guru'];
    $jabatan = $_POST['jabatan'];
    $foto_url = '';

    if (isset($_FILES['foto_guru']) && $_FILES['foto_guru']['error'] == 0) {
        $imageFileType = strtolower(pathinfo($_FILES["foto_guru"]["name"], PATHINFO_EXTENSION));
        $unique_filename = 'guru_' . time() . '.' . $imageFileType;
        $final_target = $target_dir_guru . $unique_filename;
        
        if (move_uploaded_file($_FILES["foto_guru"]["tmp_name"], $final_target)) {
            $foto_url = "uploads/guru/" . $unique_filename;
        } else {
            $response['message'] = 'Gagal mengunggah foto.';
            echo json_encode($response);
            exit();
        }
    }

    $stmt = $conn->prepare("INSERT INTO profil_guru (nama_guru, jabatan, foto_url) VALUES (?, ?, ?)");
    $stmt->bind_param("sss", $nama, $jabatan, $foto_url);
    if ($stmt->execute()) {
        $last_id = $conn->insert_id;
        $response['status'] = 'success';
        $response['message'] = 'Guru baru berhasil ditambahkan.';
        $response['new_row'] = [
            'id' => $last_id,
            'nama_guru' => htmlspecialchars($nama),
            'jabatan' => htmlspecialchars($jabatan)
        ];
    } else {
        $response['message'] = 'Gagal menyimpan ke database: ' . $conn->error;
    }
    $stmt->close();
    echo json_encode($response);
    exit();
}

// Logika Hapus Guru
if (isset($_POST['action']) && $_POST['action'] == 'hapus_guru_ajax') {
    header('Content-Type: application/json');
    $response = ['status' => 'error', 'message' => 'Terjadi kesalahan tidak dikenal.'];
    $id = intval($_POST['id']);
    
    // Ambil path foto untuk dihapus
    $stmt_del = $conn->prepare("SELECT foto_url FROM profil_guru WHERE id = ?");
    $stmt_del->bind_param("i", $id);
    $stmt_del->execute();
    $result_del = $stmt_del->get_result();
    $row_del = $result_del->fetch_assoc();
    $stmt_del->close();
    
    $stmt_delete = $conn->prepare("DELETE FROM profil_guru WHERE id = ?");
    $stmt_delete->bind_param("i", $id);

    if ($stmt_delete->execute()) {
        // Hapus file fisik jika ada
        if ($row_del && $row_del['foto_url'] && file_exists("../" . $row_del['foto_url'])) {
            unlink("../" . $row_del['foto_url']);
        }
        $response['status'] = 'success';
        $response['message'] = 'Data guru berhasil dihapus!';
    } else {
        $response['message'] = 'Gagal menghapus dari database: ' . $conn->error;
    }
    $stmt_delete->close();
    echo json_encode($response);
    exit();
}

// Logika Update Banner
if (isset($_POST['action']) && $_POST['action'] == 'upload_banner_ajax') {
    header('Content-Type: application/json');
    $response = ['status' => 'error', 'message' => 'Terjadi kesalahan tidak dikenal.'];

    if (isset($_FILES['foto_banner']) && $_FILES['foto_banner']['error'] == 0) {
        $imageFileType = strtolower(pathinfo($_FILES["foto_banner"]["name"], PATHINFO_EXTENSION));
        $unique_filename = 'banner_guru_' . time() . '.' . $imageFileType;
        $final_target = $target_dir_banner . $unique_filename;
        
        if (move_uploaded_file($_FILES["foto_banner"]["tmp_name"], $final_target)) {
            $new_banner_path = "uploads/banner/" . $unique_filename; 
            
            $count_row = $conn->query("SELECT id FROM profil_guru WHERE id = 1")->num_rows;
            if ($count_row == 0) {
                $conn->query("INSERT INTO profil_guru (id) VALUES (1)");
            }
            
            $stmt_update = $conn->prepare("UPDATE profil_guru SET foto_banner_guru = ? WHERE id = 1");
            $stmt_update->bind_param("s", $new_banner_path);
            
            if ($stmt_update->execute()) {
                // Hapus banner lama
                if ($current_banner && file_exists("../" . $current_banner)) {
                    unlink("../" . $current_banner);
                }
                $response['status'] = 'success';
                $response['message'] = 'Foto Banner Guru berhasil diperbarui!';
                $response['new_banner_url'] = '../' . $new_banner_path;
            } else {
                $response['message'] = 'Gagal update database: ' . $conn->error;
            }
            $stmt_update->close();
        } else {
            $response['message'] = 'Gagal memindahkan file yang diunggah.';
        }
    } else {
        $response['message'] = 'File tidak terunggah atau terjadi kesalahan file.';
    }
    echo json_encode($response);
    exit();
}


// ----------------------------------------------------------------------
// TAMPILAN UTAMA
// ----------------------------------------------------------------------

// Ambil data guru untuk tampilan tabel
$daftar_guru = [];
$result_guru = $conn->query("SELECT id, nama_guru, jabatan, foto_url FROM profil_guru WHERE id != 1 ORDER BY id ASC");
if ($result_guru) {
    while($row = $result_guru->fetch_assoc()) {
        $daftar_guru[] = $row;
    }
}
?>

<style>
/* CSS Tambahan untuk Card dan Table di dalam Dashboard Anda */
.card {
    background: #fff;
    border-radius: 8px;
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
    padding: 20px;
    margin-bottom: 20px;
}
.card h1, .card h2 {
    color: #333;
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
table {
    width: 100%;
    border-collapse: collapse;
}
table th, table td {
    border: 1px solid #ddd;
    padding: 12px;
    text-align: left;
}
table th {
    background-color: #f2f2f2;
}
.btn-submit {
    background-color: #4CAF50;
    color: white;
    padding: 10px 15px;
    border: none;
    border-radius: 4px;
    cursor: pointer;
    margin-top: 15px;
}
.current-banner-preview img {
    max-width: 100%;
    height: auto;
    max-height: 150px;
    margin-top: 10px;
    border: 1px solid #ddd;
    padding: 5px;
}
</style>

<div class="card" id="alert-area" style="display: none;">
    <div class="alert" id="alert-message"></div>
</div>

<div class="card">
    <h1 style="color: #4CAF50;"><i class="fas fa-user-friends"></i> Administrasi Profil Guru</h1>
</div>

---

<div class="card form-section">
    <h2><i class="fas fa-camera"></i> Upload Foto Banner Seluruh Guru</h2>
    <form method="post" enctype="multipart/form-data" id="bannerForm">
        <label for="foto_banner" style="display: block; margin-top: 10px; font-weight: bold;">Pilih Foto Banner (Maks 5MB)</label>
        <input type="file" id="foto_banner" name="foto_banner" accept="image/*" required style="width: 100%; padding: 8px; margin-top: 5px; border: 1px solid #ccc; border-radius: 4px; box-sizing: border-box;">
        
        <p style="margin-top: 10px;">Banner Saat Ini:</p>
        <div class="current-banner-preview">
            <img id="banner-preview-img" src="<?php echo $current_banner ? "../" . htmlspecialchars($current_banner) : 'placeholder.jpg'; ?>" alt="Banner Guru" style="max-width: 100%; height: auto; display: block; max-height: 150px; margin-top: 10px; border: 1px solid #ddd; padding: 5px; <?php echo $current_banner ? '' : 'display: none;'; ?>">
            <p id="no-banner-text" style="color: #888; margin-top: 10px; <?php echo $current_banner ? 'display: none;' : ''; ?>">Belum ada foto banner yang diunggah.</p>
        </div>

        <button type="submit" id="submitBtnBanner" class="btn-submit"><i class="fas fa-upload"></i> Unggah Banner</button>
    </form>
</div>

---

<div class="card form-section">
    <h2><i class="fas fa-user-plus"></i> Tambah Data Guru Baru</h2>
    <form method="post" enctype="multipart/form-data" id="tambahGuruForm">
        <label for="nama_guru" style="display: block; margin-top: 10px; font-weight: bold;">Nama Guru</label>
        <input type="text" id="nama_guru" name="nama_guru" required style="width: 100%; padding: 8px; margin-top: 5px; border: 1px solid #ccc; border-radius: 4px; box-sizing: border-box;">
        
        <label for="jabatan" style="display: block; margin-top: 10px; font-weight: bold;">Jabatan</label>
        <input type="text" id="jabatan" name="jabatan" required style="width: 100%; padding: 8px; margin-top: 5px; border: 1px solid #ccc; border-radius: 4px; box-sizing: border-box;">
        
        <label for="foto_guru" style="display: block; margin-top: 10px; font-weight: bold;">Foto Guru Individual</label>
        <input type="file" id="foto_guru" name="foto_guru" accept="image/*" required style="width: 100%; padding: 8px; margin-top: 5px; border: 1px solid #ccc; border-radius: 4px; box-sizing: border-box;">
        
        <button type="submit" id="submitBtnGuru" class="btn-submit"><i class="fas fa-plus-circle"></i> Tambah Guru</button>
    </form>
</div>

---

<div class="card">
    <h2><i class="fas fa-list"></i> Daftar Tenaga Pendidik</h2>
    <table id="guruTable">
        <thead>
            <tr>
                <th>ID</th>
                <th>Nama</th>
                <th>Jabatan</th>
                <th>Aksi</th>
            </tr>
        </thead>
        <tbody>
            <?php if (!empty($daftar_guru)): ?>
                <?php foreach ($daftar_guru as $guru): ?>
                <tr id="guru-row-<?php echo $guru['id']; ?>">
                    <td><?php echo $guru['id']; ?></td>
                    <td><?php echo htmlspecialchars($guru['nama_guru']); ?></td>
                    <td><?php echo htmlspecialchars($guru['jabatan']); ?></td>
                    <td>
                        <a href="editprofilguru.php?id=<?php echo $guru['id']; ?>" style="color: #4CAF50; margin-right: 10px;">Edit</a>
                        <a href="#" class="delete-guru-ajax" data-id="<?php echo $guru['id']; ?>" style="color: red; text-decoration: none;">Hapus</a>
                    </td>
                </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr id="no-data-row"><td colspan="4" style="text-align: center;">Belum ada data guru.</td></tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<?php $conn->close(); ?>

<script>
    $(document).ready(function() {
        
        // Fungsi untuk menampilkan pesan alert
        function showAlert(message, type) {
            var alertArea = $('#alert-area');
            var alertMessage = $('#alert-message');
            
            alertMessage.removeClass('success error').addClass(type).html(message);
            alertArea.fadeIn().delay(5000).fadeOut(); // Tampilkan 5 detik
        }
        
        // --- 1. AJAX Form Tambah Guru (Menghilangkan Scroll Jump) ---
        $('#tambahGuruForm').on('submit', function(e) {
            e.preventDefault();
            var form = $(this);
            var btn = $('#submitBtnGuru');
            var formData = new FormData(this); 

            formData.append('action', 'tambah_guru_ajax'); // Penanda untuk PHP AJAX handler

            btn.attr('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Menambahkan...');

            $.ajax({
                type: 'POST',
                url: 'adminprofilguru.php', // Mengirim ke halaman yang sama
                data: formData,
                contentType: false, 
                processData: false, 
                dataType: 'json', 
                success: function(response) {
                    if (response.status === 'success') {
                        // 1. Reset Form
                        form[0].reset();
                        
                        // 2. Tambahkan Baris Baru ke Tabel
                        var newRow = '<tr id="guru-row-' + response.new_row.id + '">' +
                            '<td>' + response.new_row.id + '</td>' +
                            '<td>' + response.new_row.nama_guru + '</td>' +
                            '<td>' + response.new_row.jabatan + '</td>' +
                            '<td>' +
                                '<a href="editprofilguru.php?id=' + response.new_row.id + '" style="color: #4CAF50; margin-right: 10px;">Edit</a>' +
                                '<a href="#" class="delete-guru-ajax" data-id="' + response.new_row.id + '" style="color: red; text-decoration: none;">Hapus</a>' +
                            '</td>' +
                            '</tr>';
                            
                        // Hapus baris "Belum ada data guru." jika ada
                        $('#no-data-row').remove();
                        
                        // Tambahkan baris baru ke tbody tabel
                        $('#guruTable tbody').append(newRow); 

                        // 3. Tampilkan Notifikasi
                        showAlert(response.message, 'success');

                    } else {
                        showAlert('Gagal menambah guru: ' + response.message, 'error');
                    }
                    btn.attr('disabled', false).html('<i class="fas fa-plus-circle"></i> Tambah Guru');
                },
                error: function(xhr, status, error) {
                    showAlert('Gagal menambah guru via AJAX. Cek konsol.', 'error');
                    console.error("AJAX Error:", status, error);
                    btn.attr('disabled', false).html('<i class="fas fa-plus-circle"></i> Tambah Guru');
                }
            });
        });
        
        // --- 2. AJAX Form Upload Banner ---
        $('#bannerForm').on('submit', function(e) {
            e.preventDefault();
            var form = $(this);
            var btn = $('#submitBtnBanner');
            var formData = new FormData(this);

            btn.attr('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Mengunggah...');
            formData.append('action', 'upload_banner_ajax'); 

            $.ajax({
                type: 'POST',
                url: 'adminprofilguru.php',
                data: formData,
                contentType: false, 
                processData: false, 
                dataType: 'json', 
                success: function(response) {
                    if (response.status === 'success') {
                        // Update preview gambar tanpa reload
                        $('#banner-preview-img').attr('src', response.new_banner_url).show();
                        $('#no-banner-text').hide();
                        
                        showAlert(response.message, 'success');
                    } else {
                        showAlert('Gagal mengunggah banner: ' + response.message, 'error');
                    }
                    btn.attr('disabled', false).html('<i class="fas fa-upload"></i> Unggah Banner');
                },
                error: function(xhr, status, error) {
                    showAlert('Gagal mengunggah banner via AJAX.', 'error');
                    btn.attr('disabled', false).html('<i class="fas fa-upload"></i> Unggah Banner');
                }
            });
        });

        // --- 3. AJAX Hapus Guru ---
        $(document).on('click', '.delete-guru-ajax', function(e) {
            e.preventDefault();
            var guruId = $(this).data('id');
            var $row = $('#guru-row-' + guruId);

            if (confirm('Yakin ingin menghapus guru ini?')) {
                $.ajax({
                    type: 'POST',
                    url: 'adminprofilguru.php',
                    data: { action: 'hapus_guru_ajax', id: guruId },
                    dataType: 'json', 
                    success: function(response) {
                        if (response.status === 'success') {
                            // Hapus baris dari tabel
                            $row.fadeOut(300, function() {
                                $(this).remove();
                                
                                // Cek jika tabel kosong, tampilkan pesan 'Belum ada data'
                                if ($('#guruTable tbody tr').length === 0) {
                                    var noDataRow = '<tr id="no-data-row"><td colspan="4" style="text-align: center;">Belum ada data guru.</td></tr>';
                                    $('#guruTable tbody').append(noDataRow);
                                }
                            });
                            showAlert(response.message, 'success');
                        } else {
                            showAlert('Gagal menghapus guru: ' + response.message, 'error');
                        }
                    },
                    error: function() {
                        showAlert('Gagal menghapus guru via AJAX.', 'error');
                    }
                });
            }
        });
    });
</script>