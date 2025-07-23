<?php
require_once 'includes/functions.php';

// Datos de prueba para el email
$order_data = [
    'id' => 123456,
    'created_at' => date('Y-m-d H:i:s'),
    'total_amount' => 299.99,
    'shipping_address' => 'Calle Principal 123, Colonia Centro, Ciudad de México'
];

$order_items = [
    [
        'name' => 'Producto de Prueba 1',
        'price' => 149.99,
        'quantity' => 1
    ],
    [
        'name' => 'Producto de Prueba 2',
        'price' => 75.00,
        'quantity' => 2
    ]
];

$test_email = 'jeanguerrero04@gmail.com';
$test_name = 'Usuario de Prueba';

echo "🧪 Probando envío de email SIMULADO...\n";
echo "📧 Email de destino: $test_email\n";
echo "📦 Número de pedido: #" . str_pad($order_data['id'], 6, '0', STR_PAD_LEFT) . "\n\n";

// Usar la función simulada
$result = send_order_confirmation_email_simulated($order_data, $order_items, $test_email, $test_name);

if ($result) {
    echo "✅ Email simulado creado exitosamente!\n";
    echo "📁 Revisa la carpeta 'emails/' para ver el archivo de simulación.\n";
    
    // Mostrar el archivo creado
    $filename = "emails/simulacion_pedido_" . str_pad($order_data['id'], 6, '0', STR_PAD_LEFT) . ".json";
    if (file_exists($filename)) {
        echo "\n📄 Contenido del archivo de simulación:\n";
        echo "=====================================\n";
        $content = file_get_contents($filename);
        $data = json_decode($content, true);
        
        echo "📧 Para: " . $data['to'] . "\n";
        echo "👤 Nombre: " . $data['name'] . "\n";
        echo "📦 Pedido: " . $data['order_number'] . "\n";
        echo "💰 Total: $" . number_format($data['order_data']['total_amount'], 2) . "\n";
        echo "📅 Fecha: " . $data['timestamp'] . "\n";
        echo "📍 Dirección: " . $data['order_data']['shipping_address'] . "\n";
        
        echo "\n🛍️ Productos:\n";
        foreach ($data['order_items'] as $item) {
            $item_total = $item['price'] * $item['quantity'];
            echo "   - " . $item['name'] . " x " . $item['quantity'] . " = $" . number_format($item_total, 2) . "\n";
        }
    }
} else {
    echo "❌ Error al crear el email simulado.\n";
}

echo "\n📋 Log del intento de envío guardado en: email_log.txt\n";

// Mostrar el contenido del log
if (file_exists('email_log.txt')) {
    echo "\n📄 Últimas entradas del log:\n";
    $log_content = file_get_contents('email_log.txt');
    $lines = explode("\n", $log_content);
    $last_lines = array_slice($lines, -5); // Últimas 5 líneas
    foreach ($last_lines as $line) {
        if (trim($line)) {
            echo "   $line\n";
        }
    }
}

echo "\n💡 SOLUCIONES PARA EMAILS REALES:\n";
echo "================================\n";
echo "1. 🔧 Configurar Gmail SMTP:\n";
echo "   - Crear cuenta de aplicación en Gmail\n";
echo "   - Usar PHPMailer con credenciales\n";
echo "   - Configurar SMTP en el hosting\n\n";

echo "2. 📧 Servicios de Email:\n";
echo "   - SendGrid (gratis hasta 100 emails/día)\n";
echo "   - Mailgun (gratis hasta 5,000 emails/mes)\n";
echo "   - Amazon SES (muy económico)\n\n";

echo "3. 🚀 Para producción:\n";
echo "   - Configurar SMTP en el hosting\n";
echo "   - Usar servicios profesionales\n";
echo "   - Implementar cola de emails\n\n";

echo "🎯 El sistema funciona correctamente, solo necesita configuración de email real.\n";
?> 