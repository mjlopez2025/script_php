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



    // 1. Extraer datos de la tabla desc_prop_tipo
    $query = "
        SELECT 
      descripcion AS desc_prop_tipo
      FROM  negocio.sga_propuestas_tipos";
    
    $stmt = $conn_produccion->query($query);
    $propuestas_tipo = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $insertados = 0;
    $omitidos = 0;

    if (count($propuestas_tipo) > 0) {
        foreach ($propuestas_tipo as $prop_tipo) {
            // Iniciar una nueva transacción por cada prop_tipo
            $conn_cubetera->beginTransaction();
            
            try {
                $stmt_insert = $conn_cubetera->prepare(
                    "INSERT INTO guarani.propuestas_tipo 
                    (desc_prop_tipo) 
                    VALUES (?)"
                );
                
                $stmt_insert->execute([
                    $prop_tipo['desc_prop_tipo']
                ]);
                
                $conn_cubetera->commit();
                $insertados++;
            } catch (PDOException $e) {
                $conn_cubetera->rollBack();
                $omitidos++;
                echo "⚠️ Omitido: {$prop_tipo['desc_prop_tipo']} - " . $e->getMessage() . "\n";
            }
        }
    } else {
        echo "🔍 No se encontraron registros en la tabla sga_prop_tipos\n";
    }
    
    echo "\n🎉 ¡Proceso completado! \n";
    echo "📊 Estadísticas:\n";
    echo "   - Insertados: $insertados prop_tipos\n";
    echo "   - Omitidos: $omitidos\n";

} catch (PDOException $e) {
    die("❌ Error crítico: " . $e->getMessage());
} finally {
    if (isset($conn_produccion)) $conn_produccion = null;
    if (isset($conn_cubetera)) $conn_cubetera = null;
}
?>