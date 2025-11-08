<?php
$host = "sql305.infinityfree.com";        
$dbname = "if0_40115623_sillasymesas";         
$username = "if0_40115623";    
$password = "iDmUuA1bslT";      

try {
    $conn = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    echo "<div style='color:red;'>Error de conexión: " . $e->getMessage() . "</div>";
    // Detener ejecución para evitar que scripts posteriores fallen al usar $conn
    exit();
}
?>