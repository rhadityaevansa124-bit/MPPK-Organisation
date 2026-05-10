<?php
session_start();
if(!isset($_SESSION['username']) || $_SESSION['role'] != 'user'){
    header("location:../login.php");
    exit();
}

include '../koneksi/koneksi.php';

$username = $_SESSION['username'];

$queryUser = mysqli_query($koneksi, "SELECT * FROM tb_login WHERE username='$username'");
$user = mysqli_fetch_assoc($queryUser);

// TAMBAHAN: Ambil foto dari database tb_galerry
$photos = [];
$stmt_photos = $koneksi->prepare("SELECT id, file, title, description FROM tb_galerry ORDER BY id DESC LIMIT 5");
$stmt_photos->execute();
$res_photos = $stmt_photos->get_result();
while ($row_photo = $res_photos->fetch_assoc()) {
    $photos[] = $row_photo;
}
$stmt_photos->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MPPK</title>
    <link rel="stylesheet" href="../style/utama.css">
    <style>
        html {
            scroll-behavior: smooth;
        }

            body {
    margin: 0;
    font-family: 'Segoe UI', Arial, sans-serif;
    color: #333;
    position: relative;
    min-height: 100vh;
    background: url('../ya.png') no-repeat center center fixed;
    background-size: cover;
    overflow-x: hidden;
    padding-top: 80px;
    }

    .navbar {
        position: fixed;
        top: 0;
        left: 0;
        width: 95%;
        background: linear-gradient(90deg, rgba(48, 5, 241, 0.8), rgba(0, 0, 0, 0.9));
        z-index: 999;
        transition: top 0.3s;
    }

        /* Modal Styling */
        .modal-overlay {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.6);
            z-index: 2000;
            animation: fadeIn 0.3s ease;
        }

        .modal-overlay.active {
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .modal-content {
            background: #fff;
            border-radius: 16px;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.3);
            max-width: 600px;
            width: 90%;
            max-height: 80vh;
            overflow-y: auto;
            animation: slideUp 0.3s ease;
            position: relative;
        }

        .modal-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 24px;
            border-bottom: 1px solid #eee;
        }

        .modal-header h2 {
            margin: 0;
            color: #00796b;
            font-size: 1.6em;
        }

        .modal-close-btn {
            background: none;
            border: none;
            font-size: 28px;
            color: #999;
            cursor: pointer;
            transition: color 0.2s;
            padding: 0;
            width: 32px;
            height: 32px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .modal-close-btn:hover {
            color: #333;
        }

        .modal-body {
            padding: 24px;
        }

        .modal-img {
            width: 100%;
            height: 300px;
            object-fit: cover;
            border-radius: 12px;
            margin-bottom: 16px;
        }

        .modal-description {
            font-size: 1.1em;
            color: #333;
            line-height: 1.6;
            margin: 0;
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
            }
            to {
                opacity: 1;
            }
        }

        @keyframes slideUp {
            from {
                transform: translateY(40px);
                opacity: 0;
            }
            to {
                transform: translateY(0);
                opacity: 1;
            }
        }

        .menu-item {
            cursor: pointer;
        }
        #notif-dropdown {
    background: #fff;
    color: #222;
}

    #notif-dropdown div {
        padding: 10px;
        border-bottom: 1px solid #eee;
    }

    #notif-dropdown div:hover {
        background: #f5f5f5;
    }
    </style>
</head>
<body>
    
    <nav class="navbar">
        <div class="navbar-brand">
            <img src="MPPK.jpg" alt="Logo" style="height:48px;vertical-align:middle;">
            <span style="margin-left:12px;font-weight:bold;font-size:1.2em;">
                MPPK<br>
                <span style="font-size:0.9em;font-weight:normal;">SMKN 1 Cibinong</span>
                <span style="font-size:0.8em;font-weight:bold;display:block;margin-top:2px;">
                    Selamat datang, <?= htmlspecialchars($_SESSION['username']) ?>👋 </span>
            </span>
        </div>
        <ul>
            <li style="position:relative;">
    <span onclick="toggleNotif()" style="cursor:pointer; font-size:20px;">
        🔔
        <span id="notif-count" style="
            position:absolute;
            top:-5px;
            right:-10px;
            background:red;
            color:white;
            font-size:12px;
            padding:2px 6px;
            border-radius:50%;
            display:none;
        ">0</span>
    </span>

    <div id="notif-dropdown" style="
        display:none;
        position:absolute;
        right:0;
        top:30px;
        width:300px;
        background:white;
        box-shadow:0 5px 20px rgba(0,0,0,0.2);
        border-radius:8px;
        max-height:300px;
        overflow-y:auto;
        z-index:999;
    ">
        <div style="padding:10px; font-weight:bold; border-bottom:1px solid #eee;">
            Notifikasi
        </div>
        <div id="notif-list"></div>
    </div>
