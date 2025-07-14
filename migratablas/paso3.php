<?php

try {
    echo "Starting process to duplicate records with second teachers...\n";
   
    // SQL query to duplicate records with second teachers
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
                num_doc2_Guarani
            )
            SELECT 
                responsabilidad_academica_guarani, 
                propuesta_formativa_guarani, 
                periodo_guarani, 
                actividad_guarani, 
                docente_guarani, 
                comision_guarani, 
                cursados_guarani,
                ape_nom2_Guarani AS ape_nom1_Guarani,
                tipo_doc2_Guarani AS tipo_doc1_Guarani, 
                num_doc2_Guarani AS num_doc1_Guarani, 
                NULL AS ape_nom2_Guarani,
                NULL AS tipo_doc2_Guarani, 
                NULL AS num_doc2_Guarani
            FROM 
                public.doc_de_guarani
            WHERE 
                ape_nom2_Guarani IS NOT NULL 
                AND ape_nom2_Guarani <> ''
                AND (tipo_doc2_Guarani IS NOT NULL AND tipo_doc2_Guarani <> '')
                AND (num_doc2_Guarani IS NOT NULL AND num_doc2_Guarani <> '')";

    // Execute the query
    $affectedRows = $conn->exec($sql);    
    echo "Operacion exitosa. \n";
    echo "Registros procesados: " . $affectedRows . "\n";


    echo "Limpiando columas para el segundo docente.\n";
    $sql = "UPDATE public.doc_de_guarani SET ape_nom2_Guarani ='', tipo_doc2_Guarani ='', num_doc2_Guarani ='', Docente_Guarani =''
            WHERE ape_nom2_Guarani IS NOT NULL AND ape_nom2_Guarani <> ''
                AND (tipo_doc2_Guarani IS NOT NULL AND tipo_doc2_Guarani <> '')
                AND (num_doc2_Guarani IS NOT NULL AND num_doc2_Guarani <> '')";
    $affectedRows = $conn->exec($sql);
    echo "Registros procesados: " . $affectedRows . "\n";


} catch (PDOException $e) {
    echo "Database Error: " . $e->getMessage() . "\n";
} catch (Exception $e) {
    echo "General Error: " . $e->getMessage() . "\n";
}
