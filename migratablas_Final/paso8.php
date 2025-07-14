<?php

// Configuración del archivo de salida
$archivo_xlsx = 'exportacion_docentes_combinados.csv'; // Exporta como XLSX que podés abrir con Excel

try {
    echo "--------------------------------\n";
    echo "📤 Iniciando exportación a XLSX...\n";
    
    // Consulta para obtener los datos de la tabla docentes_combinados
    $query = "
        SELECT 
            apellido_nombre_mapuche AS \"Apellido y Nombre (Mapuche)\",
            nro_documento_mapuche AS \"DNI (Mapuche)\",
            categoria_mapuche AS \"Categoría (Mapuche)\",
            nro_cargo_mapuche AS \"N° Cargo (Mapuche)\",  -- Nuevo campo
            dedicacion_mapuche AS \"Dedicación (Mapuche)\",
            estado_mapuche AS \"Estado (Mapuche)\",
            responsabilidad_academica_guarani AS \"Responsabilidad Académica (Guaraní)\",
            Propuesta_Formativa_Guarani AS \"Propuesta Formativa (Guaraní)\",
            Periodo_Guarani AS \"Periodo (Guaraní)\",
            Actividad_Guarani AS \"Actividad (Guaraní)\",
            transversales AS \"Transversales\",
            Codigo_Actividad_Guarani AS \"Código Actividad (Guaraní)\",  -- Nuevo campo
            Comision_Guarani AS \"Comisión (Guaraní)\",
            Cursados_Guarani AS \"Cursando (Guaraní)\"
        FROM 
            docentes_combinados
    ";
    
    // Verificamos que exista la conexión
    global $conn;
    $stmt = $conn->query($query);
    
    // Abrir archivo CSV para escritura
    $file = fopen($archivo_xlsx, 'w');
    
    // Escribir encabezados (mismo orden que la SELECT)
    $encabezados = [
        'Apellido y Nombre (Mapuche)',
        'DNI (Mapuche)',
        'Categoría (Mapuche)',
        'N° Cargo (Mapuche)',  // Nuevo encabezado
        'Dedicación (Mapuche)',
        'Estado (Mapuche)',
        'Responsabilidad Académica (Guaraní)',
        'Propuesta Formativa (Guaraní)',
        'Periodo (Guaraní)',
        'Actividad (Guaraní)',
        'Transversales',
        'Código Actividad (Guaraní)',  // Nuevo encabezado
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
    echo "📄 Archivo generado: $archivo_xlsx\n";
    echo "📊 Total de registros exportados: $contador\n";
    
} catch (PDOException $e) {
    echo "❌ Error al exportar: " . $e->getMessage() . "\n";
    if (isset($file)) {
        fclose($file);
        unlink($archivo_xlsx); // Borra archivo parcial si hubo error
    }
}
?>