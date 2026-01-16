<?php
/* Archivo: includes/funciones.php (LIMPIO) */

function limpiar_str($str) {
    return htmlspecialchars(trim($str));
}

function verificar_admin() {
    if (!isset($_SESSION['admin_id'])) {
        header("Location: ../login.php");
        exit();
    }
}
?>