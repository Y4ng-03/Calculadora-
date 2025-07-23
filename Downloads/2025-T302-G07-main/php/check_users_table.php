<?php
require_once 'config/database.php';

echo "Verificando estructura de la tabla users...\n\n";

try {
    $stmt = $pdo->query("DESCRIBE users");
    echo "Campos de la tabla users:\n";
    while ($row = $stmt->fetch()) {
        echo "- " . $row['Field'] . " (" . $row['Type'] . ")\n";
    }
    
    echo "\nEjemplo de usuario:\n";
    $stmt = $pdo->query("SELECT * FROM users LIMIT 1");
    $user = $stmt->fetch();
    if ($user) {
        print_r($user);
    } else {
        echo "No hay usuarios en la tabla.\n";
    }
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?> 