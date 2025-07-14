<?php
try {
           
            // Actualizar registro en la base de datos
            $updateSql = "INSERT INTO public.doc_de_guarani ( propuesta_formativa_guarani, 
             comision_guarani, actividad_guarani, docente_guarani, cursados_guarani, ape_nom1_guarani, tipo_doc1_guarani, num_doc1_guarani, ape_nom2_guarani, tipo_doc2_guarani, num_doc2_guarani, dedicacion) 
             VALUES ( 
             propuesta_formativa_guarani, comision_guarani, actividad_guarani, docente_guarani, cursados_guarani, ape_nom1_guarani, tipo_doc1_guarani, num_doc1_guarani, ape_nom2_guarani, tipo_doc2_guarani, num_doc2_guarani, dedicacion
             )
        SELECT 
            propuesta_formativa_guarani, comision_guarani, actividad_guarani, docente_guarani, cursados_guarani, ape_nom2_guarani AS ape_nom1_guarani, tipo_doc2_guarani AS tipo_doc1_guarani, num_doc2_guarani AS num_doc1_guarani, NULL AS ape_nom2_guarani,
            NULL AS tipo_doc2_guarani, NULL AS num_doc2_guarani, dedicacion
        FROM public.doc_de_guarani
        WHERE ape_nom2_guarani IS NOT NULL AND ape_nom2_guarani <> '' AND (tipo_doc2_guarani IS NOT NULL AND tipo_doc2_guarani <> '')
            AND (num_doc2_guarani IS NOT NULL AND num_doc2_guarani <> '');";


            $updateStmt = $conn->prepare($updateSql);
            $updateStmt->execute();
            echo "Reinsertando registros registros21 \n";


        $limpiaSql = "UPDATE public.doc_de_guarani SET docente_guarani ='', ape_nom2_guarani ='', tipo_doc2_guarani ='',   num_doc2_guarani =''";
        $updateStmt = $conn->prepare($limpiaSql);
        $updateStmt->execute();
        echo "Limpiando campos de segundo docente \n";


        $contandoSql = "select count(*) from public.doc_de_guarani";
        $updateStmt = $conn->prepare($contandoSql);
        $updateStmt->execute();
        $totalRegistros = $updateStmt->fetchColumn();
        echo "Total de registros: $totalRegistros\n";



} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}
