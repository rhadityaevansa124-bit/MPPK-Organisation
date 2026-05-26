<?php
session_start();
if (!isset($_SESSION['username']) || $_SESSION['role'] != 'admin') {
    header("location:../login.php");
    exit();
}

include "../koneksi/koneksi.php";

// ── Handle DELETE ──
if (isset($_GET['hapus'])) {
    $hid = intval($_GET['hapus']);
    $row = mysqli_fetch_assoc(mysqli_query($koneksi, "SELECT foto FROM tb_pengurus WHERE id=$hid"));
    if ($row && $row['foto']) {
        $f = "../uploads/" . $row['foto'];
        if (file_exists($f)) unlink($f);
    }
    mysqli_query($koneksi, "DELETE FROM tb_pengurus WHERE id=$hid");
    header("location:kelola_pengurus.php?msg=hapus");
    exit();
}

// ── Handle ADD ──
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['aksi']) && $_POST['aksi'] === 'tambah') {
    $nama     = trim($_POST['nama']);
    $jabatan  = trim($_POST['jabatan']);
    $instagram= trim($_POST['instagram']);
    $urutan   = intval($_POST['urutan']);
    $foto_file = '';

    if (isset($_FILES['foto']) && $_FILES['foto']['error'] === UPLOAD_ERR_OK) {

    // 🔴 CEK UKURAN (TARUH DI SINI)
    if ($_FILES['foto']['size'] > 5 * 1024 * 1024) {
        die("Ukuran foto terlalu besar! Maksimal 5MB");
    }

    $ext  = strtolower(pathinfo($_FILES['foto']['name'], PATHINFO_EXTENSION));
    $allowed = ['jpg','jpeg','png','webp','gif'];

    if (in_array($ext, $allowed)) {

        $foto_file = 'pengurus_' . uniqid() . '.' . $ext;

        $upload_path = "../uploads/";
        if (!is_dir($upload_path)) {
            mkdir($upload_path, 0777, true);
        }

        if (!move_uploaded_file($_FILES['foto']['tmp_name'], $upload_path . $foto_file)) {
            die("Upload gagal!");
        }

    } else {
        die("Format tidak didukung!");
    }
}

    $stmt = $koneksi->prepare("INSERT INTO tb_pengurus (nama, jabatan, foto, instagram, urutan) VALUES (?,?,?,?,?)");
    $stmt->bind_param("ssssi", $nama, $jabatan, $foto_file, $instagram, $urutan);
    $stmt->execute(); $stmt->close();
    header("location:kelola_pengurus.php?msg=tambah");
    exit();
}

// ── Handle EDIT ──
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['aksi']) && $_POST['aksi'] === 'edit') {
    $eid      = intval($_POST['id']);
    $nama     = trim($_POST['nama']);
    $jabatan  = trim($_POST['jabatan']);
    $instagram= trim($_POST['instagram']);
    $urutan   = intval($_POST['urutan']);

    // Ambil foto lama
    $old = mysqli_fetch_assoc(mysqli_query($koneksi, "SELECT foto FROM tb_pengurus WHERE id=$eid"));
    $foto_file = $old['foto'];

    if (isset($_FILES['foto']) && $_FILES['foto']['error'] === UPLOAD_ERR_OK) {

    // 🔴 CEK UKURAN DI SINI JUGA
    if ($_FILES['foto']['size'] > 5 * 1024 * 1024) {
        die("Ukuran foto terlalu besar!");
    }

    $ext  = strtolower(pathinfo($_FILES['foto']['name'], PATHINFO_EXTENSION));
    $allowed = ['jpg','jpeg','png','webp','gif'];

    if (in_array($ext, $allowed)) {

        $baru = 'pengurus_' . uniqid() . '.' . $ext;

        $upload_path = "../uploads/";
        if (!is_dir($upload_path)) {
            mkdir($upload_path, 0777, true);
        }

        if (move_uploaded_file($_FILES['foto']['tmp_name'], $upload_path . $baru)) {
            if ($foto_file && file_exists($upload_path . $foto_file)) {
                unlink($upload_path . $foto_file);
            }
            $foto_file = $baru;
        } else {
            die("Upload gagal!");
        }

    }
}

    $stmt = $koneksi->prepare("UPDATE tb_pengurus SET nama=?, jabatan=?, foto=?, instagram=?, urutan=? WHERE id=?");
    $stmt->bind_param("ssssii", $nama, $jabatan, $foto_file, $instagram, $urutan, $eid);
    $stmt->execute(); $stmt->close();
    header("location:kelola_pengurus.php?msg=edit");
    exit();
}

