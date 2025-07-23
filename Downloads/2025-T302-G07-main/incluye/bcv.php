<?php
// includes/bcv.php
// Devuelve la tasa BCV del día (float)

function get_bcv_rate() {
    $respaldo = 109.50; // Tasa fija de respaldo
    $cache_file = __DIR__ . '/../bcv_rate_cache.json';

    // Si ya hay un valor guardado, usarlo (sin importar la fecha)
    if (file_exists($cache_file)) {
        $cache = json_decode(file_get_contents($cache_file), true);
        if (isset($cache['rate'])) {
            return floatval($cache['rate']);
        }
    }

    // Si no hay cache, usar el respaldo
    return $respaldo;
} 