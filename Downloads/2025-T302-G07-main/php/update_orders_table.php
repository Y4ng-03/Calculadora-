<?php
// Archivo para actualizar la estructura de la tabla orders
require_once 'config/database.php';

echo "<!DOCTYPE html>";
echo "<html lang='es'>";
echo "<head>";
echo "<meta charset='UTF-8'>";
echo "<meta name='viewport' content='width=device-width, initial-scale=1.0'>";
echo "<title>Actualizar Tabla Orders - Discarchar</title>";
echo "<style>";
echo "body { font-family: Arial, sans-serif; margin: 20px; background: #f5f5f5; }";
echo ".container { max-width: 800px; margin: 0 auto; background: white; padding: 20px; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }";
echo ".section { margin-bottom: 30px; padding: 15px; border: 1px solid #ddd; border-radius: 5px; }";
echo ".success { background: #d4edda; border-color: #c3e6cb; color: #155724; }";
echo ".error { background: #f8d7da; border-color: #f5c6cb; color: #721c24; }";
echo ".warning { background: #fff3cd; border-color: #ffeaa7; color: #856404; }";
echo ".info { background: #d1ecf1; border-color: #bee5eb; color: #0c5460; }";
echo "h1, h2 { color: #333; }";
echo "pre { background: #f8f9fa; padding: 10px; border-radius: 5px; overflow-x: auto; }";
echo "</style>";
echo "</head>";
echo "<body>";

echo "<div class='container'>";
echo "<h1>🔧 Actualizar Tabla Orders - Discarchar</h1>";

// Verificar si los campos de facturación ya existen
echo "<div class='section'>";
echo "<h2>1. Verificar Estructura Actual</h2>";

try {
    $stmt = $pdo->query("DESCRIBE orders");
    $columns = $stmt->fetchAll();
    
    echo "<p>Columnas actuales en la tabla orders:</p>";
    echo "<ul>";
    foreach ($columns as $column) {
        echo "<li><strong>" . $column['Field'] . "</strong> - " . $column['Type'] . "</li>";
    }
    echo "</ul>";
    
    // Verificar si faltan campos de facturación
    $required_fields = ['billing_name', 'billing_phone', 'billing_email', 'billing_city', 'billing_postal_code', 'notes'];
    $existing_fields = array_column($columns, 'Field');
    $missing_fields = array_diff($required_fields, $existing_fields);
    
    if (empty($missing_fields)) {
        echo "<p class='success'>✅ Todos los campos de facturación ya existen</p>";
    } else {
        echo "<p class='warning'>⚠️ Faltan los siguientes campos: " . implode(', ', $missing_fields) . "</p>";
    }
    
} catch (Exception $e) {
    echo "<p class='error'>❌ Error verificando estructura: " . $e->getMessage() . "</p>";
}
echo "</div>";

// Agregar campos faltantes
echo "<div class='section'>";
echo "<h2>2. Agregar Campos Faltantes</h2>";

$alter_queries = [
    "ALTER TABLE orders ADD COLUMN IF NOT EXISTS billing_name VARCHAR(100) AFTER shipping_address",
    "ALTER TABLE orders ADD COLUMN IF NOT EXISTS billing_phone VARCHAR(20) AFTER billing_name",
    "ALTER TABLE orders ADD COLUMN IF NOT EXISTS billing_email VARCHAR(100) AFTER billing_phone",
    "ALTER TABLE orders ADD COLUMN IF NOT EXISTS billing_city VARCHAR(100) AFTER billing_email",
    "ALTER TABLE orders ADD COLUMN IF NOT EXISTS billing_postal_code VARCHAR(10) AFTER billing_city",
    "ALTER TABLE orders ADD COLUMN IF NOT EXISTS notes TEXT AFTER billing_postal_code"
];

foreach ($alter_queries as $query) {
    try {
        $pdo->exec($query);
        echo "<p class='success'>✅ Ejecutado: " . htmlspecialchars($query) . "</p>";
    } catch (Exception $e) {
        echo "<p class='error'>❌ Error ejecutando: " . htmlspecialchars($query) . "</p>";
        echo "<p class='error'>Error: " . $e->getMessage() . "</p>";
    }
}
echo "</div>";

// Verificar estructura final
echo "<div class='section'>";
echo "<h2>3. Verificar Estructura Final</h2>";

try {
    $stmt = $pdo->query("DESCRIBE orders");
    $columns = $stmt->fetchAll();
    
    echo "<p>Estructura final de la tabla orders:</p>";
    echo "<ul>";
    foreach ($columns as $column) {
        echo "<li><strong>" . $column['Field'] . "</strong> - " . $column['Type'] . "</li>";
    }
    echo "</ul>";
    
    echo "<p class='success'>✅ Tabla orders actualizada correctamente</p>";
    
} catch (Exception $e) {
    echo "<p class='error'>❌ Error verificando estructura final: " . $e->getMessage() . "</p>";
}
echo "</div>";

// Probar inserción de datos
echo "<div class='section'>";
echo "<h2>4. Probar Inserción de Datos</h2>";

try {
    $test_data = [
        'user_id' => 1,
        'total_amount' => 100.00,
        'shipping_address' => 'Calle Test 123',
        'billing_name' => 'Usuario Test',
        'billing_phone' => '1234567890',
        'billing_email' => 'test@example.com',
        'billing_city' => 'Ciudad Test',
        'billing_postal_code' => '12345',
        'notes' => 'Nota de prueba'
    ];
    
    $stmt = $pdo->prepare("
        INSERT INTO orders (user_id, total_amount, shipping_address, billing_name, billing_phone, billing_email, billing_city, billing_postal_code, notes) 
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
    ");
    
    $stmt->execute([
        $test_data['user_id'],
        $test_data['total_amount'],
        $test_data['shipping_address'],
        $test_data['billing_name'],
        $test_data['billing_phone'],
        $test_data['billing_email'],
        $test_data['billing_city'],
        $test_data['billing_postal_code'],
        $test_data['notes']
    ]);
    
    $test_order_id = $pdo->lastInsertId();
    echo "<p class='success'>✅ Inserción de prueba exitosa. ID: $test_order_id</p>";
    
    // Limpiar datos de prueba
    $stmt = $pdo->prepare("DELETE FROM orders WHERE id = ?");
    $stmt->execute([$test_order_id]);
    echo "<p class='info'>🧹 Datos de prueba limpiados</p>";
    
} catch (Exception $e) {
    echo "<p class='error'>❌ Error en inserción de prueba: " . $e->getMessage() . "</p>";
}
echo "</div>";

// Recomendaciones
echo "<div class='section info'>";
echo "<h2>💡 Próximos Pasos</h2>";
echo "<ul>";
echo "<li>La tabla orders ha sido actualizada con los campos de facturación</li>";
echo "<li>Ahora puedes probar el checkout nuevamente</li>";
echo "<li>Si sigues teniendo problemas, ejecuta el archivo test_checkout.php</li>";
echo "<li>Revisa el archivo email_log.txt para ver logs de envío de emails</li>";
echo "</ul>";
echo "</div>";

echo "</div>";
echo "</body>";
echo "</html>";
?> 