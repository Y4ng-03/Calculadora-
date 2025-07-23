<?php
// Archivo de prueba para diagnosticar problemas en el checkout
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<!DOCTYPE html>";
echo "<html lang='es'>";
echo "<head>";
echo "<meta charset='UTF-8'>";
echo "<meta name='viewport' content='width=device-width, initial-scale=1.0'>";
echo "<title>Test Checkout - Discarchar</title>";
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
echo "<h1>🧪 Test de Checkout - Discarchar</h1>";

// 1. Verificar conexión a base de datos
echo "<div class='section'>";
echo "<h2>1. Conexión a Base de Datos</h2>";
try {
    require_once 'config/database.php';
    echo "<p class='success'>✅ Conexión a base de datos exitosa</p>";
} catch (Exception $e) {
    echo "<p class='error'>❌ Error de conexión: " . $e->getMessage() . "</p>";
    echo "</div></div></body></html>";
    exit;
}
echo "</div>";

// 2. Verificar tablas necesarias
echo "<div class='section'>";
echo "<h2>2. Verificación de Tablas</h2>";
$required_tables = ['users', 'products', 'categories', 'orders', 'order_items', 'cart'];
foreach ($required_tables as $table) {
    try {
        $stmt = $pdo->query("SELECT COUNT(*) as count FROM $table");
        $result = $stmt->fetch();
        echo "<p class='success'>✅ Tabla '$table': " . $result['count'] . " registros</p>";
    } catch (PDOException $e) {
        echo "<p class='error'>❌ Tabla '$table': NO EXISTE - " . $e->getMessage() . "</p>";
    }
}
echo "</div>";

// 3. Verificar funciones necesarias
echo "<div class='section'>";
echo "<h2>3. Verificación de Funciones</h2>";
try {
    require_once 'includes/functions.php';
    echo "<p class='success'>✅ Archivo functions.php cargado correctamente</p>";
    
    // Verificar funciones específicas
    if (function_exists('is_logged_in')) {
        echo "<p class='success'>✅ Función is_logged_in() disponible</p>";
    } else {
        echo "<p class='error'>❌ Función is_logged_in() NO disponible</p>";
    }
    
    if (function_exists('send_order_confirmation_email_gmail')) {
        echo "<p class='success'>✅ Función send_order_confirmation_email_gmail() disponible</p>";
    } else {
        echo "<p class='error'>❌ Función send_order_confirmation_email_gmail() NO disponible</p>";
    }
    
} catch (Exception $e) {
    echo "<p class='error'>❌ Error cargando functions.php: " . $e->getMessage() . "</p>";
}
echo "</div>";

// 4. Verificar PHPMailer
echo "<div class='section'>";
echo "<h2>4. Verificación de PHPMailer</h2>";
if (file_exists('vendor/autoload.php')) {
    echo "<p class='success'>✅ PHPMailer instalado via Composer</p>";
    try {
        require_once 'vendor/autoload.php';
        $mail = new PHPMailer\PHPMailer\PHPMailer(true);
        echo "<p class='success'>✅ PHPMailer cargado correctamente</p>";
    } catch (Exception $e) {
        echo "<p class='error'>❌ Error cargando PHPMailer: " . $e->getMessage() . "</p>";
    }
} else {
    echo "<p class='error'>❌ PHPMailer no encontrado</p>";
}
echo "</div>";

// 5. Simular proceso de checkout
echo "<div class='section'>";
echo "<h2>5. Simulación de Checkout</h2>";

// Simular datos de usuario
$test_user_id = 1;
$test_user_email = 'test@example.com';
$test_user_name = 'Usuario Test';

// Simular datos de orden
$test_order_data = [
    'id' => 999999,
    'user_id' => $test_user_id,
    'total_amount' => 150.00,
    'shipping_address' => 'Calle Test 123, Ciudad Test',
    'status' => 'pending',
    'billing_name' => $test_user_name,
    'billing_phone' => '1234567890',
    'billing_email' => $test_user_email,
    'billing_city' => 'Ciudad Test',
    'billing_postal_code' => '12345',
    'notes' => 'Nota de prueba',
    'created_at' => date('Y-m-d H:i:s')
];

