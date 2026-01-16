<?php
/* Archivo: ajax/guardar_pedido.php */
header('Content-Type: application/json');
require '../config/db.php';
require '../includes/funciones.php';

// Leemos los datos JSON que envía Javascript
$input = json_decode(file_get_contents('php://input'), true);

if ($input) {
    $cliente = limpiar_str($input['cliente'] ?? 'Cliente Web');
    $total = (float) $input['total'];
    $productos = $input['productos'];

    try {
        $pdo->beginTransaction();

        // 1. Insertar Cabecera del Pedido
        $stmt = $pdo->prepare("INSERT INTO pedidos (cliente_nombre, total, estado, metodo_pago) VALUES (?, ?, 'pendiente', 'efectivo')");
        $stmt->execute([$cliente, $total]);
        $pedido_id = $pdo->lastInsertId();

        // 2. Insertar Detalles (Platillos)
        $stmtDetalle = $pdo->prepare("INSERT INTO pedidos_detalle (pedido_id, producto_id, nombre_producto, precio_unitario, cantidad) VALUES (?, ?, ?, ?, ?)");

        foreach ($productos as $prod) {
            $stmtDetalle->execute([
                $pedido_id,
                $prod['id'],
                $prod['nombre'],
                $prod['precio'],
                $prod['cantidad']
            ]);
        }

        $pdo->commit();
        echo json_encode(['status' => 'success', 'message' => 'Pedido #' . $pedido_id . ' guardado exitosamente.']);

    } catch (Exception $e) {
        $pdo->rollBack();
        echo json_encode(['status' => 'error', 'message' => 'Error al guardar: ' . $e->getMessage()]);
    }
} else {
    echo json_encode(['status' => 'error', 'message' => 'No hay datos']);
}
?>