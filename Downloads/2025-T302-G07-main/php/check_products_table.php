<?php
require_once 'config/database.php';

echo "Estructura de la tabla products:\n\n";
$stmt = $pdo->query("DESCRIBE products");
while ($row = $stmt->fetch()) {
    echo "- " . $row['Field'] . " (" . $row['Type'] . ")\n";
}

echo "\nEjemplo de producto:\n";
$stmt = $pdo->query("SELECT * FROM products LIMIT 1");
$prod = $stmt->fetch();
if ($prod) {
    print_r($prod);
} else {
    echo "No hay productos.\n";
}
?> 