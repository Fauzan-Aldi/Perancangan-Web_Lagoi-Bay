<?php
session_start();

// Redirect ke login.php jika belum login
if (!isset($_SESSION['user_id'])) {
  header("Location: login.php");
  exit();
}

// Koneksi ke database
$host = 'localhost';
$user = 'root';
$pass = '';
$db   = 'tourly_db';

$conn = new mysqli($host, $user, $pass, $db);
if ($conn->connect_error) {
  die("Koneksi gagal: " . $conn->connect_error);
}

// Ambil data packages dari database
$sql = "SELECT id, title, description, image, price FROM packages";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>All Travel Packages</title>
  <link rel="stylesheet" href="./assets/css/style.css">
</head>
<body>
  <h1 class="page-title">All Travel Packages</h1>
  <div class="package-container">
    <?php if ($result->num_rows > 0): ?>
      <?php while($row = $result->fetch_assoc()): ?>
        <div class="package-card">
          <img src="assets/images/<?php echo htmlspecialchars($row['image']); ?>" alt="<?php echo htmlspecialchars($row['title']); ?>" class="card-banner">
          <div class="card-content">
            <h3><?php echo htmlspecialchars($row['title']); ?></h3>
            <p><?php echo htmlspecialchars($row['description']); ?></p>
            <p class="price">Rp.<?php echo number_format($row['price'], 0, ',', '.'); ?> / per person</p>
            <form action="book.php" method="post">
              <input type="hidden" name="package_id" value="<?php echo $row['id']; ?>">
              <button type="submit" class="btn btn-primary">Book Now</button>
            </form>
          </div>
        </div>
      <?php endwhile; ?>
    <?php else: ?>
      <p class="no-packages">No packages available.</p>
    <?php endif; ?>
  </div>
</body>
</html>

<?php $conn->close(); ?>
