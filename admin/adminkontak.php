<?php
// FILE: admin/adminkontak.php (FINAL AJAX SAVE: Mencegah Reload Halaman)
include '../koneksi.php'; // Path koneksi (naik satu folder ke root)

// ----------------------------------------------------------------------
// LOGIKA 1: AJAX HANDLER UNTUK UPDATE DATA KONTAK
// ----------------------------------------------------------------------
if (isset($_POST['action']) && $_POST['action'] == 'update_kontak_ajax') {
    header('Content-Type: application/json');
    $response = ['status' => 'error', 'message' => 'Terjadi kesalahan tidak dikenal.'];

    // Ambil data dari form POST
    $alamat = $_POST['alamat'];
    $telepon_1 = $_POST['telepon_1'];
    $telepon_2 = $_POST['telepon_2'];
    $email = $_POST['email'];
    $facebook_url = $_POST['facebook_url'] ?? '';
    $twitter_url = $_POST['twitter_url'] ?? '';
    $instagram_url = $_POST['instagram_url'] ?? '';
    $youtube_url = $_POST['youtube_url'] ?? '';
    $tiktok_url = $_POST['tiktok_url'] ?? '';

    // Query UPDATE: Kita asumsikan hanya ada 1 baris data kontak dengan id=1
    $sql = "UPDATE kontak SET 
                alamat=?, 
                telepon_1=?, 
                telepon_2=?, 
                email=?, 
                facebook_url=?, 
                twitter_url=?, 
                instagram_url=?, 
                youtube_url=?, 
                tiktok_url=? 
            WHERE id = 1";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param(
        "sssssssss", 
        $alamat, 
        $telepon_1, 
        $telepon_2, 
        $email, 
        $facebook_url, 
        $twitter_url, 
        $instagram_url, 
        $youtube_url, 
        $tiktok_url
    );

    if ($stmt->execute()) {
        $response['status'] = 'success';
        $response['message'] = "Data kontak berhasil diperbarui!";
    } else {
        $response['message'] = "Gagal menyimpan data Kontak: " . $stmt->error;
    }
    $stmt->close();

    echo json_encode($response);
    exit();
}


// ----------------------------------------------------------------------
// LOGIKA 2: AMBIL DATA UNTUK TAMPILAN
// ----------------------------------------------------------------------
$kontak_edit = [];
if (isset($conn) && $conn) {
    $result = $conn->query("SELECT * FROM kontak WHERE id = 1"); 
    if ($result && $result->num_rows > 0) {
        $kontak_edit = $result->fetch_assoc();
    }
}
?>

