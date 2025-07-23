<?php
// Archivo de prueba para verificar que PHP esté funcionando
echo "<h1>PHP está funcionando correctamente</h1>";
echo "<p>Versión de PHP: " . phpversion() . "</p>";
echo "<p>Fecha y hora: " . date('Y-m-d H:i:s') . "</p>";

// Verificar extensiones importantes
$extensions = ['pdo', 'pdo_mysql', 'session', 'json'];
echo "<h2>Extensiones PHP:</h2>";
echo "<ul>";
foreach ($extensions as $ext) {
    $status = extension_loaded($ext) ? "✅ Instalada" : "❌ No instalada";
    echo "<li>$ext: $status</li>";
}
echo "</ul>";

// Verificar configuración de sesiones
echo "<h2>Configuración de sesiones:</h2>";
echo "<p>session.save_path: " . ini_get('session.save_path') . "</p>";
echo "<p>session.gc_maxlifetime: " . ini_get('session.gc_maxlifetime') . "</p>";

// Verificar permisos de escritura
$uploadDir = 'uploads/';
echo "<h2>Permisos de directorios:</h2>";
echo "<p>Directorio uploads: " . (is_writable($uploadDir) ? "✅ Escritura permitida" : "❌ Sin permisos de escritura") . "</p>";
?> 