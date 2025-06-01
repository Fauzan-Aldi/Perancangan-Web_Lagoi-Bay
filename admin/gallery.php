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
    $caption = trim($_POST['caption']);
    $imageName = "";

    if (!empty($_FILES['image']['name'])) {
        $imageName = time() . '_' . basename($_FILES["image"]["name"]);
        $targetFile = __DIR__ . "/../uploads/" . $imageName;
        if (!move_uploaded_file($_FILES["image"]["tmp_name"], $targetFile)) {
            $error = "Gagal upload gambar.";
        }
    }

    if ($caption && !$error) {
        $stmt = $conn->prepare("INSERT INTO gallery (caption, image) VALUES (?, ?)");
        $stmt->bind_param("ss", $caption, $imageName);
        if ($stmt->execute()) {
            $success = "Gambar galeri berhasil ditambahkan.";
        } else {
            $error = "Gagal menambahkan gambar galeri.";
        }
        $stmt->close();
    } else if (!$caption) {
        $error = "Caption wajib diisi.";
    }
}

// DELETE
if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);
    $stmt = $conn->prepare("DELETE FROM gallery WHERE id = ?");
    $stmt->bind_param("i", $id);
    if ($stmt->execute()) {
        $success = "Gambar berhasil dihapus.";
    } else {
        $error = "Gagal menghapus gambar.";
    }
    $stmt->close();
}

// UPDATE
if (isset($_POST['edit'])) {
    $id = intval($_POST['id']);
    $caption = trim($_POST['caption']);
    $imageName = $_POST['old_image'];

    if (!empty($_FILES['image']['name'])) {
        $imageName = time() . '_' . basename($_FILES["image"]["name"]);
        $targetFile = __DIR__ . "/../uploads/" . $imageName;
        move_uploaded_file($_FILES["image"]["tmp_name"], $targetFile);
    }

    if ($caption) {
        $stmt = $conn->prepare("UPDATE gallery SET caption=?, image=? WHERE id=?");
        $stmt->bind_param("ssi", $caption, $imageName, $id);
        if ($stmt->execute()) {
            $success = "Galeri berhasil diperbarui.";
        } else {
            $error = "Gagal memperbarui galeri.";
        }
        $stmt->close();
    } else {
        $error = "Caption wajib diisi.";
    }
}

$result = $conn->query("SELECT * FROM gallery ORDER BY id DESC");
?>

<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <title>Kelola Gallery</title>
  <link rel="stylesheet" href="admin-style.css">
</head>
<body>

<div class="sidebar">
  <h2>Admin Panel</h2>
  <a href="dashboard.php">Dashboard</a>
  <a href="staycation.php">Staycation</a>
  <a href="packages.php">Packages</a>
  <a href="gallery.php" class="active">Gallery</a>
  <a href="logout.php" style="color: #ff4d4d;">Logout</a>
</div>

<div class="content">
  <h1>Kelola Gallery</h1>

  <?php if ($error): ?>
    <p class="error-msg"><?= htmlspecialchars($error) ?></p>
  <?php endif; ?>
  <?php if ($success): ?>
    <p class="success-msg"><?= htmlspecialchars($success) ?></p>
  <?php endif; ?>

  <button class="btn" onclick="document.getElementById('addModal').style.display='flex'">+ Tambah Gambar</button>

  <!-- Modal Tambah -->
  <div class="modal-overlay" id="addModal">
    <div class="modal">
      <span class="close" onclick="document.getElementById('addModal').style.display='none'">&times;</span>
      <h2>Tambah Galeri</h2>
      <form method="post" enctype="multipart/form-data">
        <textarea name="caption" placeholder="Caption" required></textarea>
        <input type="file" name="image" accept="image/*" required />
        <button type="submit" name="add" class="btn">Simpan</button>
      </form>
    </div>
  </div>

  <!-- Modal Edit -->
  <?php if (isset($_GET['edit'])): 
    $editId = intval($_GET['edit']);
    $editStmt = $conn->prepare("SELECT * FROM gallery WHERE id = ?");
    $editStmt->bind_param("i", $editId);
    $editStmt->execute();
    $editData = $editStmt->get_result()->fetch_assoc();
    $editStmt->close();
  ?>
  <div class="modal-overlay" id="editModal" style="display: flex;">
    <div class="modal">
      <span class="close" onclick="window.location.href='gallery.php'">&times;</span>
      <h2>Edit Gambar Galeri</h2>
      <form method="post" enctype="multipart/form-data">
        <input type="hidden" name="id" value="<?= $editData['id'] ?>">
        <input type="hidden" name="old_image" value="<?= $editData['image'] ?>">
        <textarea name="caption" required><?= htmlspecialchars($editData['caption']) ?></textarea>
        <input type="file" name="image" accept="image/*" />
        <?php if ($editData['image']): ?>
          <p><img src="../uploads/<?= $editData['image'] ?>" style="max-width: 100px;"></p>
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
          <th>Gambar</th>
          <th>Caption</th>
          <th>Aksi</th>
        </tr>
      </thead>
      <tbody>
        <?php while ($row = $result->fetch_assoc()): ?>
        <tr>
          <td><?= $row['id'] ?></td>
          <td>
            <?php if ($row['image']): ?>
              <img src="../uploads/<?= $row['image'] ?>" style="max-width: 80px;" />
            <?php else: ?>
              -
            <?php endif; ?>
          </td>
          <td><?= htmlspecialchars($row['caption']) ?></td>
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
