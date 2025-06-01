<?php
session_start();

if (isset($_SESSION['user_id'])) {
  header("Location: dashboard.php");
  exit();
}

include './../config/config.php';

$error = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
  $username = trim($_POST['username']);
  $password = trim($_POST['password']);

  $hashedPassword = md5($password); // Gantilah dengan password_hash untuk produksi

  $sql = "SELECT id FROM users WHERE username=? AND password=?";
  $stmt = $conn->prepare($sql);
  $stmt->bind_param("ss", $username, $hashedPassword);
  $stmt->execute();
  $stmt->store_result();

  if ($stmt->num_rows == 1) {
    $stmt->bind_result($userId);
    $stmt->fetch();
    $_SESSION['user_id'] = $userId;
    header("Location: dashboard.php");
    exit();
  } else {
    $error = "Username atau password salah.";
  }

  $stmt->close();
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <title>Login</title>
  <link rel="stylesheet" href="styles.css" />
</head>
<body>
  <div class="login-container">
    <form method="post" action="" class="login-form">
      <img src="./../public/assets/images/logo.svg" alt="Tourly Logo" class="login-logo" />
      <input type="text" name="username" placeholder="Username" class="login-input" required />
      <input type="password" name="password" placeholder="Password" class="login-input" required />
      <button type="submit" class="login-btn">Submit</button>
    </form>
  </div>
</body>
</html>





