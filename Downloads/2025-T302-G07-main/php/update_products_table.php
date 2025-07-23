<?php
require_once 'config/database.php';

try {
    // Eliminar todos los productos actuales
    $pdo->exec("DELETE FROM products");
    echo "✅ Todos los productos anteriores han sido eliminados.\n";

    // Insertar los productos nuevos
    $sql = "INSERT INTO products (codigo_barra, name, category, price, stock_quantity, user_id, is_active) VALUES
        (1, 'MORCILLA ARTESANAL', 'Embutidos', 3.50, 20, 1, 1),
        (2, 'MORCILLA ARTESANAL C/PICANTE', 'Embutidos', 3.70, 18, 1, 1),
        (3, 'MORCILLA ARTESANAL DULCE PICANTE', 'Embutidos', 3.80, 15, 1, 1),
        (4, 'MEZCLA ARTESANAL DE CHORIZO CRIOLLO (PASTA)', 'Embutidos', 4.20, 12, 1, 1),
        (5, 'CHORIZO ARTESANAL AJO', 'Embutidos', 3.90, 25, 1, 1),
        (6, 'CHORIZO ARTESANAL AHUMADO', 'Embutidos', 4.10, 22, 1, 1),
        (7, 'CHORIZO ARTESANAL PICANTE', 'Embutidos', 4.00, 20, 1, 1),
        (8, 'HUESO COPA DE CERDO', 'Cortes de cerdo', 2.80, 30, 1, 1),
        (9, 'MORCILLA ARTESANAL', 'Embutidos', 3.50, 20, 1, 1),
        (10, 'MORCILLA ARTESANAL C/PICANTE', 'Embutidos', 3.70, 18, 1, 1),
        (11, 'MORCILLA ARTESANAL DULCE PICANTE', 'Embutidos', 3.80, 15, 1, 1),
        (12, 'MEZCLA ARTESANAL DE CHORIZO CRIOLLO (PASTA)', 'Embutidos', 4.20, 12, 1, 1),
        (13, 'CHORIZO ARTESANAL AJO', 'Embutidos', 3.90, 25, 1, 1)
    ";
    $pdo->exec($sql);
    echo "✅ Productos nuevos insertados correctamente.\n";
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}
?> 