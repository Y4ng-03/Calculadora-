<?php
// Configuración de zona horaria de Venezuela
date_default_timezone_set('America/Caracas');

// Configuración de la base de datos
$host = 'localhost';        // Tu servidor MySQL
$dbname = 'discarchar';     // Nombre de la base de datos
$username = 'Yan';         // Usuario de MySQL
$password = '7912$Ale';    // Contraseña de MySQL

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
} catch(PDOException $e) {
    die("Error de conexión: " . $e->getMessage());
}
?> 