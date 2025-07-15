<?php

// Configuración del archivo CSV
$skipRows = 1; // Saltar las primeras 2 filas (cabeceras)

try {
    echo $LINES;
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Preparar consulta SQL
    $sql = "INSERT INTO public.Docentes_Guarani (
                responsabilidad_academica_guarani, 
                propuesta_formativa_guarani, 
                comision_guarani, 
                periodo_guarani, 
                docente_guarani, 
                actividad_guarani, 
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
                :comision, 
                :periodo, 
                :docente, 
                :actividad, 
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
        
        echo "Iniciando importación desde ".CSV_FILE_GUARANI."...\n";

        while (($data = fgetcsv($handle, 0, DELIMITER)) !== FALSE) {
            $rowCount++;
            
            // Saltar las primeras filas (cabeceras)
            if ($rowCount <= $skipRows) {
                continue;
            }
            
            // Obtener datos básicos
            $responsabilidad_academica = trim($data[0]);
            $propuesta_formativa = trim($data[1]);
            $comision = trim($data[2]);
            $periodo = trim($data[3]);
            $docente = trim($data[4]);
            $actividad = trim($data[5]);
            $cursados = trim($data[6]);

            // Vincular parámetros básicos
            $stmt->bindParam(':responsabilidad_academica', $responsabilidad_academica);
            $stmt->bindParam(':propuesta_formativa', $propuesta_formativa);
            $stmt->bindParam(':comision', $comision);
            $stmt->bindParam(':periodo', $periodo);
            $stmt->bindParam(':docente', $docente);
            $stmt->bindParam(':actividad', $actividad);
            $stmt->bindParam(':cursados', $cursados);

            // Procesar docentes
            $docentes = explode('-', $docente);
            $docentes = array_map('trim', $docentes);
            
            // Inicializar todos los parámetros de docentes como vacíos
            $docentesParams = [];
            for ($i = 1; $i <= 4; $i++) {
                $docentesParams["ape_nom{$i}_Guarani"] = '';
                $docentesParams["tipo_doc{$i}_Guarani"] = '';
                $docentesParams["num_doc{$i}_Guarani"] = '';
            }

            // Procesar los docentes que existen
            foreach ($docentes as $index => $docenteStr) {
                $docenteData = array_map('trim', explode(',', $docenteStr));
                if (count($docenteData) >= 3) {
                    $i = $index + 1;
                    if ($i <= 4) { // Asegurarnos de no exceder los 4 docentes
                        $docentesParams["ape_nom{$i}_Guarani"] = $docenteData[0];
                        $docentesParams["tipo_doc{$i}_Guarani"] = $docenteData[1];
                        $docentesParams["num_doc{$i}_Guarani"] = $docenteData[2];
                    }
                }
            }

            // Vincular todos los parámetros de docentes
            foreach ($docentesParams as $param => $value) {
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