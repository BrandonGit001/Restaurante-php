<?php
/* Archivo: admin/imprimir_ticket.php */
session_start();
require '../config/db.php';
require '../includes/funciones.php';
verificar_admin();

if (!isset($_GET['id'])) {
    die("Falta el ID del pedido.");
}

$id_pedido = (int)$_GET['id'];

// 1. Datos del Pedido y Cliente
$sql = "SELECT p.*, u.nombre as cliente_real, u.telefono, u.direccion 
        FROM pedidos p 
        LEFT JOIN usuarios u ON p.cliente_nombre = u.nombre 
        WHERE p.id = ?";
        
$stmt = $pdo->prepare($sql);
$stmt->execute([$id_pedido]);
$pedido = $stmt->fetch();
$stmt = $pdo->prepare($sql);
$stmt->execute([$id_pedido]);
$pedido = $stmt->fetch();

if (!$pedido) die("Pedido no encontrado.");

// 2. Detalles del Pedido (Platillos)
$stmt = $pdo->prepare("SELECT * FROM pedidos_detalle WHERE pedido_id = ?");
$stmt->execute([$id_pedido]);
$detalles = $stmt->fetchAll();

// 3. Configuración (Para nombre del negocio)
$config = $pdo->query("SELECT * FROM configuracion WHERE id=1")->fetch();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Ticket #<?php echo $id_pedido; ?></title>
    <style>
        /* ESTILOS TIPO TICKET (58mm o 80mm) */
        body {
            font-family: 'Courier New', Courier, monospace; /* Fuente tipo máquina */
            font-size: 14px;
            width: 300px; /* Ancho típico de ticket */
            margin: 0 auto;
            color: black;
            background: white;
        }
        .header { text-align: center; margin-bottom: 20px; border-bottom: 2px dashed black; padding-bottom: 10px; }
        .info { margin-bottom: 10px; }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        th { text-align: left; border-bottom: 1px solid black; }
        td { padding: 5px 0; vertical-align: top; }
        .total { font-size: 18px; font-weight: bold; text-align: right; margin-top: 10px; border-top: 2px solid black; padding-top: 10px; }
        .footer { text-align: center; margin-top: 30px; font-size: 12px; }
        
        /* OCULTAR BOTÓN AL IMPRIMIR */
        @media print {
            .no-print { display: none; }
        }
    </style>
</head>
<body onload="window.print()"> <div class="no-print" style="margin-bottom: 20px; text-align: center;">
        <a href="ver_pedido.php?id=<?php echo $id_pedido; ?>" style="background: #ccc; text-decoration: none; padding: 5px 10px; border-radius: 5px; color: black;">⬅ Volver</a>
    </div>

    <div class="header">
        <h2 style="margin:0;"><?php echo strtoupper($config['nombre_negocio']); ?></h2>
        <p style="margin:5px 0;">Ticket de Venta</p>
        <small>Fecha: <?php echo $pedido['fecha']; ?></small>
    </div>

    <div class="info">
        <strong>Pedido #<?php echo $pedido['id']; ?></strong><br>
        Cliente: <?php echo $pedido['cliente_nombre']; ?><br>
        <?php if($pedido['direccion']): ?>
            Dir: <?php echo $pedido['direccion']; ?><br>
        <?php endif; ?>
        Tel: <?php echo $pedido['telefono'] ?? '---'; ?>
    </div>

    <table>
        <thead>
            <tr>
                <th width="10%">Cant.</th>
                <th width="60%">Producto</th>
                <th width="30%" style="text-align:right;">$$</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach($detalles as $d): ?>
            <tr>
                <td style="text-align:center;"><?php echo $d['cantidad']; ?></td>
                <td><?php echo $d['nombre_producto']; ?></td>
                <td style="text-align:right;">$<?php echo number_format($d['precio_unitario'] * $d['cantidad'], 2); ?></td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <div class="total">
        TOTAL: $<?php echo number_format($pedido['total'], 2); ?>
    </div>

    <div class="footer">
        <p>*** GRACIAS POR SU COMPRA ***</p>
        <p>Estado: <?php echo strtoupper($pedido['estado']); ?></p>
    </div>

</body>
</html>