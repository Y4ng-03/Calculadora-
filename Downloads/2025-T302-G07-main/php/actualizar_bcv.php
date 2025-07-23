<?php
require_once __DIR__ . '/includes/bcv.php';
$tasa = get_bcv_rate();
echo "Tasa BCV actualizada: $tasa\n"; 