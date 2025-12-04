<?php
// Configuración de la base de datos
$host = 'localhost';
$usuario = 'root';
$contrasena = '123';
$base_de_datos = 'mundimotos';

// Intentar la conexión
try {
    $conexion = new PDO("mysql:host=$host;dbname=$base_de_datos;charset=utf8", $usuario, $contrasena);
    // Establecer el modo de error de PDO a excepción
    $conexion->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    // echo "Conexión exitosa"; // Descomentar para probar
} catch (PDOException $e) {
    die("Error de conexión: " . $e->getMessage());
}
?>