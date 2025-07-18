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

    // 1. Extraer nombres de comisiones
    $query_comisiones = "SELECT nombre FROM negocio.sga_comisiones";
    $stmt_comisiones = $conn_produccion->query($query_comisiones);
    $comisiones = $stmt_comisiones->fetchAll(PDO::FETCH_ASSOC);

    // 2. Extraer IDs de referencia
    $periodos = $conn_cubetera->query("SELECT id_periodo_lectivo FROM guarani.periodos_lectivo LIMIT 1")->fetchColumn();
    $elementos = $conn_cubetera->query("SELECT id_elemento FROM guarani.elementos LIMIT 1")->fetchColumn();
    $turnos = $conn_cubetera->query("SELECT id_turno FROM guarani.turnos LIMIT 1")->fetchColumn();

    $insertados = 0;
    $omitidos = 0;

    // Preparar la consulta de inserción
    $stmt_insert = $conn_cubetera->prepare(
        "INSERT INTO guarani.comisiones 
        (nombre, id_periodo_lectivo, id_elemento, id_turno) 
        VALUES (?, ?, ?, ?)"
    );

    // Verificar si hay una transacción activa y cerrarla
    if ($conn_cubetera->inTransaction()) {
        $conn_cubetera->rollBack();
    }

    foreach ($comisiones as $comision) {
        try {
            // Iniciar nueva transacción para cada registro
            $conn_cubetera->beginTransaction();
            
            $stmt_insert->execute([
                $comision['nombre'],
                $periodos,
                $elementos,
                $turnos
            ]);
            
            $conn_cubetera->commit();
            $insertados++;
        } catch (PDOException $e) {
            // Si hay error, hacer rollback y continuar
            if ($conn_cubetera->inTransaction()) {
                $conn_cubetera->rollBack();
            }
            
            $omitidos++;
            echo "⚠️ Omitido: {$comision['nombre']} - " . $e->getMessage() . "\n";
            
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
    echo "   - Comisiones insertadas: $insertados\n";
    echo "   - Comisiones omitidas: $omitidos\n";

} catch (PDOException $e) {
    die("❌ Error crítico: " . $e->getMessage());
} finally {
    $conn_produccion = null;
    $conn_cubetera = null;
}
?>