<?php
session_start();
if (!isset($_SESSION['username']) || $_SESSION['role'] != 'admin') {
    header("location:../login.php");
    exit();
}

include "../koneksi/koneksi.php";
?>

<!DOCTYPE html>
<html>
<head>
    <title>Kelola Foto Gallery</title>
    <link rel="stylesheet" href="../style/admin.css">
    <style>
        .upload-section {
            background: #f5f5f5;
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 25px;
        }

        .upload-form {
            display: flex;
            gap: 10px;
            align-items: center;
            flex-wrap: wrap;
        }

        .upload-form input,
        .upload-form button {
            padding: 10px 12px;
            border: 1px solid #ddd;
            border-radius: 4px;
        }

        .upload-form button {
            background: #0b57a4;
            color: #fff;
            border: none;
            font-weight: 600;
            cursor: pointer;
        }

        .upload-form button:hover {
            background: #0e66bf;
        }

        .gallery-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
            gap: 15px;
            margin-top: 20px;
        }

        .gallery-item {
            position: relative;
            background: #fff;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }

        .gallery-item img {
            width: 100%;
            height: 150px;
            object-fit: cover;
            display: block;
        }

        .gallery-info {
            padding: 10px;
            border-top: 1px solid #eee;
        }

        .gallery-info h4 {
            margin: 0 0 5px 0;
            color: #333;
            font-size: 14px;
        }

        .gallery-info p {
            margin: 0 0 8px 0;
            color: #666;
            font-size: 12px;
            line-height: 1.3;
        }

        .gallery-actions {
            display: flex;
            gap: 8px;
        }

        .btn-edit, .btn-delete {
            flex: 1;
            padding: 6px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-weight: 600;
            font-size: 11px;
            transition: all 0.3s;
        }

        .btn-edit {
            background: #007bff;
            color: #fff;
        }

        .btn-edit:hover {
            background: #0056b3;
        }

        .btn-delete {
            background: #dc3545;
            color: #fff;
        }

        .btn-delete:hover {
            background: #c82333;
        }

        /* Modal Edit */
        .modal {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0,0,0,0.4);
            align-items: center;
            justify-content: center;
        }

        .modal.open {
            display: flex;
        }

        .modal-content {
            background-color: #fff;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
            width: 90%;
            max-width: 500px;
        }

        .modal-header {
            font-size: 20px;
            font-weight: 600;
            margin-bottom: 20px;
            color: #333;
        }

        .form-group {
            margin-bottom: 15px;
        }

        .form-group label {
            display: block;
            margin-bottom: 6px;
            font-weight: 600;
            color: #333;
            font-size: 14px;
        }

        .form-group input,
        .form-group textarea {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
            box-sizing: border-box;
            font-family: inherit;
            font-size: 14px;
        }

        .form-group textarea {
            resize: vertical;
            min-height: 80px;
        }

        .preview-img {
            width: 100%;
            max-width: 200px;
            margin-bottom: 15px;
            border-radius: 4px;
        }

        .modal-footer {
            display: flex;
            gap: 10px;
        }

        .modal-footer button {
            flex: 1;
            padding: 10px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-weight: 600;
        }

        .btn-save {
            background: #28a745;
            color: #fff;
        }

        .btn-save:hover {
            background: #218838;
        }

        .btn-cancel {
            background: #6c757d;
            color: #fff;
        }

        .btn-cancel:hover {
            background: #5a6268;
        }
    </style>
</head>

