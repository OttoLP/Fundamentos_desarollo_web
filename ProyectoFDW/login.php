<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>

    <link rel="stylesheet" href="styles.css">
    <style>
        html, body {
            min-height: 100vh;
            margin: 0;
        }

        body.login-page {
            display: flex;
            align-items: center;
            justify-content: center;
            background: linear-gradient(180deg, #f7e6d1 0%, #e4c7a0 55%, #c79b70 100%) !important;
            color: #4b2f1b;
        }

        .login-form {
            width: 360px;
            padding: 28px;
            border-radius: 18px;
            background: rgba(245, 232, 212, 0.96);
            box-shadow: 0 18px 40px rgba(84, 60, 40, 0.16);
            border: 1px solid rgba(160, 114, 63, 0.22);
        }
    </style>
</head>

<body class="login-page">
    <form action="./db/login.php" method="POST" class="login-form">
        <h1>Iniciar Sesión</h1>
        <label for="username">Usuario:</label>
        <input type="text" id="username" name="username">
        
        <label for="password">Contraseña:</label>
        <input type="password" id="password" name="password">
        
        <button type="submit">Ingresar</button>
        
        <?php
        if (isset($_GET['error'])) {
            $error = $_GET['error'];
            echo "<span class='error'> $error </span>";
        }
        ?>
    </form>
</body>

</html>