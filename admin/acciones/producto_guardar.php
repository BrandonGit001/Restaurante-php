<?php
/* Archivo: admin/acciones/producto_guardar.php */
session_start();
require '../../config/db.php';       // Ojo a los ../.. porque estamos en subcarpeta
require '../../includes/funciones.php';
verificar_admin();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    
    // 1. Recibir datos del formulario
    $nombre = limpiar_str($_POST['nombre']);
    $descripcion = limpiar_str($_POST['descripcion']);
    $precio = (float) $_POST['precio'];
    $categoria_id = (int) $_POST['categoria_id'];
    
    // Checkbox: si no se marcan, no se envían en POST, así que asignamos 0
    $destacado = isset($_POST['destacado']) ? 1 : 0;
    $activo = isset($_POST['activo']) ? 1 : 0;

    $imagen_nombre = null; // Por defecto sin imagen

    // 2. Procesar Imagen (Si subieron una)
    if (isset($_FILES['foto']) && $_FILES['foto']['error'] === UPLOAD_ERR_OK) {
        // Usamos la función maestra que creamos
        $nombre_imagen = "";
if (isset($_FILES['foto']) && $_FILES['foto']['error'] === UPLOAD_ERR_OK) {
    $ext = pathinfo($_FILES['foto']['name'], PATHINFO_EXTENSION);
    $nombre_imagen = uniqid() . "." . $ext; // Genera nombre único
    
    // OJO: Como estás en la carpeta 'acciones', subimos 2 niveles (../../)
    move_uploaded_file($_FILES['foto']['tmp_name'], "../../uploads/productos/" . $nombre_imagen);
    
    $subida = $nombre_imagen; // Para que tu código de abajo siga funcionando
}
        if ($subida) {
            $imagen_nombre = $subida;
        } else {
            // Si falla (formato incorrecto), podrías redirigir con error
            // Por ahora lo dejamos pasar sin foto
        }
    }

    // 3. Insertar en Base de Datos
    try {
        $sql = "INSERT INTO productos (categoria_id, nombre, descripcion, precio, imagen, activo, destacado) 
                VALUES (:cat, :nom, :desc, :pre, :img, :act, :dest)";
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            ':cat' => $categoria_id,
            ':nom' => $nombre,
            ':desc' => $descripcion,
            ':pre' => $precio,
            ':img' => $imagen_nombre,
            ':act' => $activo,
            ':dest' => $destacado
        ]);

        // 4. Redirigir al listado con éxito
        header("Location: ../productos.php?mensaje=guardado");
        exit();

    } catch (PDOException $e) {
        die("Error al guardar: " . $e->getMessage());
    }

} else {
    // Si intentan entrar directo sin POST
    header("Location: ../productos.php");
    exit();
}