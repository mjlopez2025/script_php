<?php

try {
    echo $LINES;
    echo "******************************************************************\n";
    echo "*Verificando existencia de la tabla docentes_mapuche...\n*";
    echo "******************************************************************\n";

    $checkTable = "SELECT EXISTS (SELECT 1 FROM information_schema.tables WHERE table_schema = 'public' AND table_name = 'docentes_mapuche')";


    $stmt = $conn->query($checkTable);
    $tableExists = $stmt->fetchColumn();


    if (!$tableExists) {
        echo "La tabla no existe. Creando tabla docentes_mapuche...\n";


        // Crear tabla docentes_guarani con campo de código de actividad
        $createTable =" CREATE TABLE IF NOT EXISTS docentes_mapuche (
    id SERIAL PRIMARY KEY,
    apellido_nombre_mapuche VARCHAR(255),
    dni_mapuche VARCHAR(20),
    categoria_mapuche VARCHAR(100),
    nro_cargo_mapuche INTEGER,
    dedicacion_mapuche VARCHAR(50),
    estado_mapuche VARCHAR(255),
    dependencia_designada_mapuche VARCHAR(255),
    junio_2025_Legajos VARCHAR(255)
);";
        
        $conn->exec($createTable);
        echo "Tabla docentes_mapuche creada exitosamente.\n";
    } else {
        echo "La tabla docentes_mapuche ya existe. No se realizaron cambios.\n";
    }

} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}
