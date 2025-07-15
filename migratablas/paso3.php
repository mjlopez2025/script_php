<?php

try {
    
    // 2. Extraer año y limpiar periodo (primera pasada)
    $stmt = $conn->prepare("
        UPDATE public.Docentes_Guarani 
        SET 
            anio_guarani = SUBSTRING(periodo_guarani FROM 1 FOR 4),
            periodo_guarani = TRIM(SUBSTRING(periodo_guarani FROM 10))
        WHERE periodo_guarani LIKE '2024 - 1 - %'
    ");
    $stmt->execute();
    
    // 3. Limpiar guiones residuales (segunda pasada)
    $stmt = $conn->prepare("
        UPDATE public.Docentes_Guarani 
        SET periodo_guarani = TRIM(SUBSTRING(periodo_guarani FROM 2))
        WHERE periodo_guarani LIKE '- %'
    ");
    $stmt->execute();
    
    echo "Normalización completada con éxito.\n";
    
    // Mostrar muestra de resultados
    $sample = $conn->query("
        SELECT anio_guarani, periodo_guarani 
        FROM public.Docentes_Guarani 
        LIMIT 5
    ");
    
    echo "\nMuestra actualizada:\n";
    foreach ($sample->fetchAll(PDO::FETCH_ASSOC) as $row) {
        echo "anio_guarani: {$row['anio_guarani']} | Periodo: {$row['periodo_guarani']}\n";
    }
    
} catch (PDOException $e) {
    echo "Error de base de datos: " . $e->getMessage() . "\n";
}