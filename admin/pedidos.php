<?php
/* Archivo: admin/pedidos.php */
session_start();
require '../config/db.php';
require '../includes/funciones.php';
verificar_admin();

// CONSULTA: Traer pedidos ordenados por fecha (más nuevos primero)
$sql = "SELECT * FROM pedidos ORDER BY fecha DESC";
$stmt = $pdo->query($sql);
$pedidos = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Pedidos Recibidos</title>
    <link rel="stylesheet" href="css/admin.css">
    <meta http-equiv="refresh" content="30">
    <style>
        .estado-pendiente { background: #ffeaa7; color: #d35400; padding: 5px 10px; border-radius: 15px; font-weight: bold; }
        .estado-preparando { background: #74b9ff; color: #0984e3; padding: 5px 10px; border-radius: 15px; font-weight: bold; }
        .estado-listo { background: #55efc4; color: #00b894; padding: 5px 10px; border-radius: 15px; font-weight: bold; }
        .estado-entregado { background: #dfe6e9; color: #636e72; padding: 5px 10px; border-radius: 15px; }
    </style>
</head>
<body>

<div class="admin-layout">
    <nav class="sidebar">
        <h2>Mi Restaurante</h2>
        <a href="dashboard.php">📊 Resumen</a>
        <a href="pedidos.php" class="active">🛎️ Pedidos</a> <a href="productos.php">🍔 Productos</a>
        <a href="categorias.php">📂 Categorías</a>
        <a href="galeria.php">📸 Galería</a>
        <a href="resenas.php">⭐ Reseñas</a>
        <a href="configuracion.php">⚙️ Configuración</a>
        <a href="logout.php" class="salir">Cerrar Sesión</a>
    </nav>

    <main class="main-content">
        <div class="header-admin">
            <h1>Monitor de Pedidos</h1>
            <span style="font-size: 0.9rem; color: #666;">Se actualiza cada 30 seg</span>
        </div>

        <table class="tabla-datos">
            <thead>
                <tr>
                    <th>#ID</th>
                    <th>Cliente</th>
                    <th>Fecha / Hora</th>
                    <th>Total</th>
                    <th>Estado</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach($pedidos as $p): ?>
                <tr>
                    <td><strong>#<?php echo $p['id']; ?></strong></td>
                    <td><?php echo htmlspecialchars($p['cliente_nombre']); ?></td>
                    <td><?php echo date('d/m/Y H:i', strtotime($p['fecha'])); ?></td>
                    <td>$<?php echo number_format($p['total'], 2); ?></td>
                    <td>
                        <?php 
                            $clase = 'estado-' . $p['estado']; // ej: estado-pendiente
                            echo "<span class='$clase'>" . ucfirst($p['estado']) . "</span>";
                        ?>
                    </td>
                    <td>
                        <a href="ver_pedido.php?id=<?php echo $p['id']; ?>" class="btn btn-azul">Ver Detalles 👁️</a>
                    </td>
                </tr>
                <?php endforeach; ?>

                <?php if(empty($pedidos)): ?>
                    <tr><td colspan="6" style="text-align:center">No hay pedidos registrados aún.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>

    </main>
</div>

</body>
</html>