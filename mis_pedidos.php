<?php
/* mis_pedidos.php - COMPLETO CON RESEÑAS */
session_start();
require 'config/db.php';
require 'includes/funciones.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user_id'];
$cliente = $_SESSION['user_nombre'] ?? 'Usuario';

// --- 1. LÓGICA PARA GUARDAR LA RESEÑA 💾 ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['accion']) && $_POST['accion'] === 'calificar') {
    $pedido_id = $_POST['pedido_id'];
    $calificacion = $_POST['calificacion'];
    $comentario = $_POST['comentario'];

    // Insertamos la reseña
    $stmt = $pdo->prepare("INSERT INTO resenas (pedido_id, usuario_id, calificacion, comentario) VALUES (?, ?, ?, ?)");
    if($stmt->execute([$pedido_id, $user_id, $calificacion, $comentario])) {
        // (Opcional) Marcamos el pedido como calificado para no dejar calificar doble
        // Pero por ahora lo dejamos simple.
        echo "<script>alert('¡Gracias por tu opinión! ⭐'); window.location.href='mis_pedidos.php';</script>";
    }
}

// --- 2. TRAER PEDIDOS DEL CLIENTE ---
$sql = "SELECT * FROM pedidos WHERE usuario_id = ? ORDER BY id DESC";
$stmt = $pdo->prepare($sql);
$stmt->execute([$user_id]);
$mis_pedidos = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Verificamos qué pedidos ya tienen reseña para ocultar el botón (Opcional PRO)
$sql_resenas = "SELECT pedido_id FROM resenas WHERE usuario_id = ?";
$stmt_r = $pdo->prepare($sql_resenas);
$stmt_r->execute([$user_id]);
$resenas_hechas = $stmt_r->fetchAll(PDO::FETCH_COLUMN); // Array con IDs calificados
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mis Pedidos</title>
    <link rel="stylesheet" href="css/estilos.css">
    <style>
        /* Estilos del Modal de Calificar */
        .modal-overlay {
            display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%;
            background: rgba(0,0,0,0.5); z-index: 9999; justify-content: center; align-items: center;
        }
        .modal-box {
            background: white; padding: 30px; border-radius: 15px; width: 90%; max-width: 400px;
            text-align: center; box-shadow: 0 10px 25px rgba(0,0,0,0.2); animation: popIn 0.3s ease;
        }
        @keyframes popIn { from{transform:scale(0.8); opacity:0;} to{transform:scale(1); opacity:1;} }
        
        .estrellas { font-size: 2rem; color: #ccc; cursor: pointer; transition: color 0.2s; }
        .estrellas:hover, .estrellas.activa { color: #f1c40f; }
        
        /* Ocultamos los radio buttons reales */
        .rating-group { display: flex; justify-content: center; flex-direction: row-reverse; gap: 5px; margin: 15px 0;}
        .rating-group input { display: none; }
        .rating-group label { font-size: 2.5rem; color: #ddd; cursor: pointer; }
        /* Magia CSS para colorear estrellas */
        .rating-group input:checked ~ label,
        .rating-group label:hover,
        .rating-group label:hover ~ label { color: #f1c40f; }

        .btn-enviar-review { background: #27ae60; color: white; border: none; padding: 12px; width: 100%; border-radius: 50px; font-weight: bold; cursor: pointer; font-size: 1rem; margin-top: 15px;}
        .btn-cancelar { background: transparent; border: none; color: #777; margin-top: 10px; cursor: pointer; text-decoration: underline;}
    </style>
</head>
<body style="background: #fdfbf7; font-family: 'Poppins', sans-serif;">

    <div style="max-width: 800px; margin: 0 auto; padding: 20px;">
        <a href="index.php" style="text-decoration: none; color: #333; font-weight: bold;">⬅ Volver al Menú</a>
        
        <div style="display:flex; align-items:center; justify-content:space-between; margin-top:10px;">
            <h1>Mis Pedidos 🧾</h1>
            <span style="background:#333; color:white; padding:5px 15px; border-radius:20px;">Hola, <?php echo htmlspecialchars($cliente); ?></span>
        </div>

        <?php if (empty($mis_pedidos)): ?>
            <div style="text-align: center; padding: 60px; background: white; border-radius: 15px; margin-top: 20px;">
                <h3 style="color:#777;">Aún no has hecho pedidos.</h3>
                <p>¿Se te antoja algo? Ve al menú.</p>
                <a href="index.php" style="background:#f1c40f; color:#333; padding:10px 20px; text-decoration:none; border-radius:20px; font-weight:bold;">Ir a pedir</a>
            </div>
        <?php else: ?>

            <?php foreach ($mis_pedidos as $p): ?>
                <?php 
                // --- LÓGICA DE ESTADO ---
                $mensaje_estado = ""; $color_estado = "#ccc"; $barra_progreso = "0%";
                $estado = strtolower($p['estado'] ?? 'pendiente'); // Evita error si es null
                if(empty($estado)) $estado = 'pendiente';

                switch($estado) {
                    case 'pendiente': $mensaje_estado = "⏳ Recibido. Esperando..."; $color_estado = "#f1c40f"; $barra_progreso = "10%"; break;
                    case 'cocinando': $mensaje_estado = "🔥 Cocinando..."; $color_estado = "#e67e22"; $barra_progreso = "50%"; break;
                    case 'enviado':   $mensaje_estado = "🛵 En camino..."; $color_estado = "#3498db"; $barra_progreso = "80%"; break;
                    case 'completado': case 'entregado': $mensaje_estado = "✅ ¡Disfrútalo!"; $color_estado = "#27ae60"; $barra_progreso = "100%"; break;
                    case 'cancelado': $mensaje_estado = "❌ Cancelado"; $color_estado = "#c0392b"; $barra_progreso = "0%"; break;
                    default: $mensaje_estado = "Estado: $estado";
                }

                // Si hay mensaje del admin, lo usamos
                if (!empty($p['mensaje_admin'])) {
                    $mensaje_estado = "💬 " . htmlspecialchars($p['mensaje_admin']);
                }
                ?>

                <div style="background: white; border-left: 6px solid <?php echo $color_estado; ?>; padding: 25px; border-radius: 12px; margin-bottom: 25px; box-shadow: 0 4px 15px rgba(0,0,0,0.05);">
                    <div style="display:flex; justify-content:space-between; align-items:center;">
                        <h3 style="margin:0;">Pedido #<?php echo $p['id']; ?></h3>
                        <span style="background:<?php echo $color_estado; ?>; color:white; padding:4px 12px; border-radius:15px; font-size:0.7rem; font-weight:bold; text-transform:uppercase;">
                            <?php echo $estado; ?>
                        </span>
                    </div>

                    <p style="color:<?php echo $color_estado; ?>; font-weight:bold; margin: 10px 0; font-size:0.9rem;">
                        <?php echo $mensaje_estado; ?>
                    </p>
                    
                    <div style="background:#eee; height:6px; border-radius:10px; overflow:hidden; margin-bottom:15px;">
                        <div style="background:<?php echo $color_estado; ?>; width:<?php echo $barra_progreso; ?>; height:100%;"></div>
                    </div>

                    <div style="color:#555; font-size:0.95rem; line-height:1.6;">
                        <?php echo nl2br(htmlspecialchars($p['productos'])); ?>
                    </div>

                    <hr style="border:0; border-top:1px solid #f0f0f0; margin:15px 0;">

                    <div style="display:flex; justify-content:space-between; align-items:center;">
                        <strong style="font-size:1.3rem;">Total: $<?php echo number_format($p['total'], 2); ?></strong>
                        
                        <?php if( ($estado == 'completado' || $estado == 'entregado') && !in_array($p['id'], $resenas_hechas) ): ?>
                            <button onclick="abrirCalificar(<?php echo $p['id']; ?>)" 
                                    style="background:#f1c40f; border:none; padding:10px 20px; border-radius:30px; cursor:pointer; font-weight:bold; display:flex; align-items:center; gap:5px; box-shadow: 0 3px 10px rgba(241, 196, 15, 0.3);">
                                ⭐ Calificar
                            </button>
                        <?php elseif(in_array($p['id'], $resenas_hechas)): ?>
                            <span style="color:#27ae60; font-size:0.9rem;">✅ ¡Gracias por tu reseña!</span>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; ?>

        <?php endif; ?>
    </div>

    <div id="modalReview" class="modal-overlay">
        <div class="modal-box">
            <h2>Califica tu comida 🍔</h2>
            <p>¿Qué tal estuvo el pedido?</p>
            
            <form method="POST" action="mis_pedidos.php">
                <input type="hidden" name="accion" value="calificar">
                <input type="hidden" name="pedido_id" id="inputPedidoId">

                <div class="rating-group">
                    <input type="radio" id="star5" name="calificacion" value="5" required /><label for="star5">★</label>
                    <input type="radio" id="star4" name="calificacion" value="4" /><label for="star4">★</label>
                    <input type="radio" id="star3" name="calificacion" value="3" /><label for="star3">★</label>
                    <input type="radio" id="star2" name="calificacion" value="2" /><label for="star2">★</label>
                    <input type="radio" id="star1" name="calificacion" value="1" /><label for="star1">★</label>
                </div>

                <textarea name="comentario" placeholder="Escribe un comentario corto..." 
                          style="width:100%; height:80px; padding:10px; border:1px solid #ddd; border-radius:10px; resize:none; font-family:inherit;"></textarea>

                <button type="submit" class="btn-enviar-review">Enviar Reseña 🚀</button>
                <button type="button" class="btn-cancelar" onclick="cerrarCalificar()">Cancelar</button>
            </form>
        </div>
    </div>

    <script>
        function abrirCalificar(idPedido) {
            document.getElementById('inputPedidoId').value = idPedido;
            document.getElementById('modalReview').style.display = 'flex';
        }

        function cerrarCalificar() {
            document.getElementById('modalReview').style.display = 'none';
        }
    </script>

</body>
</html>