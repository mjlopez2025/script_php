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

    // 1. Extraer datos de la tabla sga_elementos
    $query = "
        SELECT 
            elemento AS cod_elemento,
            nombre AS desc_elemento
        FROM negocio.sga_elementos
        ORDER BY elemento
    ";
    
    $stmt = $conn_produccion->query($query);
    $elementos = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $insertados = 0;
    $omitidos = 0;

    if (count($elementos) > 0) {
        foreach ($elementos as $elemento) {
            // Iniciar una nueva transacción por cada elemento
            $conn_cubetera->beginTransaction();
            
            try {
                $stmt_insert = $conn_cubetera->prepare(
                    "INSERT INTO guarani.elementos 
                    (desc_elemento, cod_elemento, id_estado) 
                    VALUES (?, ?, 1)"
                );
                
                $stmt_insert->execute([
                    $elemento['desc_elemento'],
                    $elemento['cod_elemento']
                ]);
                
                $conn_cubetera->commit();
                $insertados++;
                echo "✅ Insertado: {$elemento['desc_elemento']} (Código: {$elemento['cod_elemento']})\n";
            } catch (PDOException $e) {
                $conn_cubetera->rollBack();
                $omitidos++;
                echo "⚠️ Omitido: {$elemento['desc_elemento']} - " . $e->getMessage() . "\n";
            }
        }
    } else {
        echo "🔍 No se encontraron registros en la tabla sga_elementos\n";
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