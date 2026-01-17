<?php
/* Archivo: index.php (VERSIÓN LIMPIA Y FINAL) */
session_start();
require 'config/db.php';

// 1. Cargar Configuración
$stmt = $pdo->query("SELECT * FROM configuracion WHERE id = 1");
$config = $stmt->fetch(PDO::FETCH_ASSOC);

// 2. Cargar Categorías
$stmt = $pdo->query("SELECT * FROM categorias ORDER BY orden ASC");
$categorias = $stmt->fetchAll(PDO::FETCH_ASSOC);

// 3. Cargar Destacado (Opcional, solo para sacar datos si quieres)
$stmt = $pdo->query("SELECT * FROM productos WHERE destacado = 1 AND activo = 1 LIMIT 1");
$destacado = $stmt->fetch(PDO::FETCH_ASSOC);

// 4. Productos 
$sql = "SELECT p.*, c.nombre as categoria_nombre,
        0 as promedio, 
        0 as num_resenas 
        FROM productos p 
        LEFT JOIN categorias c ON p.categoria_id = c.id 
        WHERE p.activo = 1 
        ORDER BY c.orden ASC, p.id DESC";
$stmt = $pdo->query($sql);
$productos = $stmt->fetchAll(PDO::FETCH_ASSOC);
// 5. Cargar Galería
$galeria = $pdo->query("SELECT * FROM galeria ORDER BY id DESC LIMIT 6")->fetchAll(PDO::FETCH_ASSOC);
// Variables auxiliares
$esta_abierto = (bool) $config['estado_tienda'];
$tiempo_estimado = $config['tiempo_estimado'] ?? '';
// CORRECCIÓN DEL ERROR PHP: Usamos 'user_nombre' que es como lo guardaste en login
$usuario_nombre = $_SESSION['user_nombre'] ?? 'Invitado'; 
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($config['nombre_negocio']); ?></title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/style.css">
    
    <style>
        /* --- ESTILOS CORRECTIVOS --- */
        
        /* 1. NAVBAR FIJO (El Techo) */
        header.navbar-fija {
            position: fixed;
            top: 0; left: 0; width: 100%;
            background: rgba(255, 255, 255, 0.95); /* Blanco semi-transparente */
            backdrop-filter: blur(10px); /* Efecto borroso tipo iPhone */
            z-index: 1000; /* Siempre arriba */
            padding: 15px 5%;
            box-shadow: 0 2px 15px rgba(0,0,0,0.05);
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-sizing: border-box;
        }

        /* 2. EL BANNER PARALLAX (La Magia sin romper nada) */
        .hero-banner {
            height: 60vh; /* Ocupa el 60% de la altura de la pantalla */
            background-image: linear-gradient(rgba(0,0,0,0.5), rgba(0,0,0,0.7)), url('https://images.unsplash.com/photo-1550547660-d9450f859349?ixlib=rb-1.2.1&auto=format&fit=crop&w=1950&q=80');
            
            /* ESTA LÍNEA HACE EL EFECTO BONITO: */
            background-attachment: fixed; 
            
            background-size: cover;
            background-position: center;
            display: flex;
            align-items: center;     /* Centra verticalmente */
            justify-content: center; /* Centra horizontalmente */
            text-align: center;
            color: white;
            margin-bottom: 0;
            position: relative;
            z-index: 1;
        }

        .hero-content h1 { font-size: 3.5rem; margin: 0; text-shadow: 2px 2px 10px rgba(0,0,0,0.5); }
        .hero-content p { font-size: 1.2rem; margin-top: 10px; opacity: 0.9; }

        /* 3. EL CUERPO DE LA PÁGINA */
        body {
            padding-top: 80px; /* Espacio para que el navbar no tape el banner */
            background-color: #f8f9fa; /* Fondo gris muy clarito */
            margin: 0;
        }

        /* 4. SECCIÓN DE MENÚ */
        .menu-section {
            background: #f8f9fa; 
            padding: 40px 5%;
            position: relative;
            z-index: 2; /* Va encima del banner visualmente */
        }
    </style>
</head>

