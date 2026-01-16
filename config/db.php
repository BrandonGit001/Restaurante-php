<?php
/*
* Archivo: config/db.php
* Objetivo: Conectar a la base de datos usando PDO
*/

$host = 'localhost';
$db   = 'restaurante_db';
$user = 'root';
$pass = ''; // En XAMPP suele ser vacía. Si usas MAMP es 'root'
$charset = 'utf8mb4';

$dsn = "mysql:host=$host;dbname=$db;charset=$charset";

$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION, // Lanza errores si algo falla
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,       // Devuelve arrays asociativos
    PDO::ATTR_EMULATE_PREPARES   => false,                  // Seguridad real para consultas preparadas
];

try {
    $pdo = new PDO($dsn, $user, $pass, $options);
    // Si quieres probar si conecta, descomenta la linea de abajo:
    // echo "¡Conexión exitosa a la base de datos!";
} catch (\PDOException $e) {
    // Si falla, mostramos el error (solo para desarrollo)
    throw new \PDOException($e->getMessage(), (int)$e->getCode());
}
?>