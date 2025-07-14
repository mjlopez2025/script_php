<?php
// Configuración de la conexión
$remoteConfig = [
    'host'     => '172.16.1.58',
    'port'     => '5432',
    'dbname'   => 'sii',
    'user'     => 'postgres',
    'password' => 'postgres'
];

// Verificar extensión PDO
if (!extension_loaded('pdo_pgsql')) {
    die("❌ La extensión pdo_pgsql no está instalada\n");
}

// Configurar DSN
$dsn = "pgsql:host={$remoteConfig['host']};port={$remoteConfig['port']};dbname={$remoteConfig['dbname']}";

// Establecer conexión
try {
    $conn = new PDO($dsn, $remoteConfig['user'], $remoteConfig['password']);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    echo "✅ Conexión exitosa a PostgreSQL\n";
} catch (PDOException $e) {
    die("❌ Error de conexión: " . $e->getMessage() . "\n");
}

// Crear tabla si no existe
try {
    $conn->exec("
    CREATE TABLE IF NOT EXISTS docentes_guarani (
        id SERIAL PRIMARY KEY,
        resposabilidad_academica VARCHAR(500),
        propuesta_formativa VARCHAR(500),
        periodo VARCHAR(500),
        actividad VARCHAR(500),
        docente VARCHAR(500),
        comision VARCHAR(500),
        cursados_2024 VARCHAR(500)
    )");
    echo "✅ Tabla verificada/creada exitosamente\n";
} catch (PDOException $e) {
    die("❌ Error al crear tabla: " . $e->getMessage());
}

// Configuración del archivo CSV
$csvFile = 'docentes_guarani.csv';
$delimiter = ',';
$enclosure = '"';

// Verificar si el archivo existe
if (!file_exists($csvFile)) {
    die("❌ El archivo $csvFile no existe\n");
}

// Validar estructura del CSV
function validarFilaCSV($row, $rowNumber) {
    if (count($row) < 7) {
        echo "⚠️ Fila $rowNumber incompleta. Campos esperados: 7, encontrados: " . count($row) . "\n";
        echo "Contenido: " . implode(',', $row) . "\n";
        return false;
    }
    return true;
}

try {
    echo "📖 Procesando archivo CSV: $csvFile\n";

    $file = fopen($csvFile, 'r');
    $header = fgetcsv($file, 0, $delimiter, $enclosure);

    $rowCount = 0;
    $successCount = 0;
    $batchSize = 100;
    $batchCount = 0;

    $stmt = $conn->prepare("
        INSERT INTO docentes_guarani (
            resposabilidad_academica,
            propuesta_formativa,
            periodo,
            actividad,
            docente,
            comision,
            cursados_2024
        ) VALUES (?, ?, ?, ?, ?, ?, ?)
    ");

    while (($row = fgetcsv($file, 0, $delimiter, $enclosure)) !== false) {
        $rowCount++;

        if (!validarFilaCSV($row, $rowCount)) {
            continue;
        }

        if ($batchCount === 0 && !$conn->inTransaction()) {
            $conn->beginTransaction();
        }

        $data = [
            !empty($row[0]) ? trim($row[0]) : null,
            !empty($row[1]) ? trim($row[1]) : null,
            //isset($row[1]) && ($row[1]) ? (int)$row[1] : null,
            !empty($row[2]) ? trim($row[2]) : null,
            !empty($row[3]) ? trim($row[3]) : null,
            //isset($row[3]) && ($row[3]) ? (int)$row[3] : null,
            !empty($row[4]) ? trim($row[4]) : null,
            !empty($row[5]) ? trim($row[5]) : null,
            !empty($row[6]) ? trim($row[6]) : null
        ];

        try {
            $stmt->execute($data);
            $successCount++;
            $batchCount++;

            if ($batchCount >= $batchSize) {
                $conn->commit();
                $batchCount = 0;
                echo "✔️ Procesadas $rowCount filas...\n";
            }
        } catch (PDOException $e) {
            echo "⚠️ Error en fila $rowCount: " . $e->getMessage() . "\n";
            if ($conn->inTransaction()) {
                $conn->rollBack();
            }
            $batchCount = 0;
            continue;
        }
    }

    if ($batchCount > 0 && $conn->inTransaction()) {
        $conn->commit();
    }

    fclose($file);

    echo "\n🎉 Importación completada\n";
    echo "📊 Resumen:\n";
    echo "- Total filas en CSV: $rowCount\n";
    echo "- Filas importadas: $successCount\n";
    echo "- Errores: " . ($rowCount - $successCount) . "\n";

    echo "\n🔍 Muestra de datos insertados (primeros 2 registros):\n";
    $muestra = $conn->query("SELECT * FROM docentes_guarani LIMIT 2")->fetchAll(PDO::FETCH_ASSOC);
    print_r($muestra);

} catch (Exception $e) {
    if (isset($conn) && $conn->inTransaction()) {
        $conn->rollBack();
    }
    die("\n❌ Error fatal: " . $e->getMessage() . "\n");
} finally {
    $conn = null;
}