<body data-telefono="<?php echo htmlspecialchars($config['telefono_whatsapp']); ?>" 
      data-negocio="<?php echo htmlspecialchars($config['nombre_negocio']); ?>"
      data-modo="<?php echo $config['usar_whatsapp']; ?>">
        <?php if(!$esta_abierto): ?>
        <div style="position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.9); z-index: 9999; display: flex; flex-direction: column; align-items: center; justify-content: center; color: white; text-align: center;">
            <div style="font-size: 5rem;">😴</div>
            <h1>¡Lo sentimos, estamos cerrados!</h1>
            <p>Nuestro horario de atención ha terminado por hoy.</p>
            <p>Vuelve mañana para probar el mejor sabor.</p>
            <a href="login.php" style="margin-top: 20px; color: #777; font-size: 0.8rem;">Soy Admin</a>
        </div>
    <?php endif; ?>
    <header class="navbar-fija">
        <div class="logo" style="font-weight: 800; font-size: 1.5rem; color: #333;">
            <?php echo htmlspecialchars($config['nombre_negocio']); ?> 🍔
        </div>
        
        <div class="user-menu" style="display:flex; align-items:center; gap:15px;">
            
            <?php if(isset($_SESSION['user_id'])): ?>
                <span style="font-weight:600; color:#27ae60;">Hola, <?php echo htmlspecialchars($usuario_nombre); ?></span>
                <a href="mis_pedidos.php" style="color:#333; text-decoration:none;">Mis Pedidos</a>
                <a href="menu.php" style="text-decoration: none; color: #333; font-weight: bold; border-bottom: 2px solid #f1c40f;">
        📜 Menú Completo
    </a>
                <a href="logout.php" style="color:red; text-decoration:none;">Salir</a>
            <?php else: ?>
                <a href="login.php" style="color:#333; text-decoration:none; font-weight:bold;">Entrar</a>
            <?php endif; ?>

            <a href="javascript:void(0)" onclick="toggleCarrito()" style="text-decoration:none; position:relative;">
                <span style="font-size:1.5rem;">🛒</span>
                <span id="carrito-count" style="position:absolute; top:-5px; right:-5px; background:red; color:white; font-size:0.7rem; padding:2px 6px; border-radius:50%;">0</span>
            </a>
        </div>
    </header>

    <div class="hero-banner">
        <div class="hero-content">
            <?php if ($destacado): ?>
                <span style="background: #f1c40f; color: #333; padding: 5px 15px; border-radius: 20px; font-weight: bold; font-size: 0.9rem;">¡HOY RECOMENDAMOS!</span>
                <h1><?php echo htmlspecialchars($destacado['nombre']); ?></h1>
                <p><?php echo htmlspecialchars($destacado['descripcion']); ?></p>
                <div style="font-size: 2rem; font-weight: bold; margin: 15px 0;">$<?php echo $destacado['precio']; ?></div>
            <?php else: ?>
                <h1>¡El Sabor que Mereces! 🍔</h1>
                <p>Las mejores hamburguesas de la ciudad.</p>
            <?php endif; ?>
            
            <button onclick="document.getElementById('menu').scrollIntoView({behavior: 'smooth'});" 
                    style="background: #25d366; color: white; border: none; padding: 12px 30px; border-radius: 30px; font-size: 1.1rem; margin-top: 20px; cursor: pointer;">
                Ver Menú 👇
            </button>
        </div>
    </div>

    <section id="menu" class="menu-section">
        
        <div class="filters" style="text-align: center; margin-bottom: 30px; overflow-x: auto; white-space: nowrap; padding-bottom: 10px;">
            <button class="filter-btn active" style="margin: 5px; padding: 8px 20px; border: none; background: #333; color: white; border-radius: 20px;">Todo</button>
            <?php foreach ($categorias as $cat): ?>
                <button class="filter-btn" style="margin: 5px; padding: 8px 20px; border: 1px solid #ddd; background: white; color: #333; border-radius: 20px;"><?php echo htmlspecialchars($cat['nombre']); ?></button>
            <?php endforeach; ?>
        </div>

        <div class="products-grid">
            <?php foreach ($productos as $prod): ?>
                <div class="card">
                    <?php if ($prod['imagen']): ?>
                        <img src="uploads/productos/<?php echo $prod['imagen']; ?>" class="card-img">
                    <?php else: ?>
                        <div style="height:200px; background:#eee; display:flex; align-items:center; justify-content:center; color:#999;">📷</div>
                    <?php endif; ?>
                    
                    <div class="card-body">
                        <div class="card-title"><?php echo htmlspecialchars($prod['nombre']); ?></div>
                        
                       

                        <div class="card-desc"><?php echo htmlspecialchars(substr($prod['descripcion'], 0, 60)); ?>...</div>
                        
                        <div class="card-footer">
                            <div class="precio">$<?php echo number_format($prod['precio'], 2); ?></div>
                            <button class="btn-add" onclick="agregarAlCarrito(<?php echo $prod['id']; ?>, '<?php echo htmlspecialchars($prod['nombre']); ?>', <?php echo $prod['precio']; ?>)">Agregar +</button>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
        <div style="text-align: center; margin: 40px 0 60px 0;">
        <h3 style="color: #555;">¿Te quedaste con hambre de más? 😋</h3>
        <a href="menu.php" style="
            background: #2c3e50; 
            color: white; 
            padding: 15px 40px; 
            text-decoration: none; 
            font-size: 1.2rem; 
            font-weight: bold; 
            border-radius: 50px; 
            box-shadow: 0 5px 15px rgba(0,0,0,0.2);
            transition: transform 0.2s;
            display: inline-block;
        " onmouseover="this.style.transform='scale(1.05)'" 
          onmouseout="this.style.transform='scale(1)'">
            📜 Ver Menú Completo
        </a>
    </div>
        <?php if(!empty($galeria)): ?>
