<?php
/* Archivo: admin/acciones/categoria_eliminar.php */
session_start();
require '../../config/db.php';
require '../../includes/funciones.php';
verificar_admin();

if (isset($_GET['id'])) {
    $id = (int) $_GET['id'];
    
    try {
        $stmt = $pdo->prepare("DELETE FROM categorias WHERE id = ?");
        $stmt->execute([$id]);
        
        // Redirigir
        header("Location: ../categorias.php?mensaje=eliminado");
        exit();

    } catch (PDOException $e) {
        die("Error al eliminar: " . $e->getMessage());
    }
} else {
    header("Location: ../categorias.php");
}
?>