$test_order_items = [
    [
        'product_id' => 1,
        'name' => 'Producto Test',
        'quantity' => 2,
        'price' => 75.00
    ]
];

echo "<p class='info'>📋 Datos de prueba preparados</p>";

// Probar inserción en base de datos
try {
    $pdo->beginTransaction();
    
    // Insertar orden de prueba
    $stmt = $pdo->prepare("
        INSERT INTO orders (user_id, total_amount, shipping_address, status, billing_name, billing_phone, billing_email, billing_city, billing_postal_code, notes) 
        VALUES (?, ?, ?, 'pending', ?, ?, ?, ?, ?, ?)
    ");
    $stmt->execute([
        $test_user_id, 
        $test_order_data['total_amount'], 
        $test_order_data['shipping_address'], 
        $test_order_data['billing_name'], 
        $test_order_data['billing_phone'], 
        $test_order_data['billing_email'], 
        $test_order_data['billing_city'], 
        $test_order_data['billing_postal_code'], 
        $test_order_data['notes']
    ]);
    $test_order_id = $pdo->lastInsertId();
    echo "<p class='success'>✅ Orden de prueba creada con ID: $test_order_id</p>";
    
    // Insertar items de la orden
    $stmt = $pdo->prepare("
        INSERT INTO order_items (order_id, product_id, quantity, price) 
        VALUES (?, ?, ?, ?)
    ");
    
    foreach ($test_order_items as $item) {
        $stmt->execute([$test_order_id, $item['product_id'], $item['quantity'], $item['price']]);
    }
    echo "<p class='success'>✅ Items de orden insertados correctamente</p>";
    
    $pdo->commit();
    echo "<p class='success'>✅ Transacción completada exitosamente</p>";
    
    // Limpiar datos de prueba
    $stmt = $pdo->prepare("DELETE FROM order_items WHERE order_id = ?");
    $stmt->execute([$test_order_id]);
    $stmt = $pdo->prepare("DELETE FROM orders WHERE id = ?");
    $stmt->execute([$test_order_id]);
    echo "<p class='info'>🧹 Datos de prueba limpiados</p>";
    
} catch (Exception $e) {
    $pdo->rollBack();
    echo "<p class='error'>❌ Error en transacción: " . $e->getMessage() . "</p>";
    echo "<pre>" . $e->getTraceAsString() . "</pre>";
}
echo "</div>";

// 6. Probar envío de email
echo "<div class='section'>";
echo "<h2>6. Prueba de Envío de Email</h2>";

try {
    // Usar datos de prueba
    $test_order_data['id'] = 999999;
    $result = send_order_confirmation_email_gmail($test_order_data, $test_order_items, $test_user_email, $test_user_name);
    
    if ($result) {
        echo "<p class='success'>✅ Email enviado correctamente</p>";
    } else {
        echo "<p class='error'>❌ Error enviando email</p>";
    }
} catch (Exception $e) {
    echo "<p class='error'>❌ Excepción enviando email: " . $e->getMessage() . "</p>";
    echo "<pre>" . $e->getTraceAsString() . "</pre>";
}
echo "</div>";

// 7. Verificar permisos de archivos
echo "<div class='section'>";
echo "<h2>7. Permisos de Archivos</h2>";
$directories = ['uploads', 'emails'];
foreach ($directories as $dir) {
    if (is_dir($dir)) {
        $writable = is_writable($dir) ? 'escribible' : 'no escribible';
        $class = is_writable($dir) ? 'success' : 'warning';
        echo "<p class='$class'>📁 $dir ($writable)</p>";
    } else {
        echo "<p class='error'>❌ $dir (NO EXISTE)</p>";
    }
}
echo "</div>";

// 8. Recomendaciones
echo "<div class='section info'>";
echo "<h2>💡 Recomendaciones</h2>";
echo "<ul>";
echo "<li>Si hay errores de base de datos, ejecuta el archivo discarchar.sql</li>";
echo "<li>Si hay errores de email, verifica la configuración de Gmail SMTP</li>";
echo "<li>Si hay errores de permisos, verifica los permisos de los directorios</li>";
echo "<li>Revisa el archivo email_log.txt para ver logs de envío de emails</li>";
echo "</ul>";
echo "</div>";

echo "</div>";
echo "</body>";
echo "</html>";
?> 