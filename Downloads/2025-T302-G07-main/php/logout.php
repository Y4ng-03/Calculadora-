<?php
require_once 'includes/functions.php';

// Destruir la sesión
session_destroy();

// Redirigir al login
redirect('login.php');
?> 