<style>
    /* * =========================================================
    * CARD STYLE DENGAN BACKGROUND PUTIH & SHADOW
    * =========================================================
    */
    .card { 
        background: white; 
        padding: 20px; 
        border-radius: 8px; 
        box-shadow: 0 4px 12px rgba(0,0,0,0.1); 
        margin-bottom: 25px; 
    }
    .page-title { 
        color: #333;
        border-bottom: 2px solid #ddd; 
        padding-bottom: 10px; 
        margin-bottom: 20px; 
        font-size: 1.8em;
    }
    h3 {
        color: #4CAF50; 
        border-bottom: 1px solid #eee;
        padding-bottom: 5px;
        margin-bottom: 15px;
    }
    form label { display: block; margin: 10px 0 5px; font-weight: bold; }
    form input[type="text"], form textarea { 
        width: 100%; 
        padding: 10px; 
        margin-bottom: 15px; 
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
        transition: background-color 0.3s;
        margin-top: 15px; 
    }
    .btn-submit:hover { background-color: #1B5E20; }
    
    /* Pesan Status */
    .success-message { color: #155724; font-weight: bold; margin-bottom: 15px; background: #d4edda; padding: 10px; border-radius: 4px; border: 1px solid #c3e6cb;}
    .error-message { color: #721c24; font-weight: bold; margin-bottom: 15px; background: #f8d7da; padding: 10px; border-radius: 4px; border: 1px solid #f5c6cb;}
</style>

<div style="padding: 20px;"> 
    
    <div class="card" id="ajax-alert-area" style="display: none; margin-bottom: 15px;">
        <p id="ajax-alert-message" class="error-message"></p>
    </div>

    <div class="card">
        <h2 class="page-title"><i class="fas fa-envelope"></i> Manajemen Informasi Kontak Sekolah</h2>
    </div>


    <form method="POST" id="kontakForm">
        
        <div class="card">
            <h3><i class="fas fa-phone-square-alt"></i> Detail Kontak Sekolah</h3>
            
            <label for="alamat">Alamat Lengkap</label>
            <textarea id="alamat" name="alamat" required><?php echo htmlspecialchars($kontak_edit['alamat'] ?? ''); ?></textarea>
            
            <label for="telepon_1">Telepon 1</label>
            <input type="text" id="telepon_1" name="telepon_1" value="<?php echo htmlspecialchars($kontak_edit['telepon_1'] ?? ''); ?>" required>
            
            <label for="telepon_2">Telepon 2 (Opsional)</label>
            <input type="text" id="telepon_2" name="telepon_2" value="<?php echo htmlspecialchars($kontak_edit['telepon_2'] ?? ''); ?>">
            
            <label for="email">Email</label>
            <input type="text" id="email" name="email" value="<?php echo htmlspecialchars($kontak_edit['email'] ?? ''); ?>" required>
        </div>
        
        <div class="card">
            <h3><i class="fas fa-share-alt-square"></i> Link Sosial Media (URL Lengkap)</h3>
            
            <label for="facebook_url">Facebook URL</label>
            <input type="text" id="facebook_url" name="facebook_url" value="<?php echo htmlspecialchars($kontak_edit['facebook_url'] ?? ''); ?>">
            
            <label for="twitter_url">Twitter URL</label>
            <input type="text" id="twitter_url" name="twitter_url" value="<?php echo htmlspecialchars($kontak_edit['twitter_url'] ?? ''); ?>">
            
            <label for="instagram_url">Instagram URL</label>
            <input type="text" id="instagram_url" name="instagram_url" value="<?php echo htmlspecialchars($kontak_edit['instagram_url'] ?? ''); ?>">
            
            <label for="youtube_url">Youtube URL</label>
            <input type="text" id="youtube_url" name="youtube_url" value="<?php echo htmlspecialchars($kontak_edit['youtube_url'] ?? ''); ?>">
            
            <label for="tiktok_url">TikTok URL</label>
            <input type="text" id="tiktok_url" name="tiktok_url" value="<?php echo htmlspecialchars($kontak_edit['tiktok_url'] ?? ''); ?>">
            
        </div>
        
        <button type="submit" id="submitBtn" class="btn-submit"><i class="fas fa-save"></i> Simpan Semua Perubahan Kontak</button>
    </form>
</div>

<script>
    // Pastikan jQuery sudah dimuat sebelum script ini
    $(document).ready(function() {
        
        // Fungsi untuk menampilkan pesan alert AJAX
        function showAlert(message, type) {
            var alertArea = $('#ajax-alert-area');
            var alertMessage = $('#ajax-alert-message');
            
            alertMessage.removeClass('success-message error-message').addClass(type).html(message);
            alertArea.show();
            
            // Scroll ke atas agar pesan terlihat
            $('html, body').animate({
                scrollTop: alertArea.offset().top - 20
            }, 300); 

            // Sembunyikan setelah 5 detik
            alertArea.delay(5000).fadeOut();
        }

        $('#kontakForm').on('submit', function(e) {
            e.preventDefault();
            var form = $(this);
            var btn = $('#submitBtn');
            var original_btn_html = btn.html();
            
            // Persiapan data untuk AJAX
            var formData = form.serializeArray();
            formData.push({name: 'action', value: 'update_kontak_ajax'});

            btn.attr('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Menyimpan...');

            $.ajax({
                type: 'POST',
                url: 'adminkontak.php', // Target file PHP yang sama
                data: formData,
                dataType: 'json', 
                success: function(response) {
                    if (response.status === 'success') {
                        showAlert('<i class="fas fa-check-circle"></i> ' + response.message, 'success-message');
                    } else {
                        showAlert('<i class="fas fa-exclamation-triangle"></i> ' + response.message, 'error-message');
                    }
                    btn.attr('disabled', false).html(original_btn_html);
                },
                error: function(xhr, status, error) {
                    showAlert('<i class="fas fa-times-circle"></i> Gagal memproses data kontak via AJAX. Cek konsol.', 'error-message');
                    console.error("AJAX Error:", status, error);
                    btn.attr('disabled', false).html(original_btn_html);
                }
            });
        });
    });
</script>

<?php 
if (isset($conn)) $conn->close(); 
?>