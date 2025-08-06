<?php
// Configuración para mostrar todos los errores
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Configuración de conexión al servidor
$config_sii = [
    'host'     => '172.16.1.58',
    'port'     => '5433',
    'dbname'   => 'tinkuy',
    'user'     => 'mjlopez',
    'password' => '13082019'
];

// Crear string de conexión DSN
$dsn = "pgsql:host={$config_sii['host']};port={$config_sii['port']};dbname={$config_sii['dbname']}";

// Establecer conexión
    $conn = new PDO($dsn, $config_sii['user'], $config_sii['password']);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);




// Configuración de la conexión a la base de datos Wichi (solo para consulta)
$config_wichi = [
    'host'     => '172.16.1.61',
    'port'     => '5432',
    'dbname'   => 'siu_wichi',
    'user'     => 'postgres',
    'password' => 'postgres'
];

$conn_wichi = new PDO(
        "pgsql:host={$config_wichi['host']};port={$config_wichi['port']};dbname={$config_wichi['dbname']}",
        $config_wichi['user'],
        $config_wichi['password']
    );
?>