<section style="padding: 50px 0; background: white;">
    <div class="container" style="text-align: center;">
        <h2 style="font-size: 2.2rem; margin-bottom: 10px; color: #333;">Nuestros Momentos 📸</h2>
        <p style="color: #666; margin-bottom: 30px;">Siguenos el ritmo y antójate</p>
        
        <div style="
            display: grid; 
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); 
            gap: 10px;
        ">
            <?php foreach($galeria as $foto): ?>
                <div style="position: relative; overflow: hidden; border-radius: 10px; height: 250px; group">
                    <img src="uploads/galeria/<?php echo $foto['imagen']; ?>" 
                         style="width: 100%; height: 100%; object-fit: cover; transition: transform 0.3s ease;"
                         onmouseover="this.style.transform='scale(1.1)'" 
                         onmouseout="this.style.transform='scale(1)'">
                         
                    <?php if($foto['descripcion']): ?>
                        <div style="
                            position: absolute; bottom: 0; left: 0; width: 100%; 
                            background: rgba(0,0,0,0.6); color: white; padding: 10px; 
                            font-size: 0.9rem;">
                            <?php echo htmlspecialchars($foto['descripcion']); ?>
                        </div>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>
<?php endif; ?>
        <div class="container" style="max-width: 1000px; margin: 0 auto;">
            <h2 style="font-size: 2.2rem; margin-bottom: 50px; color: #2c3e50;">¿Por qué nos prefieren? ❤️</h2>
            
            <div style="display: flex; flex-wrap: wrap; justify-content: center; gap: 40px;">
                
                <div style="flex: 1; min-width: 250px; padding: 20px;">
                    <div style="font-size: 3.5rem; margin-bottom: 20px;">🥩</div>
                    <h3 style="margin-bottom: 15px; color: #333; font-weight: 800;">Carne Premium</h3>
                    <p style="color: #666; line-height: 1.6;">Seleccionamos los cortes más finos y los molemos diariamente. 100% sabor real, 0% congelados.</p>
                </div>

                <div style="flex: 1; min-width: 250px; padding: 20px;">
                    <div style="font-size: 3.5rem; margin-bottom: 20px;">🚀</div>
                    <h3 style="margin-bottom: 15px; color: #333; font-weight: 800;">Envío Veloz</h3>
                    <p style="color: #666; line-height: 1.6;">Del asador a tu puerta en tiempo récord. Utilizamos empaques térmicos para que llegue caliente.</p>
                </div>

                <div style="flex: 1; min-width: 250px; padding: 20px;">
                    <div style="font-size: 3.5rem; margin-bottom: 20px;">🥬</div>
                    <h3 style="margin-bottom: 15px; color: #333; font-weight: 800;">Vegetales Frescos</h3>
                    <p style="color: #666; line-height: 1.6;">Apoyamos a productores locales. La lechuga truena de fresca y el tomate es del día.</p>
                </div>

            </div>
        </div>
    </section>

    <footer style="text-align: center; padding: 40px; background: #2c3e50; color: white;">
        <p>&copy; 2026 <strong><?php echo htmlspecialchars($config['nombre_negocio']); ?></strong></p>
    </footer>

    <div class="modal-overlay" id="modal-carrito">
        <div class="modal-content">
            <button class="close-btn" onclick="toggleCarrito()">X</button>
            <h2>Tu Pedido</h2>
            <div id="carrito-items"></div>
            <div class="total-row"><span>Total:</span><span id="carrito-total">$0.00</span></div>
            <button class="btn-pedir" onclick="enviarPedido()">Confirmar Pedido</button>
        </div>
    </div>

    <script src="assets/js/app.js"></script>
</body>
</html>