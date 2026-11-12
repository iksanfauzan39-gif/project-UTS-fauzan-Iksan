<?php
session_start();
require 'koneksi.php';

// Proteksi Halaman Login
if (!isset($_SESSION['user_logged_in']) || $_SESSION['user_logged_in'] !== true) {
    header("Location: login.php");
    exit;
}

// Fitur Logout
if (isset($_GET['action']) && $_GET['action'] === 'logout') {
    session_destroy();
    header("Location: login.php");
    exit;
}

// READ: Ambil data produk, dengan filter pencarian opsional
$search = trim($_GET['search'] ?? '');
$total_produk = (int) $pdo->query("SELECT COUNT(*) FROM products")->fetchColumn();

if ($search !== '') {
    $stmt = $pdo->prepare("SELECT * FROM products WHERE ProductName LIKE ? ORDER BY ProductID DESC");
    $stmt->execute(['%' . $search . '%']);
} else {
    $stmt = $pdo->query("SELECT * FROM products ORDER BY ProductID DESC");
}
$produk_list = $stmt->fetchAll();

// Cek apakah ada aksi Edit (untuk mengisi form)
$edit_data = null;
if (isset($_GET['action']) && $_GET['action'] === 'edit' && isset($_GET['id'])) {
    $stmt_edit = $pdo->prepare("SELECT * FROM products WHERE ProductID = ?");
    $stmt_edit->execute([$_GET['id']]);
    $edit_data = $stmt_edit->fetch();
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Ruang abu</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; font-family: 'Segoe UI', sans-serif; }
        body {
            background:
                linear-gradient(rgba(0, 0, 0, 0.35), rgba(0, 0, 0, 0.35)),
                url('https://png.pngtree.com/thumb_back/fh260/background/20210908/pngtree-mottled-wall-texture-abstract-light-gray-background-image_808475.jpg')
                center / cover fixed;
            color: #222;
            display: flex;
            min-height: 100vh;
        }
        .sidebar { width: 250px; background: rgba(15, 15, 15, 0.92); color: #fff; padding: 25px; display: flex; flex-direction: column; justify-content: space-between; }
        .sidebar .brand { font-size: 24px; font-weight: 700; margin-bottom: 30px; display: flex; align-items: center; gap: 8px; }
        .sidebar nav a { display: block; color: #d1d1d1; text-decoration: none; padding: 12px 15px; border-radius: 8px; margin-bottom: 8px; transition: 0.3s; }
        .sidebar nav a:hover, .sidebar nav a.active { background-color: #333; color: #fff; }
        .main-content { flex: 1; padding: 30px; overflow-y: auto; }
        .header { background: rgba(255, 255, 255, 0.9); backdrop-filter: blur(12px); -webkit-backdrop-filter: blur(12px); padding: 20px 30px; border-radius: 12px; box-shadow: 0 4px 12px rgba(0,0,0,0.12); display: flex; justify-content: space-between; align-items: center; margin-bottom: 25px; }
        .user-badge { display: flex; align-items: center; gap: 10px; font-weight: 600; color: #222; }
        .user-badge img { width: 40px; height: 40px; border-radius: 50%; object-fit: cover; }
        .cards { display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 20px; margin-bottom: 30px; }
        .card { background: rgba(255, 255, 255, 0.9); backdrop-filter: blur(12px); -webkit-backdrop-filter: blur(12px); padding: 20px; border-radius: 12px; box-shadow: 0 4px 12px rgba(0,0,0,0.12); }
        .card h3 { font-size: 14px; color: #666; margin-bottom: 10px; }
        .card .value { font-size: 24px; font-weight: 700; color: #171717; }
        .header-actions { display: flex; align-items: center; gap: 20px; }
        .logout-btn { display: inline-flex; align-items: center; gap: 8px; padding: 10px 14px; border-radius: 8px; background: #171717; color: #fff; text-decoration: none; font-weight: 600; transition: background 0.2s; }
        .logout-btn:hover { background: #444; }

        /* Styling CRUD */
        .crud-section { background: rgba(255, 255, 255, 0.9); backdrop-filter: blur(12px); -webkit-backdrop-filter: blur(12px); padding: 25px; border-radius: 12px; box-shadow: 0 4px 12px rgba(0,0,0,0.12); }
        .crud-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; }
        .form-crud { display: flex; gap: 10px; flex-wrap: wrap; margin-bottom: 25px; background: rgba(245, 245, 245, 0.9); padding: 15px; border-radius: 8px; border: 1px solid #ddd; }
        .form-crud input { padding: 10px 12px; border: 1px solid #ccc; border-radius: 6px; flex: 1; min-width: 150px; }
        .btn { padding: 10px 18px; border: none; border-radius: 6px; cursor: pointer; font-weight: 600; transition: 0.2s; text-decoration: none; display: inline-flex; align-items: center; gap: 6px; }
        .btn-success { background: linear-gradient(90deg, #0d0c0d, #141414 50%, #101010); color: #fff; }
        .btn-success:hover { opacity: 0.9; }
        .btn-warning { background: #555; color: #fff; }
        .btn-secondary { background: #888; color: #fff; }
        
        .table-container { width: 100%; overflow-x: auto; }
        .search-form { display: flex; gap: 10px; margin-bottom: 15px; }
        .search-form input { flex: 1; min-width: 0; padding: 10px 12px; border: 1px solid #ccc; border-radius: 6px; }
        .search-form .btn { background: #171717; color: #fff; }
        .search-form .btn:hover { background: #444; }
        .search-reset { align-self: center; color: #555; text-decoration: none; font-size: 14px; }
        .search-reset:hover { color: #111; }
        table { width: 100%; border-collapse: collapse; text-align: left; }
        th, td { padding: 12px 15px; border-bottom: 1px solid #ddd; }
        th { background-color: #e9e9e9; color: #222; }
        tr:hover { background-color: #f5f5f5; }
        .actions { display: flex; gap: 8px; }
        .actions a { padding: 6px 10px; border-radius: 4px; text-decoration: none; font-size: 13px; color: #fff; }
        .btn-edit { background-color: #555; }
        .btn-delete { background-color: #e74c3c; }
    </style>
</head>
<body>

    <aside class="sidebar">
        <div>
            <div class="brand">Ruang abu</div>
            <nav>
                <a href="dashboard.php" class="active"><i class="fa-solid fa-house"></i> Dashboard</a>
                <a href="#"><i class="fa-solid fa-user"></i> Profil</a>
                <a href="#"><i class="fa-solid fa-gear"></i> Pengaturan</a>
            </nav>
        </div>
    </aside>

    <main class="main-content">
        <header class="header">
            <h2>Dashboard Utama</h2>
            <div class="header-actions">
                <div class="user-badge">
                <img src="fauzan.png.png" alt="Foto profil">
                    <span><?= htmlspecialchars($_SESSION['user_email'] ?? 'User'); ?></span>
                </div>
                <a href="dashboard.php?action=logout" class="logout-btn"><i class="fa-solid fa-right-from-bracket"></i> Logout</a>
            </div>
        </header>

        <section class="cards">
            <div class="card">
                <h3>Status Akun</h3>
                <div class="value">Aktif</div>
            </div>
            <div class="card">
                <h3>Domain Email</h3>
                <div class="value">Gmail</div>
            </div>
            <div class="card">
                <h3>Total Produk</h3>
                <div class="value"><?= $total_produk; ?></div>
            </div>
        </section>

        <!-- KONTEN CRUD PRODUK -->
        <section class="crud-section">
            <div class="crud-header">
                <h3><i class="fa-solid fa-box"></i> Manajemen Produk</h3>
            </div>

            <!-- Form mengarah ke proses_produk.php -->
            <form method="POST" action="proses_produk.php" class="form-crud">
                <?php if ($edit_data): ?>
                    <input type="hidden" name="product_id" value="<?= $edit_data['ProductID'] ?>">
                    <input type="text" name="product_name" placeholder="Nama Produk" value="<?= htmlspecialchars($edit_data['ProductName']) ?>" required>
                    <input type="number" step="0.01" name="price" placeholder="Harga" value="<?= htmlspecialchars($edit_data['Price'] ?? 0) ?>" required>
                    <button type="submit" name="edit_produk" class="btn btn-warning"><i class="fa-solid fa-pen-to-square"></i> Update Produk</button>
                    <a href="dashboard.php" class="btn btn-secondary">Batal</a>
                <?php else: ?>
                    <input type="text" name="product_name" placeholder="Nama Produk Baru" required>
                    <input type="number" step="0.01" name="price" placeholder="Harga Produk" required>
                    <button type="submit" name="tambah_produk" class="btn btn-success"><i class="fa-solid fa-plus"></i> Tambah Produk</button>
                <?php endif; ?>
            </form>

            <!-- Tabel Data -->
            <form method="GET" action="dashboard.php" class="search-form">
                <input type="search" name="search" placeholder="Cari nama produk..." value="<?= htmlspecialchars($search, ENT_QUOTES, 'UTF-8'); ?>">
                <button type="submit" class="btn"><i class="fa-solid fa-magnifying-glass"></i> Cari</button>
                <?php if ($search !== ''): ?>
                    <a href="dashboard.php" class="search-reset">Reset</a>
                <?php endif; ?>
            </form>
            <div class="table-container">
                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Nama Produk</th>
                            <th>Harga</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (count($produk_list) > 0): ?>
                            <?php foreach ($produk_list as $produk): ?>
                                <tr>
                                    <td><?= htmlspecialchars($produk['ProductID']) ?></td>
                                    <td><?= htmlspecialchars($produk['ProductName']) ?></td>
                                    <td>$<?= number_format($produk['Price'] ?? 0, 2) ?></td>
                                    <td class="actions">
                                        <a href="dashboard.php?action=edit&id=<?= $produk['ProductID'] ?><?= $search !== '' ? '&amp;search=' . urlencode($search) : '' ?>" class="btn-edit"><i class="fa-solid fa-pen"></i> Edit</a>
                                        <a href="proses_produk.php?action=delete&id=<?= $produk['ProductID'] ?>" class="btn-delete" onclick="return confirm('Yakin ingin menghapus produk ini?')"><i class="fa-solid fa-trash"></i> Hapus</a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="4" style="text-align: center; color: #888;"><?= $search !== '' ? 'Produk tidak ditemukan.' : 'Belum ada data produk di database.' ?></td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </section>
    </main>

</body>
</html>