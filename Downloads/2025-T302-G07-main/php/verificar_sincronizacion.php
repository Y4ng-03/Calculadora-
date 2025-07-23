<?php
// Configuración de zona horaria de Venezuela
date_default_timezone_set('America/Caracas');

require_once 'config/database.php';

echo "<h1>Verificación Rápida de Sincronización</h1>";
echo "<style>
    body { font-family: Arial, sans-serif; margin: 20px; }
    .success { color: green; font-weight: bold; }
    .error { color: red; font-weight: bold; }
    .warning { color: orange; font-weight: bold; }
    .info { color: blue; }
    .section { margin: 15px 0; padding: 10px; border: 1px solid #ddd; border-radius: 5px; }
    .summary { background: #f0f8ff; padding: 15px; border-radius: 5px; margin: 20px 0; }
    .count { font-size: 24px; font-weight: bold; color: #2196F3; }
</style>";

$checks = [];
$total_checks = 0;
$passed_checks = 0;

// 1. Verificar conexión
$total_checks++;
try {
    $pdo->query("SELECT 1");
    $checks[] = ['Conexión a BD', 'success', '✓ Conectado correctamente'];
    $passed_checks++;
} catch (Exception $e) {
    $checks[] = ['Conexión a BD', 'error', '✗ Error: ' . $e->getMessage()];
}

// 2. Verificar tablas
$required_tables = ['users', 'categories', 'products', 'cart', 'orders', 'order_items'];
$total_checks += count($required_tables);

try {
    $stmt = $pdo->query("SHOW TABLES");
    $existing_tables = [];
    while ($row = $stmt->fetch()) {
        // Obtener el primer valor del array, sin importar la clave
        $existing_tables[] = reset($row);
    }
    
    foreach ($required_tables as $table) {
        if (in_array($table, $existing_tables)) {
            $checks[] = ["Tabla $table", 'success', '✓ Existe'];
            $passed_checks++;
        } else {
            $checks[] = ["Tabla $table", 'error', '✗ No existe'];
        }
    }
} catch (Exception $e) {
    foreach ($required_tables as $table) {
        $checks[] = ["Tabla $table", 'error', '✗ Error: ' . $e->getMessage()];
    }
}

// 3. Contar registros
$total_checks += 3;

try {
    // Contar usuarios
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM users");
    $user_count = $stmt->fetch()['count'];
    $checks[] = ['Usuarios registrados', 'success', "✓ $user_count usuarios"];
    $passed_checks++;
    
    // Contar productos
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM products WHERE is_active = 1");
    $product_count = $stmt->fetch()['count'];
    $checks[] = ['Productos activos', 'success', "✓ $product_count productos"];
    $passed_checks++;
    
    // Contar categorías
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM categories WHERE is_active = 1");
    $category_count = $stmt->fetch()['count'];
    $checks[] = ['Categorías activas', 'success', "✓ $category_count categorías"];
    $passed_checks++;
    
} catch (Exception $e) {
    $checks[] = ['Conteo de registros', 'error', '✗ Error: ' . $e->getMessage()];
}

// 4. Verificar usuario admin
$total_checks++;
try {
    $stmt = $pdo->prepare("SELECT id, username, role FROM users WHERE username = 'admin'");
    $stmt->execute();
    $admin = $stmt->fetch();
    
    if ($admin) {
        $checks[] = ['Usuario admin', 'success', "✓ Admin existe (ID: {$admin['id']}, Rol: {$admin['role']})"];
        $passed_checks++;
    } else {
        $checks[] = ['Usuario admin', 'warning', '⚠ Admin no existe'];
    }
} catch (Exception $e) {
    $checks[] = ['Usuario admin', 'error', '✗ Error: ' . $e->getMessage()];
}

// Mostrar resumen
echo "<div class='summary'>";
echo "<h2>Resumen de Verificación</h2>";
echo "<p><span class='count'>$passed_checks/$total_checks</span> verificaciones exitosas</p>";

$percentage = round(($passed_checks / $total_checks) * 100, 1);
if ($percentage >= 90) {
    echo "<p class='success'>✅ Sincronización EXITOSA ($percentage%)</p>";
} elseif ($percentage >= 70) {
    echo "<p class='warning'>⚠ Sincronización PARCIAL ($percentage%) - Revisar problemas</p>";
} else {
    echo "<p class='error'>❌ Sincronización FALLIDA ($percentage%) - Necesita corrección</p>";
}

echo "<p><strong>Información del sistema:</strong></p>";
echo "<ul>";
echo "<li><strong>Base de datos:</strong> $dbname</li>";
echo "<li><strong>Host:</strong> $host</li>";
echo "<li><strong>Usuario:</strong> $username</li>";
echo "<li><strong>Fecha:</strong> " . date('Y-m-d H:i:s') . "</li>";
echo "</ul>";
echo "</div>";

// Mostrar detalles de cada verificación
echo "<div class='section'>";
echo "<h3>Detalles de Verificaciones</h3>";
echo "<table style='width: 100%; border-collapse: collapse;'>";
echo "<tr style='background: #f5f5f5;'>";
echo "<th style='padding: 8px; border: 1px solid #ddd; text-align: left;'>Verificación</th>";
echo "<th style='padding: 8px; border: 1px solid #ddd; text-align: left;'>Estado</th>";
echo "<th style='padding: 8px; border: 1px solid #ddd; text-align: left;'>Detalles</th>";
echo "</tr>";

foreach ($checks as $check) {
    $status_class = $check[1];
    echo "<tr>";
    echo "<td style='padding: 8px; border: 1px solid #ddd;'><strong>{$check[0]}</strong></td>";
    echo "<td style='padding: 8px; border: 1px solid #ddd;'><span class='$status_class'>{$check[2]}</span></td>";
    echo "<td style='padding: 8px; border: 1px solid #ddd;'>";
    
    if ($check[1] === 'error') {
        echo "<span class='error'>Requiere atención inmediata</span>";
    } elseif ($check[1] === 'warning') {
        echo "<span class='warning'>Revisar si es necesario</span>";
    } else {
        echo "<span class='success'>Correcto</span>";
    }
    
    echo "</td>";
    echo "</tr>";
}
echo "</table>";
echo "</div>";

// Mostrar productos de ejemplo
echo "<div class='section'>";
echo "<h3>Productos Disponibles (Primeros 5)</h3>";
try {
    $stmt = $pdo->query("SELECT p.name, p.price, c.name as category FROM products p LEFT JOIN categories c ON p.category_id = c.id WHERE p.is_active = 1 ORDER BY p.id LIMIT 5");
    $products = $stmt->fetchAll();
    
    if (count($products) > 0) {
        echo "<table style='width: 100%; border-collapse: collapse;'>";
        echo "<tr style='background: #f5f5f5;'>";
        echo "<th style='padding: 8px; border: 1px solid #ddd; text-align: left;'>Producto</th>";
        echo "<th style='padding: 8px; border: 1px solid #ddd; text-align: left;'>Precio</th>";
        echo "<th style='padding: 8px; border: 1px solid #ddd; text-align: left;'>Categoría</th>";
        echo "</tr>";
        
        foreach ($products as $product) {
            echo "<tr>";
            echo "<td style='padding: 8px; border: 1px solid #ddd;'>" . htmlspecialchars($product['name']) . "</td>";
            echo "<td style='padding: 8px; border: 1px solid #ddd;'>$" . number_format($product['price'], 2) . "</td>";
            echo "<td style='padding: 8px; border: 1px solid #ddd;'>" . htmlspecialchars($product['category'] ?? 'Sin categoría') . "</td>";
            echo "</tr>";
        }
        echo "</table>";
    } else {
        echo "<p class='warning'>No hay productos disponibles</p>";
    }
} catch (Exception $e) {
    echo "<p class='error'>Error al obtener productos: " . $e->getMessage() . "</p>";
}
echo "</div>";

// Acciones recomendadas
echo "<div class='section'>";
echo "<h3>Acciones Recomendadas</h3>";

if ($percentage < 90) {
    echo "<div class='warning'>";
    echo "<p><strong>⚠ Problemas detectados. Recomendaciones:</strong></p>";
    echo "<ul>";
    if ($percentage < 70) {
        echo "<li>Ejecutar <a href='diagnostico_completo.php' target='_blank'>diagnóstico completo</a></li>";
        echo "<li>Usar 'Crear/Actualizar Tablas' en el diagnóstico</li>";
        echo "<li>Usar 'Insertar Datos de Ejemplo' en el diagnóstico</li>";
    }
    if ($product_count == 0) {
        echo "<li>No hay productos - Insertar datos de ejemplo</li>";
    }
    if ($user_count == 0) {
        echo "<li>No hay usuarios - Crear usuario admin</li>";
    }
    echo "<li>Verificar configuración de base de datos</li>";
    echo "<li>Limpiar caché si es necesario</li>";
    echo "</ul>";
    echo "</div>";
} else {
    echo "<div class='success'>";
    echo "<p><strong>✅ Todo está correcto. Tu entorno está sincronizado.</strong></p>";
    echo "<p>Puedes proceder a usar la aplicación normalmente.</p>";
    echo "</div>";
}

echo "</div>";

// Enlaces útiles
echo "<div class='section'>";
echo "<h3>Enlaces Útiles</h3>";
echo "<ul>";
echo "<li><a href='diagnostico_completo.php' target='_blank'>🔧 Diagnóstico Completo</a></li>";
echo "<li><a href='shop.php' target='_blank'>🛍️ Tienda</a></li>";
echo "<li><a href='register.php' target='_blank'>👤 Registro</a></li>";
echo "<li><a href='login.php' target='_blank'>🔐 Login</a></li>";
echo "<li><a href='INSTRUCCIONES_SINCRONIZACION.md' target='_blank'>📖 Instrucciones Completas</a></li>";
echo "</ul>";
echo "</div>";

// Información para compartir
echo "<div class='section'>";
echo "<h3>Información para Compartir con Compañeros</h3>";
echo "<p><strong>Copia esta información para comparar con tu compañero:</strong></p>";
echo "<div style='background: #f9f9f9; padding: 10px; border-radius: 5px; font-family: monospace;'>";
echo "📊 RESUMEN DE VERIFICACIÓN<br>";
echo "✅ Verificaciones exitosas: $passed_checks/$total_checks ($percentage%)<br>";
echo "👥 Usuarios: $user_count<br>";
echo "🛍️ Productos: $product_count<br>";
echo "📂 Categorías: $category_count<br>";
echo "🖥️ Sistema: " . (isset($_SERVER['SERVER_SOFTWARE']) ? $_SERVER['SERVER_SOFTWARE'] : 'CLI') . "<br>";
echo "📅 Fecha: " . date('Y-m-d H:i:s') . "<br>";
echo "</div>";
echo "</div>";
?> 