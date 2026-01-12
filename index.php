<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="icon" type="image/png" sizes="32x32" href="assets\img\Logo.webp">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-EVSTQN3/azprG1Anm3QDgpJLIm9Nao0Yz1ztcQTwFspd3yD65VohhpuuCOmLASjC" crossorigin="anonymous">   

    <title>Portal Administrasi Tugas Akhir</title>
    
    <style>
        body {
            font-family: 'Inter', sans-serif;
        }
        .fullscreen-bg {
            position: relative;
            height: 100vh;
            overflow: hidden;
        }
        video {
            position: absolute;
            top: 50%;
            left: 50%;
            min-width: 100%;
            min-height: 100%;
            width: auto;
            height: auto;
            z-index: 1;
            transform: translate(-50%, -50%);
        }
        .overlay {
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0, 0, 0, 0.5); /* Dark overlay */
            z-index: 2;
        }
        .content {
            position: relative;
            z-index: 3;
            color: white;
            text-align: center;
            top: 50%;
            transform: translateY(-50%);
        }
        h1 {
            color: #ffffff;
            text-shadow: 0 0 10px rgba(0, 0, 0, 1),
                         0 0 20px rgba(0, 0, 0, 1);
        }
        .btn-login {
            display: inline-block;
            padding: 7px 25px;
            font-weight: 500;
            color: #000000;
            background-color: #ffffff;
            border-radius: 8px;
            text-decoration: none;
            transition: all 0.3s ease;
        }
        .btn-login:hover {
            color: #000000;
            background: linear-gradient(135deg, #FF74C7, #FF983D);
            transform: translateY(-2px);
        }
    </style>
</head>
<body>

    <div class="fullscreen-bg">
        <video autoplay muted loop>
            <source src="assets/img/PoliteknikNest.mp4" type="video/mp4">
        </video>
        <div class="overlay"></div>
        <div class="content text-center">
            <h1>Sistem Informasi Tugas Akhir <br>Politeknik Nest Sukoharjo</h1>
            <br>
            <div class="d-flex flex-wrap justify-content-center">
                <img src="assets/img/client-31.png" alt="Teknologi Informasi Politeknik Nest" class="img-fluid m-2" style="max-height:60px;">
                <img src="assets/img/Teknologi Informasi Politeknik Nest.png" alt="Teknologi Informasi Politeknik Nest" class="img-fluid m-2" style="max-height:60px;">
                <img src="assets/img/hotel.png" alt="Teknologi Informasi Politeknik Nest" class="img-fluid m-2" style="max-height:60px;">
            </div>
            <br><br><br><br>
            <a href="login.php" class="btn btn-login">Start</a>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js" integrity="sha384-MrcW6ZMFYlzcLA8Nl+NtUVF0sA7MsXsP1UyJoMp4YLEuNSfAP+JcXn/tWtIaxVXM" crossorigin="anonymous"></script></body>

</html>