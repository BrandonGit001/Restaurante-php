<?php
session_start();
require 'config/db.php';
// require 'includes/funciones.php'; // Si da error, lo dejamos comentado
// ... después de $esta_abierto = ...

// 👇 AGREGA ESTA LÍNEA MÁGICA PARA QUE EL HEADER SEPA EL NOMBRE 👇
$usuario_nombre = $_SESSION['user_nombre'] ?? 'Invitado';
// 1. OBTENER CONFIGURACIÓN (Directo)
$stmt_conf = $pdo->query("SELECT * FROM configuracion LIMIT 1");
$config = $stmt_conf->fetch(PDO::FETCH_ASSOC);

// VALORES POR DEFECTO (Por si falla la base de datos)
if (!$config) {
    $config = [
        'nombre_negocio' => 'Mi Restaurante',
        'telefono_whatsapp' => '0000000000',
        'estado_tienda' => 1, // Corregido: antes era 'tienda_abierta'
        'usar_whatsapp' => 1
    ];
}
$esta_abierto = $config['estado_tienda'] ?? 1; // Corregido el nombre de la columna

// 2. CATEGORÍAS
$stmt_cat = $pdo->query("SELECT * FROM categorias ORDER BY orden ASC");
$categorias = $stmt_cat->fetchAll(PDO::FETCH_ASSOC);

// 3. PRODUCTOS (SIN LÍMITE)
$sql = "SELECT p.*, c.nombre as categoria_nombre, 
        0 as promedio, 
        0 as num_resenas 
        FROM productos p 
        LEFT JOIN categorias c ON p.categoria_id = c.id 
        WHERE p.activo = 1";

if (isset($_GET['categoria'])) {
    $cat_id = intval($_GET['categoria']);
    $sql .= " AND categoria_id = $cat_id";
}

$sql .= " ORDER BY c.orden ASC, p.id DESC";
$stmt = $pdo->query($sql);
$productos = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Menú Completo | <?php echo htmlspecialchars($config['nombre_negocio']); ?></title>
    
    <link rel="stylesheet" href="assets/css/style.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;800&display=swap" rel="stylesheet">
  
   <script src="assets/js/app.js" defer></script>
    <style>
        /* AJUSTES EXTRA PARA LAS TARJETAS */
        .card img { 
            width: 100%; height: 200px !important; object-fit: cover; 
        }
        .descripcion-corta {
            display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden;
            color: #777; margin-bottom: 10px; font-size: 0.9rem;
        }
        /* Para ocultar el header fijo del index si estorba, o ajustarlo */
        #modal-carrito.activo {
                display: flex !important; 
            }
        body { padding-top: 80px; } 
    </style>
</head>

