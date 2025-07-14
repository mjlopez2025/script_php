<?php
// Configuración de conexiones
$config_produccion = [
    'host'     => 'localhost',
    'dbname'   => 'guarani',
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
    // Conexiones
    $conn_produccion = new PDO(
        "pgsql:host={$config_produccion['host']};dbname={$config_produccion['dbname']}",
        $config_produccion['user'],
        $config_produccion['password']
    );
    $conn_produccion->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    $conn_cubetera = new PDO(
        "pgsql:host={$config_cubetera['host']};dbname={$config_cubetera['dbname']}",
        $config_cubetera['user'],
        $config_cubetera['password']
    );
    $conn_cubetera->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Obtener múltiples registros (no solo LIMIT 1)
    $comisiones = $conn_cubetera->query("SELECT id_comision FROM guarani.comisiones")->fetchAll(PDO::FETCH_ASSOC);
    $asignaciones = $conn_cubetera->query("SELECT id_asignacion FROM guarani.asignaciones")->fetchAll(PDO::FETCH_ASSOC);
    $tipos_clase = $conn_cubetera->query("SELECT id_tipo_clase FROM guarani.tipos_clase")->fetchAll(PDO::FETCH_ASSOC);

    $insertados = 0;
    $omitidos = 0;

    // Preparar la consulta de inserción
    $stmt_insert = $conn_cubetera->prepare(
        "INSERT INTO guarani.comisiones_hs 
        (id_comision, id_asignacion, id_tipo_clase)
        VALUES (?, ?, ?)"
    );

    // Verificar si hay una transacción activa y cerrarla
    if ($conn_cubetera->inTransaction()) {
        $conn_cubetera->rollBack();
    }

    // Asumimos que hay relación 1:1 entre las tablas
    $max_records = min(count($comisiones), count($asignaciones), count($tipos_clase));
    
    for ($i = 0; $i < $max_records; $i++) {
        try {
            // Iniciar nueva transacción para cada registro
            $conn_cubetera->beginTransaction();
            
            $stmt_insert->execute([
                $comisiones[$i]['id_comision'],
                $asignaciones[$i]['id_asignacion'],
                $tipos_clase[$i]['id_tipo_clase']
            ]);
            
            $conn_cubetera->commit();
            $insertados++;
            
            // Mostrar progreso cada 100 registros
            if ($insertados % 100 == 0) {
                echo "Procesados $insertados registros...\n";
            }
        } catch (PDOException $e) {
            // Si hay error, hacer rollback y continuar
            if ($conn_cubetera->inTransaction()) {
                $conn_cubetera->rollBack();
            }
            
            $omitidos++;
            echo "⚠️ Omitido registro #$i - " . $e->getMessage() . "\n";
            
            // Reconectar si es necesario
            if ($e->getCode() == '25P02' || strpos($e->getMessage(), 'active transaction') !== false) {
                $conn_cubetera = new PDO(
                    "pgsql:host={$config_cubetera['host']};dbname={$config_cubetera['dbname']}",
                    $config_cubetera['user'],
                    $config_cubetera['password']
                );
                $conn_cubetera->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            }
        }
    }

    echo "\n✅ Resultado final:\n";
    echo "   - Registros insertados: $insertados\n";
    echo "   - Registros omitidos: $omitidos\n";

} catch (PDOException $e) {
    die("❌ Error crítico: " . $e->getMessage());
} finally {
    $conn_produccion = null;
    $conn_cubetera = null;
}
?>