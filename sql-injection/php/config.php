<?php
// Configuración de la base de datos - edita estos valores con los de tu hosting (InfinityFree)
define('DB_HOST', 'sql305.infinityfree.com'); // en InfinityFree suele ser algo como sqlXXX.epizy.com
define('DB_USER', 'if0_40115623');
define('DB_PASS', 'iDmUuA1bslT');
define('DB_NAME', 'if0_40115623_injecttiontest');

function db_connect() {
    $mysqli = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
    if ($mysqli->connect_errno) {
        // Para producción, reemplazar por un manejo de errores más amable
        die('Error al conectar con la base de datos: ' . $mysqli->connect_error);
    }
    $mysqli->set_charset('utf8mb4');
    return $mysqli;
}
?>