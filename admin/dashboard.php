<?php
session_start();
if (!isset($_SESSION['user_id'])) {
  header("Location: index.php");
  exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Dashboard Admin</title>
  <link rel="stylesheet" href="admin-style.css">
</head>
<body>

  <div class="sidebar">
    <h2>Admin Panel</h2>
    <a href="dashboard.php">Dashboard</a>
    <a href="staycation.php">Staycation</a>
    <a href="packages.php">Packages</a>
    <a href="gallery.php">Gallery</a>
    <a href="logout.php" style="color: red;">Logout</a>
  </div>

  <div class="content">
    <h1>Selamat Datang di Dashboard</h1>
    <p>Gunakan menu di sebelah kiri untuk mengelola konten website.</p>
    <div class="cards">
      <div class="card">
        <h3>Staycation</h3>
        <p>Kelola daftar tempat staycation yang tersedia.</p>
        <a href="staycation.php" class="btn">Kelola</a>
      </div>
      <div class="card">
        <h3>Packages</h3>
        <p>Atur paket perjalanan yang ditawarkan.</p>
        <a href="packages.php" class="btn">Kelola</a>
      </div>
      <div class="card">
        <h3>Gallery</h3>
        <p>Manajemen gambar dan dokumentasi wisata.</p>
        <a href="gallery.php" class="btn">Kelola</a>
      </div>
    </div>
  </div>

</body>
</html>
