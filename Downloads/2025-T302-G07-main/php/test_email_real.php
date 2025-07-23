<?php
require_once 'includes/functions.php';

// Datos de prueba para el email
$order_data = [
    'id' => 999999,
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
$test_name = 'Jean Guerrero';

echo "🧪 Probando envío de email REAL con PHPMailer...\n";
echo "📧 Email de destino: $test_email\n";
echo "📦 Número de pedido: #" . str_pad($order_data['id'], 6, '0', STR_PAD_LEFT) . "\n";
echo "🔧 Usando Gmail SMTP...\n\n";

// Usar la función real de Gmail
$result = send_order_confirmation_email_gmail($order_data, $order_items, $test_email, $test_name);

if ($result) {
    echo "✅ Email enviado exitosamente!\n";
    echo "📧 Revisa tu bandeja de entrada en Gmail.\n";
    echo "📁 También revisa la carpeta de spam por si acaso.\n";
} else {
    echo "❌ Error al enviar el email.\n";
    echo "🔧 Revisa el archivo email_log.txt para ver el error específico.\n";
}

echo "\n📋 Log del intento de envío guardado en: email_log.txt\n";

// Mostrar el contenido del log
if (file_exists('email_log.txt')) {
    echo "\n📄 Últimas entradas del log:\n";
    $log_content = file_get_contents('email_log.txt');
    $lines = explode("\n", $log_content);
    $last_lines = array_slice($lines, -3); // Últimas 3 líneas
    foreach ($last_lines as $line) {
        if (trim($line)) {
            echo "   $line\n";
        }
    }
}

echo "\n🎯 ¡El sistema de emails reales está configurado!\n";
echo "💡 Ahora cuando hagas pedidos reales, recibirás emails de confirmación.\n";
?> 