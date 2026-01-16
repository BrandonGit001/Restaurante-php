<?php
/* Archivo: registro.php */
session_start();
require 'config/db.php';
require 'includes/funciones.php';

$mensaje = "";

// SI YA ESTÁ LOGUEADO, LO MANDAMOS AL INICIO
if(isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

// LÓGICA DE REGISTRO
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nombre = limpiar_str($_POST['nombre']);
    $email = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);
    $pass = $_POST['password'];
    $tel = limpiar_str($_POST['telefono']);
    $dir = limpiar_str($_POST['direccion']);

    // Validar que no exista el email
    $stmt = $pdo->prepare("SELECT id FROM usuarios WHERE email = ?");
    $stmt->execute([$email]);
    
    if($stmt->rowCount() > 0) {
        $mensaje = "❌ Ese correo ya está registrado.";
    } else {
        // Encriptar contraseña (MUY IMPORTANTE)
        $pass_hash = password_hash($pass, PASSWORD_DEFAULT);

        try {
            $sql = "INSERT INTO usuarios (nombre, email, password, telefono, direccion) VALUES (?, ?, ?, ?, ?)";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$nombre, $email, $pass_hash, $tel, $dir]);
            
            $mensaje = "✅ ¡Cuenta creada! <a href='login.php'>Inicia sesión aquí</a>";
        } catch (PDOException $e) {
            $mensaje = "Error: " . $e->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Crear Cuenta</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
        .auth-container {
            max-width: 400px; margin: 50px auto; padding: 30px;
            background: white; border-radius: 15px; box-shadow: 0 10px 30px rgba(0,0,0,0.1);
        }
        .input-group { margin-bottom: 15px; }
        .input-group label { display: block; margin-bottom: 5px; font-weight: bold; color: var(--secondary); }
        .input-group input, .input-group textarea {
            width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 8px;
            font-family: inherit;
        }
        .btn-auth {
            width: 100%; padding: 12px; background: var(--secondary); color: white;
            border: none; border-radius: 8px; font-weight: bold; cursor: pointer;
        }
        .btn-auth:hover { background: var(--primary); }
    </style>
</head>
<body>

    <header>
        <div class="logo">Mi Restaurante</div>
        <a href="index.php" style="text-decoration: none; color: var(--secondary); font-weight: bold;">⬅ Volver al Menú</a>
    </header>

    <div class="auth-container">
        <h2 style="text-align: center; margin-bottom: 20px; color: var(--secondary);">Crear Cuenta</h2>
        
        <?php if($mensaje): ?>
            <div style="background: #e8f0fe; color: #1967d2; padding: 10px; border-radius: 5px; margin-bottom: 15px; text-align: center;">
                <?php echo $mensaje; ?>
            </div>
        <?php endif; ?>

        <form method="POST" action="">
            <div class="input-group">
                <label>Nombre Completo:</label>
                <input type="text" name="nombre" required placeholder="Ej: Juan Pérez">
            </div>
            
            <div class="input-group">
                <label>Correo Electrónico:</label>
                <input type="email" name="email" required placeholder="juan@gmail.com">
            </div>

            <div class="input-group">
                <label>Contraseña:</label>
                <input type="password" name="password" required placeholder="******">
            </div>

            <div class="input-group">
                <label>Teléfono (WhatsApp):</label>
                <input type="text" name="telefono" required placeholder="521...">
            </div>

            <div class="input-group">
                <label>Dirección de Entrega:</label>
                <textarea name="direccion" rows="2" placeholder="Calle, Número, Colonia..."></textarea>
            </div>

            <button type="submit" class="btn-auth">Registrarme</button>
        </form>
        
        <p style="text-align: center; margin-top: 15px;">
            ¿Ya tienes cuenta? <a href="login.php" style="color: var(--primary);">Inicia Sesión</a>
        </p>
    </div>

</body>
</html>