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

// Inicializar variables para evitar errores
$conn_produccion = null;
$conn_cubetera = null;

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

    // 1. Extraer datos de la tabla periodos_lectivo
    $query = "
        SELECT 
            nombre, 
            anio_academico,
            fecha_inicio,
            fecha_fin      
            FROM negocio.sga_periodos
    ";
    
    $stmt = $conn_produccion->query($query);
    $periodos = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $insertados = 0;
    $omitidos = 0;

    if (count($periodos) > 0) {
        foreach ($periodos as $periodo) {
            // Iniciar una nueva transacción por cada asignacion
            $conn_cubetera->beginTransaction();
            
            try {
                $stmt_insert = $conn_cubetera->prepare(
                    "INSERT INTO guarani.periodos_lectivo
                    (nombre, anio_academico, fecha_ini, fecha_fin)
                    VALUES (?, ?, ?, ?)"
                );
                
                $stmt_insert->execute([
                    $periodo['nombre'],
                    $periodo['anio_academico'],
                    $periodo['fecha_inicio'],
                    $periodo['fecha_fin'],
                ]);
                
                $conn_cubetera->commit();
                $insertados++;
                echo "✅ Insertado: {$periodo['nombre']} (Año: {$periodo['anio_academico']})\n";
            } catch (PDOException $e) {
                $conn_cubetera->rollBack();
                $omitidos++;
                echo "⚠️ Omitido: {$periodo['nombre']} - " . $e->getMessage() . "\n";
            }
        }
    } else {
        echo "🔍 No se encontraron registros en la tabla sga_asignaciones\n";
    }
    
    echo "\n🎉 ¡Proceso completado! \n";
    echo "📊 Estadísticas:\n";
    echo "   - Insertados: $insertados elementos\n";
    echo "   - Omitidos: $omitidos\n";

} catch (PDOException $e) {
    die("❌ Error crítico: " . $e->getMessage());
} finally {
    if (isset($conn_produccion)) $conn_produccion = null;
    if (isset($conn_cubetera)) $conn_cubetera = null;
}
?>