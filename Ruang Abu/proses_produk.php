<?php
session_start();
require 'koneksi.php';

// Proteksi Akses
if (!isset($_SESSION['user_logged_in']) || $_SESSION['user_logged_in'] !== true) {
    header("Location: login.php");
    exit;
}


// 1. CREATE (Tambah Data)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['tambah_produk'])) {
    $productName = trim($_POST['product_name']);
    $price       = $_POST['price'];

    if (!empty($productName) && !empty($price)) {
        $stmt = $pdo->prepare("INSERT INTO products (ProductName, Price) VALUES (?, ?)");
        $stmt->execute([$productName, $price]);
    }
    header("Location: dashboard.php");
    exit;
}

// 2. UPDATE (Edit Data)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['edit_produk'])) {
    $productID   = $_POST['product_id'];
    $productName = trim($_POST['product_name']);
    $price       = $_POST['price'];

    $stmt = $pdo->prepare("UPDATE products SET ProductName = ?, Price = ? WHERE ProductID = ?");
    $stmt->execute([$productName, $price, $productID]);
    header("Location: dashboard.php");
    exit;
}

// 3. DELETE (Hapus Data)
if (isset($_GET['action']) && $_GET['action'] === 'delete' && isset($_GET['id'])) {
    $stmt = $pdo->prepare("DELETE FROM products WHERE ProductID = ?");
    $stmt->execute([$_GET['id']]);
    header("Location: dashboard.php");
    exit;
}
?>