<?php
/* Archivo: reset_admin.php (CREADOR DE ADMIN) */
require 'config/db.php';

// DATOS DEL NUEVO ADMINISTRADOR
$email_admin = "admisuperior@gmail.com"; // El correo que quieres usar
$password_texto = "123456";              // La contraseña que usarás

// Encriptamos la contraseña
$pass_hash = password_hash($password_texto, PASSWORD_DEFAULT);

try {
    // 1. Verificar si ya existe el correo
    $stmt = $pdo->prepare("SELECT id FROM usuarios WHERE email = ?");
    $stmt->execute([$email_admin]);
    $existe = $stmt->fetch();

    if ($existe) {
        // CASO A: El usuario EXISTE -> Lo actualizamos a Admin
        $sql = "UPDATE usuarios SET password = ?, rol = 'admin', nombre = 'Admin Supremo' WHERE email = ?";
        $update = $pdo->prepare($sql);
        $update->execute([$pass_hash, $email_admin]);
        echo "<h1 style='color:green'>✅ USUARIO ACTUALIZADO</h1>";
        echo "El usuario existente ahora es ADMIN.<br>";
    } else {
        // CASO B: El usuario NO EXISTE -> Lo creamos desde cero
        $sql = "INSERT INTO usuarios (nombre, email, password, rol, telefono, direccion) VALUES (?, ?, ?, 'admin', '0000000000', 'Oficina Central')";
        $insert = $pdo->prepare($sql);
        $insert->execute(['Admin Supremo', $email_admin, $pass_hash]);
        echo "<h1 style='color:blue'>✨ USUARIO CREADO</h1>";
        echo "Se ha creado una cuenta nueva de Administrador.<br>";
    }

    echo "<hr>";
    echo "Correo: <b>$email_admin</b><br>";
    echo "Contraseña: <b>$password_texto</b><br>";
    echo "<br><a href='login.php' style='font-size:20px'>👉 IR A INICIAR SESIÓN</a>";

} catch (PDOException $e) {
    echo "<h1>❌ Error SQL</h1>" . $e->getMessage();
}
?>