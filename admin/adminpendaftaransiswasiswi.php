<?php
// FILE: admin/adminpendaftaransiswasiswi.php
// Pastikan file ini berada di dalam folder 'admin'

// --- PENGATURAN KONEKSI PHP ---
include '../koneksi.php'; // Sesuaikan path ke koneksi.php

// Cek koneksi
if (!isset($conn) || $conn->connect_error) {
    die("Koneksi Database Gagal: " . (isset($conn) ? $conn->connect_error : 'Variable $conn tidak terdefinisi.'));
}

$message = '';

// --- LOGIKA HAPUS DATA ---
if (isset($_GET['action']) && $_GET['action'] == 'delete' && isset($_GET['id'])) {
    $id = intval($_GET['id']);

    // 1. Ambil path file sebelum dihapus
    $stmt_select = $conn->prepare("SELECT akte_kelahiran_path, ijazah_tk_path, kartu_keluarga_path, ktp_ayah_path, ktp_ibu_path FROM pendaftaran_siswa WHERE id = ?");
    $stmt_select->bind_param("i", $id);
    $stmt_select->execute();
    $result_select = $stmt_select->get_result();
    $paths = $result_select->fetch_assoc();
    $stmt_select->close();

    // 2. Hapus data dari database
    $stmt_delete = $conn->prepare("DELETE FROM pendaftaran_siswa WHERE id = ?");
    $stmt_delete->bind_param("i", $id);

    if ($stmt_delete->execute()) {
        // 3. Hapus file-file terkait
        if ($paths) {
            foreach ($paths as $path) {
                // Path di database 'uploads/namafile.jpg'. Kita harus mundur satu folder dari 'admin/'
                if (!empty($path) && file_exists('../' . $path)) { 
                    unlink('../' . $path);
                }
            }
        }
        $message = '<div class="alert success-message">Data pendaftar berhasil dihapus!</div>';
    } else {
        $message = '<div class="alert error-message">Gagal menghapus data: ' . $stmt_delete->error . '</div>';
    }
    $stmt_delete->close();
}

// --- LOGIKA AMBIL DATA UNTUK TABEL ---
$data_pendaftar = [];
// Pastikan nama kolom 'id', 'nama_lengkap', 'nomor_kontak', 'tanggal_daftar' sudah benar
$query = "SELECT id, nama_lengkap, nomor_kontak, tanggal_daftar FROM pendaftaran_siswa ORDER BY tanggal_daftar DESC";
$result = $conn->query($query);

