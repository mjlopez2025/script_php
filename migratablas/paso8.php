<?php

// Configuración del archivo CSV de salida
$archivo_xlsx = 'exportacion_docentes_guarani.xlsx';

try {
    echo "--------------------------------\n";
    echo "📤 Iniciando exportación a XLSX...\n";
    
    // Consulta para obtener los datos con los nombres de columnas que necesitas
    $query = "
        SELECT 
            \"responsabilidadacademica\" AS \"Responsabilidad Academica de Guarani\",
            \"propuestaformativa\" AS \"Propuesta Formativa de Guarani\",
            \"Periodo\" AS \"Periodo de Guarani\",
            \"actividad\" AS \"Actividad de Guarani\",
            \"ape_nom1\" AS \"Apellido y Nombre de Guarani\",
            \"tipo_doc1\" AS \"Tipo de Doc. de Guarani\",
            \"num_doc1\" AS \"Num de Doc. de Guarani\",
            \"cursados\" AS \"Cursando de Guarani\",
            \"comisión\" AS \"Comisión de Guarani\",
            
        FROM 
            doc_de_guarani
    ";
    
    $stmt = $conn->query($query);
    
    // Abrir archivo CSV para escritura
    $file = fopen($archivo_xlsx, 'w');
    
    // Escribir encabezados
    $encabezados = [
        'Responsabilidad Academica de Guarani',
        'Propuesta Formativa de Guarani',
        'Periodo de Guarani',
        'Actividad de Guarani',
        'Apellido y Nombre de Guarani',
        'Tipo de Doc. de Guarani',
        'Num de Doc. de Guarani',
        'Comisión de Guarani',
        'Cursando de Guarani'
    ];
    fputcsv($file, $encabezados);
    
    // Escribir datos
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
        // Opcional: eliminar archivo parcial si hubo error
        unlink($archivo_xlsx);
    }
}
?>