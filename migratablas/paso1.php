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
    echo "Verificando existencia de la tabla Docentes_Guarani...\n";
    echo "****************************************************\n";
    // Consulta para verificar si la tabla existe
// Consulta para verificar si la tabla existeresponsabilidad_academica_guarani
    $checkTable = "SELECT EXISTS (SELECT 1 FROM information_schema.tables WHERE table_schema = 'public' AND table_name = 'Docentes_Guarani')";

    $stmt = $conn->query($checkTable);
    $tableExists = $stmt->fetchColumn();

    if (!$tableExists) {
        echo "La tabla no existe. Creando tabla Docentes_Guarani...\n";
        
        // SQL para crear la tabla
        $createTable = "CREATE TABLE public.Docentes_Guarani (
                        responsabilidad_academica_guarani varchar(500) NULL,
                        Propuesta_Formativa_Guarani varchar(500) NULL,
                        Comision_Guarani varchar(500) NULL,
                        Anio_guarani varchar (500) NULL,
                        Periodo_Guarani varchar(500) NULL,
                        Docente_Guarani varchar(500) NULL,
                        Codigo_guarani  varchar(500) NULL,
                        Actividad_Guarani varchar(500) NULL,
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
                        num_doc4_Guarani varchar(500) NULL,
                        ape_nom5_Guarani varchar(500) NULL,
                        tipo_doc5_Guarani varchar(500) NULL,
                        num_doc5_Guarani varchar(500) NULL
                       )";
        
        $conn->exec($createTable);
        echo "Tabla Docentes_Guarani creada exitosamente.\n";

    } else {
        echo "La tabla Docentes_Guarani ya existe. No se realizaron cambios.\n";
    }

} catch (PDOException $e) {
    echo "La tabla Docentes_Guarani ya existe.";
}


