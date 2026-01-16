<?php
/* Archivo: admin/index.php (Login del Administrador) */
session_start();
require '../config/db.php';
require '../includes/funciones.php';

$mensaje = "";

// Si ya es admin, pásale directo al dashboard
if (isset($_SESSION['admin_id'])) {
    header("Location: dashboard.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Limpiamos los datos de entrada
    $email = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);
    $password = $_POST['password'];

    // Buscamos usuario que sea ADMIN
    $sql = "SELECT * FROM usuarios WHERE email = ? AND rol = 'admin'";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$email]);
    $admin = $stmt->fetch();

    // Verificamos contraseña encriptada
    if ($admin && password_verify($password, $admin['password'])) {
        $_SESSION['admin_id'] = $admin['id'];
        $_SESSION['admin_nombre'] = $admin['nombre'];
        
        header("Location: dashboard.php");
        exit();
    } else {
        $mensaje = "❌ Acceso denegado. Verifica tus datos o permisos.";
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Login Admin</title>
    <link rel="stylesheet" href="css/admin.css">
    <style>
        body { display: flex; justify-content: center; align-items: center; height: 100vh; background: #2c3e50; font-family: sans-serif; }
        .login-box { background: white; padding: 40px; border-radius: 10px; box-shadow: 0 10px 25px rgba(0,0,0,0.5); width: 300px; text-align: center; }
        .input-group { margin-bottom: 20px; text-align: left; }
        .input-group label { display: block; margin-bottom: 5px; font-weight: bold; color: #333; }
        .input-group input { width: 100%; padding: 10px; border: 1px solid #ccc; border-radius: 5px; box-sizing: border-box; }
        .btn { background: #e74c3c; color: white; padding: 10px 20px; border: none; border-radius: 5px; cursor: pointer; width: 100%; font-size: 16px; }
        .btn:hover { background: #c0392b; }
    </style>
</head>
<body>

    <div class="login-box">
        <h2 style="margin-bottom: 20px; color: #e74c3c;">Panel Admin</h2>
        
        <?php if($mensaje): ?>
            <div style="background: #fadbd8; color: #721c24; padding: 10px; margin-bottom: 15px; border-radius: 5px; font-size: 0.9rem;">
                <?php echo $mensaje; ?>
            </div>
        <?php endif; ?>

        <form method="POST" action="">
            <div class="input-group">
                <label>Correo Admin:</label>
                <input type="email" name="email" required placeholder="admin@restaurante.com">
            </div>
            <div class="input-group">
                <label>Contraseña:</label>
                <input type="password" name="password" required placeholder="******">
            </div>
            <button type="submit" class="btn">Entrar</button>
        </form>
        
        <p style="margin-top: 20px; font-size: 0.8rem;">
            <a href="../index.php" style="color: #666;">⬅ Volver a la web</a>
        </p>
    </div>

</body>
</html>