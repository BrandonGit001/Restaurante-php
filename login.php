<?php
/* Archivo: login.php (Login Inteligente) */
session_start();
require 'config/db.php';
require 'includes/funciones.php';

$error = "";

// Si ya hay sesión, redirigir según quién sea
if(isset($_SESSION['admin_id'])) {
    header("Location: admin/dashboard.php");
    exit();
}
if(isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);
    $pass = $_POST['password'];

    // Buscamos al usuario por email
    $stmt = $pdo->prepare("SELECT * FROM usuarios WHERE email = ?");
    $stmt->execute([$email]);
    $usuario = $stmt->fetch();

    // Verificamos contraseña
    if ($usuario && password_verify($pass, $usuario['password'])) {
        
        // --- AQUÍ ESTÁ LA MAGIA DEL LOGIN INTELIGENTE ---
        
        if ($usuario['rol'] === 'admin') {
            // CASO 1: ES ADMINISTRADOR
            $_SESSION['admin_id'] = $usuario['id'];      // Variable para el Panel Admin
            $_SESSION['user_id'] = $usuario['id'];       // Variable para la Web (opcional)
            $_SESSION['user_nombre'] = $usuario['nombre'];
            $_SESSION['user_rol'] = 'admin';
            
            header("Location: admin/dashboard.php"); // 👉 Al Panel de Control
            exit();

        } else {
            // CASO 2: ES CLIENTE NORMAL
            $_SESSION['user_id'] = $usuario['id'];
            $_SESSION['user_nombre'] = $usuario['nombre'];
            $_SESSION['user_rol'] = 'cliente';
            
            header("Location: index.php"); // 👉 Al Menú de Comida
            exit();
        }

    } else {
        $error = "❌ Correo o contraseña incorrectos.";
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Iniciar Sesión</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
        .auth-container {
            max-width: 400px; margin: 50px auto; padding: 30px;
            background: white; border-radius: 15px; box-shadow: 0 10px 30px rgba(0,0,0,0.1);
        }
        .btn-auth {
            width: 100%; padding: 12px; background: var(--primary); color: white;
            border: none; border-radius: 8px; font-weight: bold; cursor: pointer;
        }
        .input-group { margin-bottom: 15px; }
        .input-group input { width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 8px; }
    </style>
</head>
<body>

    <header>
        <div class="logo">Mi Restaurante</div>
        <a href="index.php" style="text-decoration: none; color: var(--secondary); font-weight: bold;">⬅ Volver al Menú</a>
    </header>

    <div class="auth-container">
        <h2 style="text-align: center; margin-bottom: 20px; color: var(--secondary);">Bienvenido</h2>
        
        <?php if($error): ?>
            <div style="background: #fadbd8; color: #721c24; padding: 10px; border-radius: 5px; margin-bottom: 15px; text-align: center;">
                <?php echo $error; ?>
            </div>
        <?php endif; ?>

        <form method="POST" action="">
            <div class="input-group">
                <label>Correo Electrónico:</label>
                <input type="email" name="email" required>
            </div>

            <div class="input-group">
                <label>Contraseña:</label>
                <input type="password" name="password" required>
            </div>

            <button type="submit" class="btn-auth">Entrar</button>
        </form>
        
        <p style="text-align: center; margin-top: 15px;">
            ¿No tienes cuenta? <a href="registro.php" style="color: var(--secondary);">Regístrate aquí</a>
        </p>
    </div>

</body>
</html>