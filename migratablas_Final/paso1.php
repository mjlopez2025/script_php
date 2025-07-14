<?php



///KOZAK Ana María, DNI, 20407462 - 
// PINTOS SANCHEZ Roberto Esteban, DNI, 92477446 -
// SANTACATTERINA Martin Pablo, DNI, 31309858
//echo "Limpiando campos de segundo docente \n";
//$updateStmt = $conn->prepare("TRUNCATE TABLE public.doc_de_guarani");
//$updateStmt->execute();

// $updateStmt = $conn->prepare("DROP TABLE public.doc_de_guarani");
// $updateStmt->execute();

// $updateStmt = $conn->prepare("DROP TABLE public.doc_de_mapuche");
// $updateStmt->execute();
try {
    echo $LINES;
    echo "Verificando existencia de la tabla doc_de_guarani... y la tabla doc_de_mapuche...\n";

    $checkTable = "SELECT EXISTS (SELECT 1 FROM information_schema.tables WHERE table_schema = 'public' AND table_name = 'doc_de_guarani')";
    $checkTableMapuche = "SELECT EXISTS (SELECT 1 FROM information_schema.tables WHERE table_schema = 'public' AND table_name = 'doc_de_mapuche')";

    $stmt = $conn->query($checkTable);
    $stmtMapuche = $conn->query($checkTableMapuche);
    $tableExists = $stmt->fetchColumn();
    $tableExistsMapuche = $stmtMapuche->fetchColumn();

    if (!$tableExists) {
        echo "La tabla no existe. Creando tabla doc_de_guarani...\n";
        echo "La tabla doc_de_mapuche no existe. Creando tabla doc_de_mapuche...\n";
        
        // Crear tabla doc_de_guarani con campo de código de actividad
        $createTable = "CREATE TABLE public.doc_de_guarani (
                        responsabilidad_academica_guarani varchar(500) NULL,
                        Propuesta_Formativa_Guarani varchar(500) NULL,
                        Periodo_Guarani varchar(500) NULL,
                        Actividad_Guarani varchar(500) NULL,
                        Codigo_Actividad_Guarani varchar(100) NULL,  -- Nuevo campo agregado
                        Docente_Guarani varchar(500) NULL,
                        docente_dni_guarani varchar(50) NULL,
                        Comision_Guarani varchar(500) NULL,
                        Cursados_Guarani varchar(500) NULL,
                        ape_nom1_Guarani varchar(500) NULL,
                        tipo_doc1_Guarani varchar(500) NULL,
                        num_doc1_Guarani varchar(500) NULL,
                        ape_nom2_Guarani varchar(500) NULL,
                        tipo_doc2_Guarani varchar(500) NULL,
                        num_doc2_Guarani varchar(500) NULL,
                        ape_nom3_Guarani varchar(500) NULL,
                        tipo_doc3_Guarani varchar(500) NULL,
                        num_doc3_Guarani varchar(500) NULL,
                        ape_nom4_Guarani varchar(500) NULL,
                        tipo_doc4_Guarani varchar(500) NULL,
                        num_doc4_Guarani varchar(500) NULL
                       )";
        
        $conn->exec($createTable);
        echo "Tabla doc_de_guarani creada exitosamente.\n";
    } else {
        echo "La tabla doc_de_guarani ya existe. No se realizaron cambios.\n";
    }

} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}
