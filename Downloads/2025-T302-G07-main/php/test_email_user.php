<?php
require_once 'config/database.php';
require_once 'includes/functions.php';

// Simular un usuario logueado (usuario ID 1 - admin)
$user_id = 1;

// Obtener información del usuario
$stmt = $pdo->prepare("SELECT username, email FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch();

if (!$user) {
    echo "❌ Error: Usuario no encontrado.\n";
    exit;
}

echo "👤 Usuario: " . $user['username'] . "\n";
echo "📧 Email registrado: " . $user['email'] . "\n\n";

// Datos de prueba para el email
$order_data = [
    'id' => 888888,
    'created_at' => date('Y-m-d H:i:s'),
    'total_amount' => 199.99,
    'shipping_address' => 'Calle de Prueba 456, Colonia Test, Ciudad de México'
];

$order_items = [
    [
        'name' => 'Producto de Prueba A',
        'price' => 99.99,
        'quantity' => 1
    ],
    [
        'name' => 'Producto de Prueba B',
        'price' => 50.00,
        'quantity' => 2
    ]
];

$test_name = $user['username'];

echo "🧪 Probando envío de email al usuario registrado...\n";
echo "📧 Email de destino: " . $user['email'] . "\n";
echo "📦 Número de pedido: #" . str_pad($order_data['id'], 6, '0', STR_PAD_LEFT) . "\n";
echo "🔧 Usando Gmail SMTP...\n\n";

// Usar la función real de Gmail con el email del usuario registrado
$result = send_order_confirmation_email_gmail($order_data, $order_items, $user['email'], $test_name);

if ($result) {
    echo "✅ Email enviado exitosamente al usuario registrado!\n";
    echo "📧 Revisa la bandeja de entrada de: " . $user['email'] . "\n";
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

echo "\n🎯 ¡El sistema ahora envía emails al usuario registrado!\n";
echo "💡 Cuando hagas pedidos reales, la confirmación llegará a tu email de registro.\n";
?> 