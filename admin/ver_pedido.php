<?php
/* Archivo: admin/ver_pedido.php */
session_start();
require '../config/db.php';
require '../includes/funciones.php';
verificar_admin();

if(!isset($_GET['id'])) header("Location: pedidos.php");
$id_pedido = (int)$_GET['id'];

// 1. ACTUALIZAR ESTADO (Si se presionó un botón)
if(isset($_POST['nuevo_estado'])) {
    $nuevo = $_POST['nuevo_estado'];
    $stmt = $pdo->prepare("UPDATE pedidos SET estado = ? WHERE id = ?");
    $stmt->execute([$nuevo, $id_pedido]);
    $mensaje = "Estado actualizado a: " . strtoupper($nuevo);
}

// 2. OBTENER INFO DEL PEDIDO
$stmt = $pdo->prepare("SELECT * FROM pedidos WHERE id = ?");
$stmt->execute([$id_pedido]);
$pedido = $stmt->fetch();

if(!$pedido) die("Pedido no encontrado");

// 3. OBTENER DETALLES (PLATILLOS)
$stmt = $pdo->prepare("SELECT * FROM pedidos_detalle WHERE pedido_id = ?");
$stmt->execute([$id_pedido]);
$detalles = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Detalle Pedido #<?php echo $id_pedido; ?></title>
    <link rel="stylesheet" href="css/admin.css">
</head>
<body>

<div class="admin-layout">
    <nav class="sidebar">
        <h2>Mi Restaurante</h2>
        <a href="pedidos.php" class="active">⬅ Volver</a>
    </nav>

    <main class="main-content">
        <h1>Pedido #<?php echo $id_pedido; ?></h1>
        <p>Cliente: <strong><?php echo htmlspecialchars($pedido['cliente_nombre']); ?></strong></p>
        <p>Fecha: <?php echo $pedido['fecha']; ?></p>
            <?php 
    // Mensaje automático
    $msg_aviso = "Hola " . $pedido['cliente_nombre'] . ", tu pedido #" . $pedido['id'] . " ya está " . strtoupper($pedido['estado']) . ".";
    
    // NOTA: Si guardaste el teléfono en la tabla de usuarios, podrías usarlo aquí.
    // Por ahora dejaremos el número en blanco para que tú elijas el contacto en tu cel.
?>
<a href="https://wa.me/?text=<?php echo urlencode($msg_aviso); ?>" target="_blank" 
   class="btn" style="background:#25D366; color:white; text-decoration:none; padding:5px 10px; font-size:0.8rem; border-radius:5px;">
   📲 Avisar por WhatsApp
</a>
<a href="imprimir_ticket.php?id=<?php echo $pedido['id']; ?>" 
   target="_blank" 
   class="btn" style="background: #34495e; color: white; padding: 10px 15px; text-decoration: none; border-radius: 5px; margin-left: 10px;">
   🖨️ Imprimir Ticket
</a>
        <?php if(isset($mensaje)): ?>
            <div style="background:#d4edda; color:#155724; padding:10px; margin:10px 0; border-radius:5px;">
                <?php echo $mensaje; ?>
            </div>
        <?php endif; ?>

        <div class="stat-card" style="margin: 20px 0; text-align: left;">
            <h3>Cambiar Estado:</h3>
            <form method="POST" style="display:flex; gap:10px;">
                <button type="submit" name="nuevo_estado" value="pendiente" class="btn" style="background:#ffeaa7; color:#d35400;">Pendiente</button>
                <button type="submit" name="nuevo_estado" value="preparando" class="btn" style="background:#74b9ff; color:#0984e3;">Cocinando 🔥</button>
                <button type="submit" name="nuevo_estado" value="listo" class="btn" style="background:#55efc4; color:#00b894;">Listo ✅</button>
                <button type="submit" name="nuevo_estado" value="entregado" class="btn" style="background:#dfe6e9; color:#636e72;">Entregado</button>
            </form>
        </div>

        <table class="tabla-datos">
            <thead>
                <tr>
                    <th>Producto</th>
                    <th>Precio Unit.</th>
                    <th>Cant.</th>
                    <th>Subtotal</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach($detalles as $d): ?>
                <tr>
                    <td><?php echo htmlspecialchars($d['nombre_producto']); ?></td>
                    <td>$<?php echo number_format($d['precio_unitario'], 2); ?></td>
                    <td>x<?php echo $d['cantidad']; ?></td>
                    <td>$<?php echo number_format($d['precio_unitario'] * $d['cantidad'], 2); ?></td>
                </tr>
                <?php endforeach; ?>
                <tr style="background: #f8f9fa; font-weight: bold;">
                    <td colspan="3" style="text-align: right;">TOTAL:</td>
                    <td style="font-size: 1.2rem; color: #27ae60;">$<?php echo number_format($pedido['total'], 2); ?></td>
                </tr>
            </tbody>
        </table>

    </main>
</div>

</body>
</html>