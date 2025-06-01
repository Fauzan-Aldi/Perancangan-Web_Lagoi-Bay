<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

include './../config/config.php';

$error = "";
$success = "";

// CREATE
if (isset($_POST['add'])) {
    $title = trim($_POST['title']);
    $price = trim($_POST['price']);
    $description = trim($_POST['description']);
    $imageName = "";

    if (!empty($_FILES['image']['name'])) {
        $imageName = time() . '_' . basename($_FILES["image"]["name"]);
        $targetFile = __DIR__ . "/../uploads/" . $imageName;
        if (!move_uploaded_file($_FILES["image"]["tmp_name"], $targetFile)) {
            $error = "Gagal mengunggah gambar.";
        }
    }

    if ($title && $price && !$error) {
        $stmt = $conn->prepare("INSERT INTO packages (title, price, description, image) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("sdss", $title, $price, $description, $imageName);
        if ($stmt->execute()) {
            $success = "Paket berhasil ditambahkan.";
        } else {
            $error = "Gagal menambahkan paket.";
        }
        $stmt->close();
    } elseif (!$error) {
        $error = "Judul dan harga wajib diisi.";
    }
}

// DELETE
if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);
    $stmt = $conn->prepare("DELETE FROM packages WHERE id = ?");
    $stmt->bind_param("i", $id);
    if ($stmt->execute()) {
        $success = "Paket berhasil dihapus.";
    } else {
        $error = "Gagal menghapus paket.";
    }
    $stmt->close();
}

// UPDATE
if (isset($_POST['edit'])) {
    $id = intval($_POST['id']);
    $title = trim($_POST['title']);
    $price = trim($_POST['price']);
    $description = trim($_POST['description']);
    $imageName = $_POST['old_image'];

    if (!empty($_FILES['image']['name'])) {
        $imageName = time() . '_' . basename($_FILES["image"]["name"]);
        $targetFile = __DIR__ . "/../uploads/" . $imageName;
        if (!move_uploaded_file($_FILES["image"]["tmp_name"], $targetFile)) {
            $error = "Gagal mengunggah gambar baru.";
        }
    }

    if ($title && $price && !$error) {
        $stmt = $conn->prepare("UPDATE packages SET title=?, price=?, description=?, image=? WHERE id=?");
        $stmt->bind_param("sdssi", $title, $price, $description, $imageName, $id);
        if ($stmt->execute()) {
            $success = "Paket berhasil diupdate.";
        } else {
            $error = "Gagal mengupdate paket.";
        }
        $stmt->close();
    } elseif (!$error) {
        $error = "Judul dan harga wajib diisi.";
    }
}

$result = $conn->query("SELECT * FROM packages ORDER BY id DESC");
?>

<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <title>Kelola Packages</title>
  <link rel="stylesheet" href="admin-style.css">
</head>
<body>

<div class="sidebar">
  <h2>Admin Panel</h2>
  <a href="dashboard.php">Dashboard</a>
  <a href="staycation.php">Staycation</a>
  <a href="packages.php" class="active">Packages</a>
  <a href="gallery.php">Gallery</a>
  <a href="logout.php" style="color: #ff4d4d;">Logout</a>
</div>

<div class="content">
  <h1>Kelola Packages</h1>

  <?php if ($error): ?>
    <p class="error-msg"><?= htmlspecialchars($error) ?></p>
  <?php endif; ?>
  <?php if ($success): ?>
    <p class="success-msg"><?= htmlspecialchars($success) ?></p>
  <?php endif; ?>

  <button class="btn" onclick="document.getElementById('addModal').style.display='flex'">+ Tambah Paket</button>

  <!-- Modal Tambah -->
  <div class="modal-overlay" id="addModal">
    <div class="modal">
      <span class="close" onclick="document.getElementById('addModal').style.display='none'">&times;</span>
      <h2>Tambah Paket</h2>
      <form method="post" enctype="multipart/form-data">
        <input type="text" name="title" placeholder="Judul Paket" required />
        <input type="number" name="price" placeholder="Harga" step="0.01" required />
        <textarea name="description" placeholder="Deskripsi" rows="3"></textarea>
        <input type="file" name="image" accept="image/*" />
        <button type="submit" name="add" class="btn">Simpan</button>
      </form>
    </div>
  </div>

  <!-- Modal Edit -->
  <?php if (isset($_GET['edit'])): 
    $editId = intval($_GET['edit']);
    $editStmt = $conn->prepare("SELECT * FROM packages WHERE id = ?");
    $editStmt->bind_param("i", $editId);
    $editStmt->execute();
    $editData = $editStmt->get_result()->fetch_assoc();
    $editStmt->close();
  ?>
  <div class="modal-overlay" id="editModal" style="display: flex;">
    <div class="modal">
      <span class="close" onclick="window.location.href='packages.php'">&times;</span>
      <h2>Edit Paket</h2>
      <form method="post" enctype="multipart/form-data">
        <input type="hidden" name="id" value="<?= $editData['id'] ?>">
        <input type="hidden" name="old_image" value="<?= $editData['image'] ?>">
        <input type="text" name="title" value="<?= htmlspecialchars($editData['title']) ?>" required />
        <input type="number" name="price" value="<?= htmlspecialchars($editData['price']) ?>" required />
        <textarea name="description"><?= htmlspecialchars($editData['description']) ?></textarea>
        <input type="file" name="image" accept="image/*" />
        <?php if ($editData['image']): ?>
          <p><img src="../uploads/<?= $editData['image'] ?>" alt="Preview" style="max-width: 100px;"></p>
        <?php endif; ?>
        <button type="submit" name="edit" class="btn">Update</button>
      </form>
    </div>
  </div>
  <?php endif; ?>

  <!-- Tabel -->
  <div class="table-wrapper">
    <table>
      <thead>
        <tr>
          <th>ID</th>
          <th>Judul</th>
          <th>Harga</th>
          <th>Deskripsi</th>
          <th>Gambar</th>
          <th>Aksi</th>
        </tr>
      </thead>
      <tbody>
        <?php while ($row = $result->fetch_assoc()): ?>
        <tr>
          <td><?= $row['id'] ?></td>
          <td><?= htmlspecialchars($row['title']) ?></td>
          <td>Rp <?= number_format($row['price'], 0, ',', '.') ?></td>
          <td><?= htmlspecialchars($row['description']) ?></td>
          <td>
            <?php if ($row['image']): ?>
              <img src="../uploads/<?= $row['image'] ?>" style="max-width: 80px;" />
            <?php else: ?>
              -
            <?php endif; ?>
          </td>
          <td>
            <a href="?edit=<?= $row['id'] ?>" class="btn" style="background:#2980b9;">Edit</a>
            <a href="?delete=<?= $row['id'] ?>" onclick="return confirm('Yakin ingin menghapus?')" class="btn" style="background:#c0392b;">Hapus</a>
          </td>
        </tr>
        <?php endwhile; ?>
      </tbody>
    </table>
  </div>
</div>

</body>
</html>
