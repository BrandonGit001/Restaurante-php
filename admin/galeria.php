<?php
/* Archivo: admin/galeria.php */
session_start();
require '../config/db.php';
require '../includes/funciones.php';
verificar_admin();

$mensaje = "";

// 1. SUBIR IMAGEN
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_FILES['foto'])) {
    $carpeta = "../uploads/galeria/";
    if (!file_exists($carpeta)) mkdir($carpeta, 0777, true); // Crear carpeta si no existe

    $nombre_archivo = uniqid() . "_" . $_FILES['foto']['name'];
    $ruta_destino = $carpeta . $nombre_archivo;

    if (move_uploaded_file($_FILES['foto']['tmp_name'], $ruta_destino)) {
        $desc = limpiar_str($_POST['descripcion']);
        $pdo->prepare("INSERT INTO galeria (imagen, descripcion) VALUES (?, ?)")
            ->execute([$nombre_archivo, $desc]);
        $mensaje = "✅ Foto subida con éxito.";
    } else {
        $mensaje = "❌ Error al subir la imagen.";
    }
}

// 2. BORRAR IMAGEN
if (isset($_GET['borrar'])) {
    $id = (int)$_GET['borrar'];
    // Primero obtenemos el nombre para borrar el archivo físico
    $stmt = $pdo->prepare("SELECT imagen FROM galeria WHERE id = ?");
    $stmt->execute([$id]);
    $foto = $stmt->fetch();

    if ($foto) {
        unlink("../uploads/galeria/" . $foto['imagen']); // Borrar archivo
        $pdo->prepare("DELETE FROM galeria WHERE id = ?")->execute([$id]); // Borrar de BD
        header("Location: galeria.php");
        exit();
    }
}

// 3. OBTENER GALERÍA
$fotos = $pdo->query("SELECT * FROM galeria ORDER BY id DESC")->fetchAll();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Galería - Admin</title>
    <link rel="stylesheet" href="css/admin.css">
</head>
<body>
<div class="admin-layout">
    <nav class="sidebar">
        <h2>Mi Restaurante</h2>
            <a href="dashboard.php">📊 Resumen</a>
        <a href="pedidos.php">🛎️ Pedidos</a>
        <a href="reportes.php">📈 Reportes</a> 
        <a href="productos.php">🍔 Productos</a>
        <a href="categorias.php">📂 Categorías</a>
        <a href="galeria.php" class="active">📸 Galería</a>
        <a href="resenas.php">⭐ Reseñas</a>
        <a href="configuracion.php">⚙️ Configuración</a>
        <a href="logout.php" class="salir">Cerrar Sesión</a>
    </nav>

    <main class="main-content">
        <h1>Galería de Imágenes 📸</h1>
        
        <div style="background:white; padding:20px; border-radius:10px; margin-bottom:30px; box-shadow:0 4px 10px rgba(0,0,0,0.05);">
            <h3>Subir Nueva Foto</h3>
            <?php if($mensaje): ?><p style="color:green; font-weight:bold;"><?php echo $mensaje; ?></p><?php endif; ?>
            
            <form method="POST" enctype="multipart/form-data" style="display:flex; gap:10px; align-items:flex-end;">
                <div>
                    <label>Seleccionar Foto:</label><br>
                    <input type="file" name="foto" required accept="image/*">
                </div>
                <div style="flex-grow:1;">
                    <label>Descripción (Opcional):</label><br>
                    <input type="text" name="descripcion" class="form-control" placeholder="Ej: Clientes felices comiendo">
                </div>
                <button type="submit" class="btn btn-main">Subir ⬆️</button>
            </form>
        </div>

        <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(150px, 1fr)); gap: 15px;">
            <?php foreach($fotos as $f): ?>
                <div style="background:white; padding:5px; border-radius:8px; box-shadow:0 2px 5px rgba(0,0,0,0.1); text-align:center;">
                    <img src="../uploads/galeria/<?php echo $f['imagen']; ?>" style="width:100%; height:120px; object-fit:cover; border-radius:5px;">
                    <p style="font-size:0.8rem; margin:5px 0; color:#555;"><?php echo $f['descripcion']; ?></p>
                    <a href="galeria.php?borrar=<?php echo $f['id']; ?>" onclick="return confirm('¿Borrar foto?')" style="color:red; text-decoration:none; font-size:0.8rem;">🗑️ Eliminar</a>
                </div>
            <?php endforeach; ?>
        </div>
    </main>
</div>
</body>
</html>