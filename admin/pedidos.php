<?php
/* admin/pedidos.php - VERSIÓN MAESTRA FLEXIBLE */
session_start();
require '../config/db.php';
require '../includes/funciones.php';
verificar_admin();

// 1. PROCESAR EL FORMULARIO (GUARDAR CAMBIOS) 💾
if (isset($_POST['accion']) && $_POST['accion'] == 'actualizar') {
    $id_pedido = $_POST['id'];
    $nuevo_estado = $_POST['estado'];
    $mensaje = $_POST['mensaje_admin'];

    // Actualizamos Estado y Nota
    $stmt = $pdo->prepare("UPDATE pedidos SET estado = ?, mensaje_admin = ? WHERE id = ?");
    $stmt->execute([$nuevo_estado, $mensaje, $id_pedido]);
    
    // Recargamos para ver cambios
    header("Location: pedidos.php");
    exit;
}

// 2. OBTENER PEDIDOS
$stmt = $pdo->query("SELECT * FROM pedidos ORDER BY id DESC");
$pedidos = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Administrar Pedidos</title>
    <link rel="stylesheet" href="css/admin.css">
    <style>
        /* Estilos rápidos para que se vea bien */
        body { font-family: sans-serif; background: #f4f6f9; }
        .badge { padding: 5px 12px; border-radius: 15px; font-size: 0.75rem; font-weight: bold; color: white; text-transform: uppercase; letter-spacing: 0.5px; }
        
        /* Colores de estado */
        .st-pendiente { background-color: #f1c40f; color: #333; }
        .st-cocinando { background-color: #e67e22; }
        .st-enviado   { background-color: #3498db; }
        .st-completado, .st-entregado { background-color: #27ae60; }
        .st-cancelado { background-color: #c0392b; }
        .st-vacio     { background-color: #95a5a6; } /* Gris para cuando no hay estado */

        table { width: 100%; background: white; border-collapse: collapse; box-shadow: 0 2px 10px rgba(0,0,0,0.05); border-radius: 8px; overflow: hidden; margin-top: 20px; }
        th, td { padding: 15px; text-align: left; border-bottom: 1px solid #eee; font-size: 0.9rem; }
        th { background: #2c3e50; color: white; }
        tr:hover { background: #f9f9f9; }

        /* Botón de Administrar */
        .btn-admin { 
            background: #2c3e50; color: white; border: none; 
            padding: 8px 15px; border-radius: 5px; cursor: pointer; 
            font-weight: bold; display: flex; align-items: center; gap: 5px;
        }
        .btn-admin:hover { background: #34495e; }

        /* MODAL */
        .modal-overlay {
            display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%;
            background: rgba(0,0,0,0.6); z-index: 2000; justify-content: center; align-items: center;
        }
        .modal-box {
            background: white; padding: 30px; border-radius: 12px; width: 90%; max-width: 450px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.3);
        }
        .form-group { margin-bottom: 15px; text-align: left; }
        .form-group label { display: block; margin-bottom: 5px; font-weight: bold; color: #555; }
        .form-control { width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 5px; font-size: 1rem; }
        textarea.form-control { resize: vertical; height: 80px; font-family: sans-serif; }
        
        .btn-save { width: 100%; background: #27ae60; color: white; border: none; padding: 12px; border-radius: 5px; font-size: 1rem; cursor: pointer; font-weight: bold; margin-top: 10px;}
        .btn-close { background: transparent; border: none; color: #777; cursor: pointer; float: right; font-size: 1.5rem; margin-top: -15px; margin-right: -10px;}
        .nota-corta {
    max-width: 200px;         /* Ancho máximo de la columna */
    white-space: nowrap;      /* No permite saltos de línea */
    overflow: hidden;         /* Oculta lo que sobre */
    text-overflow: ellipsis;  /* Pone "..." al final */
    display: inline-block;
    vertical-align: middle;
}
    </style>
</head>
<body>

<div class="admin-layout">
    <nav class="sidebar">
        <h2>👨‍🍳 Cocina</h2>
     <a href="dashboard.php">📊 Resumen</a>
        <a href="pedidos.php" class="active">🛎️ Pedidos</a>
        <a href="reportes.php">📈 Reportes</a> 
        <a href="productos.php">🍔 Productos</a>
        <a href="categorias.php">📂 Categorías</a>
        <a href="galeria.php">📸 Galería</a>
        <a href="resenas.php">⭐ Reseñas</a>
        <a href="configuracion.php">⚙️ Configuración</a>
        <a href="logout.php" class="salir">Cerrar Sesión</a>
    </nav>

    <main class="main-content">
        <h1 style="color:#2c3e50;">Gestión de Pedidos 🛎️</h1>

        <?php if(empty($pedidos)): ?>
            <div style="text-align:center; padding:50px; color:#777;">
                <h3>Todo tranquilo... 🍃</h3>
                <p>No hay pedidos registrados.</p>
            </div>
        <?php else: ?>
            <div style="overflow-x: auto;">
                <table>
                    <thead>
                        <tr>
                            <th>#ID</th>
                            <th>Cliente</th>
                            <th>Total</th>
                            <th>Estado Actual</th>
                            <th>Nota Interna / Cliente</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($pedidos as $p): ?>
                        <?php 
                            // Aseguramos que no esté vacío para el color CSS
                            $estado_css = !empty($p['estado']) ? strtolower($p['estado']) : 'vacio'; 
                            $estado_txt = !empty($p['estado']) ? ucfirst($p['estado']) : 'Sin Estado';
                        ?>
                        <tr>
                            <td><strong>#<?php echo $p['id']; ?></strong></td>
                            <td>
                                <?php echo htmlspecialchars($p['cliente_nombre']); ?><br>
                                <small style="color:#777;">📅 <?php echo date('d/m H:i', strtotime($p['fecha'])); ?></small>
                            </td>
                            <td>$<?php echo number_format($p['total'], 2); ?></td>
                            
                            <td><span class="badge st-<?php echo $estado_css; ?>"><?php echo $estado_txt; ?></span></td>
                            
                            <td style="font-style: italic; color: #555;">
                                <?php echo !empty($p['mensaje_admin']) ? '💬 "'.htmlspecialchars($p['mensaje_admin']).'"' : '-'; ?>
                            </td>

                                <td>
                                    <button class="btn-admin" 
                                            data-id="<?php echo $p['id']; ?>"
                                            data-estado="<?php echo $p['estado']; ?>"
                                            data-mensaje="<?php echo htmlspecialchars($p['mensaje_admin'] ?? ''); ?>"
                                            onclick="abrirGestorDesdeData(this)">
                                        ⚙️ Gestionar
                                    </button>
                                 <a href="ticket.php?id=<?php echo $p['id']; ?>" 
                                    target="_blank" 
                                    class="btn-admin" 
                                    style="background: #7f8c8d; text-decoration: none; justify-content: center; width: 50px; margin-top:5px;">
                                    🖨️
                                 </a>
                                </td>

                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </main>
</div>

<div id="modalGestor" class="modal-overlay">
    <div class="modal-box">
        <button class="btn-close" onclick="cerrarGestor()">&times;</button>
        <h2 style="margin-top:0; color:#2c3e50;">Gestionar Pedido</h2>
        <p style="color:#777; margin-bottom:20px;">Cambia el estado o avisa al cliente.</p>
        
        <form method="POST">
            <input type="hidden" name="accion" value="actualizar">
            <input type="hidden" name="id" id="modalId">

            <div class="form-group">
                <label>📍 Estado del Pedido:</label>
                <select name="estado" id="modalEstado" class="form-control">
                    <option value="pendiente">🟡 Pendiente (Recibido)</option>
                    <option value="cocinando">🔥 Cocinando (En preparación)</option>
                    <option value="enviado">🛵 Enviado (En camino)</option>
                    <option value="completado">✅ Completado (Entregado)</option>
                    <option value="cancelado">❌ Cancelado</option>
                </select>
            </div>

            <div class="form-group">
                <label>💬 Nota para el Cliente:</label>
                <textarea name="mensaje_admin" id="modalMensaje" class="form-control" placeholder="Ej: Se acabó el gas, tardamos 10 min más..."></textarea>
                <small style="color:#888;">Este mensaje reemplazará el texto automático en la app del cliente.</small>
            </div>

            <button type="submit" class="btn-save">💾 Guardar Cambios</button>
        </form>
    </div>
</div>

<script>
    // Nueva función inteligente que lee los datos del botón
    function abrirGestorDesdeData(boton) {
        // 1. Sacamos los datos de la "mochila" (data-attributes)
        let id = boton.getAttribute('data-id');
        let estado = boton.getAttribute('data-estado');
        let mensaje = boton.getAttribute('data-mensaje');

        // 2. Llenamos el modal
        document.getElementById('modalId').value = id;
        document.getElementById('modalMensaje').value = mensaje; // Aquí ya entra el texto completo
        
        // 3. Ajustamos el selector
        if(estado && estado !== 'null' && estado !== '') {
            document.getElementById('modalEstado').value = estado.toLowerCase();
        } else {
            document.getElementById('modalEstado').value = 'pendiente';
        }

        // 4. Mostramos
        document.getElementById('modalGestor').style.display = 'flex';
    }

    function cerrarGestor() {
        document.getElementById('modalGestor').style.display = 'none';
    }

    window.onclick = function(event) {
        var modal = document.getElementById('modalGestor');
        if (event.target == modal) {
            cerrarGestor();
        }
    }
</script>

</body>
</html>