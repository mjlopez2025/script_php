<?php

// Configuración del archivo de salida
$archivo_csv = 'nueva_tabla.csv'; // Exporta como CSV

try {
    echo "--------------------------------\n";
    echo "📤 Iniciando exportación a CSV...\n";
    
    // 1. Primero verificamos y contamos los duplicados exactos
    $duplicatesQuery = "
        SELECT COUNT(*) - COUNT(DISTINCT (
            apellido_nombre_mapuche,
            dni_mapuche,
            categoria_mapuche,
            nro_cargo_mapuche,
            dedicacion_mapuche,
            estado_mapuche,
            dependencia_designada_mapuche,
            responsabilidad_academica_guarani,
            propuesta_formativa_guarani,
            periodo_guarani,
            actividad_guarani,
            transversales,
            codigo_actividad_guarani,
            comision_guarani,
            cursando_guarani
        )) AS total_duplicados
        FROM nueva_tabla
    ";
    
    $duplicatesCount = $conn->query($duplicatesQuery)->fetchColumn();
    echo "🔍 Registros duplicados encontrados: $duplicatesCount\n";
    
    // 2. Eliminamos los duplicados exactos (conservando solo una copia)
    if ($duplicatesCount > 0) {
        $deleteDuplicates = "
            DELETE FROM nueva_tabla
            WHERE id NOT IN (
                SELECT MIN(id)
                FROM nueva_tabla
                GROUP BY 
                    apellido_nombre_mapuche,
                    dni_mapuche,
                    categoria_mapuche,
                    nro_cargo_mapuche,
                    dedicacion_mapuche,
                    estado_mapuche,
                    dependencia_designada_mapuche,
                    responsabilidad_academica_guarani,
                    propuesta_formativa_guarani,
                    periodo_guarani,
                    actividad_guarani,
                    transversales,
                    codigo_actividad_guarani,
                    comision_guarani,
                    cursando_guarani
            )
        ";
        
        $conn->exec($deleteDuplicates);
        echo "♻️ $duplicatesCount registros duplicados eliminados (conservando 1 copia de cada)\n";
    } else {
        echo "✅ No se encontraron registros duplicados exactos\n";
    }
    
    // 3. Consulta para obtener los datos limpios
    $query = "
        SELECT 
            apellido_nombre_mapuche AS \"Apellido y Nombre (Mapuche)\",
            dni_mapuche AS \"DNI (Mapuche)\",
            categoria_mapuche AS \"Categoría (Mapuche)\",
            nro_cargo_mapuche AS \"N° Cargo (Mapuche)\",
            dedicacion_mapuche AS \"Dedicación (Mapuche)\",
            estado_mapuche AS \"Estado (Mapuche)\",
            dependencia_designada_mapuche AS \"Dependencia Designada (Mapuche)\",
            responsabilidad_academica_guarani AS \"Responsabilidad Académica (Guaraní)\",
            propuesta_formativa_guarani AS \"Propuesta Formativa (Guaraní)\",
            periodo_guarani AS \"Periodo (Guaraní)\",
            actividad_guarani AS \"Actividad (Guaraní)\",
            transversales AS \"Transversales\",
            codigo_actividad_guarani AS \"Código Actividad (Guaraní)\",
            comision_guarani AS \"Comisión (Guaraní)\",
            cursando_guarani AS \"Cursando (Guaraní)\"
        FROM 
            nueva_tabla;
    ";
    
    // Verificamos que exista la conexión
    global $conn;
    $stmt = $conn->query($query);
    
    // Abrir archivo CSV para escritura
    $file = fopen($archivo_csv, 'w');
    
    // Escribir encabezados
    $encabezados = [
        'Apellido y Nombre (Mapuche)',
        'DNI (Mapuche)',
        'Categoría (Mapuche)',
        'N° Cargo (Mapuche)',
        'Dedicación (Mapuche)',
        'Estado (Mapuche)',
        'Dependencia Designada (Mapuche)',
        'Responsabilidad Académica (Guaraní)',
        'Propuesta Formativa (Guaraní)',
        'Periodo (Guaraní)',
        'Actividad (Guaraní)',
        'Transversales',
        'Código Actividad (Guaraní)',
        'Comisión (Guaraní)',
        'Cursando (Guaraní)'
    ];
    fputcsv($file, $encabezados);
    
    // Escribir datos fila por fila
    $contador = 0;
    while ($fila = $stmt->fetch(PDO::FETCH_ASSOC)) {
        fputcsv($file, $fila);
        $contador++;
    }
    
    fclose($file);
    
    echo "✅ Exportación completada con éxito!\n";
    echo "📄 Archivo generado: $archivo_csv\n";
    echo "📊 Total de registros únicos exportados: $contador\n";
    
} catch (PDOException $e) {
    echo "❌ Error al exportar: " . $e->getMessage() . "\n";
    if (isset($file)) {
        fclose($file);
        unlink($archivo_csv); // Borra archivo parcial si hubo error
    }
}
?>