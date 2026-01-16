<?php
/* Archivo: mis_pedidos.php */
session_start();
require 'config/db.php';

// Si no está logueado, lo mandamos al login
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$cliente = $_SESSION['user_nombre'];

// Consultar los pedidos de este cliente exacto
// (Nota: Busca por el nombre exacto con el que se registró)
$sql = "SELECT * FROM pedidos WHERE cliente_nombre = ? ORDER BY id DESC";
$stmt = $pdo->prepare($sql);
$stmt->execute([$cliente]);
$mis_pedidos = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Mis Pedidos</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
        .container { max-width: 800px; margin: 40px auto; padding: 20px; }
        .pedido-card {
            background: white; border-radius: 10px; padding: 20px;
            margin-bottom: 20px; box-shadow: 0 5px 15px rgba(0,0,0,0.05);
            border-left: 5px solid #ddd;
        }
        /* Colores según estado */
        .borde-pendiente { border-left-color: #f1c40f; }
        .borde-preparando { border-left-color: #3498db; }
        .borde-listo { border-left-color: #2ecc71; }
        .borde-entregado { border-left-color: #95a5a6; }

        .pedido-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 15px; }
        .pedido-fecha { color: #888; font-size: 0.9rem; }
        .pedido-estado { font-weight: bold; padding: 5px 10px; border-radius: 15px; background: #eee; font-size: 0.8rem; text-transform: uppercase; }
        
        .btn-volver { text-decoration: none; color: var(--secondary); font-weight: bold; display: inline-block; margin-bottom: 20px; }
    </style>
</head>
<body>

    <header>
        <div class="logo">Mi Restaurante</div>
        <div class="user-menu">
            <span style="font-weight:bold; color:var(--primary);">Hola, <?php echo htmlspecialchars($cliente); ?></span>
            <a href="logout.php" style="color:red; text-decoration:none; font-weight:600; font-size:0.9rem;">Salir</a>
        </div>
    </header>

    <div class="container">
        <a href="index.php" class="btn-volver">⬅ Volver al Menú</a>
        <h1>Mis Pedidos 🧾</h1>

        <?php if (empty($mis_pedidos)): ?>
            <div style="text-align: center; padding: 40px; background: #fff; border-radius: 10px;">
                <h3>Aún no has hecho pedidos.</h3>
                <p>¡Se te antoja algo? Ve al menú.</p>
                <a href="index.php" class="btn-add" style="text-decoration: none; display: inline-block; margin-top: 10px;">Ir a pedir</a>
            </div>
        <?php else: ?>
            
            <?php foreach ($mis_pedidos as $p): ?>
                <?php $clase = 'borde-' . $p['estado']; ?>
                
                <div class="pedido-card <?php echo $clase; ?>">
                    <div class="pedido-header">
                        <div>
                            <strong style="font-size: 1.2rem;">Pedido #<?php echo $p['id']; ?></strong>
                            <div class="pedido-fecha">📅 <?php echo date('d/m/Y h:i A', strtotime($p['fecha'])); ?></div>
                        </div>
                        <div class="pedido-estado">
                            <?php echo strtoupper($p['estado']); ?>
                        </div>
                    </div>
                    
                    <div style="display: flex; justify-content: space-between; align-items: center; border-top: 1px solid #eee; padding-top: 10px;">
                        <span style="color: #666;">Total Pagado:</span>
                        <strong style="font-size: 1.3rem; color: var(--secondary);">$<?php echo number_format($p['total'], 2); ?></strong>
                    </div>

                    <div style="margin-top: 15px; height: 5px; background: #eee; border-radius: 5px; overflow: hidden;">
                        <?php 
                            $ancho = '5%';
                            $color = '#ddd';
                            if($p['estado']=='pendiente') { $ancho='25%'; $color='#f1c40f'; }
                            if($p['estado']=='preparando') { $ancho='50%'; $color='#3498db'; }
                            if($p['estado']=='listo') { $ancho='75%'; $color='#2ecc71'; }
                            if($p['estado']=='entregado') { $ancho='100%'; $color='#95a5a6'; }
                        ?>
                        <div style="width: <?php echo $ancho; ?>; background: <?php echo $color; ?>; height: 100%;"></div>
                    </div>
                    <small style="color:<?php echo $color; ?>; font-weight:bold; margin-top:5px; display:block;">
                        <?php 
                            if($p['estado']=='pendiente') echo "Recibido, esperando confirmación...";
                            if($p['estado']=='preparando') echo "¡Cocinando! 🔥";
                            if($p['estado']=='listo') echo "¡Listo para recoger/enviar! ✅";
                            if($p['estado']=='entregado') echo "Entregado. ¡Provecho!";
                        ?>
                    </small>

                </div>
            <?php endforeach; ?>

        <?php endif; ?>
    </div>

</body>
</html>