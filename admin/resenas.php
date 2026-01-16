<?php
/* Archivo: admin/resenas.php */
session_start();
require '../config/db.php';
require '../includes/funciones.php';
verificar_admin();

// BORRAR RESEÑA
if (isset($_GET['borrar'])) {
    $id = (int)$_GET['borrar'];
    $pdo->prepare("DELETE FROM resenas WHERE id = ?")->execute([$id]);
    header("Location: resenas.php");
    exit();
}

// CONSULTA DE RESEÑAS
// Unimos tablas para saber: Quién escribió (Usuario) y De qué plato (Producto)
$sql = "SELECT r.*, u.nombre as usuario, p.nombre as producto, p.imagen 
        FROM resenas r 
        JOIN usuarios u ON r.usuario_id = u.id 
        JOIN productos p ON r.producto_id = p.id 
        ORDER BY r.id DESC";
$resenas = $pdo->query($sql)->fetchAll();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Reseñas - Admin</title>
    <link rel="stylesheet" href="css/admin.css">
    <style>
        .star { color: #f1c40f; font-size: 1.2rem; }
        .comentario-box { background: #f9f9f9; padding: 10px; border-radius: 5px; border-left: 3px solid var(--primary); font-style: italic; color: #555; }
        .img-mini { width: 40px; height: 40px; border-radius: 50%; object-fit: cover; margin-right: 10px; vertical-align: middle; }
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
        <a href="galeria.php">📸 Galería</a>
        <a href="resenas.php" class="active">⭐ Reseñas</a> <a href="configuracion.php">⚙️ Configuración</a>
        <a href="logout.php" class="salir">Cerrar Sesión</a>
    </nav>

    <main class="main-content">
        <div class="header-admin">
            <h1>Opiniones de Clientes ⭐</h1>
        </div>

        <?php if(empty($resenas)): ?>
            <div style="text-align:center; padding:40px; background:white; border-radius:10px;">
                <h3>Aún no hay opiniones.</h3>
                <p>Espera a que los clientes prueben tu comida.</p>
            </div>
        <?php else: ?>
            <table class="tabla-datos">
                <thead>
                    <tr>
                        <th>Fecha</th>
                        <th>Cliente</th>
                        <th>Platillo</th>
                        <th>Calif.</th>
                        <th>Comentario</th>
                        <th>Acción</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach($resenas as $r): ?>
                    <tr>
                        <td style="font-size: 0.85rem; color:#666;">
                            <?php echo date('d/m/Y', strtotime($r['fecha'])); ?>
                        </td>
                        <td>
                            <strong><?php echo htmlspecialchars($r['usuario']); ?></strong>
                        </td>
                        <td>
                            <?php if($r['imagen']): ?>
                                <img src="../uploads/productos/<?php echo $r['imagen']; ?>" class="img-mini">
                            <?php endif; ?>
                            <?php echo htmlspecialchars($r['producto']); ?>
                        </td>
                        <td>
                            <?php 
                                // Pintar estrellitas según el número
                                for($i=1; $i<=5; $i++) {
                                    echo ($i <= $r['calificacion']) ? '<span class="star">★</span>' : '<span style="color:#ddd;">★</span>';
                                }
                            ?>
                        </td>
                        <td width="30%">
                            <div class="comentario-box">"<?php echo htmlspecialchars($r['comentario']); ?>"</div>
                        </td>
                        <td>
                            <a href="resenas.php?borrar=<?php echo $r['id']; ?>" 
                               class="btn btn-rojo" 
                               style="background:#e74c3c; color:white; padding:5px 10px; text-decoration:none; border-radius:5px; font-size:0.8rem;"
                               onclick="return confirm('¿Borrar este comentario?');">
                               🗑️ Eliminar
                            </a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>

    </main>
</div>

</body>
</html>