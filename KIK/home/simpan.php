<?php
session_start(); 
if (!isset($_SESSION['username']) || $_SESSION['role'] != 'user') {
    header("location:../login.php");
    exit();
}

include '../koneksi/koneksi.php';

if ($_SERVER['REQUEST_METHOD'] != 'POST') {
    header("location:daftar.php");
    exit();
}

$username   = $_SESSION['username'];
$nama       = isset($_POST['nama'])      ? trim($_POST['nama'])      : '';
$email      = isset($_POST['email'])     ? trim($_POST['email'])     : '';
$no_telepon = isset($_POST['no_telpon']) ? trim($_POST['no_telpon']) : '';
$tgl_lahir  = isset($_POST['tgl_lahir']) ? trim($_POST['tgl_lahir']) : '';
$alamat     = isset($_POST['alamat'])    ? trim($_POST['alamat'])    : '';
$jurusan    = isset($_POST['jurusan'])   ? trim($_POST['jurusan'])   : '';
$alasan     = isset($_POST['alasan'])    ? trim($_POST['alasan'])    : '';

// ── Validasi field kosong ──
if (empty($nama) || empty($email) || empty($no_telepon) || empty($tgl_lahir) || empty($alamat) || empty($jurusan) || empty($alasan)) {
    echo "<script>alert('Semua field harus diisi!');history.back();</script>";
    exit();
}

// ── Validasi format email ──
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo "<script>alert('Format email tidak valid!');history.back();</script>";
    exit();
}

// ════════════════════════════════════════════════════
// CEK 1: Apakah USERNAME ini sudah pernah daftar?
// (Berdasarkan join ke tb_login, bukan hanya email)
// ════════════════════════════════════════════════════
$cek_username = $koneksi->prepare(
    "SELECT td.id, td.status, td.kode_resi
     FROM tb_daftar td
     JOIN tb_login tl ON tl.email = td.email
     WHERE tl.username = ?
     LIMIT 1"
);
$cek_username->bind_param("s", $username);
$cek_username->execute();
$res_username = $cek_username->get_result();

if ($res_username->num_rows > 0) {
    $existing = $res_username->fetch_assoc();
    $cek_username->close();

    $status_label = match($existing['status']) {
        'pending'  => 'menunggu konfirmasi admin',
        'accepted' => 'sudah DITERIMA',
        'rejected' => 'DITOLAK',
        default    => $existing['status']
    };

    // Arahkan ke tampil daftar dengan pesan
    echo "<script>
        alert('Kamu sudah pernah mendaftar!\\nKode Resi: {$existing['kode_resi']}\\nStatus: {$status_label}\\n\\nSetiap akun hanya bisa mendaftar satu kali.');
        window.location='tampildaftar.php';
    </script>";
    exit();
}
$cek_username->close();

// ════════════════════════════════════════════════════
// CEK 2: Email sudah dipakai akun lain?
// ════════════════════════════════════════════════════
$cek_email = $koneksi->prepare("SELECT id FROM tb_daftar WHERE email = ? LIMIT 1");
$cek_email->bind_param("s", $email);
$cek_email->execute();
$cek_email->store_result();

if ($cek_email->num_rows > 0) {
    $cek_email->close();
    echo "<script>alert('Email ini sudah digunakan pada pendaftaran lain!');history.back();</script>";
    exit();
}
$cek_email->close();

// ════════════════════════════════════════════════════
// GENERATE KODE RESI + SIMPAN (dengan proteksi race condition)
// ════════════════════════════════════════════════════
$kode_resi = null;
$max_retry = 5;
$berhasil  = false;

for ($attempt = 1; $attempt <= $max_retry; $attempt++) {

    $koneksi->begin_transaction();

    try {
        $q_urut = $koneksi->query(
            "SELECT COUNT(*) AS total FROM tb_daftar
             WHERE DATE(created_at) = CURDATE()
             FOR UPDATE"
        );
        $urut_row   = $q_urut->fetch_assoc();
        $nomor_urut = intval($urut_row['total']) + 1;
        $kode_resi  = 'MPPK-' . date('Ymd') . '-' . str_pad($nomor_urut, 4, '0', STR_PAD_LEFT);

        $stmt = $koneksi->prepare(
            "INSERT INTO tb_daftar
                (kode_resi, nama, email, no_telepon, tgl_lahir, alamat, jurusan, alasan, status, created_at)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'pending', NOW())"
        );

        if (!$stmt) throw new Exception("Prepare failed: " . $koneksi->error);

        $stmt->bind_param("ssssssss",
            $kode_resi, $nama, $email, $no_telepon,
            $tgl_lahir, $alamat, $jurusan, $alasan
        );

        $stmt->execute();
        $stmt->close();
        $koneksi->commit();
        $berhasil = true;
        break;

    } catch (Exception $e) {
        $koneksi->rollback();
        if ($koneksi->errno != 1062) {
            echo "<script>alert('Gagal menyimpan data: " . addslashes($e->getMessage()) . "');history.back();</script>";
            exit();
        }
        usleep(100000 * $attempt);
    }
}

if ($berhasil) {
    $id_baru = $koneksi->insert_id;
    echo "<script>
        alert('Pendaftaran berhasil!\\nKode Resi kamu: $kode_resi\\nMohon menunggu konfirmasi dari admin.');
        window.location='tampilDaftar.php?id=$id_baru';
    </script>";
} else {
    echo "<script>alert('Server sedang sibuk, silakan coba lagi.');history.back();</script>";
}
?>