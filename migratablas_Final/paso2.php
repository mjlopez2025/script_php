<?php

// Configuración del archivo CSV
$skipRows = 1; // Saltar las primeras filas (cabeceras)

try {
    echo $LINES;
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Preparar consulta SQL con el nuevo campo incluido
    $sql = "INSERT INTO public.doc_de_guarani (
                responsabilidad_academica_guarani, 
                propuesta_formativa_guarani, 
                periodo_guarani, 
                actividad_guarani, 
                codigo_actividad_guarani,  -- NUEVO CAMPO
                docente_guarani, 
                docente_dni_guarani,
                comision_guarani, 
                cursados_guarani,
                ape_nom1_Guarani,
                tipo_doc1_Guarani,
                num_doc1_Guarani,
                ape_nom2_Guarani,
                tipo_doc2_Guarani,
                num_doc2_Guarani,
                ape_nom3_Guarani,
                tipo_doc3_Guarani,
                num_doc3_Guarani,
                ape_nom4_Guarani,
                tipo_doc4_Guarani,
                num_doc4_Guarani
            ) VALUES (
                :responsabilidad_academica, 
                :propuesta_formativa, 
                :periodo, 
                :actividad, 
                :codigo_actividad_guarani,  -- NUEVO PARAMETRO
                :docente, 
                :docente_dni_guarani,
                :comision, 
                :cursados,
                :ape_nom1_Guarani,
                :tipo_doc1_Guarani,
                :num_doc1_Guarani,
                :ape_nom2_Guarani,
                :tipo_doc2_Guarani,
                :num_doc2_Guarani,
                :ape_nom3_Guarani,
                :tipo_doc3_Guarani,
                :num_doc3_Guarani,
                :ape_nom4_Guarani,
                :tipo_doc4_Guarani,
                :num_doc4_Guarani
            )";

    $stmt = $conn->prepare($sql);

    // Abrir archivo CSV
    if (($handle = fopen(CSV_FILE_GUARANI, "r")) !== FALSE) {
        $rowCount = 0;
        $importedCount = 0;

        echo "Iniciando importación desde " . CSV_FILE_GUARANI . "...\n";

        while (($data = fgetcsv($handle, 0, DELIMITER)) !== FALSE) {
            $rowCount++;

            // Saltar cabeceras
            if ($rowCount <= $skipRows) {
                continue;
            }

            $responsabilidad_academica = trim($data[0]);
            $propuesta_formativa = trim($data[1]);
            $periodo = trim($data[2]);

                    
            // Extraer código entre paréntesis al inicio
            if (preg_match('/^\(([^)]+)\)\s*-\s*(.+)$/', $actividadCompleta, $matches)) {
                $codigoActividad = trim($matches[1]); // AF045
                $actividad = trim($matches[2]);       // Diseño de Productos...
            }


            $docente = trim($data[4]);
            $comision = trim($data[5]);
            $cursados = trim($data[6]);

            // Separar docentes
            $docentes = array_pad(explode(' - ', $docente), 4, '');
            $docenteFields = [];
            for ($i = 1; $i <= 4; $i++) {
                $docenteFields["ape_nom{$i}_Guarani"] = '';
                $docenteFields["tipo_doc{$i}_Guarani"] = '';
                $docenteFields["num_doc{$i}_Guarani"] = '';
            }

            foreach ($docentes as $index => $docenteStr) {
    if ($index >= 4) break; // ✅ evita pasar de 4 docentes

    if (empty(trim($docenteStr))) continue;

    $docenteParts = array_map('trim', explode(',', $docenteStr));
    $i = $index + 1;

    if (isset($docenteParts[0])) {
        $docenteFields["ape_nom{$i}_Guarani"] = $docenteParts[0];
    }
    if (isset($docenteParts[1])) {
        $docenteFields["tipo_doc{$i}_Guarani"] = $docenteParts[1];
    }
    if (isset($docenteParts[2])) {
        $docenteFields["num_doc{$i}_Guarani"] = $docenteParts[2];
    }
}


            // Bind de parámetros
            $stmt->bindParam(':responsabilidad_academica', $responsabilidad_academica);
            $stmt->bindParam(':propuesta_formativa', $propuesta_formativa);
            $stmt->bindParam(':periodo', $periodo);
            $stmt->bindParam(':actividad', $actividad);
            $stmt->bindParam(':codigo_actividad_guarani', $codigoActividad);
            $stmt->bindParam(':docente', $docente);
            $stmt->bindParam(':comision', $comision);
            $stmt->bindParam(':cursados', $cursados);

            // Docente principal
            $stmt->bindValue(':docente_dni_guarani', $docenteFields["num_doc1_Guarani"]);

            // Vincular docentes
            foreach ($docenteFields as $param => $value) {
                $stmt->bindValue(":{$param}", $value);
            }

            $stmt->execute();
            $importedCount++;
        }

        fclose($handle);
        echo "\nImportación completada con éxito.\n";
        echo "Total de filas en CSV: " . ($rowCount - $skipRows) . "\n";
        echo "Total de registros importados: $importedCount\n";

    } else {
        echo "Error: No se pudo abrir el archivo CSV.\n";
    }

} catch (PDOException $e) {
    echo "Error de base de datos: " . $e->getMessage() . "\n";
} catch (Exception $e) {
    echo "Error general: " . $e->getMessage() . "\n";
}
