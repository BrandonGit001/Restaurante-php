<?php
/* admin/ticket.php - CORREGIDO */
require '../config/db.php';
// require '../includes/funciones.php'; // Lo comentamos porque no lo necesitamos urgente aquí

if (!isset($_GET['id'])) {
    die("Falta el ID del pedido");
}

$id_pedido = $_GET['id'];

// 1. OBTENER DATOS DEL PEDIDO
$stmt = $pdo->prepare("SELECT * FROM pedidos WHERE id = ?");
$stmt->execute([$id_pedido]);
$p = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$p) {
    die("Pedido no encontrado");
}

// 2. OBTENER CONFIGURACIÓN (Consulta Directa para evitar errores)
// En lugar de usar la función, la pedimos directo aquí:
$stmt_conf = $pdo->query("SELECT * FROM configuracion LIMIT 1");
$config = $stmt_conf->fetch(PDO::FETCH_ASSOC);

// Si por alguna razón no hay config, usamos datos de relleno para que no falle
if (!$config) {
    $config = [
        'nombre_negocio' => 'Mi Restaurante',
        'telefono_whatsapp' => '000-000-0000'
    ];
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Ticket #<?php echo $p['id']; ?></title>
    <style>
        /* ESTILOS TIPO TICKET TÉRMICO (80mm) */
        body {
            font-family: 'Courier New', Courier, monospace;
            width: 300px; 
            margin: 0 auto;
            background: #fff;
            padding: 10px;
            color: black;
        }
        .centrado { text-align: center; }
        .linea { border-top: 1px dashed #000; margin: 10px 0; }
        .grande { font-size: 1.2rem; font-weight: bold; }
        .flex { display: flex; justify-content: space-between; }
        
        /* Ocultar botón al imprimir */
        @media print {
            .no-print { display: none; }
            body { margin: 0; padding: 0; }
        }
    </style>
</head>
<body onload="window.print()"> <button class="no-print" onclick="window.close()" style="width:100%; padding:10px; margin-bottom:10px; cursor:pointer; background: #ddd; border: none;">❌ Cerrar Ventana</button>

    <div class="centrado">
        <h2 style="margin:0;">🍔 <?php echo strtoupper($config['nombre_negocio']); ?> 🍔</h2>
        <p style="font-size:0.8rem;">
            Pedido Web<br>
            Tel: <?php echo $config['telefono_whatsapp']; ?>
        </p>
    </div>

    <div class="linea"></div>

    <div class="flex">
        <span>FECHA:</span>
        <span><?php echo date('d/m/Y H:i', strtotime($p['fecha'])); ?></span>
    </div>
    <div class="flex">
        <span>TICKET:</span>
        <span class="grande">#<?php echo $p['id']; ?></span>
    </div>
    
    <div style="margin-top:5px;">
        <strong>CLIENTE:</strong><br>
        <?php echo strtoupper($p['cliente_nombre']); ?>
    </div>

    <div class="linea"></div>

    <div style="text-align: left;">
        <?php echo nl2br(strtoupper($p['productos'])); ?>
    </div>

    <div class="linea"></div>

    <div class="flex grande" style="margin-top:10px;">
        <span>TOTAL:</span>
        <span>$<?php echo number_format($p['total'], 2); ?></span>
    </div>

    <div class="centrado" style="margin-top: 20px;">
        <p>*** GRACIAS POR SU COMPRA ***<br>
        ¡Buen Provecho!</p>
    </div>

</body>
</html>