<?php
try {
    $conn->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Iniciar transacción
    $conn->beginTransaction();

    // Consulta SQL modificada para coincidir con el CSV
    $sql = "INSERT INTO docentes_mapuche (
         apellido_nombre_mapuche, dni_mapuche, categoria_mapuche, nro_cargo_mapuche,
    dedicacion_mapuche, estado_mapuche, dependencia_designada_mapuche, junio_2025_Legajos
    ) VALUES (
        :apellido_nombre_mapuche, :dni_mapuche, :categoria_mapuche, :nro_cargo_mapuche,
    :dedicacion_mapuche, :estado_mapuche, :dependencia_designada_mapuche, :junio_2025_Legajos
    )";
    
    $stmt = $conn->prepare($sql);

    // Procesar archivo CSV
    $handle = fopen(CSV_FILE_MAPUCHE, 'r');
    if (!$handle) {
        throw new Exception("No se pudo abrir el archivo CSV '".CSV_FILE_MAPUCHE."'");
    }

    // Saltar encabezado
    fgetcsv($handle, 0, ',', '"');

    $count = 0;
    $errors = 0;
    $lineNumber = 1;

    while (($data = fgetcsv($handle, 0, ',', '"')) !== false) {
        $lineNumber++;
        
        try {
            // Validación básica
            if (count($data) < 8) {
                throw new Exception("Faltan campos en la línea $lineNumber");
            }

            // Procesamiento especial para campos
            $legajoCompleto = (isset($data[7]) && trim($data[7]) === '1.0') ? 1 : 0;
            
            // Asignar valores con procesamiento específico
            $params = [
                ':apellido_nombre_mapuche' => $data[0] ?? null,
                ':dni_mapuche' => $data[1] ?? null, // Mantener como string para preservar ceros iniciales
                ':categoria_mapuche' => $data[2] ?? null,
                ':nro_cargo_mapuche' => $data[3] ?? null, // Mantener como string si puede tener prefijos
                ':dedicacion_mapuche' => $data[4] ?? null,
                ':estado_mapuche' => $data[5] ?? null,
                ':dependencia_designada_mapuche' => $data[6] ?? null,
                ':junio_2025_Legajos' => $legajoCompleto
            ];

            // Validación de campos obligatorios
            if (empty($params[':apellido_nombre_mapuche']) || empty($params[':dni_mapuche'])) {
                throw new Exception("Nombre o DNI faltante en línea $lineNumber");
            }

            $stmt->execute($params);
            $count++;
            
            // Comprometer cada 100 filas para mejor rendimiento
            if ($count % 100 === 0) {
                $conn->commit();
                $conn->beginTransaction();
            }
        } catch (Exception $e) {
            $errors++;
            error_log("Error línea $lineNumber: " . $e->getMessage());
            error_log("Datos problemáticos: " . json_encode($data));
            continue;
        }
    }

    // Comprometer transacción final
    $conn->commit();
    fclose($handle);
    
    // Resultado final mejorado
    echo "\n✅ Importación completada:\n";
    echo "================================\n";
    echo "Total líneas procesadas: " . ($lineNumber - 1) . "\n";
    echo "Registros importados exitosamente: $count\n";
    echo "Errores encontrados: $errors\n";
    echo "Tasa de éxito: " . round(($count/($lineNumber-1))*100, 2) . "%\n";
    echo "================================\n";
    
} catch (PDOException $e) {
    if (isset($conn) && $conn->inTransaction()) {
        $conn->rollBack();
    }
    echo "❌ Error de base de datos: " . $e->getMessage() . "\n";
    error_log("Error PDO: " . $e->getMessage());
} catch (Exception $e) {
    echo "❌ Error general: " . $e->getMessage() . "\n";
    error_log("Error General: " . $e->getMessage());
}
?>