<?php
/**
 * Bootstrap aplikasi.
 * Autoload semua class dari folder /classes dan siapkan helper umum.
 */

spl_autoload_register(function ($class) {
    $file = __DIR__ . '/../classes/' . $class . '.php';
    if (file_exists($file)) {
        require_once $file;
    }
});

require_once __DIR__ . '/../includes/csrf.php';
require_once __DIR__ . '/../includes/functions.php';

// Objek global yang dipakai hampir semua halaman
$auth = new Auth();
