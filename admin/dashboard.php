<?php
/* Archivo: admin/dashboard.php */
session_start();
require '../config/db.php';
require '../includes/funciones.php';

// Verificar seguridad
verificar_admin();

// DETECTAR NOMBRE DEL ADMIN (Compatibilidad con ambos logins)
// Busca 'admin_nombre' O 'user_nombre'. Si no hay ninguno, pone 'Administrador'.
$nombre_admin = $_SESSION['admin_nombre'] ?? $_SESSION['user_nombre'] ?? 'Administrador';

// CONSULTAS PARA LAS ESTADÍSTICAS (KPIs)
// 1. Total Ventas (Suma de pedidos que NO estén cancelados)
$stmt = $pdo->query("SELECT SUM(total) FROM pedidos WHERE estado != 'cancelado'");
$total_ventas = $stmt->fetchColumn() ?: 0; // Si es null, pone 0

// 2. Pedidos Hoy
$hoy = date('Y-m-d');
$stmt = $pdo->prepare("SELECT COUNT(*) FROM pedidos WHERE DATE(fecha) = ?");
$stmt->execute([$hoy]);
$pedidos_hoy = $stmt->fetchColumn();

// 3. Pedidos Pendientes (Acción requerida)
$stmt = $pdo->query("SELECT COUNT(*) FROM pedidos WHERE estado = 'pendiente'");
$pendientes = $stmt->fetchColumn();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Dashboard - Admin</title>
    <link rel="stylesheet" href="css/admin.css">
</head>
<body>

<div class="admin-layout">
    <nav class="sidebar">
        <h2>Mi Restaurante</h2>
        <a href="dashboard.php" class="active">📊 Resumen</a>
        <a href="pedidos.php">🛎️ Pedidos <?php if($pendientes>0) echo "<span class='badge'>$pendientes</span>"; ?></a>
        <a href="productos.php">🍔 Productos</a>
        <a href="categorias.php">📂 Categorías</a>
        <a href="galeria.php">📸 Galería</a>
        <a href="resenas.php">⭐ Reseñas</a>
        <a href="configuracion.php">⚙️ Configuración</a>
        <a href="logout.php" class="salir">Cerrar Sesión</a>
       
    </nav>

    <main class="main-content">
        <div class="header-admin">
            <h1>Bienvenido, <?php echo htmlspecialchars($nombre_admin); ?> 👋</h1>
            <p>Aquí tienes el resumen de hoy.</p>
        </div>

        <div class="stats-grid">
            <div class="stat-card">
                <h3>Ventas Totales</h3>
                <p class="numero">$<?php echo number_format($total_ventas, 2); ?></p>
            </div>
            
            <div class="stat-card">
                <h3>Pedidos de Hoy</h3>
                <p class="numero"><?php echo $pedidos_hoy; ?></p>
            </div>

            <div class="stat-card" style="<?php echo ($pendientes > 0) ? 'border-left: 5px solid #e74c3c;' : ''; ?>">
                <h3>Pendientes</h3>
                <p class="numero"><?php echo $pendientes; ?></p>
                <?php if($pendientes > 0): ?>
                    <a href="pedidos.php" style="color: #e74c3c; font-weight: bold; text-decoration: none;">Ver pedidos &rarr;</a>
                <?php else: ?>
                    <span style="color: #27ae60; font-size: 0.9rem;">¡Todo al día!</span>
                <?php endif; ?>
            </div>
        </div>

        <div style="margin-top: 40px;">
            <h2>Accesos Rápidos</h2>
            <div style="display: flex; gap: 20px; margin-top: 20px;">
                <a href="productos.php" class="btn btn-main">+ Nuevo Platillo</a>
                <a href="configuracion.php" class="btn btn-azul">Abrir/Cerrar Tienda</a>
            </div>
        </div>

    </main>
</div>

</body>
</html>