<body>
    <div class="sidebar">
        <h2>ADMIN PANEL</h2>
        <a href="dashboard.php">Dashboard</a>
        <a href="kelola_pengurus.php">Kelola Pengurus</a>
        <a href="galerry_manage.php" class="active">Kelola Foto</a>
        <a href="pendaftaran.php">ACC Pendaftaran</a>
        <a href="logout.php">Logout</a>
    </div>

    <div class="content">
        <h1>Kelola Foto Gallery</h1>

        <!-- Upload Foto Baru -->
        <div class="upload-section">
            <h3>Upload Foto Baru</h3>
            <form class="upload-form" action="upload_foto.php" method="POST" enctype="multipart/form-data">
                <input type="file" name="foto" accept="image/*" required style="flex: 1; min-width: 200px;">
                <input type="text" name="title" placeholder="Judul program" required style="flex: 1; min-width: 150px;">
                <input type="text" name="description" placeholder="Deskripsi singkat" required style="flex: 1; min-width: 200px;">
                <button type="submit">Upload</button>
            </form>
        </div>

        <!-- Galeri Foto -->
        <h3>Foto Gallery</h3>
        <div class="gallery-grid">
            <?php
            $data = mysqli_query($koneksi, "SELECT * FROM tb_galerry ORDER BY id DESC");
            
            if (!$data) {
                echo "<p>Error: " . mysqli_error($koneksi) . "</p>";
            } elseif (mysqli_num_rows($data) == 0) {
                echo "<p>Belum ada foto. Upload foto terlebih dahulu.</p>";
            } else {
                while ($d = mysqli_fetch_array($data)) {
            ?>
            <div class="gallery-item">
                <img src="../uploads/<?= htmlspecialchars($d['file']) ?>" alt="Foto">
                <div class="gallery-info">
                    <h4><?= htmlspecialchars($d['title'] ?? 'Untitled') ?></h4>
                    <p><?= htmlspecialchars($d['description'] ?? 'Tidak ada deskripsi') ?></p>
                    <div class="gallery-actions">
                        <button class="btn-edit" onclick="openEditModal(<?= $d['id'] ?>, '<?= htmlspecialchars($d['file']) ?>', '<?= htmlspecialchars($d['title'] ?? '') ?>', '<?= htmlspecialchars($d['description'] ?? '') ?>')">Edit</button>
                        <button class="btn-delete" onclick="deletePhoto(<?= $d['id'] ?>)">Hapus</button>
                    </div>
                </div>
            </div>
            <?php 
                }
            }
            ?>
        </div>
    </div>

    <!-- Modal Edit -->
    <div id="editModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">Edit Foto & Text</div>
            <form id="editForm" action="update_foto.php" method="POST" enctype="multipart/form-data">
                <input type="hidden" id="fotoId" name="id">
                
                <div class="form-group">
                    <label>Foto Saat Ini:</label>
                    <img id="currentImg" class="preview-img" src="">
                </div>

                <div class="form-group">
                    <label for="newFoto">Ganti Foto (opsional):</label>
                    <input type="file" id="newFoto" name="foto" accept="image/*">
                    <img id="previewImg" class="preview-img" style="display:none;">
                </div>

                <div class="form-group">
                    <label for="title">Judul Program:</label>
                    <input type="text" id="title" name="title" required>
                </div>

                <div class="form-group">
                    <label for="description">Deskripsi:</label>
                    <textarea id="description" name="description" required></textarea>
                </div>

                <div class="modal-footer">
                    <button type="submit" class="btn-save">Simpan</button>
                    <button type="button" class="btn-cancel" onclick="closeEditModal()">Batal</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function openEditModal(id, file, title, description) {
            document.getElementById('fotoId').value = id;
            document.getElementById('currentImg').src = '../uploads/' + file;
            document.getElementById('title').value = title;
            document.getElementById('description').value = description;
            document.getElementById('editModal').classList.add('open');
        }

        function closeEditModal() {
            document.getElementById('editModal').classList.remove('open');
            document.getElementById('newFoto').value = '';
            document.getElementById('previewImg').style.display = 'none';
        }

        function deletePhoto(id) {
            if (confirm('Yakin ingin menghapus foto ini?')) {
                window.location.href = 'hapus_foto.php?id=' + id;
            }
        }

        // Preview foto sebelum upload
        document.getElementById('newFoto').addEventListener('change', function(e) {
            const file = e.target.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function(event) {
                    document.getElementById('previewImg').src = event.target.result;
                    document.getElementById('previewImg').style.display = 'block';
                };
                reader.readAsDataURL(file);
            }
        });

        // Tutup modal saat klik di luar
        document.getElementById('editModal').addEventListener('click', function(e) {
            if (e.target === this) {
                closeEditModal();
            }
        });
    </script>
</body>
</html>
