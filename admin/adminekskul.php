<?php
// FILE: admin/adminekskul.php (FINAL - CARD STYLE + FULL AJAX + PRESTASI + MATERI UTAMA)
include '../koneksi.php'; // Sesuaikan path koneksi

$uploadDir = '../uploads/ekskul/'; // Folder tempat menyimpan gambar kegiatan
if (!is_dir($uploadDir)) {
    mkdir($uploadDir, 0777, true);
}

/**
 * Fungsi untuk membuat slug yang aman untuk URL/nama file.
 */
function create_file_name($text) {
    // 1. Ubah ke huruf kecil
    $text = strtolower($text);
    // 2. Hapus karakter non-alphanumeric, kecuali spasi dan strip
    $text = preg_replace('/[^\\w\\s-]/', '', $text);
    // 3. Ganti spasi, underscore, dan strip ganda dengan strip tunggal
    $text = preg_replace('/[\\s_-]+/', '-', $text);
    // 4. Potong strip di awal/akhir
    $text = trim($text, '-');
    // 5. Tambahkan ekstensi .php
    return $text . ".php";
}

$ekskul_edit = null;
$action_type = 'Tambah';

// --- LOGIKA LOAD DATA UNTUK EDIT ---
if (isset($_GET['action']) && $_GET['action'] == 'edit' && isset($_GET['id'])) {
    $id = intval($_GET['id']);
    // Ambil kolom 'prestasi' dan 'materi_utama'
    $stmt = $conn->prepare("SELECT id, nama_ekskul, deskripsi, jadwal, pembina, kelompok, file_name, gambar_kegiatan, prestasi, materi_utama FROM ekstrakurikuler WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    $ekskul_edit = $result->fetch_assoc();
    $stmt->close();
    
    if ($ekskul_edit) {
        $action_type = 'Edit';
    } else {
        header("Location: adminekskul.php");
        exit();
    }
}


// ----------------------------------------------------------------------
// AJAX HANDLER 1: TAMBAH / EDIT EKSTRAKURIKULER
// ----------------------------------------------------------------------
if (isset($_POST['action']) && $_POST['action'] == 'submit_ekskul_ajax') {
    header('Content-Type: application/json');
    $response = ['status' => 'error', 'message' => 'Terjadi kesalahan tidak dikenal.'];

    $id = intval($_POST['id'] ?? 0);
    $nama_ekskul = $_POST['nama_ekskul'];
    $deskripsi = $_POST['deskripsi'];
    $jadwal = $_POST['jadwal'];
    $pembina = $_POST['pembina'];
    $kelompok = $_POST['kelompok'];
    $prestasi = $_POST['prestasi'] ?? '';
    $materi_utama = $_POST['materi_utama'] ?? ''; // AMBIL DATA MATERI UTAMA BARU
    $current_image = $_POST['current_image'] ?? ''; 
    
    $file_name = create_file_name($nama_ekskul);
    $gambar_kegiatan = $current_image;
    
    // --- Proses Upload Gambar (Sama seperti sebelumnya) ---
    if (isset($_FILES['gambar_kegiatan']) && $_FILES['gambar_kegiatan']['error'] == 0) {
        $imageFileType = strtolower(pathinfo($_FILES["gambar_kegiatan"]["name"], PATHINFO_EXTENSION));
        $unique_filename = time() . '-' . $file_name . '.' . $imageFileType;
        $final_target = $uploadDir . $unique_filename;
        
        if (move_uploaded_file($_FILES["gambar_kegiatan"]["tmp_name"], $final_target)) {
            $gambar_kegiatan = "uploads/ekskul/" . $unique_filename;
            
            if ($id > 0 && $current_image && file_exists("../" . $current_image)) {
                unlink("../" . $current_image);
            }
        } else {
            $response['message'] = 'Gagal mengunggah gambar kegiatan.';
            echo json_encode($response);
            exit();
        }
    }

    // --- Query Database (DENGAN KOLOM PRESTASI DAN MATERI_UTAMA) ---
    if ($id > 0) {
        // UPDATE
        $sql = "UPDATE ekstrakurikuler SET nama_ekskul=?, deskripsi=?, jadwal=?, pembina=?, kelompok=?, prestasi=?, materi_utama=?, file_name=?, gambar_kegiatan=? WHERE id=?";
        $stmt = $conn->prepare($sql);
        // Tambahkan 's' untuk prestasi, dan 's' untuk materi_utama
        $stmt->bind_param("sssssssssi", $nama_ekskul, $deskripsi, $jadwal, $pembina, $kelompok, $prestasi, $materi_utama, $file_name, $gambar_kegiatan, $id); 
        
        if ($stmt->execute()) {
            $response['status'] = 'success';
            $response['message'] = 'Ekstrakurikuler berhasil diperbarui!';
            $response['is_update'] = true;
            $response['id'] = $id;
            $response['data_row'] = [
                'nama_ekskul' => htmlspecialchars($nama_ekskul),
                'file_name' => htmlspecialchars($file_name),
                'jadwal' => htmlspecialchars($jadwal),
                'prestasi' => htmlspecialchars($prestasi),
                'materi_utama' => htmlspecialchars($materi_utama) // Kirim kembali materi_utama
            ];
        } else {
            $response['message'] = "Gagal mengupdate database: " . $stmt->error;
        }
    } else {
        // INSERT
        $sql = "INSERT INTO ekstrakurikuler (nama_ekskul, deskripsi, jadwal, pembina, kelompok, prestasi, materi_utama, file_name, gambar_kegiatan) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
         // Tambahkan 's' untuk prestasi dan 's' untuk materi_utama (total 9 's')
        $stmt->bind_param("sssssssss", $nama_ekskul, $deskripsi, $jadwal, $pembina, $kelompok, $prestasi, $materi_utama, $file_name, $gambar_kegiatan); 
        
        if ($stmt->execute()) {
            $last_id = $conn->insert_id;
            $response['status'] = 'success';
            $response['message'] = 'Ekstrakurikuler baru berhasil ditambahkan!';
            $response['is_update'] = false;
            $response['id'] = $last_id;
            $response['data_row'] = [
                'nama_ekskul' => htmlspecialchars($nama_ekskul),
                'file_name' => htmlspecialchars($file_name),
                'jadwal' => htmlspecialchars($jadwal),
                'prestasi' => htmlspecialchars($prestasi),
                'materi_utama' => htmlspecialchars($materi_utama) // Kirim kembali materi_utama
            ];
        } else {
            $response['message'] = "Gagal menyimpan ke database: " . $stmt->error;
        }
    }
    
    $stmt->close();
    echo json_encode($response);
    exit();
}

// ----------------------------------------------------------------------
// AJAX HANDLER 2: HAPUS EKSTRAKURIKULER (Tidak berubah)
// ----------------------------------------------------------------------
if (isset($_POST['action']) && $_POST['action'] == 'hapus_ekskul_ajax') {
    header('Content-Type: application/json');
    $response = ['status' => 'error', 'message' => 'Gagal menghapus ekskul.'];
    $id = intval($_POST['id'] ?? 0);
    
    // 1. Ambil path gambar untuk dihapus
    $stmt = $conn->prepare("SELECT gambar_kegiatan FROM ekstrakurikuler WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    $stmt->close();

    if ($row) {
        $imagePath = '../' . $row['gambar_kegiatan'];
        
        // 2. Hapus dari database
        $stmt_del = $conn->prepare("DELETE FROM ekstrakurikuler WHERE id = ?");
        $stmt_del->bind_param("i", $id);
        
        if ($stmt_del->execute()) {
            // 3. Hapus file fisik
            if ($row['gambar_kegiatan'] && file_exists($imagePath)) {
                unlink($imagePath);
            }
            $response['status'] = 'success';
            $response['message'] = "Sukses: Ekstrakurikuler berhasil dihapus!";
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
// TAMPILAN UTAMA (Load Data List)
// ----------------------------------------------------------------------
// Ambil kolom prestasi dan materi_utama untuk ditampilkan di tabel
$ekskul_list = $conn->query("SELECT id, nama_ekskul, file_name, jadwal, prestasi, materi_utama FROM ekstrakurikuler ORDER BY id ASC");
?>

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
    transition: background-color 0.3s; margin-top: 15px;
}
.btn-submit:hover { background-color: #1B5E20; }

table { width: 100%; border-collapse: collapse; margin-top: 20px; }
th, td { padding: 12px; border: 1px solid #ddd; text-align: left; font-size: 0.9em; }
th { background-color: #f2f2f2; }
.image-preview { margin-top: 10px; }
.image-preview img { max-width: 150px; height: auto; border: 1px solid #ddd; padding: 5px; border-radius: 4px; }
</style>

<div class="card" id="alert-area" style="display: none;">
    <div class="alert" id="alert-message"></div>
</div>

<div class="card">
    <h1><i class="fas fa-running"></i> Administrasi Ekstrakurikuler</h1>
</div>

<div class="card">
    <h2><i class="fas fa-<?php echo ($action_type == 'Edit') ? 'edit' : 'plus-circle'; ?>"></i> <?php echo $action_type; ?> Ekstrakurikuler</h2>
    
    <form method="POST" enctype="multipart/form-data" id="ekskulForm">
        
        <input type="hidden" name="id" value="<?php echo htmlspecialchars($ekskul_edit['id'] ?? 0); ?>">
        <input type="hidden" name="current_image" value="<?php echo htmlspecialchars($ekskul_edit['gambar_kegiatan'] ?? ''); ?>">

        <label for="nama_ekskul">Nama Ekstrakurikuler</label>
        <input type="text" id="nama_ekskul" name="nama_ekskul" value="<?php echo htmlspecialchars($ekskul_edit['nama_ekskul'] ?? ''); ?>" required>
        
        <label for="kelompok">Kelompok (e.g., Seni/Olahraga/Akademik)</label>
        <input type="text" id="kelompok" name="kelompok" value="<?php echo htmlspecialchars($ekskul_edit['kelompok'] ?? ''); ?>">
        
        <label for="pembina">Nama Pembina</label>
        <input type="text" id="pembina" name="pembina" value="<?php echo htmlspecialchars($ekskul_edit['pembina'] ?? ''); ?>">

        <label for="jadwal">Jadwal Singkat (e.g., Setiap Jumat, 14.00)</label>
        <input type="text" id="jadwal" name="jadwal" value="<?php echo htmlspecialchars($ekskul_edit['jadwal'] ?? ''); ?>">

        <label for="deskripsi">Deskripsi Lengkap</label>
        <textarea id="deskripsi" name="deskripsi" rows="4" required><?php echo htmlspecialchars($ekskul_edit['deskripsi'] ?? ''); ?></textarea>
        
        <label for="prestasi">Daftar Prestasi (Pisahkan dengan baris baru untuk memudahkan pembacaan)</label>
        <textarea id="prestasi" name="prestasi" rows="4"><?php echo htmlspecialchars($ekskul_edit['prestasi'] ?? ''); ?></textarea>
        
        <label for="materi_utama">Materi Utama Pembelajaran</label>
        <textarea id="materi_utama" name="materi_utama" rows="4"><?php echo htmlspecialchars($ekskul_edit['materi_utama'] ?? ''); ?></textarea>
        <label for="gambar_kegiatan">Gambar Kegiatan <?php echo ($action_type == 'Edit' ? '(Abaikan jika tidak diubah)' : ''); ?></label>
        <input type="file" id="gambar_kegiatan" name="gambar_kegiatan" accept="image/*" <?php echo ($action_type == 'Tambah' ? 'required' : ''); ?>>

        <?php if ($action_type == 'Edit' && !empty($ekskul_edit['gambar_kegiatan'])): ?>
            <div class="image-preview">
                <p>Gambar saat ini:</p>
                <img src="../<?php echo htmlspecialchars($ekskul_edit['gambar_kegiatan']); ?>" alt="Gambar Ekskul">
            </div>
        <?php endif; ?>

        <button type="submit" id="submitBtn" class="btn-submit"><i class="fas fa-save"></i> <?php echo $action_type; ?> Ekskul</button>
        <?php if ($action_type == 'Edit'): ?>
            <a href="adminekskul.php" style="margin-left: 10px; color: #333; text-decoration: none;">Batal Edit</a>
        <?php endif; ?>
    </form>
</div>

<div class="card">
    <h2><i class="fas fa-list"></i> Daftar Ekstrakurikuler</h2>
    <table id="ekskulTable">
        <thead>
            <tr>
                <th>ID</th>
                <th>Nama Ekskul</th>
                <th>Nama File</th>
                <th>Jadwal</th>
                <th>Prestasi Singkat</th>
                <th>Materi Utama Singkat</th> <th>Aksi</th>
            </tr>
        </thead>
        <tbody>
            <?php if ($ekskul_list->num_rows > 0): ?>
                <?php while($row = $ekskul_list->fetch_assoc()): 
                    // Tampilan Singkat untuk Prestasi dan Materi
                    $short_prestasi = empty($row['prestasi']) ? '-' : (strlen($row['prestasi']) > 30 ? substr(htmlspecialchars($row['prestasi']), 0, 30) . '...' : htmlspecialchars($row['prestasi']));
                    $short_materi = empty($row['materi_utama']) ? '-' : (strlen($row['materi_utama']) > 30 ? substr(htmlspecialchars($row['materi_utama']), 0, 30) . '...' : htmlspecialchars($row['materi_utama']));
                ?>
                <tr id="ekskul-row-<?php echo $row['id']; ?>">
                    <td><?php echo $row['id']; ?></td>
                    <td><?php echo htmlspecialchars($row['nama_ekskul']); ?></td>
                    <td><?php echo htmlspecialchars($row['file_name'] ?? '-'); ?></td>
                    <td><?php echo htmlspecialchars($row['jadwal']); ?></td>
                    <td title="<?php echo htmlspecialchars($row['prestasi'] ?? '-'); ?>"><?php echo $short_prestasi; ?></td>
                    <td title="<?php echo htmlspecialchars($row['materi_utama'] ?? '-'); ?>"><?php echo $short_materi; ?></td>
                    <td class="action-links">
                        <a href="adminekskul.php?action=edit&id=<?php echo $row['id']; ?>" style="color: #4CAF50; margin-right: 10px;">Edit</a>
                        <a href="#" class="delete-ekskul-ajax" data-id="<?php echo $row['id']; ?>" style="color: red; text-decoration: none;">Hapus</a>
                    </td>
                </tr>
                <?php endwhile; ?>
            <?php else: ?>
                <tr id="no-data-row"><td colspan="7" style="text-align: center;">Belum ada data ekstrakurikuler.</td></tr> 
            <?php endif; ?>
        </tbody>
    </table>
</div>

<?php $conn->close(); ?>

<script>
    $(document).ready(function() {
        
        function showAlert(message, type) {
            var alertArea = $('#alert-area');
            var alertMessage = $('#alert-message');
            
            alertMessage.removeClass('success error').addClass(type).html(message);
            
            $('html, body').animate({
                scrollTop: alertArea.offset().top - 20
            }, 300); 

            alertArea.fadeIn().delay(5000).fadeOut();
        }
        
        // Fungsi pembantu untuk memotong teks
        function getShortText(text) {
            if (!text) return '-';
            var safeText = $('<div>').text(text).html(); // Encode HTML entities
            return (safeText.length > 30) ? safeText.substring(0, 30) + '...' : safeText;
        }

        // --- 1. AJAX Form Tambah/Edit Ekskul (Diperbarui untuk Prestasi & Materi Utama) ---
        $('#ekskulForm').on('submit', function(e) {
            e.preventDefault();
            var form = $(this);
            var btn = $('#submitBtn');
            var is_editing = form.find('input[name="id"]').val() > 0;
            var original_btn_text = btn.text();
            
            var formData = new FormData(this); 
            formData.append('action', 'submit_ekskul_ajax'); 

            btn.attr('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Memproses...');

            $.ajax({
                type: 'POST',
                url: 'adminekskul.php', 
                data: formData,
                contentType: false, 
                processData: false, 
                dataType: 'json', 
                success: function(response) {
                    if (response.status === 'success') {
                        var rowData = response.data_row;
                        
                        var shortPrestasi = getShortText(rowData.prestasi);
                        var shortMateri = getShortText(rowData.materi_utama); // NEW
                        
                        var newRowHtml = '<tr id="ekskul-row-' + response.id + '">' +
                            '<td>' + response.id + '</td>' +
                            '<td>' + rowData.nama_ekskul + '</td>' +
                            '<td>' + rowData.file_name + '</td>' +
                            '<td>' + rowData.jadwal + '</td>' +
                            // Kolom Prestasi
                            '<td title="' + rowData.prestasi + '">' + shortPrestasi + '</td>' + 
                            // Kolom Materi Utama
                            '<td title="' + rowData.materi_utama + '">' + shortMateri + '</td>' + // NEW
                            '<td class="action-links">' +
                                '<a href="adminekskul.php?action=edit&id=' + response.id + '" style="color: #4CAF50; margin-right: 10px;">Edit</a>' +
                                '<a href="#" class="delete-ekskul-ajax" data-id="' + response.id + '" style="color: red; text-decoration: none;">Hapus</a>' +
                            '</td>' +
                            '</tr>';

                        if (response.is_update) {
                            // Update baris yang sudah ada dan kembali ke mode tambah
                            $('#ekskul-row-' + response.id).replaceWith(newRowHtml);
                            window.location.href = 'adminekskul.php'; 
                        } else {
                            // Tambah baris baru
                            $('#no-data-row').remove();
                            $('#ekskulTable tbody').append(newRowHtml);
                            form[0].reset(); // Reset form setelah tambah
                        }
                        
                        showAlert(response.message, 'success');

                    } else {
                        showAlert('Gagal memproses ekskul: ' + response.message, 'error');
                    }
                    btn.attr('disabled', false).html(original_btn_text);
                },
                error: function(xhr, status, error) {
                    showAlert('Gagal memproses ekskul via AJAX. Cek konsol.', 'error');
                    console.error("AJAX Error:", status, error);
                    btn.attr('disabled', false).html(original_btn_text);
                }
            });
        });
        
        // --- 2. AJAX Hapus Ekskul (Diperbarui colspan) ---
        $(document).on('click', '.delete-ekskul-ajax', function(e) {
            e.preventDefault();
            var ekskulId = $(this).data('id');
            var $row = $('#ekskul-row-' + ekskulId);

            if (confirm('Yakin ingin menghapus ekstrakurikuler ini?')) {
                $.ajax({
                    type: 'POST',
                    url: 'adminekskul.php',
                    data: { action: 'hapus_ekskul_ajax', id: ekskulId },
                    dataType: 'json', 
                    success: function(response) {
                        if (response.status === 'success') {
                            $row.fadeOut(300, function() {
                                $(this).remove();
                                
                                // Cek jika tabel kosong, gunakan colspan 7
                                if ($('#ekskulTable tbody tr').length === 0) {
                                    var noDataRow = '<tr id="no-data-row"><td colspan="7" style="text-align: center;">Belum ada data ekstrakurikuler.</td></tr>';
                                    $('#ekskulTable tbody').append(noDataRow);
                                }
                            });
                            showAlert(response.message, 'success');
                        } else {
                            showAlert('Gagal menghapus ekskul: ' + response.message, 'error');
                        }
                    },
                    error: function() {
                        showAlert('Gagal menghapus ekskul via AJAX.', 'error');
                    }
                });
            }
        });

    });
</script>