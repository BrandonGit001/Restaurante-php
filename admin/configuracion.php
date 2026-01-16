<?php
/* Archivo: admin/configuracion.php */
session_start();
require '../config/db.php';
require '../includes/funciones.php';
verificar_admin();

$mensaje = "";

// 1. GUARDAR CAMBIOS
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nombre = limpiar_str($_POST['nombre_negocio']);
    $tel = limpiar_str($_POST['telefono_whatsapp']);
    $tiempo = limpiar_str($_POST['tiempo_estimado']);
    $msg_cierre = limpiar_str($_POST['mensaje_cierre']);
    
    // Checkboxes
    $estado = isset($_POST['estado_tienda']) ? 1 : 0;
    $usar_wa = isset($_POST['usar_whatsapp']) ? 1 : 0;

    $sql = "UPDATE configuracion SET 
            nombre_negocio=?, telefono_whatsapp=?, tiempo_estimado=?, 
            mensaje_cierre=?, estado_tienda=?, usar_whatsapp=? 
            WHERE id=1";
            
    $stmt = $pdo->prepare($sql);
    if ($stmt->execute([$nombre, $tel, $tiempo, $msg_cierre, $estado, $usar_wa])) {
        $mensaje = "✅ ¡Configuración guardada!";
    } else {
        $mensaje = "❌ Error al guardar.";
    }
}

// 2. LEER CONFIGURACIÓN ACTUAL
$stmt = $pdo->query("SELECT * FROM configuracion WHERE id = 1");
$config = $stmt->fetch();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Configuración</title>
    <link rel="stylesheet" href="css/admin.css">
    <style>
        .config-card { background: white; padding: 25px; border-radius: 10px; box-shadow: 0 4px 10px rgba(0,0,0,0.05); max-width: 600px; margin: 0 auto; }
        .form-group { margin-bottom: 20px; }
        .form-group label { display: block; font-weight: bold; margin-bottom: 8px; color: #444; }
        .form-control { width: 100%; padding: 12px; border: 1px solid #ccc; border-radius: 6px; box-sizing: border-box; }
        
        /* Switch Toggle (Estilo bonito para Abrir/Cerrar) */
        .switch-container { display: flex; align-items: center; justify-content: space-between; background: #f8f9fa; padding: 15px; border-radius: 8px; margin-bottom: 15px; border: 1px solid #eee; }
        .switch-label { font-size: 1.1rem; font-weight: bold; }
        
        input[type=checkbox] { transform: scale(1.5); cursor: pointer; }
    </style>
</head>
<body>

<div class="admin-layout">
    <nav class="sidebar">
        <h2>Mi Restaurante</h2>
        <a href="dashboard.php">📊 Resumen</a>
        <a href="pedidos.php">🛎️ Pedidos</a>
        <a href="productos.php">🍔 Productos</a>
        <a href="categorias.php">📂 Categorías</a>
        <a href="resenas.php">⭐ Reseñas</a>
        <a href="configuracion.php" class="active">⚙️ Configuración</a>
        <a href="logout.php" class="salir">Cerrar Sesión</a>
    </nav>

    <main class="main-content">
        <h1 style="text-align: center;">Ajustes del Negocio ⚙️</h1>

        <?php if($mensaje): ?>
            <div style="text-align:center; background:#d4edda; color:#155724; padding:10px; border-radius:5px; margin-bottom:20px;">
                <?php echo $mensaje; ?>
            </div>
        <?php endif; ?>

        <div class="config-card">
            <form method="POST">
                
                <div class="switch-container" style="border-left: 5px solid <?php echo $config['estado_tienda'] ? '#2ecc71' : '#e74c3c'; ?>;">
                    <div class="switch-label">
                        <?php echo $config['estado_tienda'] ? '🟢 TIENDA ABIERTA' : '🔴 TIENDA CERRADA'; ?>
                    </div>
                    <label>
                        <input type="checkbox" name="estado_tienda" <?php echo $config['estado_tienda'] ? 'checked' : ''; ?>>
                        Activar
                    </label>
                </div>

                <div class="form-group">
                    <label>Nombre del Negocio:</label>
                    <input type="text" name="nombre_negocio" class="form-control" value="<?php echo htmlspecialchars($config['nombre_negocio']); ?>">
                </div>

                <div class="form-group">
                    <label>Teléfono para WhatsApp (con código país):</label>
                    <input type="text" name="telefono_whatsapp" class="form-control" value="<?php echo htmlspecialchars($config['telefono_whatsapp']); ?>">
                    <small style="color:#888;">Ej: 5215512345678 (Sin espacios ni guiones)</small>
                </div>

                <div class="form-group">
                    <label>Tiempo de Espera (Visible al cliente):</label>
                    <input type="text" name="tiempo_estimado" class="form-control" value="<?php echo htmlspecialchars($config['tiempo_estimado']); ?>">
                </div>

                <div class="form-group">
                    <label>Mensaje cuando está cerrado:</label>
                    <textarea name="mensaje_cierre" class="form-control" rows="2"><?php echo htmlspecialchars($config['mensaje_cierre']); ?></textarea>
                </div>

                <div class="switch-container">
                    <div class="switch-label" style="font-size: 0.9rem;">
                        📲 Modo WhatsApp
                    </div>
                    <label>
                        <input type="checkbox" name="usar_whatsapp" <?php echo $config['usar_whatsapp'] ? 'checked' : ''; ?>>
                        Sí
                    </label>
                </div>
                <p style="font-size: 0.8rem; color: #666; margin-top: -10px; margin-bottom: 20px;">
                    *Si desactivas WhatsApp, los pedidos solo se guardarán en el panel Admin.
                </p>

                <button type="submit" class="btn btn-main" style="width: 100%; padding: 15px; font-size: 1.1rem;">
                    💾 Guardar Cambios
                </button>
            </form>
        </div>
    </main>
</div>

</body>
</html>