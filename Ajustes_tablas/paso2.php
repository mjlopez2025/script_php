<?php

try {

    // 3. Configuración para PostgreSQL
    $conn->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Iniciar transacción
    $conn->beginTransaction();

    // Preparar consulta SQL
    $sql = "INSERT INTO docentes_combinados (
        apellido_nombre_mapuche, dni_mapuche, categoria_mapuche, nro_cargo_mapuche,
        dedicacion_mapuche, estado_mapuche, responsabilidad_academica_guarani,
        propuesta_formativa_guarani, periodo_guarani, actividad_guarani,
        transversales, codigo_actividad_guarani, comision_guarani, cursando_guarani
    ) VALUES (
        :apellido_nombre, :dni, :categoria, :nro_cargo,
        :dedicacion, :estado, :responsabilidad,
        :propuesta_formativa, :periodo, :actividad,
        :transversales, :codigo_actividad, :comision, :cursando
    )";
    
    $stmt = $conn->prepare($sql);

    // 4. Procesar archivo CSV
    $handle = fopen(CSV_FILE_GUARANI, 'r');
    if (!$handle) {
        throw new Exception("No se pudo abrir el archivo CSV 'CSV_FILE_GUARANI'");
    }

    // Saltar encabezado
    fgetcsv($handle, 0, ',', '"');

    $count = 0;
    $errors = 0;
    $lineNumber = 1; // Comenzar desde 1 porque ya leímos el encabezado

    while (($data = fgetcsv($handle, 0, ',', '"')) !== false) {
        $lineNumber++;
        
        try {
            // Validar datos mínimos
            if (count($data) < 14 || empty($data[0]) || empty($data[1])) {
                throw new Exception("Datos incompletos o inválidos en línea $lineNumber");
            }

            // Asignar valores
            $params = [
                ':apellido_nombre' => $data[0],
                ':dni' => is_numeric($data[1]) ? (int)$data[1] : null,
                ':categoria' => $data[2] ?? null,
                ':nro_cargo' => is_numeric($data[3]) ? (int)$data[3] : null,
                ':dedicacion' => $data[4] ?? null,
                ':estado' => $data[5] ?? null,
                ':responsabilidad' => $data[6] ?? null,
                ':propuesta_formativa' => $data[7] ?? null,
                ':periodo' => $data[8] ?? null,
                ':actividad' => $data[9] ?? null,
                ':transversales' => $data[10] ?? null,
                ':codigo_actividad' => $data[11] ?? null,
                ':comision' => $data[12] ?? null,
                ':cursando' => is_numeric($data[13]) ? (int)$data[13] : null
            ];

            $stmt->execute($params);
            $count++;
            
            // Comprometer cada 100 filas
            if ($count % 100 === 0) {
                $conn->commit();
                $conn->beginTransaction();
            }
        } catch (Exception $e) {
            $errors++;
            echo "⚠️ Error línea $lineNumber: " . $e->getMessage() . "\n";
            continue;
        }
    }

    // Comprometer transacción final
    $conn->commit();
    fclose($handle);
    
    // Resultado final
    echo "\n✅ Importación completada:\n";
    echo "Total líneas procesadas: " . ($lineNumber - 1) . "\n";
    echo "Registros importados: $count\n";
    echo "Errores encontrados: $errors\n";

} catch (PDOException $e) {
    if (isset($conn) && $conn->inTransaction()) {
        $conn->rollBack();
    }
    echo "❌ Error de base de datos: " . $e->getMessage() . "\n";
    if (isset($sql)) {
        echo "Consulta SQL: " . $sql . "\n";
    }
} catch (Exception $e) {
    echo "❌ Error general: " . $e->getMessage() . "\n";
}


?>