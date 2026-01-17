<?php
/* admin/reportes.php */
session_start();
require '../config/db.php';
require '../includes/funciones.php';
verificar_admin();

// RANGO DE FECHAS (Por defecto: Hoy)
$fecha_inicio = $_GET['inicio'] ?? date('Y-m-d'); // Hoy
$fecha_fin = $_GET['fin'] ?? date('Y-m-d');       // Hoy

// 1. CONSULTA DE DINERO TOTAL 💰
// Sumamos el 'total' de los pedidos que NO estén cancelados ni pendientes
// (O ajusta el WHERE según tus estados: 'completado', 'entregado', etc)
$sql = "SELECT SUM(total) as venta_total, COUNT(*) as num_pedidos 
        FROM pedidos 
        WHERE fecha BETWEEN ? AND ? 
        AND estado != 'cancelado' AND estado != 'pendiente'";

$stmt = $pdo->prepare($sql);
// Agregamos horas para cubrir todo el día (00:00:00 a 23:59:59)
$stmt->execute([$fecha_inicio . " 00:00:00", $fecha_fin . " 23:59:59"]);
$reporte = $stmt->fetch(PDO::FETCH_ASSOC);

$venta_total = $reporte['venta_total'] ?? 0;
$num_pedidos = $reporte['num_pedidos'] ?? 0;
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Reportes Financieros</title>
    <link rel="stylesheet" href="css/admin.css">
    <style>
        .kpi-container { display: flex; gap: 20px; margin-top: 20px; }
        .kpi-card { flex: 1; background: white; padding: 20px; border-radius: 10px; box-shadow: 0 4px 10px rgba(0,0,0,0.05); text-align: center; }
        .kpi-number { font-size: 2.5rem; font-weight: bold; color: #2c3e50; margin: 10px 0; }
        .kpi-label { color: #7f8c8d; font-size: 0.9rem; text-transform: uppercase; letter-spacing: 1px; }
        .filter-bar { background: white; padding: 15px; border-radius: 10px; display: flex; align-items: flex-end; gap: 10px; }
    </style>
</head>
<body>
<div class="admin-layout">
    <nav class="sidebar">
        <h2>Mi Restaurante</h2>
          <a href="dashboard.php">📊 Resumen</a>
        <a href="pedidos.php">🛎️ Pedidos</a>
        <a href="reportes.php" class="active">📈 Reportes</a> 
        <a href="productos.php">🍔 Productos</a>
        <a href="categorias.php">📂 Categorías</a>
        <a href="galeria.php">📸 Galería</a>
        <a href="resenas.php">⭐ Reseñas</a>
        <a href="configuracion.php">⚙️ Configuración</a>
        <a href="logout.php" class="salir">Cerrar Sesión</a>
        
       
    </nav>

    <main class="main-content">
        <h1>Reporte de Ventas 📈</h1>

        <form class="filter-bar">
            <div>
                <label>Desde:</label><br>
                <input type="date" name="inicio" value="<?php echo $fecha_inicio; ?>" class="form-control">
            </div>
            <div>
                <label>Hasta:</label><br>
                <input type="date" name="fin" value="<?php echo $fecha_fin; ?>" class="form-control">
            </div>
            <button type="submit" class="btn btn-main">Filtrar 🔍</button>
        </form>

        <div class="kpi-container">
            <div class="kpi-card">
                <div style="font-size: 3rem;">💰</div>
                <div class="kpi-number">$<?php echo number_format($venta_total, 2); ?></div>
                <div class="kpi-label">Ventas Totales</div>
                <small style="color: #27ae60;">En el periodo seleccionado</small>
            </div>

            <div class="kpi-card">
                <div style="font-size: 3rem;">📦</div>
                <div class="kpi-number"><?php echo $num_pedidos; ?></div>
                <div class="kpi-label">Pedidos Completados</div>
            </div>

            <div class="kpi-card">
                <div style="font-size: 3rem;">🧾</div>
                <div class="kpi-number">
                    $<?php echo ($num_pedidos > 0) ? number_format($venta_total / $num_pedidos, 2) : "0.00"; ?>
                </div>
                <div class="kpi-label">Ticket Promedio</div>
                <small>Lo que gasta un cliente promedio</small>
            </div>
        </div>

    </main>
</div>
</body>
</html>