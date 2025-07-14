<?php

// Configuración del archivo CSV

$skipRows = 1; // Saltar las primeras 2 filas (cabeceras)

try {
    echo $LINES;
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    
 // Preparar consulta SQL - usa nombres de columnas exactos como en la tabla
    $sql = "INSERT INTO public.doc_de_guarani (
                responsabilidad_academica_guarani, 
                propuesta_formativa_guarani, 
                periodo_guarani, 
                actividad_guarani, 
                docente_guarani, 
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
                :docente, 
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
            $periodo = trim($data[2]);
            $actividad = trim($data[3]);
            $docente = trim($data[4]);
            $comision = trim($data[5]);
            $cursados = trim($data[6]);

            // Procesar campo Docente para separar información
            // Insertar datos en la base de datos
            $stmt->bindParam(':responsabilidad_academica', $responsabilidad_academica);
            $stmt->bindParam(':propuesta_formativa', $propuesta_formativa);
            $stmt->bindParam(':periodo', $periodo);
            $stmt->bindParam(':actividad', $actividad);
            $stmt->bindParam(':docente', $docente);
            $stmt->bindParam(':comision', $comision);
            $stmt->bindParam(':cursados', $cursados);

            //KOZAK Ana María, DNI, 20407462 - 
            //PINTOS SANCHEZ Roberto Esteban, DNI, 92477446 -
            //SANTACATTERINA Martin Pablo, DNI, 31309858
            $d = explode('-', $docente);

            foreach ($d as $key => $value) {
                $f = explode(',', $value);

                $cd=1;
                foreach ($f as $fk => $fv) {
                    echo "***************: $cd";
                    if ($fk == 0) {
                        $param = ":ape_nom{$cd}_Guarani";
                    }
                    if ($fk == 1) {
                        $param = ":tipo_doc{$cd}_Guarani";
                    }
                    if ($fk == 2) {
                        $param = ":num_doc{$cd}_Guarani";
                    }

                    $valorCampo= trim($fv);
                    $stmt->bindParam($param , $valorCampo);
                    $cd++;
                }
                for ($i = $cd; $i <= 4; $i++) {
                    $vacio='';
                    $stmt->bindParam(":ape_nom{$i}_Guarani", $vacio);
                    $stmt->bindParam(":tipo_doc{$i}_Guarani", $vacio);
                    $stmt->bindParam(":num_doc{$i}_Guarani", $vacio);
                }    
            }
            



            $stmt->execute();
            $importedCount++;

        }

        fclose($handle);
        
        echo "\nImportación completada con éxito.\n";
        echo "Total de filas en CSV: " . ($rowCount - $skipRows) . "\n";
        echo "Total de registros importados: $importedCount\n";

        //--- corrigir caracteres en campo "Docente" ---    
        //$correctDocente = "UPDATE public.doc_de_guarani SET Docente_guarani = REPLACE(Docente_guarani, '.-, ', '') WHERE Docente_guarani LIKE '.-, %';";
        //$stmt = $conn->query($correctDocente);
    } else {
        echo "Error: No se pudo abrir el archivo CSV.\n";
    }

} catch (PDOException $e) {
    echo "Error de base de datos: " . $e->getMessage() . "\n";
} catch (Exception $e) {
    echo "Error general: " . $e->getMessage() . "\n";
}

