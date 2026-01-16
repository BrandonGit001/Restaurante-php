<?php
/* Archivo: admin/categorias.php */
session_start();
require '../config/db.php';
require '../includes/funciones.php';
verificar_admin();

$mensaje = "";

// --- 1. LÓGICA: GUARDAR NUEVA CATEGORÍA ---
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['crear'])) {
    $nombre = limpiar_str($_POST['nombre']);
    $orden = (int)$_POST['orden'];

    if(!empty($nombre)) {
        $stmt = $pdo->prepare("INSERT INTO categorias (nombre, orden) VALUES (?, ?)");
        if($stmt->execute([$nombre, $orden])) {
            $mensaje = "✅ Categoría '$nombre' agregada.";
        } else {
            $mensaje = "❌ Error al guardar.";
        }
    }
}

// --- 2. LÓGICA: BORRAR CATEGORÍA ---
if (isset($_GET['borrar'])) {
    $id_borrar = (int)$_GET['borrar'];
    // Ojo: Podríamos validar que no tenga productos asociados antes de borrar,
    // pero por ahora lo haremos simple.
    $stmt = $pdo->prepare("DELETE FROM categorias WHERE id = ?");
    $stmt->execute([$id_borrar]);
    header("Location: categorias.php"); // Recargar para limpiar la URL
    exit();
}

// --- 3. CONSULTA: TRAER TODAS ---
$categorias = $pdo->query("SELECT * FROM categorias ORDER BY orden ASC")->fetchAll();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Categorías - Admin</title>
    <link rel="stylesheet" href="css/admin.css">
    <style>
        .form-inline { display: flex; gap: 10px; align-items: flex-end; background: white; padding: 20px; border-radius: 8px; box-shadow: 0 2px 5px rgba(0,0,0,0.05); margin-bottom: 20px; }
        .form-group { flex: 1; }
        .form-group label { display: block; font-weight: bold; margin-bottom: 5px; }
        .form-group input { width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 5px; }
        .btn-delete { background: #e74c3c; color: white; padding: 5px 10px; text-decoration: none; border-radius: 5px; font-size: 0.8rem; }
    </style>
</head>
<body>

<div class="admin-layout">
    <nav class="sidebar">
        <h2>Mi Restaurante</h2>
        <a href="dashboard.php">📊 Resumen</a>
        <a href="pedidos.php">🛎️ Pedidos</a>
        <a href="productos.php">🍔 Productos</a>
        <a href="categorias.php" class="active">📂 Categorías</a>
        <a href="galeria.php">📸 Galería</a>
        <a href="resenas.php">⭐ Reseñas</a>
        <a href="configuracion.php">⚙️ Configuración</a>
        <a href="logout.php" class="salir">Cerrar Sesión</a>
    </nav>

    <main class="main-content">
        <div class="header-admin">
            <h1>Gestión de Categorías</h1>
        </div>

        <?php if($mensaje): ?>
            <div style="background: #d4edda; color: #155724; padding: 10px; border-radius: 5px; margin-bottom: 15px;">
                <?php echo $mensaje; ?>
            </div>
        <?php endif; ?>

        <form method="POST" class="form-inline">
            <div class="form-group">
                <label>Nombre de Categoría:</label>
                <input type="text" name="nombre" placeholder="Ej: Bebidas, Tacos..." required>
            </div>
            <div class="form-group" style="max-width: 100px;">
                <label>Orden:</label>
                <input type="number" name="orden" value="1" required>
            </div>
            <button type="submit" name="crear" class="btn btn-main" style="height: 40px;">+ Agregar</button>
        </form>

        <table class="tabla-datos">
            <thead>
                <tr>
                    <th width="50">Orden</th>
                    <th>Nombre</th>
                    <th width="100">Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach($categorias as $cat): ?>
                <tr>
                    <td style="text-align: center; font-weight: bold;"><?php echo $cat['orden']; ?></td>
                    <td><?php echo htmlspecialchars($cat['nombre']); ?></td>
                    <td>
                        <a href="categorias.php?borrar=<?php echo $cat['id']; ?>" 
                           class="btn-delete"
                           onclick="return confirm('¿Seguro? Si borras esto, los productos de esta categoría quedarán huerfanos.')">
                           🗑️ Borrar
                        </a>
                    </td>
                </tr>
                <?php endforeach; ?>

                <?php if(empty($categorias)): ?>
                    <tr><td colspan="3" style="text-align: center;">No hay categorías creadas.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>

    </main>
</div>

</body>
</html>