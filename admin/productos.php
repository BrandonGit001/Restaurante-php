<?php
/* Archivo: admin/productos.php (CORREGIDO) */
session_start();
require '../config/db.php';
require '../includes/funciones.php';
verificar_admin();

// --- LÓGICA DE BORRADO ---
if (isset($_GET['borrar'])) {
    $id_borrar = (int)$_GET['borrar'];

    // 1. Primero buscamos la foto para borrarla del disco
    $stmt = $pdo->prepare("SELECT imagen FROM productos WHERE id = ?");
    $stmt->execute([$id_borrar]);
    $foto = $stmt->fetchColumn();

    // Si existe foto, la borramos de la carpeta uploads
    if($foto && file_exists("../uploads/productos/" . $foto)) {
        unlink("../uploads/productos/" . $foto);
    }

    // 2. AHORA SÍ: Borramos el producto de la base de datos
    // (Aquí estaba el error, antes decía "categorias")
    $stmt = $pdo->prepare("DELETE FROM productos WHERE id = ?");
    $stmt->execute([$id_borrar]);

    // Recargamos para ver los cambios
    header("Location: productos.php");
    exit();
}

// --- CONSULTA DE PRODUCTOS ---
$sql = "SELECT p.*, c.nombre as categoria_nombre 
        FROM productos p 
        LEFT JOIN categorias c ON p.categoria_id = c.id 
        ORDER BY p.id DESC";
$stmt = $pdo->query($sql);
$productos = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Productos - Admin</title>
    <link rel="stylesheet" href="css/admin.css">
</head>
<body>

<div class="admin-layout">
    
<nav class="sidebar">
        <h2>Mi Restaurante</h2>
        <a href="dashboard.php" >📊 Resumen</a>
        <a href="pedidos.php">🛎️ Pedidos</a>
         <a href="reportes.php">📈 Reportes</a> 
        <a href="productos.php" class="active">🍔 Productos</a>
        <a href="categorias.php">📂 Categorías</a>
        <a href="galeria.php">📸 Galería</a>
        <a href="resenas.php">⭐ Reseñas</a>
        <a href="configuracion.php">⚙️ Configuración</a>
        <a href="logout.php" class="salir">Cerrar Sesión</a>
    </nav>

    <main class="main-content">
        <div class="header-admin">
            <h1>Mis Platillos</h1>
            <a href="producto_nuevo.php" class="btn btn-verde">+ Nuevo Platillo</a>
        </div>

        <table class="tabla-datos">
            <thead>
                <tr>
                    <th>Imagen</th>
                    <th>Nombre</th>
                    <th>Categoría</th>
                    <th>Precio</th>
                    <th>Estado</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($productos as $prod): ?>
                <tr>
                    <td>
                        <?php if($prod['imagen']): ?>
                            <img src="../uploads/productos/<?php echo $prod['imagen']; ?>" class="img-thumb">
                        <?php else: ?>
                            <span>Sin foto</span>
                        <?php endif; ?>
                    </td>
                    <td>
                        <strong><?php echo htmlspecialchars($prod['nombre']); ?></strong>
                        <?php if($prod['destacado']): ?>
                            <span style="font-size:10px;">⭐ Día</span>
                        <?php endif; ?>
                    </td>
                    <td><?php echo htmlspecialchars($prod['nombre_categoria'] ?? 'Sin categoría'); ?></td>
                    <td>$<?php echo number_format($prod['precio'], 2); ?></td>
                    <td>
                        <?php if($prod['activo']): ?>
                            <span class="badge badge-verde">Activo</span>
                        <?php else: ?>
                            <span class="badge badge-rojo">Oculto</span>
                        <?php endif; ?>
                    </td>
                    <td>
                        <a href="producto_form.php?id=<?php echo $prod['id']; ?>" class="btn btn-azul">
                            Editar ✏️
                        </a>

                            <a href="productos.php?borrar=<?php echo $prod['id']; ?>" 
                                class="btn btn-rojo"
                                style="background:#e74c3c; color:white; padding:5px 10px; text-decoration:none; border-radius:5px;"
                                onclick="return confirm('¿Seguro que quieres borrar este plato?');">
                                    Borrar 🗑️
                                </a>
                    </td>
                </tr>
                <?php endforeach; ?>

                <?php if(empty($productos)): ?>
                <tr>
                    <td colspan="6" style="text-align:center; padding: 20px;">
                        No hay productos registrados aún. ¡Agrega el primero!
                    </td>
                </tr>
                <?php endif; ?>
            </tbody>
        </table>

    </main>
</div>

</body>
</html>
