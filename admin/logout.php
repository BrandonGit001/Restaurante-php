<?php
session_start();
session_destroy(); // Destruye todas las variables de sesión
header("Location: index.php"); // Te regresa al login
exit();
?>