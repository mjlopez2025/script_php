<?php
// Configuración de conexiones
$config_guarani = [
    'dsn'      => 'pgsql:host=localhost;dbname=guarani',
    'user'     => 'postgres',
    'password' => '13082019'
];

$config_cubetera = [
    'dsn'      => 'pgsql:host=localhost;dbname=cubetera',
    'user'     => 'postgres',
    'password' => '13082019'
];

try {
    // 1. Conexiones
    $conn_guarani = new PDO($config_guarani['dsn'], $config_guarani['user'], $config_guarani['password']);
    $conn_cubetera = new PDO($config_cubetera['dsn'], $config_cubetera['user'], $config_cubetera['password']);
    $conn_guarani->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $conn_cubetera->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // 2. Consulta PRINCIPAL corregida
    $query = "
        SELECT 
            np.nombre AS desc_propuesta,
            np.codigo AS cod_propuesta,
            cpt.id_propuesta_tipo AS id_tipo,
            cpe.id_prop_estado AS id_estado
        FROM 
            negocio.sga_propuestas np
        -- JOIN para TIPOS (guarani -> cubetera)
        JOIN negocio.sga_propuestas_tipos gpt ON np.propuesta_tipo = gpt.id
        JOIN cubetera.guarani.propuestas_tipo cpt ON gpt.descripcion = cpt.desc_prop_tipo
        -- JOIN para ESTADOS (guarani -> cubetera)
        JOIN negocio.sga_propuestas_estados gpe ON np.estado = gpe.id
        JOIN cubetera.guarani.propuestas_estados cpe ON gpe.descripcion = cpe.desc_prop_estado
        WHERE 
            np.nombre IS NOT NULL 
            AND np.codigo IS NOT NULL
    ";

    $stmt = $conn_guarani->query($query);
    $propuestas = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if (empty($propuestas)) {
        die("🔴 No se encontraron registros válidos.");
    }

    // 3. Insertar en cubetera.guarani.propuestas
    $conn_cubetera->beginTransaction();
    $stmt_insert = $conn_cubetera->prepare("
        INSERT INTO guarani.propuestas 
            (desc_propuesta, cod_propuesta, id_porpuesta_tipo, id_estado) 
        VALUES 
            (:desc, :cod, :tipo, :estado)
    ");

    $insertados = 0;
    foreach ($propuestas as $p) {
        try {
            $stmt_insert->execute([
                ':desc'   => $p['desc_propuesta'],
                ':cod'    => $p['cod_propuesta'],
                ':tipo'   => $p['id_tipo'],
                ':estado' => $p['id_estado']
            ]);
            $insertados++;
        } catch (PDOException $e) {
            echo "⚠️ Error insertando: " . $p['desc_propuesta'] . " - " . $e->getMessage() . "\n";
        }
    }
    $conn_cubetera->commit();

    echo "✅ ¡Completado! Registros insertados: $insertados\n";
    echo "📊 Total procesados: " . count($propuestas) . "\n";

} catch (PDOException $e) {
    if (isset($conn_cubetera) && $conn_cubetera->inTransaction()) {
        $conn_cubetera->rollBack();
    }
    die("❌ Error crítico: " . $e->getMessage());
} finally {
    $conn_guarani = null;
    $conn_cubetera = null;
}
?>