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

    // 1. Primero diagnosticamos: ¿Qué datos estamos obteniendo realmente?
    $query_test = "SELECT * FROM negocio.sga_propuestas_estados LIMIT 5";
    $stmt_test = $conn_produccion->query($query_test);
    $primeros_registros = $stmt_test->fetchAll(PDO::FETCH_ASSOC);
    
   

    // 2. Extraer datos - versión más flexible
    $query = "SELECT * FROM negocio.sga_propuestas_estados";
    $stmt = $conn_produccion->query($query);
    $estados = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $insertados = 0;
    $omitidos = 0;

    if (count($estados) > 0) {
        // Preparar la consulta de inserción
        $stmt_insert = $conn_cubetera->prepare(
            "INSERT INTO guarani.propuestas_estados (desc_prop_estado) VALUES (?)"
        );

        foreach ($estados as $index => $estado) {
            // Versión más flexible para identificar el campo de descripción
            $descripcion = $estado['desc_prop_estado'] ?? 
                          $estado['descripcion'] ?? 
                          $estado['nombre'] ?? 
                          $estado['estado'] ?? 
                          null;

            if (empty($descripcion)) {
                echo "⚠️ Registro $index omitido - Estructura del registro:\n";
                print_r($estado);
                echo "\n";
                $omitidos++;
                continue;
            }

            try {
                $conn_cubetera->beginTransaction();
                $stmt_insert->execute([$descripcion]);
                $conn_cubetera->commit();
                $insertados++;
                echo "✓ Insertado: $descripcion\n";
            } catch (PDOException $e) {
                if ($conn_cubetera->inTransaction()) {
                    $conn_cubetera->rollBack();
                }
                $omitidos++;
                echo "⚠️ Error en registro $index: " . $e->getMessage() . "\n";
            }
        }
    } else {
        echo "🔍 No se encontraron registros en la tabla origen\n";
    }
    
    echo "\n🎉 ¡Proceso completado! \n";
    echo "📊 Estadísticas:\n";
    echo "   - Insertados: $insertados estados\n";
    echo "   - Omitidos: $omitidos\n";

} catch (PDOException $e) {
    die("❌ Error crítico: " . $e->getMessage());
} finally {
    $conn_produccion = null;
    $conn_cubetera = null;
}
?>