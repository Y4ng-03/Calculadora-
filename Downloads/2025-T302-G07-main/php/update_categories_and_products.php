<?php
require_once 'config/database.php';

// 1. Crear categorías si no existen
$categorias = [
    'Embutidos',
    'Cortes de cerdo'
];
$category_ids = [];

foreach ($categorias as $cat) {
    // Verificar si ya existe
    $stmt = $pdo->prepare("SELECT id FROM categories WHERE name = ?");
    $stmt->execute([$cat]);
    $row = $stmt->fetch();
    if ($row) {
        $category_ids[$cat] = $row['id'];
    } else {
        $stmt = $pdo->prepare("INSERT INTO categories (name, is_active) VALUES (?, 1)");
        $stmt->execute([$cat]);
        $category_ids[$cat] = $pdo->lastInsertId();
    }
}

echo "IDs de categorías:\n";
foreach ($category_ids as $cat => $id) {
    echo "- $cat: $id\n";
}

// 2. Actualizar productos con el category_id correcto
$updates = [
    ['Embutidos', $category_ids['Embutidos']],
    ['Cortes de cerdo', $category_ids['Cortes de cerdo']]
];
foreach ($updates as [$cat, $cat_id]) {
    $stmt = $pdo->prepare("UPDATE products SET category_id = ? WHERE category = ?");
    $stmt->execute([$cat_id, $cat]);
}

echo "\n✅ Productos actualizados con category_id correcto.\n";

// 3. (Opcional) Eliminar columna 'category' si ya no se usará
// $pdo->exec("ALTER TABLE products DROP COLUMN category");
// echo "Columna 'category' eliminada de products.\n";
?> 