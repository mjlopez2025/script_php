<?php


try {
    // Consulta para obtener registros donde el campo Docente contiene múltiples docentes
    $sql = "SELECT propuesta_formativa_guarani, comision_guarani, docente_guarani FROM public.doc_de_guarani WHERE docente_guarani LIKE '%,%'";
    $stmt = $conn->query($sql);
    
    // Procesar cada registro
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $docenteStr = $row['docente_guarani'];
        $propuestaFormativa = $row['propuesta_formativa_guarani'];
        $comision = $row['comision_guarani'];
        
        // Separar los docentes (formato: "Apellido Nombre, TipoDoc, NumDoc - Apellido Nombre, TipoDoc, NumDoc")
        $docentes = explode(' - ', $docenteStr);
        
        if (count($docentes) >= 1) {
            // Procesar primer docente
            $docente1 = explode(', ', trim($docentes[0]));
            $ape_nom1 = trim($docente1[0]);
            $tipo_doc1 = isset($docente1[1]) ? trim($docente1[1]) : '';
            $num_doc1 = isset($docente1[2]) ? trim($docente1[2]) : '';
            
            // Inicializar variables para segundo docente
            $ape_nom2 = '';
            $tipo_doc2 = '';
            $num_doc2 = '';
            
            // Procesar segundo docente si existe
            if (count($docentes) >= 2) {
                $docente2 = explode(', ', trim($docentes[1]));
                $ape_nom2 = trim($docente2[0]);
                $tipo_doc2 = isset($docente2[1]) ? trim($docente2[1]) : '';
                $num_doc2 = isset($docente2[2]) ? trim($docente2[2]) : '';
            }
            
            // Actualizar registro en la base de datos
            $updateSql = "UPDATE public.doc_de_guarani 
                          SET ape_nom1_guarani = :ape_nom1, 
                              tipo_doc1_guarani = :tipo_doc1, 
                              num_doc1_guarani = :num_doc1,
                              ape_nom2_guarani = :ape_nom2, 
                              tipo_doc2_guarani = :tipo_doc2, 
                              num_doc2_guarani = :num_doc2
                          WHERE propuesta_formativa_guarani = :propuesta AND comision_guarani = :comision";
            
            $updateStmt = $conn->prepare($updateSql);
            $updateStmt->bindParam(':ape_nom1', $ape_nom1);
            $updateStmt->bindParam(':tipo_doc1', $tipo_doc1);
            $updateStmt->bindParam(':num_doc1', $num_doc1);
            $updateStmt->bindParam(':ape_nom2', $ape_nom2);
            $updateStmt->bindParam(':tipo_doc2', $tipo_doc2);
            $updateStmt->bindParam(':num_doc2', $num_doc2);
            $updateStmt->bindParam(':propuesta', $propuestaFormativa);
            $updateStmt->bindParam(':comision', $comision);
            
            $updateStmt->execute();
            
        }
    }
    
    echo "Proceso completado con éxito.\n";
    
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}