</li>
            <li><a href="Home.php">Home</a></li>
            <li><a href="#program">Gallery</a></li>
            <li><a href="https://www.instagram.com/mppksmkn1cbn?igsh=MXNyaTZ6MHFycm93ag==">Tentang Kami</a></li>
            <li><a href="../admin/logout.php">LogOut</a></li>
            <li><a href="#" onclick="openProfile()">Profile</a></li>
        </ul>
    </nav>

    <section class="rpl">
        <h1>MPPK<br>Organization</h1>
        <p>(Majelis Perwakilan Program Kejuruan)</p>
    </section>

    <section id="program" class="menu-section">
        <h2>Program Kerja MPPK</h2>
        <div class="menu-items">
            <?php
            // Tampilkan foto dari database, jika tidak ada gunakan placeholder
            for ($i = 0; $i < 5; $i++) {
                $foto = isset($photos[$i]) ? $photos[$i] : null;
                $title = $foto ? htmlspecialchars($foto['title']) : 'Program';
                $description = $foto ? htmlspecialchars($foto['description']) : 'Deskripsi program';
                $img_src = $foto ? '../uploads/' . htmlspecialchars($foto['file']) : 'placeholder.jpg';
                $item_id = $i;
            ?>
            <div class="menu-item" onclick="openModal(<?= $item_id ?>, '<?= $title ?>', '<?= addslashes($description) ?>', '<?= $img_src ?>')">
                <img src="<?= $img_src ?>" alt="<?= $title ?>" style="object-fit: cover; width: 100%; height: 200px;">
                <h3><?= $title ?></h3>
                <p><?= substr($description, 0, 100) . (strlen($description) > 100 ? '...' : '') ?></p>
            </div>
            <?php } ?>
        </div>
    </section>
    <a href="daftar.php" class="floating-btn">DAFTAR SEKARANG</a>

    <!-- Modal untuk menampilkan detail foto -->
    <div class="modal-overlay" id="photoModal">
        <div class="modal-content">
            <div class="modal-header">
                <h2 id="modalTitle">Judul Foto</h2>
                <button class="modal-close-btn" onclick="closeModal()">×</button>
            </div>
            <div class="modal-body">
                <img id="modalImage" class="modal-img" src="" alt="Foto">
                <p id="modalDescription" class="modal-description"></p>
            </div>
        </div>
    </div>

    <div id="profileModal" class="modal-overlay">
    <div class="modal-content">
        <div class="modal-header">
            <h2>Profil Saya</h2>
            <button class="modal-close-btn" onclick="closeProfile()">×</button>
        </div>

        <div class="modal-body">
            <p><strong>Nama Lengkap:</strong> <?= $_SESSION['username'] ?></p>
            <p><strong>Email:</strong> <?= $user['email'] ?></p>
            <p><strong>Password:</strong> <?= $user['password'] ?></p>
        </div>
    </div>
</div>
    <script>
function toggleNotif() {
    let box = document.getElementById("notif-dropdown");
    box.style.display = (box.style.display === "block") ? "none" : "block";
}

function loadNotif() {
    fetch('get_notif.php')
    .then(res => res.json())
    .then(data => {

        let list = document.getElementById("notif-list");
        let count = document.getElementById("notif-count");

        list.innerHTML = "";

        if (data.length > 0) {
            count.style.display = "inline-block";
            count.innerText = data.length;
        } else {
            count.style.display = "none";
        }

        data.forEach(n => {
            let item = document.createElement("div");
            item.style.padding = "10px";
            item.style.borderBottom = "1px solid #eee";
            item.style.cursor = "pointer";

            item.innerHTML = `
                <div>${n.message}</div>
                <small style="color:gray">${n.created_at}</small>
            `;

            item.onclick = function() {
                markRead(n.id);
                item.style.background = "#f0f0f0";
            };

            list.appendChild(item);
        });
    });
}

function markRead(id) {
    fetch('mark_notif_read.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: 'id=' + id
    });
}

// auto refresh
setInterval(loadNotif, 3000);
loadNotif();

let lastScrollTop = 0;
const navbar = document.querySelector(".navbar");

window.addEventListener("scroll", function () {
    let scrollTop = window.pageYOffset || document.documentElement.scrollTop;

    if (scrollTop > lastScrollTop) {
        
        navbar.style.top = "-100px";
    } else {
        
        navbar.style.top = "0";
    }

    lastScrollTop = scrollTop;
});
</script>
</body>
<div class="scrolling-text">
    <div class="scrolling-text-content">
       ----------------------------------------⭐ Selamat datang di situs MPPK SMKN 1 Cibinong! ⭐----------------------------------------
        ⭐ © 2026 MPPK SMKN 1 Cibinong | Developed by Team 7 ⭐----------------------------------------
    </div>
</div>
<script>
function openModal(id, title, description, imgSrc) {
    document.getElementById('modalTitle').textContent = title;
    document.getElementById('modalDescription').textContent = description;
    document.getElementById('modalImage').src = imgSrc;
    document.getElementById('photoModal').classList.add('active');
}

function closeModal() {
    document.getElementById('photoModal').classList.remove('active');
}

// Tutup modal ketika klik di luar modal
document.getElementById('photoModal').addEventListener('click', function(e) {
    if (e.target === this) {
        closeModal();
    }
});

// Tutup modal dengan tombol Escape
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        closeModal();
    }
});

function openProfile() {
    document.getElementById("profileModal").classList.add("active");
}

function closeProfile() {
    document.getElementById("profileModal").classList.remove("active");
}
document.getElementById("profileModal").addEventListener("click", function(e) {
    if (e.target === this) {
        closeProfile();
    }
});
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        closeProfile();
    }
});
</script>
</html>