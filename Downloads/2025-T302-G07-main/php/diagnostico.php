<?php
// Archivo de diagnóstico completo para Discarchar
echo "<!DOCTYPE html>";
echo "<html lang='es'>";
echo "<head>";
echo "<meta charset='UTF-8'>";
echo "<meta name='viewport' content='width=device-width, initial-scale=1.0'>";
echo "<title>Diagnóstico - Discarchar</title>";
echo "<style>";
echo "body { font-family: Arial, sans-serif; margin: 20px; background: #f5f5f5; }";
echo ".container { max-width: 800px; margin: 0 auto; background: white; padding: 20px; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }";
echo ".section { margin-bottom: 30px; padding: 15px; border: 1px solid #ddd; border-radius: 5px; }";
echo ".success { background: #d4edda; border-color: #c3e6cb; color: #155724; }";
echo ".error { background: #f8d7da; border-color: #f5c6cb; color: #721c24; }";
echo ".warning { background: #fff3cd; border-color: #ffeaa7; color: #856404; }";
echo ".info { background: #d1ecf1; border-color: #bee5eb; color: #0c5460; }";
echo "h1, h2 { color: #333; }";
echo "ul { margin: 10px 0; }";
echo "li { margin: 5px 0; }";
echo "</style>";
echo "</head>";
echo "<body>";

echo "<div class='container'>";
echo "<h1>🔍 Diagnóstico del Sistema Discarchar</h1>";

// 1. Información del servidor
echo "<div class='section info'>";
echo "<h2>📊 Información del Servidor</h2>";
echo "<p><strong>Servidor:</strong> " . $_SERVER['SERVER_SOFTWARE'] . "</p>";
echo "<p><strong>PHP Version:</strong> " . phpversion() . "</p>";
echo "<p><strong>Document Root:</strong> " . $_SERVER['DOCUMENT_ROOT'] . "</p>";
echo "<p><strong>Script Path:</strong> " . __FILE__ . "</p>";
echo "</div>";

// 2. Extensiones PHP necesarias
echo "<div class='section'>";
echo "<h2>🔧 Extensiones PHP</h2>";
$required_extensions = ['pdo', 'pdo_mysql', 'session', 'json', 'mbstring'];
$all_ok = true;

foreach ($required_extensions as $ext) {
    $status = extension_loaded($ext);
    $class = $status ? 'success' : 'error';
    $icon = $status ? '✅' : '❌';
    echo "<p class='$class'>$icon $ext: " . ($status ? 'Instalada' : 'NO INSTALADA') . "</p>";
    if (!$status) $all_ok = false;
}

if ($all_ok) {
    echo "<p class='success'>✅ Todas las extensiones necesarias están instaladas</p>";
} else {
    echo "<p class='error'>❌ Faltan algunas extensiones necesarias</p>";
}
echo "</div>";

// 3. Configuración de sesiones
echo "<div class='section'>";
echo "<h2>🔐 Configuración de Sesiones</h2>";
$session_path = ini_get('session.save_path');
$session_gc = ini_get('session.gc_maxlifetime');
$session_status = session_status();

echo "<p><strong>Session Save Path:</strong> $session_path</p>";
echo "<p><strong>Session GC Maxlifetime:</strong> $session_gc segundos</p>";
echo "<p><strong>Session Status:</strong> " . ($session_status === PHP_SESSION_ACTIVE ? 'Activa' : 'Inactiva') . "</p>";

if (is_writable($session_path)) {
    echo "<p class='success'>✅ El directorio de sesiones es escribible</p>";
} else {
    echo "<p class='error'>❌ El directorio de sesiones NO es escribible</p>";
}
echo "</div>";

// 4. Conexión a la base de datos
echo "<div class='section'>";
echo "<h2>🗄️ Base de Datos</h2>";

try {
    require_once 'config/database.php';
    echo "<p class='success'>✅ Conexión a la base de datos exitosa</p>";
    
    // Verificar tablas
    $tables = ['users', 'products', 'categories', 'orders', 'order_items'];
    echo "<h3>Tablas de la base de datos:</h3>";
    
    foreach ($tables as $table) {
        try {
            $stmt = $pdo->query("SELECT COUNT(*) as count FROM $table");
            $result = $stmt->fetch();
            echo "<p class='success'>✅ Tabla '$table': " . $result['count'] . " registros</p>";
        } catch (PDOException $e) {
            echo "<p class='error'>❌ Tabla '$table': NO EXISTE</p>";
        }
    }
    
} catch (Exception $e) {
    echo "<p class='error'>❌ Error de conexión: " . $e->getMessage() . "</p>";
}
echo "</div>";

// 5. Archivos y directorios
echo "<div class='section'>";
echo "<h2>📁 Archivos y Directorios</h2>";

$required_files = [
    'config/database.php',
    'includes/functions.php',
    'css/style.css',
    'js/shop.js',
    'shop.php',
    'login.php',
    'register.php'
];

$required_dirs = [
    'uploads',
    'emails',
    'admin'
];

foreach ($required_files as $file) {
    if (file_exists($file)) {
        echo "<p class='success'>✅ $file</p>";
    } else {
        echo "<p class='error'>❌ $file (NO EXISTE)</p>";
    }
}

foreach ($required_dirs as $dir) {
    if (is_dir($dir)) {
        $writable = is_writable($dir) ? 'escribible' : 'no escribible';
        $class = is_writable($dir) ? 'success' : 'warning';
        echo "<p class='$class'>📁 $dir ($writable)</p>";
    } else {
        echo "<p class='error'>❌ $dir (NO EXISTE)</p>";
    }
}
echo "</div>";

// 6. Configuración de PHPMailer
echo "<div class='section'>";
echo "<h2>📧 PHPMailer</h2>";
if (file_exists('vendor/autoload.php')) {
    echo "<p class='success'>✅ PHPMailer instalado via Composer</p>";
} else {
    echo "<p class='warning'>⚠️ PHPMailer no encontrado (puede que no sea necesario)</p>";
}
echo "</div>";

// 7. Prueba de funcionalidad básica
echo "<div class='section'>";
echo "<h2>🧪 Pruebas de Funcionalidad</h2>";

// Probar funciones básicas
if (function_exists('is_logged_in')) {
    echo "<p class='success'>✅ Función is_logged_in() disponible</p>";
} else {
    echo "<p class='error'>❌ Función is_logged_in() NO disponible</p>";
}

// Probar sesiones
session_start();
$_SESSION['test'] = 'test_value';
if (isset($_SESSION['test'])) {
    echo "<p class='success'>✅ Sesiones funcionando correctamente</p>";
    unset($_SESSION['test']);
} else {
    echo "<p class='error'>❌ Problemas con las sesiones</p>";
}

// Probar JSON
$test_json = json_encode(['test' => 'value']);
if ($test_json !== false) {
    echo "<p class='success'>✅ JSON funcionando correctamente</p>";
} else {
    echo "<p class='error'>❌ Problemas con JSON</p>";
}
echo "</div>";

// 8. Recomendaciones
echo "<div class='section info'>";
echo "<h2>💡 Recomendaciones</h2>";
echo "<ul>";
echo "<li>Accede a tu proyecto usando: <strong>http://localhost/proyectjean/</strong></li>";
echo "<li>Asegúrate de que Apache y MySQL estén iniciados en XAMPP</li>";
echo "<li>Si hay errores de permisos, verifica los permisos de los directorios</li>";
echo "<li>Para desarrollo, considera habilitar el modo debug en PHP</li>";
echo "</ul>";
echo "</div>";

echo "</div>";
echo "</body>";
echo "</html>";
?> 
?> 