<?php

try {
    echo "Starting process to duplicate records with second, third and fourth teachers...\n";

    for ($i = 2; $i <= 4; $i++) {
            $sql = "INSERT INTO public.doc_de_guarani (
                responsabilidad_academica_guarani, propuesta_formativa_guarani, periodo_guarani, actividad_guarani, 
                docente_guarani, docente_dni_guarani, comision_guarani, cursados_guarani
            )
            SELECT 
                responsabilidad_academica_guarani, propuesta_formativa_guarani, periodo_guarani, actividad_guarani,
                ape_nom{$i}_Guarani AS docente_guarani, 
                num_doc{$i}_Guarani AS docente_dni_guarani, 
                comision_guarani, 
                cursados_guarani
        FROM public.doc_de_guarani
        WHERE ape_nom{$i}_Guarani IS NOT NULL AND ape_nom{$i}_Guarani <> ''AND 
              num_doc{$i}_Guarani IS NOT NULL AND num_doc{$i}_Guarani <> ''";
    $affectedRows2 = $conn->exec($sql);
    echo "Registros duplicados con segundo docente: $affectedRows2\n";
   }

    echo "Limpiando columnas para docentes secundarios...\n";
    $sqlClean = "UPDATE public.doc_de_guarani SET 
                    ape_nom2_Guarani ='', tipo_doc2_Guarani ='', num_doc2_Guarani ='', 
                    ape_nom3_Guarani ='', tipo_doc3_Guarani ='', num_doc3_Guarani ='',
                    ape_nom4_Guarani ='', tipo_doc4_Guarani ='', num_doc4_Guarani =''
                 WHERE (ape_nom2_Guarani IS NOT NULL AND ape_nom2_Guarani <> '')
                    OR (ape_nom3_Guarani IS NOT NULL AND ape_nom3_Guarani <> '')
                    OR (ape_nom4_Guarani IS NOT NULL AND ape_nom4_Guarani <> '')";

    $affectedClean = $conn->exec($sqlClean);
    echo "Registros actualizados (limpieza): $affectedClean\n";

    echo "Pasa docente 1 a columnas de docente...\n";
    $sqlClean = "UPDATE public.doc_de_guarani SET 
                docente_guarani = ape_nom1_Guarani, 
                docente_dni_guarani = num_doc1_Guarani
            WHERE (ape_nom1_Guarani IS NOT NULL AND ape_nom1_Guarani <> '') ";
    $affectedClean = $conn->exec($sqlClean);

            ///AND (num_doc1_Guarani IS NOT NULL AND num_doc1_Guarani <> '')
} catch (PDOException $e) {
    echo "Database Error: " . $e->getMessage() . "\n";
} catch (Exception $e) {
    echo "General Error: " . $e->getMessage() . "\n";
}
