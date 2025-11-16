<?php
// FILE: admin/adminprofilsekolah.php (FINAL - CARD STYLE DI DALAM DASHBOARD + AJAX)
include '../koneksi.php'; // Koneksi database

// ----------------------------------------------------------------------
// LOGIKA AMBIL DATA PROFIL SEKOLAH (Untuk Tampilan)
// ----------------------------------------------------------------------
$profil_edit = [];
$stmt_select = $conn->prepare("SELECT visi, misi, sejarah_singkat FROM profil_sekolah WHERE id = 1");
$stmt_select->execute();
$result_select = $stmt_select->get_result();
if ($result_select->num_rows > 0) {
    $profil_edit = $result_select->fetch_assoc();
} else {
    // Jika data belum ada, inisialisasi baris pertama agar UPDATE bisa dilakukan
    $conn->query("INSERT INTO profil_sekolah (id, visi, misi, sejarah_singkat) VALUES (1, '', '', '')");
    $profil_edit = ['visi' => '', 'misi' => '', 'sejarah_singkat' => ''];
}
$stmt_select->close();

// ----------------------------------------------------------------------
// LOGIKA UPDATE DATA PROFIL SEKOLAH (AJAX Handler)
// ----------------------------------------------------------------------
if (isset($_POST['action']) && $_POST['action'] == 'update_profil_ajax') {
    header('Content-Type: application/json');
    $response = ['status' => 'error', 'message' => 'Terjadi kesalahan tidak dikenal.'];
    
    // Ambil data dari form
    $visi = $_POST['visi'];
    $misi = $_POST['misi'];
    $sejarah_singkat = $_POST['sejarah_singkat'];

    // Query UPDATE: Kita asumsikan hanya ada 1 baris data profil sekolah dengan id=1
    $sql = "UPDATE profil_sekolah SET 
                visi=?, 
                misi=?, 
                sejarah_singkat=? 
            WHERE id = 1";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sss", $visi, $misi, $sejarah_singkat);

    if ($stmt->execute()) {
        $response['status'] = 'success';
        $response['message'] = 'Data Profil Sekolah berhasil diperbarui!';
    } else {
        $response['message'] = 'Gagal menyimpan data Profil Sekolah: ' . $conn->error;
    }
    
    $stmt->close();
    echo json_encode($response);
    exit();
}

$conn->close();
?>

<style>
/* CSS Tambahan untuk Card dan Styling Form */
.card {
    background: #fff;
    border-radius: 8px;
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1); /* Efek Card */
    padding: 20px;
    margin-bottom: 20px;
}
.card h1, .card h2 {
    color: #333;
    border-bottom: 2px solid #f2f2f2;
    padding-bottom: 10px;
    margin-bottom: 15px;
}
form label {
    display: block;
    margin-top: 15px;
    margin-bottom: 5px;
    font-weight: bold;
    color: #555;
}
form input[type="text"], form textarea {
    width: 100%;
    padding: 10px;
    margin-bottom: 10px;
    border: 1px solid #ddd;
    border-radius: 4px;
    box-sizing: border-box;
    font-size: 14px;
}
form textarea {
    min-height: 150px;
    resize: vertical;
}
.btn-submit {
    background-color: #4CAF50;
    color: white;
    padding: 12px 20px;
    border: none;
    border-radius: 4px;
    cursor: pointer;
    margin-top: 20px;
    transition: background-color 0.3s;
}
.btn-submit:hover {
    background-color: #45a049;
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
</style>

<div class="card" id="alert-area" style="display: none;">
    <div class="alert" id="alert-message"></div>
</div>

<div class="card">
    <h2><i class="fas fa-university"></i> Manajemen Profil Sekolah</h2>
    
    <form action="adminprofilsekolah.php" method="POST" id="profilForm">
        
        <label for="visi">Visi Sekolah</label>
        <textarea id="visi" name="visi" required><?php echo htmlspecialchars($profil_edit['visi'] ?? ''); ?></textarea>
        
        <label for="misi">Misi Sekolah</label>
        <textarea id="misi" name="misi" required><?php echo htmlspecialchars($profil_edit['misi'] ?? ''); ?></textarea>
        
        <label for="sejarah_singkat">Sejarah Singkat</label>
        <textarea id="sejarah_singkat" name="sejarah_singkat" required><?php echo htmlspecialchars($profil_edit['sejarah_singkat'] ?? ''); ?></textarea>
        
        <button type="submit" name="submit_profil" id="submitBtn" class="btn-submit"><i class="fas fa-save"></i> Simpan Perubahan</button>
    </form>
</div>

<script>
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
        
        // --- AJAX Form Submission (Mencegah Scroll Jump) ---
        $('#profilForm').on('submit', function(e) {
            e.preventDefault();
            var form = $(this);
            var btn = $('#submitBtn');
            var formData = form.serializeArray(); 

            // Tambahkan identifier AJAX untuk PHP
            formData.push({name: 'action', value: 'update_profil_ajax'});

            btn.attr('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Menyimpan...');

            $.ajax({
                type: 'POST',
                url: 'adminprofilsekolah.php',
                data: formData,
                dataType: 'json', 
                success: function(response) {
                    if (response.status === 'success') {
                        showAlert(response.message, 'success');
                    } else {
                        showAlert('Gagal menyimpan profil: ' + response.message, 'error');
                    }
                    btn.attr('disabled', false).html('<i class="fas fa-save"></i> Simpan Perubahan');
                },
                error: function(xhr, status, error) {
                    showAlert('Gagal menyimpan profil via AJAX. Cek konsol.', 'error');
                    console.error("AJAX Error:", status, error);
                    btn.attr('disabled', false).html('<i class="fas fa-save"></i> Simpan Perubahan');
                }
            });
        });
    });
</script>