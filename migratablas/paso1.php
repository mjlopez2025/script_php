<?php

// $updateStmt = $conn->prepare("TRUNCATE TABLE IF EXISTS public.doc_de_guarani");
// $updateStmt->execute();
// echo "Limpiando campos de segundo docente \n";


//  echo "Dropeando tabla \n";
//  $updateStmt = $conn->prepare("DROP TABLE public.doc_de_guarani");
//  $updateStmt->execute();

//  echo "Dropeando tabla \n";
//  $updateStmt = $conn->prepare("DROP TABLE public.doc_de_mapuche");
//  $updateStmt->execute();



try {
    echo $LINES;
    echo "Verificando existencia de la tabla doc_de_guarani... y la tabla doc_de_mapuche...\n";
    // Consulta para verificar si la tabla existe
// Consulta para verificar si la tabla existeresponsabilidad_academica_guarani
    $checkTable = "SELECT EXISTS (SELECT 1 FROM information_schema.tables WHERE table_schema = 'public' AND table_name = 'doc_de_guarani')";
    $checkTableMapuche = "SELECT EXISTS (SELECT 1 FROM information_schema.tables WHERE table_schema = 'public' AND table_name = 'doc_de_mapuche')";

    $stmt = $conn->query($checkTable);
    $stmtMapuche = $conn->query($checkTableMapuche);
    $tableExists = $stmt->fetchColumn();
    $tableExistsMapuche = $stmtMapuche->fetchColumn();

    if (!$tableExists) {
        echo "La tabla no existe. Creando tabla doc_de_guarani...\n";
        echo "La tabla doc_de_mapuche no existe. Creando tabla doc_de_mapuche...\n";
        
        // SQL para crear la tabla
        $createTable = "CREATE TABLE public.doc_de_guarani (
                        responsabilidad_academica_guarani varchar(500) NULL,
                        Propuesta_Formativa_Guarani varchar(500) NULL,
                        Periodo_Guarani varchar(500) NULL,
                        Actividad_Guarani varchar(500) NULL,
                        Docente_Guarani varchar(500) NULL,
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
        $createTableMapuche = "CREATE TABLE public.doc_de_mapuche (
                        Apellido_y_Nombre varchar(500) NULL,
                        Nro_de_Documento varchar(500) NULL
                        )";
        
        $conn->exec($createTable);
        echo "Tabla doc_de_guarani creada exitosamente.\n";
        
        $conn->exec($createTableMapuche);
        echo "Tabla doc_de_mapuche creada exitosamente.\n";

    } else {
        echo "La tabla doc_de_guarani ya existe. No se realizaron cambios.\n";
        echo "La tabla doc_de_mapuche ya existe. No se realizaron cambios.\n";
    }

} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}


