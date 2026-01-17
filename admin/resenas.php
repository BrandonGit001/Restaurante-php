<?php
/* admin/resenas.php - VERSIÓN ACTUALIZADA */
session_start();
require '../config/db.php';
require '../includes/funciones.php';
verificar_admin();

// 1. BORRAR RESEÑA (Si quisieras moderar comentarios feos)
if (isset($_GET['borrar'])) {
    $id_borrar = $_GET['borrar'];
    $stmt = $pdo->prepare("DELETE FROM resenas WHERE id = ?");
    $stmt->execute([$id_borrar]);
    header("Location: resenas.php");
    exit;
}

// 2. CONSULTA: Unimos Reseñas + Datos del Usuario
// Usamos LEFT JOIN por si acaso el usuario fue borrado, que la reseña siga saliendo.
$sql = "SELECT r.*, u.nombre as nombre_cliente 
        FROM resenas r 
        LEFT JOIN usuarios u ON r.usuario_id = u.id 
        ORDER BY r.fecha DESC";
$stmt = $pdo->query($sql);
$resenas = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Reseñas de Clientes</title>
    <link rel="stylesheet" href="css/admin.css">
    <style>
        .estrellas { color: #f1c40f; letter-spacing: 2px; }
        .comentario-box { 
            background: #f9f9f9; padding: 10px; border-radius: 5px; 
            font-style: italic; color: #555; border-left: 3px solid #ccc;
        }
        table { width: 100%; border-collapse: collapse; background: white; border-radius: 8px; overflow: hidden; box-shadow: 0 2px 10px rgba(0,0,0,0.05); }
        th, td { padding: 15px; text-align: left; border-bottom: 1px solid #eee; }
        th { background: #2c3e50; color: white; }
    </style>
</head>
<body>

<div class="admin-layout">
    <nav class="sidebar">
        <h2>Mi Restaurante</h2>
            <a href="dashboard.php">📊 Resumen</a>
        <a href="pedidos.php">🛎️ Pedidos</a>
        <a href="reportes.php">📈 Reportes</a> 
        <a href="productos.php">🍔 Productos</a>
        <a href="categorias.php">📂 Categorías</a>
        <a href="galeria.php">📸 Galería</a>
        <a href="resenas.php" class="active">⭐ Reseñas</a>
        <a href="configuracion.php">⚙️ Configuración</a>
        <a href="logout.php" class="salir">Cerrar Sesión</a>
    </nav>

    <main class="main-content">
        <h1>Opiniones de Clientes ⭐</h1>

        <?php if(empty($resenas)): ?>
            <p style="padding: 20px; background: white; border-radius: 10px;">Aún no hay reseñas.</p>
        <?php else: ?>
            <div style="overflow-x: auto;">
                <table>
                    <thead>
                        <tr>
                            <th>Fecha</th>
                            <th>Cliente</th>
                            <th>Pedido #</th>
                            <th>Calificación</th>
                            <th>Comentario</th>
                            <th>Acción</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($resenas as $r): ?>
                        <tr>
                            <td style="font-size: 0.85rem; color: #777;">
                                <?php echo date('d/m/Y', strtotime($r['fecha'])); ?>
                            </td>
                            <td>
                                <strong><?php echo htmlspecialchars($r['nombre_cliente'] ?? 'Anónimo'); ?></strong>
                            </td>
                            <td>
                                <span style="background:#eee; padding:2px 6px; border-radius:4px; font-size:0.8rem;">
                                    #<?php echo $r['pedido_id']; ?>
                                </span>
                            </td>
                            <td>
                                <span class="estrellas">
                                    <?php 
                                    // Repetir la estrella ★ según el número
                                    echo str_repeat('★', $r['calificacion']); 
                                    // Completar con estrellas vacías ☆ si quieres (opcional)
                                    echo str_repeat('☆', 5 - $r['calificacion']);
                                    ?>
                                </span>
                                <small style="color:#aaa;">(<?php echo $r['calificacion']; ?>/5)</small>
                            </td>
                            <td width="40%">
                                <div class="comentario-box">
                                    "<?php echo htmlspecialchars($r['comentario']); ?>"
                                </div>
                            </td>
                            <td>
                                <a href="resenas.php?borrar=<?php echo $r['id']; ?>" 
                                   onclick="return confirm('¿Seguro que quieres borrar esta opinión?')"
                                   style="color: red; text-decoration: none; font-weight: bold;">
                                   🗑️ Borrar
                                </a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </main>
</div>

</body>
</html>