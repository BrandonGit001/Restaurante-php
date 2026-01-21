<?php
            /* Archivo: admin/producto_form.php (Sirve para CREAR y EDITAR) */
            session_start();
            require '../config/db.php';
            require '../includes/funciones.php';
            verificar_admin();

            // 1. INICIALIZAR VARIABLES VACÍAS (Para cuando es Nuevo)
            $producto = [
                'id' => '',
                'nombre' => '',
                'descripcion' => '',
                'precio' => '',
                'categoria_id' => '',
                'imagen' => '',
                'activo' => 1,      // Por defecto activo
                'destacado' => 0    // Por defecto no destacado
            ];
            $titulo = "Nuevo Platillo";

            // 2. SI LLEGA UN ID EN LA URL, ES "MODO EDICIÓN"
            if (isset($_GET['id'])) {
                $id = (int)$_GET['id'];
                $stmt = $pdo->prepare("SELECT * FROM productos WHERE id = ?");
                $stmt->execute([$id]);
                $producto_encontrado = $stmt->fetch();

                if ($producto_encontrado) {
                    $producto = $producto_encontrado;
                    $titulo = "Editar: " . htmlspecialchars($producto['nombre']);
                }
            }
                    
            // 3. PROCESAR EL FORMULARIO CUANDO LE DAS A GUARDAR
            if ($_SERVER['REQUEST_METHOD'] == 'POST') {
                $id = $_POST['id']; // Si tiene ID es update, si no es insert
                $nombre = limpiar_str($_POST['nombre']);
                $desc = limpiar_str($_POST['descripcion']);
                $stock = (int)$_POST['stock'];
                $precio = (float)$_POST['precio'];
                $cat_id = (int)$_POST['categoria_id'];
                
                // Checkboxes (Si no están marcados, no envían nada, así que asumimos 0)
                $activo = isset($_POST['activo']) ? 1 : 0;
                $destacado = isset($_POST['destacado']) ? 1 : 0;

                // MANEJO DE LA FOTO
                $nombre_imagen = $producto['imagen']; // Mantenemos la vieja por defecto
                
                // Si subieron una nueva...
                if (isset($_FILES['foto']) && $_FILES['foto']['error'] === UPLOAD_ERR_OK) {
                    $ext = pathinfo($_FILES['foto']['name'], PATHINFO_EXTENSION);
                    $nombre_imagen = time() . "_" . rand(100,999) . "." . $ext; // Nombre único
                    move_uploaded_file($_FILES['foto']['tmp_name'], "../uploads/productos/" . $nombre_imagen);
                }

                try {
                    if ($id) {
                        // --- ACTUALIZAR EXISTENTE ---
                        $sql = "UPDATE productos SET nombre=?, descripcion=?, precio=?, stock=?, categoria_id=?, imagen=?, activo=?, destacado=? WHERE id=?";
                        $stmt = $pdo->prepare($sql);
                        $stmt->execute([$nombre, $desc, $precio, $stock, $cat_id, $nombre_imagen, $activo, $destacado, $id]);
                    } else {
                        // --- CREAR NUEVO ---
                        $sql = "INSERT INTO productos (nombre, descripcion, precio, categoria_id, imagen, activo, destacado) VALUES (?, ?, ?, ?, ?, ?, ?)";
                        $stmt = $pdo->prepare($sql);
                        $stmt->execute([$nombre, $desc, $precio, $cat_id, $nombre_imagen, $activo, $destacado]);
                    }
                    
                    // Volver al listado
                    header("Location: productos.php");
                    exit();

                } catch (PDOException $e) {
                    echo "Error: " . $e->getMessage();
                }
            }

            // 4. TRAER CATEGORÍAS (Para llenar el select)
            $categorias = $pdo->query("SELECT * FROM categorias ORDER BY orden ASC")->fetchAll();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title><?php echo $titulo; ?></title>
    <link rel="stylesheet" href="css/admin.css">
    <style>
        .form-container { max-width: 600px; margin: 0 auto; background: white; padding: 30px; border-radius: 10px; box-shadow: 0 5px 15px rgba(0,0,0,0.1); }
        .form-group { margin-bottom: 15px; }
        .form-label { display: block; font-weight: bold; margin-bottom: 5px; color: #333; }
        .form-control { width: 100%; padding: 10px; border: 1px solid #ccc; border-radius: 5px; box-sizing: border-box; }
        .preview-img { width: 100px; height: 100px; object-fit: cover; border-radius: 5px; border: 1px solid #ddd; margin-top: 5px; }
        .row { display: flex; gap: 15px; }
        .col { flex: 1; }
    </style>
</head>
<body>

<div class="admin-layout">
    <nav class="sidebar">
        <h2>Mi Restaurante</h2>
        <a href="productos.php">⬅ Cancelar y Volver</a>
    </nav>

    <main class="main-content">
        <h1 style="text-align: center; margin-bottom: 30px;"><?php echo $titulo; ?></h1>

        <div class="form-container">
            <form method="POST" enctype="multipart/form-data">
                <input type="hidden" name="id" value="<?php echo $producto['id']; ?>">

                <div class="form-group">
                    <label class="form-label">Nombre del Platillo:</label>
                    <input type="text" name="nombre" class="form-control" 
                           value="<?php echo htmlspecialchars($producto['nombre']); ?>" required>
                </div>
                     <div class="form-group">
                        <label for="stock" class="form-label">Stock / Cantidad:</label>
                        <input type="number" 
                            class="form-control" 
                            id="stock" 
                            name="stock" 
                            value="<?php echo $producto['stock']; ?>" 
                            required>
                    </div>
                <div class="form-group">
                    <label class="form-label">Descripción:</label>
                    <textarea name="descripcion" class="form-control" rows="3"><?php echo htmlspecialchars($producto['descripcion']); ?></textarea>
                </div>

                <div class="row">
                    <div class="col form-group">
                        <label class="form-label">Precio ($):</label>
                        <input type="number" step="0.50" name="precio" class="form-control" 
                               value="<?php echo $producto['precio']; ?>" required>
                    </div>
                    <div class="col form-group">
                        <label class="form-label">Categoría:</label>
                        <select name="categoria_id" class="form-control">
                            <?php foreach($categorias as $cat): ?>
                                <option value="<?php echo $cat['id']; ?>" 
                                    <?php echo ($cat['id'] == $producto['categoria_id']) ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($cat['nombre']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label">Imagen:</label>
                    <?php if($producto['imagen']): ?>
                        <p style="font-size: 0.8rem; margin: 0;">Imagen actual:</p>
                        <img src="../uploads/productos/<?php echo $producto['imagen']; ?>" class="preview-img">
                        <br><br>
                    <?php endif; ?>
                    <input type="file" name="foto" accept="image/*">
                </div>

                <div class="form-group" style="background: #f8f9fa; padding: 15px; border-radius: 5px;">
                    <label style="margin-right: 20px;">
                        <input type="checkbox" name="activo" <?php echo ($producto['activo']) ? 'checked' : ''; ?>>
                        Visible en Menú 👁️
                    </label>
                    <label>
                        <input type="checkbox" name="destacado" <?php echo ($producto['destacado']) ? 'checked' : ''; ?>>
                        Destacado ⭐
                    </label>
                </div>

                <button type="submit" class="btn btn-main" style="width: 100%; padding: 15px; font-size: 1.1rem;">
                    💾 Guardar Cambios
                </button>
            </form>
        </div>
    </main>
</div>

</body>
</html>