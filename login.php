<?php
session_start();
require "config/connection.php";

// Kalau sudah login, arahkan ke dashboard
if (isset($_SESSION['user'])) {
    $role = $_SESSION['user']['role'];
    if ($role === 'admin') header("Location: admin/dashboard.php");
    elseif ($role === 'mahasiswa') header("Location: mahasiswa/dashboard.php");
    elseif ($role === 'dosen') header("Location: dosen/dashboard.php");
    exit;
}

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);

    $tables = ['admin','mahasiswa','dosen'];
    foreach ($tables as $table) {
        $stmt = $pdo->prepare("SELECT * FROM $table WHERE username=? LIMIT 1");
        $stmt->execute([$username]);
        $user = $stmt->fetch();

        if ($user) {
            // cek password plain text sementara (jika belum hash)
            if (password_verify($password, $user['password'])) {
                $_SESSION['user'] = [
                    'id' => $user['id'],  // sesuai kolom PK di tabel
                    'username' => $user['username'],
                    'role' => $table
                ];
                if ($table==='admin') header("Location: admin/dashboard.php");
                elseif ($table==='mahasiswa') header("Location: mahasiswa/dashboard.php");
                elseif ($table==='dosen') header("Location: dosen/dashboard.php");
                exit;
            }
        }
    }
    $error = "Username atau password salah!";
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Login Portal TA</title>
<link rel="stylesheet" href="style.css">
</head>
<body>
<main>
    <h1>Login Portal TA</h1>
    <?php if($error): ?>
        <p style="color:red"><?= htmlspecialchars($error) ?></p>
    <?php endif; ?>
    <form action="" method="POST">
        <label>Username:</label>
        <input type="text" name="username" required>
        <label>Password:</label>
        <input type="password" name="password" required>
        <button type="submit">Login</button>
    </form>
</main>
</body>
</html>
