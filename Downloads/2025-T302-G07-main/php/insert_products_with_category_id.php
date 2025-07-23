<?php
require_once 'config/database.php';

// Definir productos a insertar
$productos = [
    [
        'nombre' => 'MORCILLA ARTESANAL',
        'categoria' => 'Embutidos',
        'precio' => 3.50,
        'cantidad' => 20
    ],
    [
        'nombre' => 'MORCILLA ARTESANAL C/PICANTE',
        'categoria' => 'Embutidos',
        'precio' => 3.70,
        'cantidad' => 18
    ],
    [
        'nombre' => 'MORCILLA ARTESANAL DULCE PICANTE',
        'categoria' => 'Embutidos',
        'precio' => 3.80,
        'cantidad' => 15
    ],
    [
        'nombre' => 'MEZCLA ARTESANAL DE CHORIZO CRIOLLO (PASTA)',
        'categoria' => 'Embutidos',
        'precio' => 4.20,
        'cantidad' => 12
    ],
    [
        'nombre' => 'CHORIZO ARTESANAL AJO',
        'categoria' => 'Embutidos',
        'precio' => 3.90,
        'cantidad' => 25
    ],
    [
        'nombre' => 'CHORIZO ARTESANAL AHUMADO',
        'categoria' => 'Embutidos',
        'precio' => 4.10,
        'cantidad' => 22
    ],
    [
        'nombre' => 'CHORIZO ARTESANAL PICANTE',
        'categoria' => 'Embutidos',
        'precio' => 4.00,
        'cantidad' => 20
    ],
    [
        'nombre' => 'HUESO COPA DE CERDO',
        'categoria' => 'Cortes de cerdo',
        'precio' => 2.80,
        'cantidad' => 30
    ],
];

// Borrar todos los productos existentes
$pdo->exec('DELETE FROM products');

$insertados = 0;
foreach ($productos as $p) {
    // Verificar si la categoría existe, si no, crearla
    $stmt = $pdo->prepare('SELECT id FROM categories WHERE name = ?');
    $stmt->execute([$p['categoria']]);
    $cat = $stmt->fetch();
    if (!$cat) {
        $insertCat = $pdo->prepare('INSERT INTO categories (name, description, is_active) VALUES (?, ?, 1)');
        $insertCat->execute([$p['categoria'], 'Categoría creada automáticamente']);
        $cat_id = $pdo->lastInsertId();
        echo "<p style='color:green'>Categoría creada: {$p['categoria']}</p>";
    } else {
        $cat_id = $cat['id'];
    }
    $stmt = $pdo->prepare('INSERT INTO products (name, description, price, stock_quantity, category_id, is_active) VALUES (?, ?, ?, ?, ?, 1)');
    $stmt->execute([
        $p['nombre'],
        '', // descripción vacía
        $p['precio'],
        $p['cantidad'],
        $cat_id
    ]);
    $insertados++;
}

echo "<h2>Productos actualizados correctamente</h2>";
echo "<p>Se insertaron $insertados productos.</p>";
?> 