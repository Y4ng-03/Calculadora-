<?php
require_once 'config/database.php';

echo "<h1>Diagnóstico Completo - Discarchar</h1>";
echo "<style>
    body { font-family: Arial, sans-serif; margin: 20px; }
    .success { color: green; }
    .error { color: red; }
    .warning { color: orange; }
    .info { color: blue; }
    .section { margin: 20px 0; padding: 15px; border: 1px solid #ddd; border-radius: 5px; }
    pre { background: #f5f5f5; padding: 10px; border-radius: 3px; overflow-x: auto; }
</style>";

// 1. Verificar conexión a la base de datos
echo "<div class='section'>";
echo "<h2>1. Verificación de Conexión a Base de Datos</h2>";
try {
    $pdo->query("SELECT 1");
    echo "<p class='success'>✓ Conexión a la base de datos exitosa</p>";
    echo "<p><strong>Host:</strong> " . $host . "</p>";
    echo "<p><strong>Base de datos:</strong> " . $dbname . "</p>";
    echo "<p><strong>Usuario:</strong> " . $username . "</p>";
} catch (Exception $e) {
    echo "<p class='error'>✗ Error de conexión: " . $e->getMessage() . "</p>";
    exit;
}
echo "</div>";

// 2. Verificar existencia de tablas
echo "<div class='section'>";
echo "<h2>2. Verificación de Tablas</h2>";
$required_tables = ['users', 'categories', 'products', 'cart', 'orders', 'order_items'];
$existing_tables = [];

try {
    $stmt = $pdo->query("SHOW TABLES");
    while ($row = $stmt->fetch()) {
        $existing_tables[] = $row[0];
    }
    
    foreach ($required_tables as $table) {
        if (in_array($table, $existing_tables)) {
            echo "<p class='success'>✓ Tabla '$table' existe</p>";
        } else {
            echo "<p class='error'>✗ Tabla '$table' NO existe</p>";
        }
    }
} catch (Exception $e) {
    echo "<p class='error'>Error al verificar tablas: " . $e->getMessage() . "</p>";
}
echo "</div>";

// 3. Verificar estructura de tabla users
echo "<div class='section'>";
echo "<h2>3. Estructura de Tabla Users</h2>";
try {
    if (in_array('users', $existing_tables)) {
        $stmt = $pdo->query("DESCRIBE users");
        echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
        echo "<tr><th>Campo</th><th>Tipo</th><th>Nulo</th><th>Llave</th><th>Default</th><th>Extra</th></tr>";
        while ($row = $stmt->fetch()) {
            echo "<tr>";
            echo "<td>" . $row['Field'] . "</td>";
            echo "<td>" . $row['Type'] . "</td>";
            echo "<td>" . $row['Null'] . "</td>";
            echo "<td>" . $row['Key'] . "</td>";
            echo "<td>" . $row['Default'] . "</td>";
            echo "<td>" . $row['Extra'] . "</td>";
            echo "</tr>";
        }
        echo "</table>";
    } else {
        echo "<p class='error'>La tabla users no existe</p>";
    }
} catch (Exception $e) {
    echo "<p class='error'>Error al verificar estructura: " . $e->getMessage() . "</p>";
}
echo "</div>";

// 4. Verificar estructura de tabla products
echo "<div class='section'>";
echo "<h2>4. Estructura de Tabla Products</h2>";
try {
    if (in_array('products', $existing_tables)) {
        $stmt = $pdo->query("DESCRIBE products");
        echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
        echo "<tr><th>Campo</th><th>Tipo</th><th>Nulo</th><th>Llave</th><th>Default</th><th>Extra</th></tr>";
        while ($row = $stmt->fetch()) {
            echo "<tr>";
            echo "<td>" . $row['Field'] . "</td>";
            echo "<td>" . $row['Type'] . "</td>";
            echo "<td>" . $row['Null'] . "</td>";
            echo "<td>" . $row['Key'] . "</td>";
            echo "<td>" . $row['Default'] . "</td>";
            echo "<td>" . $row['Extra'] . "</td>";
            echo "</tr>";
        }
        echo "</table>";
    } else {
        echo "<p class='error'>La tabla products no existe</p>";
    }
} catch (Exception $e) {
    echo "<p class='error'>Error al verificar estructura: " . $e->getMessage() . "</p>";
}
echo "</div>";

// 5. Contar registros en cada tabla
echo "<div class='section'>";
echo "<h2>5. Conteo de Registros</h2>";
foreach ($required_tables as $table) {
    try {
        if (in_array($table, $existing_tables)) {
            $stmt = $pdo->query("SELECT COUNT(*) as count FROM $table");
            $count = $stmt->fetch()['count'];
            echo "<p><strong>$table:</strong> $count registros</p>";
        } else {
            echo "<p class='error'><strong>$table:</strong> Tabla no existe</p>";
        }
    } catch (Exception $e) {
        echo "<p class='error'><strong>$table:</strong> Error al contar: " . $e->getMessage() . "</p>";
    }
}
echo "</div>";

// 6. Mostrar algunos usuarios de ejemplo
echo "<div class='section'>";
echo "<h2>6. Usuarios Registrados</h2>";
try {
    if (in_array('users', $existing_tables)) {
        $stmt = $pdo->query("SELECT id, username, email, role, created_at FROM users ORDER BY created_at DESC LIMIT 10");
        $users = $stmt->fetchAll();
        
        if (count($users) > 0) {
            echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
            echo "<tr><th>ID</th><th>Usuario</th><th>Email</th><th>Rol</th><th>Fecha Creación</th></tr>";
            foreach ($users as $user) {
                echo "<tr>";
                echo "<td>" . $user['id'] . "</td>";
                echo "<td>" . htmlspecialchars($user['username']) . "</td>";
                echo "<td>" . htmlspecialchars($user['email']) . "</td>";
                echo "<td>" . $user['role'] . "</td>";
                echo "<td>" . $user['created_at'] . "</td>";
                echo "</tr>";
            }
            echo "</table>";
        } else {
            echo "<p class='warning'>No hay usuarios registrados</p>";
        }
    } else {
        echo "<p class='error'>La tabla users no existe</p>";
    }
} catch (Exception $e) {
    echo "<p class='error'>Error al obtener usuarios: " . $e->getMessage() . "</p>";
}
echo "</div>";

// 7. Mostrar algunos productos de ejemplo
echo "<div class='section'>";
echo "<h2>7. Productos Disponibles</h2>";
try {
    if (in_array('products', $existing_tables)) {
        $stmt = $pdo->query("SELECT p.id, p.name, p.price, p.stock_quantity, c.name as category FROM products p LEFT JOIN categories c ON p.category_id = c.id WHERE p.is_active = 1 ORDER BY p.created_at DESC LIMIT 10");
        $products = $stmt->fetchAll();
        
        if (count($products) > 0) {
            echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
            echo "<tr><th>ID</th><th>Nombre</th><th>Precio</th><th>Stock</th><th>Categoría</th></tr>";
            foreach ($products as $product) {
                echo "<tr>";
                echo "<td>" . $product['id'] . "</td>";
                echo "<td>" . htmlspecialchars($product['name']) . "</td>";
                echo "<td>$" . number_format($product['price'], 2) . "</td>";
                echo "<td>" . $product['stock_quantity'] . "</td>";
                echo "<td>" . htmlspecialchars($product['category'] ?? 'Sin categoría') . "</td>";
                echo "</tr>";
            }
            echo "</table>";
        } else {
            echo "<p class='warning'>No hay productos disponibles</p>";
        }
    } else {
        echo "<p class='error'>La tabla products no existe</p>";
    }
} catch (Exception $e) {
    echo "<p class='error'>Error al obtener productos: " . $e->getMessage() . "</p>";
}
echo "</div>";

// 8. Botón para crear/actualizar base de datos
echo "<div class='section'>";
echo "<h2>8. Acciones de Sincronización</h2>";
echo "<p class='info'>Si hay problemas con las tablas o datos, puedes usar estos botones para sincronizar:</p>";
echo "<form method='post' style='margin: 10px 0;'>";
echo "<button type='submit' name='action' value='create_tables' style='background: #4CAF50; color: white; padding: 10px 20px; border: none; border-radius: 5px; margin: 5px; cursor: pointer;'>Crear/Actualizar Tablas</button>";
echo "<button type='submit' name='action' value='insert_sample_data' style='background: #2196F3; color: white; padding: 10px 20px; border: none; border-radius: 5px; margin: 5px; cursor: pointer;'>Insertar Datos de Ejemplo</button>";
echo "<button type='submit' name='action' value='clear_cache' style='background: #FF9800; color: white; padding: 10px 20px; border: none; border-radius: 5px; margin: 5px; cursor: pointer;'>Limpiar Caché</button>";
echo "</form>";
echo "</div>";

// Procesar acciones
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    switch ($action) {
        case 'create_tables':
            echo "<div class='section'>";
            echo "<h3>Creando/Actualizando Tablas...</h3>";
            
            // Leer y ejecutar el archivo SQL
            $sql_file = file_get_contents('discarchar.sql');
            $statements = explode(';', $sql_file);
            
            foreach ($statements as $statement) {
                $statement = trim($statement);
                if (!empty($statement)) {
                    try {
                        $pdo->exec($statement);
                        echo "<p class='success'>✓ Ejecutado: " . substr($statement, 0, 50) . "...</p>";
                    } catch (Exception $e) {
                        echo "<p class='warning'>⚠ " . $e->getMessage() . "</p>";
                    }
                }
            }
            echo "<p class='success'>✓ Proceso completado. Recarga la página para ver los cambios.</p>";
            echo "</div>";
            break;
            
        case 'insert_sample_data':
            echo "<div class='section'>";
            echo "<h3>Insertando Datos de Ejemplo...</h3>";
            
            // Insertar categorías si no existen
            try {
                $stmt = $pdo->prepare("INSERT IGNORE INTO categories (name, description) VALUES (?, ?)");
                $categories = [
                    ['Electrónicos', 'Productos electrónicos y tecnología'],
                    ['Ropa', 'Vestimenta y accesorios'],
                    ['Hogar', 'Artículos para el hogar'],
                    ['Deportes', 'Equipamiento deportivo']
                ];
                
                foreach ($categories as $cat) {
                    $stmt->execute($cat);
                }
                echo "<p class='success'>✓ Categorías insertadas</p>";
            } catch (Exception $e) {
                echo "<p class='warning'>⚠ Error con categorías: " . $e->getMessage() . "</p>";
            }
            
            // Insertar productos de ejemplo
            try {
                $stmt = $pdo->prepare("INSERT IGNORE INTO products (name, description, price, stock_quantity, category_id) VALUES (?, ?, ?, ?, ?)");
                $products = [
                    ['Laptop HP Pavilion', 'Laptop de 15 pulgadas con procesador Intel i5', 899.99, 10, 1],
                    ['Smartphone Samsung Galaxy', 'Teléfono inteligente con cámara de 48MP', 599.99, 15, 1],
                    ['Camiseta Básica', 'Camiseta de algodón 100% en varios colores', 19.99, 50, 2],
                    ['Pantalón Jeans', 'Jeans clásicos de alta calidad', 49.99, 30, 2],
                    ['Lámpara de Mesa', 'Lámpara LED moderna para escritorio', 29.99, 20, 3],
                    ['Pelota de Fútbol', 'Pelota oficial de competición, tamaño 5', 39.99, 25, 4]
                ];
                
                foreach ($products as $prod) {
                    $stmt->execute($prod);
                }
                echo "<p class='success'>✓ Productos de ejemplo insertados</p>";
            } catch (Exception $e) {
                echo "<p class='warning'>⚠ Error con productos: " . $e->getMessage() . "</p>";
            }
            
            echo "<p class='success'>✓ Datos de ejemplo insertados. Recarga la página para ver los cambios.</p>";
            echo "</div>";
            break;
            
        case 'clear_cache':
            echo "<div class='section'>";
            echo "<h3>Limpiando Caché...</h3>";
            
            // Limpiar archivos de caché
            $cache_files = ['bcv_rate_cache.json', 'php_errors.log'];
            foreach ($cache_files as $file) {
                if (file_exists($file)) {
                    unlink($file);
                    echo "<p class='success'>✓ Archivo $file eliminado</p>";
                }
            }
            
            echo "<p class='success'>✓ Caché limpiado</p>";
            echo "</div>";
            break;
    }
}

echo "<div class='section'>";
echo "<h2>9. Información del Sistema</h2>";
echo "<p><strong>PHP Version:</strong> " . phpversion() . "</p>";
echo "<p><strong>Servidor:</strong> " . $_SERVER['SERVER_SOFTWARE'] . "</p>";
echo "<p><strong>Directorio:</strong> " . __DIR__ . "</p>";
echo "<p><strong>Fecha/Hora:</strong> " . date('Y-m-d H:i:s') . "</p>";
echo "</div>";

echo "<div class='section'>";
echo "<h2>10. Recomendaciones</h2>";
echo "<ul>";
echo "<li>Si las tablas no existen, usa el botón 'Crear/Actualizar Tablas'</li>";
echo "<li>Si no hay productos, usa el botón 'Insertar Datos de Ejemplo'</li>";
echo "<li>Asegúrate de que ambos compañeros usen la misma configuración de base de datos</li>";
echo "<li>Verifica que el archivo config/database.php tenga los mismos valores en ambos entornos</li>";
echo "<li>Si hay problemas de caché, usa el botón 'Limpiar Caché'</li>";
echo "</ul>";
echo "</div>";
?> 