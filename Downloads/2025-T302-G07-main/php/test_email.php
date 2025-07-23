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

$test_email = 'jeanguerrero04@gmail.com'; // Cambia esto por tu email real
$test_name = 'Usuario de Prueba';

echo "🧪 Probando envío de email de confirmación...\n";
echo "📧 Email de destino: $test_email\n";
echo "📦 Número de pedido: #" . str_pad($order_data['id'], 6, '0', STR_PAD_LEFT) . "\n\n";

// Intentar enviar el email
$result = send_order_confirmation_email($order_data, $order_items, $test_email, $test_name);

if ($result) {
    echo "✅ Email enviado exitosamente!\n";
    echo "📧 Revisa tu bandeja de entrada (y carpeta de spam) para ver el email de confirmación.\n";
} else {
    echo "❌ Error al enviar el email.\n";
    echo "🔧 Posibles causas:\n";
    echo "   - El servidor no tiene configurado el envío de emails\n";
    echo "   - El email de destino no es válido\n";
    echo "   - Configuración de SMTP faltante\n\n";
    echo "💡 Para solucionar esto, puedes:\n";
    echo "   1. Configurar un servidor SMTP en tu hosting\n";
    echo "   2. Usar servicios como SendGrid, Mailgun, o Gmail SMTP\n";
    echo "   3. Verificar que la función mail() esté habilitada en PHP\n";
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

echo "\n🎯 Para probar con tu email real, edita la variable \$test_email en este archivo.\n";
?> 