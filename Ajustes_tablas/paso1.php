<?php

try {
    echo $LINES;
    echo "Verificando existencia de la tabla docentes_guarani...\n";

    $checkTable = "SELECT EXISTS (SELECT 1 FROM information_schema.tables WHERE table_schema = 'public' AND table_name = 'docentes_guarani')";


    $stmt = $conn->query($checkTable);
    $tableExists = $stmt->fetchColumn();


    if (!$tableExists) {
        echo "La tabla no existe. Creando tabla docentes_guarani...\n";


        // Crear tabla docentes_guarani con campo de código de actividad
        $createTable =" CREATE TABLE IF NOT EXISTS docentes_combinados (
    id SERIAL PRIMARY KEY,
    apellido_nombre_mapuche VARCHAR(255),
    dni_mapuche VARCHAR(20),
    categoria_mapuche VARCHAR(100),
    nro_cargo_mapuche INTEGER,
    dedicacion_mapuche VARCHAR(50),
    estado_mapuche VARCHAR(100),
    dependencia_designada_mapuche VARCHAR(255),
    responsabilidad_academica_guarani VARCHAR(255),
    propuesta_formativa_guarani VARCHAR(255),
    periodo_guarani VARCHAR(100),
    actividad_guarani VARCHAR(255),
    transversales VARCHAR(255),
    codigo_actividad_guarani VARCHAR(50),
    comision_guarani VARCHAR(50),
    cursando_guarani INTEGER
);";
        
        $conn->exec($createTable);
        echo "Tabla docentes_guarani creada exitosamente.\n";
    } else {
        echo "La tabla docentes_guarani ya existe. No se realizaron cambios.\n";
    }

} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}
