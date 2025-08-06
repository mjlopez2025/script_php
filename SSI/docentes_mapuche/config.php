<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

$remoteConfig = [
    'host'     => '172.16.1.58',
    'port'     => '5433',
    'dbname'   => 'sii',       
    'user'     => 'mjlopez',
    'password' => '13082019'
];

if (!extension_loaded('pdo_pgsql')) {
    die("❌ La extensión pdo_pgsql no está instalada\n");
}

$dsn = "pgsql:host={$remoteConfig['host']};port={$remoteConfig['port']};dbname={$remoteConfig['dbname']}";

try {
    $conn = new PDO($dsn, $remoteConfig['user'], $remoteConfig['password']);
    //echo "✅ Conexión exitosa a PostgreSQL\n";
} catch (PDOException $e) {
    die("❌ Error de conexión: " . $e->getMessage() . "\n");
}

// Configuración para leer la base de siu_wichi
$config_wichi = [
    'host'     => '172.16.1.61',
    'port'     => '5432',
    'dbname'   => 'siu_wichi',
    'user'     => 'postgres',
    'password' => 'postgres',
    'esquema_principal' => 'mapuche' // Cambia esto al esquema correcto
];

if (!extension_loaded('pdo_pgsql')) {
    die("❌ La extensión pdo_pgsql no está instalada\n");
}

$dsn = "pgsql:host={$config_wichi['host']};port={$config_wichi['port']};dbname={$config_wichi['dbname']}";

try {
    $connw = new PDO($dsn, $config_wichi['user'], $config_wichi['password']);
    //echo "✅ Conexión exitosa a PostgreSQL\n";
} catch (PDOException $e) {
    die("❌ Error de conexión: " . $e->getMessage() . "\n");
}


