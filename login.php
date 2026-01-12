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
            if (password_verify($password, $user['password'])) {
                $_SESSION['user'] = [
                    'id' => $user['id'],
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
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Login Portal TA</title>

<style>
*{
    margin:0;
    padding:0;
    box-sizing:border-box;
    font-family:'Segoe UI',sans-serif;
}

body{
    background:#f2f2f2;
}

.container{
    width:90%;
    max-width:1100px;
    margin:40px auto;
    background:#fff;
    border-radius:16px;
    display:flex;
    overflow:hidden;
    box-shadow:0 10px 30px rgba(0,0,0,0.1);
}

/* LEFT */
.left{
    width:50%;
    padding:50px;
    background:linear-gradient(135deg,#ff6aa2,#ff9a3c);
    color:#fff;
}

.left h2{
    font-size:26px;
    line-height:1.4;
}

.left img{
    width:100%;
    margin-top:30px;
}

/* RIGHT */
.right{
    width:50%;
    padding:50px;
}

.right h2{
    margin-bottom:25px;
}

form{
    display:flex;
    flex-direction:column;
}

input{
    border:none;
    border-bottom:1px solid #ccc;
    padding:12px 5px;
    margin-bottom:20px;
    outline:none;
    font-size:14px;
}

button{
    background:linear-gradient(to right,#ff6aa2,#ff9a3c);
    border:none;
    padding:12px;
    border-radius:25px;
    color:#fff;
    font-weight:bold;
    cursor:pointer;
}

button:hover{
    opacity:0.9;
}

.error{
    color:red;
    margin-bottom:15px;
}

/* ================= MOBILE ================= */
@media(max-width:768px){

.container{
    flex-direction:column;
}

.left{
    width:100%;
    text-align:center;
    padding:30px 20px;
}

.left h2{
    font-size:20px;
}

.left img{
    width:80%;
}

.right{
    width:100%;
    padding:30px 20px;
}

}
</style>

</head>
<body>

<div class="container">

    <!-- LEFT -->
    <div class="left">
        <h2>
            Selamat datang di Portal<br>
            Pengumpulan Tugas Akhir<br>
            Politeknik Nest
        </h2>

        <!-- GANTI dengan gambar kamu -->
        <img src="assets/img/login.png">
    </div>

    <!-- RIGHT -->
    <div class="right">
        <h2>Log In</h2>

        <?php if($error): ?>
            <p class="error"><?= htmlspecialchars($error) ?></p>
        <?php endif; ?>

        <form action="" method="POST">
            <input type="text" name="username" placeholder="Username" required>
            <input type="password" name="password" placeholder="Password" required>

            <button type="submit">Log In</button>
        </form>
    </div>

</div>

</body>
</html>
