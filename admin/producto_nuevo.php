<?php
/* Archivo: admin/producto_nuevo.php */
session_start();
require '../config/db.php';
require '../includes/funciones.php';
verificar_admin();

// Traemos las categorías para el Select
$stmt = $pdo->query("SELECT * FROM categorias ORDER BY orden ASC");
$categorias = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Nuevo Producto</title>
    <link rel="stylesheet" href="css/admin.css">
</head>
<body>

<div class="admin-layout">
    <nav class="sidebar">
        <h2>Mi Restaurante</h2>
          <a href="dashboard.php" class="active">📊 Resumen</a>
        <a href="pedidos.php">🛎️ Pedidos <?php if($pendientes>0) echo "<span class='badge'>$pendientes</span>"; ?></a>
        <a href="productos.php">🍔 Productos</a>
        <a href="categorias.php">📂 Categorías</a>
        <a href="galeria.php">📸 Galería</a>
        <a href="resenas.php">⭐ Reseñas</a>
        <a href="configuracion.php">⚙️ Configuración</a>
        <a href="logout.php" class="salir">Cerrar Sesión</a>
    </nav>

    <main class="main-content">
        <div class="header-admin">
            <h1>Agregar Nuevo Platillo</h1>
            <a href="productos.php" class="btn btn-azul">Volver</a>
        </div>

        <div class="stat-card" style="text-align: left; max-width: 600px;">
            <form action="acciones/producto_guardar.php" method="POST" enctype="multipart/form-data">
                
                <label>Nombre del Platillo:</label>
                <input type="text" name="nombre" class="input-form" required placeholder="Ej: Hamburguesa Doble">

                <label>Descripción:</label>
                <textarea name="descripcion" class="input-form" rows="3" placeholder="Ingredientes, detalles..."></textarea>

                <div style="display: flex; gap: 20px;">
                    <div style="flex: 1;">
                        <label>Precio ($):</label>
                        <input type="number" name="precio" step="0.50" class="input-form" required>
                    </div>
                    <div style="flex: 1;">
                        <label>Categoría:</label>
                        <select name="categoria_id" class="input-form" required>
                            <option value="">-- Selecciona --</option>
                            <?php foreach($categorias as $cat): ?>
                                <option value="<?php echo $cat['id']; ?>">
                                    <?php echo htmlspecialchars($cat['nombre']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

                <label>Foto del Platillo:</label>
                <input type="file" name="foto" class="input-form" accept="image/*">
                <small>Se optimizará y se le pondrá sello automáticamente.</small>

                <br><br>
                
                <label>Opciones:</label>
                <div style="margin-top: 5px;">
                    <input type="checkbox" name="destacado" value="1"> ¿Es el Platillo del Día? ⭐
                </div>
                <div style="margin-top: 5px;">
                    <input type="checkbox" name="activo" value="1" checked> Mostrar en el menú inmediatamente
                </div>

                <br>
                <button type="submit" class="btn btn-verde" style="width: 100%;">Guardar Platillo</button>

            </form>
        </div>
    </main>
</div>

<style>
    .input-form {
        width: 100%;
        padding: 10px;
        margin: 5px 0 15px 0;
        border: 1px solid #ddd;
        border-radius: 4px;
        box-sizing: border-box;
    }
</style>

</body>
</html>