if ($result && $result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        $data_pendaftar[] = $row;
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Admin - Data Pendaftar Siswa</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css"> 
    <style>
        /* CSS Admin Panel Dasar (Sederhana) */
        * { box-sizing: border-box; margin: 0; padding: 0; font-family: sans-serif; }
        :root { --primary-color: #4CAF50; --secondary-color: #1B5E20; --text-dark: #333; --bg-light: #f4f4f4; }
        body { min-height: 100vh; background-color: var(--bg-light); } 
        
        .content { 
            width: 96%; 
            max-width: 1400px; 
            margin: 20px auto; 
            padding: 30px; 
            background-color: white; 
            border-radius: 8px; 
            box-shadow: 0 0 15px rgba(0,0,0,0.1); 
            overflow-x: auto; 
        }
        .content h1 { color: var(--text-dark); margin-bottom: 20px; border-bottom: 2px solid #eee; padding-bottom: 10px; }
        
        .data-card {
            background-color: #fff;
            border-radius: 8px;
            padding: 20px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.08); 
            margin-top: 20px;
            border: 1px solid #e0e0e0;
        }

        /* Table Styling */
        table { width: 100%; min-width: 600px; border-collapse: collapse; margin-top: 0; }
        th, td { padding: 12px; border: none; border-bottom: 1px solid #eee; text-align: left; }
        th { background-color: var(--bg-light); color: var(--text-dark); }
        tr:last-child td { border-bottom: none; }
        
        .action-links a { margin-right: 10px; text-decoration: none; color: #1e88e5; }
        .action-links .delete-link { color: red; }
        
        /* --- MODAL (POP-UP) STYLING --- */
        .modal {
            display: none; 
            position: fixed;
            z-index: 1000; 
            left: 0;
            top: 0;
            width: 100%; 
            height: 100%; 
            overflow: auto; 
            background-color: rgba(0,0,0,0.6); 
        }
        .modal-content {
            background-color: #fefefe;
            margin: 5% auto; 
            padding: 30px;
            border: 1px solid #888;
            width: 90%; 
            max-width: 1200px;
            border-radius: 8px;
            position: relative;
            box-shadow: 0 5px 15px rgba(0,0,0,0.3); 
        }
        .close {
            color: #aaa;
            float: right;
            font-size: 28px;
            font-weight: bold;
        }
        .close:hover, .close:focus {
            color: #000;
            text-decoration: none;
            cursor: pointer;
        }
        .detail-item {
            margin-bottom: 10px;
        }
        .detail-item strong {
            display: inline-block;
            width: 150px;
            color: var(--secondary-color);
        }
        
        /* Dokumen Viewer */
        .document-viewer {
            margin-top: 20px;
            border-top: 1px solid #ccc;
            padding-top: 20px;
        }
        .document-viewer h3 {
            color: var(--primary-color);
            margin-bottom: 15px;
        }
        .document-viewer .document-list a {
            display: inline-block;
            margin-right: 15px;
            padding: 8px 12px;
            background-color: #e3f2fd;
            color: #1e88e5;
            text-decoration: none;
            border-radius: 4px;
            transition: background-color 0.3s;
        }
        .document-viewer .document-list a:hover {
            background-color: #bbdefb;
        }

        /* Viewer Zoom/Pan */
        .zoom-container {
            width: 100%;
            height: 60vh; 
            border: 1px solid #ccc;
            margin-top: 15px;
            background-color: #f9f9f9;
            overflow: hidden; 
            position: relative;
            display: flex; 
            justify-content: center;
            align-items: center;
            cursor: default; 
            touch-action: none; 
        }
        .zoomable-image {
            display: none; 
            max-width: 100%;
            max-height: 100%;
            object-fit: contain;
            transform: translate(-50%, -50%) scale(1); 
            position: absolute; 
            top: 50%;
            left: 50%;
            transition: none; 
            will-change: transform; 
        }
    </style>
</head>
<body>

<div class="content">
    <h1>Manajemen Data Pendaftar Siswa Baru</h1>

    <?php echo $message; ?>

    <div class="data-card">
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Nama Lengkap</th>
                    <th>Nomor Kontak</th>
                    <th>Tanggal Daftar</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($data_pendaftar)): ?>
                    <?php foreach ($data_pendaftar as $pendaftar): ?>
                    <tr>
                        <td><?php echo $pendaftar['id']; ?></td>
                        <td><?php echo htmlspecialchars($pendaftar['nama_lengkap']); ?></td>
                        <td><?php echo htmlspecialchars($pendaftar['nomor_kontak']); ?></td>
                        <td><?php echo date("d M Y H:i", strtotime($pendaftar['tanggal_daftar'])); ?></td>
                        <td class="action-links">
                            <a href="#" onclick="showPendaftarDetail(<?php echo $pendaftar['id']; ?>); return false;">Detail</a>
                            <a href="adminpendaftaransiswasiswi.php?action=delete&id=<?php echo $pendaftar['id']; ?>" class="delete-link" onclick="return confirm('Yakin ingin menghapus pendaftar ini dan semua dokumennya?');">Hapus</a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr><td colspan="5" style="text-align: center; border-bottom: none;">Belum ada data pendaftar.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<div id="pendaftarDetailModal" class="modal">
    <div class="modal-content">
        <span class="close" onclick="closeModal()">&times;</span>
        <h2><i class="fas fa-info-circle"></i> Detail Pendaftar <span id="detailId"></span></h2>
        
        <div id="detailContent">
            </div>

        <div class="document-viewer">
            <h3><i class="fas fa-file-alt"></i> Dokumen Pendaftar</h3>
            <p style="margin-bottom: 10px; color: #555;">**Klik nama dokumen** di bawah untuk melihatnya di panel penampil. Gunakan *scroll mouse* untuk **zoom in/out** dan *drag* untuk menggeser gambar.</p>
            
            <div id="documentList" class="document-list">
                </div>

            <div class="zoom-container">
                <img id="docViewImage" src="" alt="Dokumen Pendaftar" class="zoomable-image">
                <p id="docViewMessage" style="text-align: center; color: #777; font-style: italic;">Pilih dokumen (gambar) untuk ditampilkan.</p>
            </div>
            
        </div>
    </div>
</div>

<script>
// Variabel dan Fungsi Dasar Modal
var modal = document.getElementById("pendaftarDetailModal");
var imageElement = document.getElementById("docViewImage");
var docViewContainer = document.querySelector(".zoom-container");
var docViewMessage = document.getElementById("docViewMessage");

// Variabel Zoom dan Pan
var currentScale = 1.0;
var currentX = 0;
var currentY = 0;
var isDragging = false;
var startX, startY;

function resetView() {
    currentScale = 1.0;
    currentX = 0;
    currentY = 0;
    imageElement.style.transform = `translate(-50%, -50%) scale(1)`; 
    docViewContainer.style.cursor = 'default';
}

function closeModal() {
    modal.style.display = "none";
    imageElement.src = ""; 
    imageElement.style.display = 'none'; 
    docViewMessage.style.display = 'block'; 
    docViewMessage.textContent = 'Pilih dokumen (gambar) untuk ditampilkan.';
    resetView();
}

window.onclick = function(event) {
    if (event.target == modal) {
        closeModal();
    }
}

/**
 * Fungsi untuk memuat detail pendaftar menggunakan AJAX
 */
function showPendaftarDetail(id) {
    // 1. Tampilkan modal loading dan reset view
    document.getElementById('detailContent').innerHTML = 'Memuat data... <i class="fas fa-spinner fa-spin"></i>';
    document.getElementById('documentList').innerHTML = 'Memuat link dokumen...';
    imageElement.style.display = 'none';
    docViewMessage.style.display = 'block';
    docViewMessage.textContent = 'Memuat link dokumen...';
    resetView();
    document.getElementById('detailId').textContent = '#' + id;
    modal.style.display = "block";

    // 2. Kirim permintaan AJAX untuk mengambil data detail
    // *** PATH RELATIF SEDERHANA (BERADA DI FOLDER YANG SAMA) ***
    // Jika masih 404, coba ganti menjadi fetch('./get_pendaftar_detail.php?id=' + id) atau fetch('/admin/get_pendaftar_detail.php?id=' + id)
    fetch('get_pendaftar_detail.php?id=' + id) 
        .then(response => {
            if (!response.ok) {
                 return response.text().then(text => { 
                     throw new Error('Request gagal. Status: ' + response.status + ' | Pesan Server: ' + (text.length < 500 ? text : 'Error response terlalu panjang.')); 
                 });
            }
            return response.json(); 
        })
        .then(data => {
            if (data.error) {
                document.getElementById('detailContent').innerHTML = '<div class="alert error-message">' + data.error + '</div>';
                document.getElementById('documentList').innerHTML = '';
                docViewMessage.textContent = 'Error: Gagal memuat data detail pendaftar.';
                return;
            }
            
            // 3. Isi Detail Pendaftar
            let detailHtml = `
                <div class="detail-item"><strong>Nama Lengkap:</strong> ${data.nama_lengkap}</div>
                <div class="detail-item"><strong>Nomor Kontak:</strong> ${data.nomor_kontak}</div>
                <div class="detail-item"><strong>Tanggal Daftar:</strong> ${data.tanggal_daftar}</div>
                `;
            document.getElementById('detailContent').innerHTML = detailHtml;

            // 4. Isi Link Dokumen (dengan fungsi view)
            let docLinks = '';
            
            // PASTIKAN NAMA PATH DI SINI SAMA DENGAN NAMA KOLOM DI get_pendaftar_detail.php
            const documents = [
                { name: 'Akte Kelahiran', path: data.akte_kelahiran_path },
                { name: 'Kartu Keluarga', path: data.kartu_keluarga_path },
                { name: 'KTP Ayah', path: data.ktp_ayah_path },
                { name: 'KTP Ibu', path: data.ktp_ibu_path },
                { name: 'Ijazah TK (Opsional)', path: data.ijazah_tk_path }
            ];

            documents.forEach(doc => {
                if (doc.path) {
                    const cleanPath = doc.path; 
                    
                    docLinks += `
                        <a href="#docViewImage" onclick="viewDocument('${cleanPath}', '${doc.name}'); return false;">
                            <i class="fas fa-eye"></i> ${doc.name}
                        </a>
                    `;
                }
            });

            if (docLinks === '') {
                document.getElementById('documentList').innerHTML = '<p>Tidak ada dokumen yang diunggah untuk pendaftar ini.</p>';
                docViewMessage.textContent = 'Tidak ada dokumen yang diunggah untuk pendaftar ini.';
            } else {
                 document.getElementById('documentList').innerHTML = docLinks;
                 docViewMessage.textContent = 'Pilih dokumen (gambar) untuk ditampilkan.';
            }
            
        })
        .catch(error => {
            // Ini akan tampil jika ada status 404/500 atau JSON invalid
            document.getElementById('detailContent').innerHTML = '<div class="alert error-message">Terjadi kesalahan fatal saat memuat detail. Detail Error (Cek F12): ' + error.message + '</div>';
            document.getElementById('documentList').innerHTML = '';
            docViewMessage.textContent = 'Error: ' + error.message;
            console.error('AJAX Error:', error);
        });
}

/**
 * Fungsi untuk menampilkan dokumen di Image Element (dengan zoom)
 */
function viewDocument(filePath, docName) {
    // filePath dari database harus diakses dari root project, jadi kita tambahkan '../' dari folder admin/
    const fullPath = '../' + filePath; 
    
    // Cek ekstensi file (sederhana)
    const isImage = /\.(jpe?g|png|gif|bmp)$/i.test(filePath);
    
    resetView();
    
    if (isImage) {
        docViewMessage.style.display = 'none';
        imageElement.src = fullPath;
        imageElement.style.display = 'block';
        
        imageElement.onload = () => {
             resetView(); 
        };
        imageElement.onerror = () => {
             imageElement.style.display = 'none';
             docViewMessage.textContent = `Gagal memuat gambar ${docName}. File mungkin rusak atau tidak ditemukan. (Path: ${fullPath})`;
             docViewMessage.style.display = 'block';
        };
    } else {
        // Notifikasi jika bukan gambar
        imageElement.src = '';
        imageElement.style.display = 'none';
        docViewMessage.textContent = `File (${docName}) bukan format gambar yang didukung untuk fitur zoom.`;
        docViewMessage.style.display = 'block';
        docViewContainer.style.cursor = 'default';
        alert(`Perhatian: File ${docName} kemungkinan bukan gambar (.jpg, .png, .gif) sehingga tidak dapat di-zoom. Ini hanya mendukung gambar.`);
    }
    
    // Opsional: Scroll ke container setelah link diklik
    docViewContainer.scrollIntoView({ behavior: 'smooth', block: 'start' });
}


// === LOGIKA ZOOM dan PAN (DRAG) ===

// Pan/Drag
docViewContainer.addEventListener('mousedown', (e) => {
    if (imageElement.style.display !== 'none' && currentScale > 1) { 
        isDragging = true;
        startX = e.clientX;
        startY = e.clientY;
        docViewContainer.style.cursor = 'grabbing';
        e.preventDefault(); 
    }
});

docViewContainer.addEventListener('mousemove', (e) => {
    if (!isDragging) return;
    
    const dx = e.clientX - startX;
    const dy = e.clientY - startY;

    currentX += dx;
    currentY += dy;

    imageElement.style.transform = `translate(-50%, -50%) scale(${currentScale}) translate(${currentX}px, ${currentY}px)`;
    
    startX = e.clientX;
    startY = e.clientY;
});

docViewContainer.addEventListener('mouseup', () => {
    isDragging = false;
    if (currentScale > 1) {
        docViewContainer.style.cursor = 'grab';
    } else {
        docViewContainer.style.cursor = 'default';
    }
});
docViewContainer.addEventListener('mouseleave', () => {
    isDragging = false;
    if (currentScale > 1) {
        docViewContainer.style.cursor = 'grab';
    } else {
        docViewContainer.style.cursor = 'default';
    }
});


// Zoom (Mouse Wheel)
docViewContainer.addEventListener('wheel', (e) => {
    if (imageElement.style.display === 'none') return; 
    
    e.preventDefault(); 
    
    const scaleFactor = e.deltaY < 0 ? 1.1 : 0.9; 
    let newScale = currentScale * scaleFactor;
    
    if (newScale < 1) newScale = 1;
    if (newScale > 4) newScale = 4;
    
    if (Math.abs(newScale - 1) < 0.05) {
        resetView();
        currentScale = 1;
        docViewContainer.style.cursor = 'default';
        return;
    }
    
    docViewContainer.style.cursor = 'grab';
    
    const rect = docViewContainer.getBoundingClientRect();
    
    const mouseX = e.clientX - rect.left - rect.width / 2;
    const mouseY = e.clientY - rect.top - rect.height / 2;
    
    const scaleChange = newScale / currentScale; 
    
    currentX = mouseX - (mouseX - currentX) * scaleChange;
    currentY = mouseY - (mouseY - currentY) * scaleChange;
    
    currentScale = newScale;

    imageElement.style.transform = `translate(-50%, -50%) scale(${currentScale}) translate(${currentX}px, ${currentY}px)`;
}, { passive: false }); 
</script>

</body>
</html>
<?php 
if (isset($conn)) $conn->close(); 
?>