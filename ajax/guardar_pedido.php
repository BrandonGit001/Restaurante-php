<?php
/* Archivo: ajax/guardar_pedido.php */
session_start(); // <--- ESTO ES LO QUE HACE QUE FUNCIONE "MIS PEDIDOS"

require '../config/db.php';
header('Content-Type: application/json');

// 1. Obtener datos del JS
$data = json_decode(file_get_contents('php://input'), true);

if (!$data) {
    echo json_encode(['status' => 'error', 'message' => 'No llegaron datos']);
    exit;
}

// 2. DETECTAR USUARIO (La magia) ✨
// Si hay sesión iniciada, el pedido se guarda a su nombre.
if (isset($_SESSION['user_id'])) {
    $usuario_id = $_SESSION['user_id'];
    $cliente_nombre = $_SESSION['user_nombre']; 
} else {
    $usuario_id = 0; // 0 = Invitado
    $cliente_nombre = $data['cliente'] ?? 'Cliente Web';
}

$total = $data['total'];
$lista_productos = "";

// 3. Formatear lista de productos
foreach ($data['productos'] as $prod) {
    // A. Esto ya lo tenías (crea el texto para el recibo)
    $lista_productos .= $prod['cantidad'] . " x " . $prod['nombre'] . "\n";

    // B. ESTO ES LO NUEVO: ¡La resta mágica! 📉
    $stmt_stock = $pdo->prepare("UPDATE productos SET stock = stock - :cant WHERE id = :id");
    $stmt_stock->execute([
        ':cant' => $prod['cantidad'],
        ':id'   => $prod['id']
    ]);
}

try {
    // 4. GUARDAR EN BD
    // OJO: Asegúrate que tu tabla tenga la columna 'usuario_id'
    $sql = "INSERT INTO pedidos (usuario_id, cliente_nombre, productos, total, fecha, estado) VALUES (?, ?, ?, ?, NOW(), 'pendiente')";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$usuario_id, $cliente_nombre, $lista_productos, $total]);

    $id_pedido = $pdo->lastInsertId();

    echo json_encode(['status' => 'success', 'message' => "Pedido #$id_pedido guardado"]);

} catch (PDOException $e) {
    echo json_encode(['status' => 'error', 'message' => 'Error BD: ' . $e->getMessage()]);
}
?>