<body data-telefono="<?php echo htmlspecialchars($config['telefono_whatsapp'] ?? ''); ?>" 
      data-negocio="<?php echo htmlspecialchars($config['nombre_negocio']); ?>"
      data-modo="<?php echo $config['usar_whatsapp'] ?? 0; ?>">

     <header class="navbar-fija">
        <div class="logo" style="font-weight: 800; font-size: 1.5rem; color: #333;">
            <?php echo htmlspecialchars($config['nombre_negocio']); ?> 🍔
        </div>
        
        <div class="user-menu" style="display:flex; align-items:center; gap:15px;">
            
            <?php if(isset($_SESSION['user_id'])): ?>
                <span style="font-weight:600; color:#27ae60;">Hola, <?php echo htmlspecialchars($usuario_nombre); ?></span>
                <a href="mis_pedidos.php" style="color:#333; text-decoration:none;">Mis Pedidos</a>
                <a href="index.php" style="text-decoration: none; color: #333; font-weight: bold; border-bottom: 2px solid #f1c40f;">
        📜 Pagina Principal
    </a>
                <a href="logout.php" style="color:red; text-decoration:none;">Salir</a>
            <?php else: ?>
                <a href="login.php" style="color:#333; text-decoration:none; font-weight:bold;">Entrar</a>
            <?php endif; ?>

            <a href="javascript:void(0)" onclick="toggleCarrito()" style="text-decoration:none; position:relative;">
                <span style="font-size:1.5rem;">🛒</span>
                <span id="carrito-count" class="badge bg-danger rounded-pill">0</span>
            </a>
        </div>
    </header>

    <div style="max-width: 1200px; margin: 0 auto; padding: 20px;">
        <h1 style="text-align: center; margin-bottom: 10px;">📜 Menú Completo</h1>
        <p style="text-align: center; color: #777; margin-bottom: 30px;">Ordena todo lo que se te antoje</p>

        <div class="categorias-scroll" style="display: flex; overflow-x: auto; gap: 10px; padding-bottom: 20px; justify-content: center;">
            <a href="menu.php" class="btn-cat <?php echo !isset($_GET['categoria']) ? 'active' : ''; ?>" 
               style="padding: 8px 20px; border-radius: 20px; border: 1px solid #ddd; text-decoration: none; color: #333; <?php echo !isset($_GET['categoria']) ? 'background:#f1c40f; border-color:#f1c40f;' : 'background:white;'; ?>">
               Todo
            </a>
            <?php foreach($categorias as $cat): ?>
                <a href="menu.php?categoria=<?php echo $cat['id']; ?>" 
                   style="padding: 8px 20px; border-radius: 20px; border: 1px solid #ddd; text-decoration: none; color: #333; white-space: nowrap; 
                   <?php echo (isset($_GET['categoria']) && $_GET['categoria'] == $cat['id']) ? 'background:#f1c40f; border-color:#f1c40f;' : 'background:white;'; ?>">
                    <?php echo htmlspecialchars($cat['nombre']); ?>
                </a>
            <?php endforeach; ?>
        </div>

        <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(250px, 1fr)); gap: 25px;">
            <?php foreach($productos as $p): ?>
                <div class="card" style="background: white; border-radius: 15px; overflow: hidden; box-shadow: 0 5px 15px rgba(0,0,0,0.05); transition: transform 0.2s; border: 1px solid #eee;">
                    
                    <?php if($p['imagen']): ?>
                        <img src="uploads/productos/<?php echo htmlspecialchars($p['imagen']); ?>" 
                             alt="<?php echo htmlspecialchars($p['nombre']); ?>">
                    <?php else: ?>
                        <div style="height:200px; background:#eee; display:flex; align-items:center; justify-content:center; color:#aaa;">Sin Foto</div>
                    <?php endif; ?>
                    
                    <div style="padding: 15px;">
                        <h3 style="margin: 0 0 5px 0; font-size: 1.1rem;"><?php echo htmlspecialchars($p['nombre']); ?></h3>
                        
                        <p class="descripcion-corta">
                            <?php echo htmlspecialchars($p['descripcion']); ?>
                        </p>
                        
                        <div style="display: flex; justify-content: space-between; align-items: center; margin-top: 15px;">
                            <span style="font-size: 1.2rem; font-weight: 800; color: #2c3e50;">
                                $<?php echo number_format($p['precio'], 2); ?>
                            </span>
                            
                           <?php if($p['stock'] > 0): ?>
    
                              <button class="btn-add" onclick="agregarAlCarrito(<?php echo $p['id']; ?>, '<?php echo htmlspecialchars($p['nombre']); ?>', <?php echo $p['precio']; ?>)">
                                    Agregar
                                </button>

                            <?php else: ?>

                                <button class="btn-add" disabled style="cursor: not-allowed;">
                                    <i class="fas fa-times-circle"></i> Agotado
                                </button>

                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>

    <div id="modal-carrito" style="display:none; position:fixed; top:0; right:0; width:100%; max-width:400px; height:100%; background:white; z-index:2000; box-shadow:-5px 0 15px rgba(0,0,0,0.1); flex-direction:column;">
        <div style="padding:20px; background:#333; color:white; display:flex; justify-content:space-between;">
            <h2>Tu Pedido 🛒</h2>
            <button onclick="toggleCarrito()" style="background:none; border:none; color:white; font-size:1.5rem; cursor:pointer;">&times;</button>
        </div>
        <div id="carrito-items" style="flex:1; overflow-y:auto; padding:20px;">
            <p style="text-align:center; color:#777;">Tu carrito está vacío.</p>
        </div>
        <div style="padding:20px; border-top:1px solid #eee;">
            <div style="display:flex; justify-content:space-between; font-size:1.2rem; font-weight:bold; margin-bottom:15px;">
                <span>Total:</span>
                <span id="carrito-total">$0.00</span>
            </div>
            <button onclick="enviarPedido()" style="width:100%; background:#f1c40f; color:#333; border:none; padding:15px; border-radius:10px; font-weight:bold; font-size:1.1rem; cursor:pointer;">
                Confirmar Pedido ✅
            </button>
        </div>
    </div>
                        
    <footer style="text-align: center; padding: 40px; background: #2c3e50; color: white;">
        <p>&copy; 2026 <strong><?php echo htmlspecialchars($config['nombre_negocio']); ?></strong></p>
    </footer>
</body>
</html>