$daftar = mysqli_query($koneksi, "SELECT * FROM tb_pengurus ORDER BY urutan ASC, id ASC");
$msg = $_GET['msg'] ?? '';
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola Pengurus — Admin</title>
    <link rel="stylesheet" href="../style/admin.css">
    <style>
        .pengurus-grid-admin {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
            gap: 16px; margin-top: 20px;
        }
        .p-card {
            background: #fff; border-radius: 12px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.08);
            overflow: hidden; border: 1px solid #eee;
            transition: box-shadow 0.2s;
        }
        .p-card:hover { box-shadow: 0 6px 20px rgba(0,0,0,0.12); }
        .p-card-img {
            width: 100%; height: 140px; object-fit: cover;
            background: linear-gradient(135deg, #1a3c5e, #234f7a);
            display: flex; align-items: center; justify-content: center;
            font-size: 48px; color: rgba(255,255,255,0.4);
        }
        .p-card-img img { width:100%; height:140px; object-fit:cover; display:block; }
        .p-card-body { padding: 14px; }
        .p-card-jabatan {
            font-size: 10px; font-weight: 700; letter-spacing: 0.1em;
            text-transform: uppercase; color: #c9a84c; margin-bottom: 4px;
        }
        .p-card-nama { font-size: 0.9rem; font-weight: 600; color: #1a1a2e; margin-bottom: 4px; }
        .p-card-ig { font-size: 11px; color: #9ca3af; }
        .p-card-actions { display: flex; gap: 8px; margin-top: 10px; }
        .p-card-actions button, .p-card-actions a {
            flex: 1; padding: 6px; border: none; border-radius: 6px;
            font-size: 11px; font-weight: 600; cursor: pointer;
            text-align: center; text-decoration: none;
        }
        .btn-p-edit  { background: #3b82f6; color: #fff; }
        .btn-p-hapus { background: #ef4444; color: #fff; }

        .form-add {
            background: #f8f9fa; border-radius: 10px;
            padding: 20px; margin-bottom: 28px;
            border: 1px solid #e5e7eb;
        }
        .form-add h3 { margin: 0 0 16px; font-size: 1rem; color: #1a3c5e; }
        .form-row { display: flex; gap: 12px; flex-wrap: wrap; }
        .form-row input, .form-row select {
            flex: 1; min-width: 140px; padding: 8px 12px;
            border: 1px solid #ddd; border-radius: 6px;
            font-size: 0.88rem;
        }
        .form-row input[type="file"] { flex: 2; }
        .btn-tambah {
            background: linear-gradient(135deg, #1a3c5e, #234f7a);
            color: #fff; border: none; border-radius: 8px;
            padding: 9px 22px; font-weight: 600; cursor: pointer;
            margin-top: 10px; font-size: 0.88rem;
        }

        .toast {
            position: fixed; top: 20px; right: 20px;
            background: #1a3c5e; color: #fff;
            padding: 12px 20px; border-radius: 10px;
            font-size: 0.88rem; z-index: 9999;
            animation: toastIn 0.3s ease, toastOut 0.3s ease 2.5s forwards;
        }
        @keyframes toastIn  { from{opacity:0;transform:translateY(-10px)} to{opacity:1;transform:translateY(0)} }
        @keyframes toastOut { from{opacity:1} to{opacity:0} }

        /* Modal Edit */
        .modal-edit {
            display: none; position: fixed;
            top:0; left:0; width:100%; height:100%;
            background: rgba(0,0,0,0.5); z-index: 2000;
            align-items: center; justify-content: center;
        }
        .modal-edit.open { display: flex; }
        .modal-edit-box {
            background: #fff; border-radius: 12px;
            padding: 28px; width: 90%; max-width: 480px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.2);
        }
        .modal-edit-box h3 { margin: 0 0 20px; color: #1a3c5e; }
        .modal-edit-box .form-row { flex-direction: column; gap: 10px; }
        .modal-edit-box input { width: 100%; box-sizing: border-box; }
        .modal-foot { display: flex; gap: 10px; margin-top: 16px; }
        .modal-foot button { flex:1; padding:10px; border:none; border-radius:8px; font-weight:600; cursor:pointer; }
        .btn-save-edit { background:#1a3c5e; color:#fff; }
        .btn-cancel-edit { background:#e5e7eb; color:#374151; }
    </style>
</head>
<body>
    <div class="sidebar">
        <h2>ADMIN PANEL</h2>
        <a href="dashboard.php">Dashboard</a>
        <a href="kelola_pengurus.php" class="active">Kelola Pengurus</a>
        <a href="galerry_manage.php">Kelola Foto</a>
        <a href="pendaftaran.php">ACC Pendaftaran</a>
        <a href="logout.php">Logout</a>
    </div>

    <div class="content">
        <h1>Kelola Struktur Pengurus</h1>

        <?php if ($msg): ?>
        <div class="toast" id="toast">
            <?php
            echo match($msg) {
                'tambah' => '✅ Pengurus berhasil ditambahkan',
                'edit'   => '✅ Data pengurus berhasil diperbarui',
                'hapus'  => '🗑️ Pengurus berhasil dihapus',
                default  => ''
            };
            ?>
        </div>
        <script>setTimeout(() => document.getElementById('toast')?.remove(), 3000);</script>
        <?php endif; ?>

        <!-- Form Tambah -->
        <div class="form-add">
            <h3>➕ Tambah Pengurus Baru</h3>
            <form method="POST" enctype="multipart/form-data">
                <input type="hidden" name="aksi" value="tambah">
                <div class="form-row">
                    <input type="text"   name="nama"      placeholder="Nama lengkap" required>
                    <input type="text"   name="jabatan"   placeholder="Jabatan (contoh: Ketua Umum)" required>
                    <input type="text"   name="instagram" placeholder="Instagram (@username)">
                    <input type="number" name="urutan"    placeholder="Urutan tampil (1=pertama)" value="99" min="1">
                    <input type="file"   name="foto"      accept="image/*">
                </div>
                <button type="submit" class="btn-tambah">Simpan Pengurus</button>
            </form>
        </div>

        <!-- Daftar Pengurus -->
        <h3>Daftar Pengurus (<?= mysqli_num_rows($daftar) ?> orang)</h3>
        <div class="pengurus-grid-admin">
            <?php while ($p = mysqli_fetch_assoc($daftar)): ?>
            <div class="p-card">
                <div class="p-card-img">
                    <?php if ($p['foto']): ?>
                        <img src="../uploads/<?= htmlspecialchars($p['foto']) ?>"
                             onerror="this.parentElement.innerHTML='👤'">
                    <?php else: ?>
                        👤
                    <?php endif; ?>
                </div>
                <div class="p-card-body">
                    <div class="p-card-jabatan"><?= htmlspecialchars($p['jabatan']) ?></div>
                    <div class="p-card-nama"><?= htmlspecialchars($p['nama']) ?></div>
                    <?php if ($p['instagram']): ?>
                        <div class="p-card-ig">📸 <?= htmlspecialchars($p['instagram']) ?></div>
                    <?php endif; ?>
                    <div class="p-card-actions">
                        <button class="btn-p-edit" onclick="openEdit(
                            <?= $p['id'] ?>,
                            '<?= addslashes($p['nama']) ?>',
                            '<?= addslashes($p['jabatan']) ?>',
                            '<?= addslashes($p['instagram'] ?? '') ?>',
                            <?= $p['urutan'] ?>,
                            '<?= addslashes($p['foto'] ?? '') ?>'
                        )">✏️ Edit</button>
                        <a class="btn-p-hapus"
                           href="kelola_pengurus.php?hapus=<?= $p['id'] ?>"
                           onclick="return confirm('Hapus <?= addslashes($p['nama']) ?>?')">🗑️ Hapus</a>
                    </div>
                </div>
            </div>
            <?php endwhile; ?>
        </div>
    </div>

    <!-- Modal Edit -->
    <div class="modal-edit" id="modalEdit">
        <div class="modal-edit-box">
            <h3>✏️ Edit Data Pengurus</h3>
            <form method="POST" enctype="multipart/form-data">
                <input type="hidden" name="aksi" value="edit">
                <input type="hidden" name="id"   id="edit_id">
                <div class="form-row">
                    <input type="text"   name="nama"      id="edit_nama"      placeholder="Nama" required>
                    <input type="text"   name="jabatan"   id="edit_jabatan"   placeholder="Jabatan" required>
                    <input type="text"   name="instagram" id="edit_instagram" placeholder="Instagram (@username)">
                    <input type="number" name="urutan"    id="edit_urutan"    placeholder="Urutan tampil" min="1">

                    <!-- Foto saat ini -->
                    <div style="margin-top:4px;">
                        <small style="color:#6b7280;font-weight:600;display:block;margin-bottom:6px;">Foto saat ini:</small>
                        <img id="edit_foto_lama" src=""
                             style="width:80px;height:80px;object-fit:cover;border-radius:50%;
                                    border:2px solid #c9a84c;display:none;">
                        <span id="edit_foto_none"
                              style="display:none;font-size:12px;color:#9ca3af;font-style:italic;">
                            Belum ada foto
                        </span>
                    </div>

                    <!-- Input file baru -->
                    <div style="margin-top:8px;">
                        <small style="color:#6b7280;font-weight:600;display:block;margin-bottom:6px;">
                            Ganti foto (opsional):
                        </small>
                        <input type="file" name="foto" id="edit_foto_input" accept="image/*"
                               style="width:100%;">
                        <!-- Preview foto baru -->
                        <img id="edit_foto_preview" src=""
                             style="display:none;width:80px;height:80px;object-fit:cover;
                                    border-radius:50%;border:2px solid #22c55e;margin-top:8px;">
                        <small style="color:#9ca3af;display:block;margin-top:4px;">
                            💡 Biarkan kosong jika tidak ingin ganti foto
                        </small>
                    </div>
                </div>
                <div class="modal-foot">
                    <button type="submit" class="btn-save-edit">💾 Simpan</button>
                    <button type="button" class="btn-cancel-edit" onclick="closeEdit()">Batal</button>
                </div>
            </form>
        </div>
    </div>

    <script>
    function openEdit(id, nama, jabatan, instagram, urutan, foto) {
        document.getElementById('edit_id').value        = id;
        document.getElementById('edit_nama').value      = nama;
        document.getElementById('edit_jabatan').value   = jabatan;
        document.getElementById('edit_instagram').value = instagram;
        document.getElementById('edit_urutan').value    = urutan;

        // Tampilkan foto lama
        const imgLama  = document.getElementById('edit_foto_lama');
        const noFoto   = document.getElementById('edit_foto_none');
        if (foto) {
            imgLama.src          = '../uploads/' + foto;
            imgLama.style.display  = 'block';
            noFoto.style.display   = 'none';
        } else {
            imgLama.style.display  = 'none';
            noFoto.style.display   = 'inline';
        }

        // Reset input file dan preview baru
        document.getElementById('edit_foto_input').value   = '';
        document.getElementById('edit_foto_preview').style.display = 'none';

        document.getElementById('modalEdit').classList.add('open');
    }

    function closeEdit() {
        document.getElementById('modalEdit').classList.remove('open');
    }

    // Preview foto baru saat dipilih
    document.getElementById('edit_foto_input').addEventListener('change', function() {
        const file = this.files[0];
        const prev = document.getElementById('edit_foto_preview');
        if (file) {
            const reader = new FileReader();
            reader.onload = e => {
                prev.src           = e.target.result;
                prev.style.display = 'block';
            };
            reader.readAsDataURL(file);
        } else {
            prev.style.display = 'none';
        }
    });

    document.getElementById('modalEdit').addEventListener('click', function(e) {
        if (e.target === this) closeEdit();
    });
    </script>
</body>
</html>