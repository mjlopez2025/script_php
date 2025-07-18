<?php
// Configuración de conexiones
$config_siu = [
    'host'     => 'localhost',
    'dbname'   => 'siu',
    'user'     => 'postgres',
    'password' => '13082019'
];

$config_cubetera = [
    'host'     => 'localhost',
    'dbname'   => 'cubetera',
    'user'     => 'postgres',
    'password' => '13082019'
];

try {
    // Conexión a la base SIU (origen)
    $dsn_siu = "pgsql:host={$config_siu['host']};dbname={$config_siu['dbname']}";
    $conn_siu = new PDO($dsn_siu, $config_siu['user'], $config_siu['password']);
    $conn_siu->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Conexión a la base CUBETERA (destino)
    $dsn_cubetera = "pgsql:host={$config_cubetera['host']};dbname={$config_cubetera['dbname']}";
    $conn_cubetera = new PDO($dsn_cubetera, $config_cubetera['user'], $config_cubetera['password']);
    $conn_cubetera->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // 1. Extraer datos de SIU
    $query_siu = "SELECT desc_titul, codc_titul FROM mapuche.dh33";
    $stmt_siu = $conn_siu->query($query_siu);
    $datos = $stmt_siu->fetchAll(PDO::FETCH_ASSOC);

    // 2. Insertar en CUBETERA
    $query_cubetera = "INSERT INTO mapuche.titulos (desc_titulo, codigo_titulo) VALUES (:desc_titulo, :codigo_titulo)";
    $stmt_cubetera = $conn_cubetera->prepare($query_cubetera);

    $conn_cubetera->beginTransaction(); // Iniciar transacción para mayor velocidad
    foreach ($datos as $fila) {
        $stmt_cubetera->execute([
            ':desc_titulo'   => $fila['desc_titul'],
            ':codigo_titulo' => $fila['codc_titul']
        ]);
    }
    $conn_cubetera->commit(); // Confirmar cambios

    echo "¡Datos migrados con éxito, corazón! 💕\n";

} catch (PDOException $e) {
    if (isset($conn_cubetera) && $conn_cubetera->inTransaction()) {
        $conn_cubetera->rollBack(); // Revertir en caso de error
    }
    die("Error: " . $e->getMessage());
} finally {
    // Cerrar conexiones
    $conn_siu = null;
    $conn_cubetera = null;
}