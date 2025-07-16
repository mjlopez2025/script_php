<?php
// =============================================
// SCRIPT PARA ACTUALIZAR DATOS DE DOCENTES
// =============================================
echo "\nIniciando actualización de datos de docentes...\n";

try {
    // 1. Conexión a la base de datos (asegúrate de que $conn ya está configurado)
    
    // 2. Contar registros totales a procesar
    $total_registros = $conn->query("SELECT COUNT(*) FROM Docentes_Guarani WHERE docente_guarani IS NOT NULL")->fetchColumn();
    echo "Total de registros en la tabla: $total_registros\n";
    
    $registros_a_procesar = $conn->query("SELECT COUNT(*) FROM Docentes_Guarani WHERE docente_guarani IS NOT NULL AND (tipo_documento IS NULL OR num_documento IS NULL)")->fetchColumn();
    echo "Registros a procesar: $registros_a_procesar\n";

    // 3. Seleccionar registros para procesar
    $registros = $conn->query("
        SELECT id, docente_guarani 
        FROM Docentes_Guarani 
        WHERE docente_guarani IS NOT NULL
          AND (tipo_documento IS NULL OR num_documento IS NULL)
    ");

    $procesados = 0;
    $omitidos = 0;
    $errores = 0;

    // 4. Procesar cada registro
    foreach ($registros->fetchAll(PDO::FETCH_ASSOC) as $registro) {
        $id = $registro['id'];
        $docente_raw = trim($registro['docente_guarani']);
        
        echo "\nProcesando ID $id: $docente_raw";
        
        // Patrón para extraer datos
        if (preg_match('/^([^,]+?)\s*[,|-]\s*([^,]+?)\s*[,|-]\s*([^,]+)$/', $docente_raw, $matches)) {
            $nombre_completo = trim($matches[1]);
            $tipo_documento = trim($matches[2]);
            $numero_documento = trim($matches[3]);
            
            // Mostrar datos extraídos
            echo "\nDatos extraídos:";
            echo "\n- Nombre completo: $nombre_completo";
            echo "\n- Tipo documento: $tipo_documento";
            echo "\n- Número documento: $numero_documento";
            
            try {
                // Actualizar los campos en la base de datos
                $stmt = $conn->prepare("
                    UPDATE Docentes_Guarani
                    SET 
                        docente_guarani = ?,
                        tipo_documento = ?,
                        num_documento = ?
                    WHERE id = ?
                ");
                
                $stmt->execute([
                    $nombre_completo,
                    $tipo_documento,
                    $numero_documento,
                    $id
                ]);
                
                echo "\n✅ Actualización exitosa";
                $procesados++;
                
            } catch (PDOException $e) {
                echo "\n🚨 Error al actualizar ID $id: " . $e->getMessage();
                $errores++;
            }
            
        } else {
            echo "\n⚠️ Formato no reconocido - No se actualizará";
            $omitidos++;
        }
        
        echo "\n" . str_repeat("-", 60);
    }

    // 5. Mostrar resumen final
    echo "\n\nRESUMEN FINAL:";
    echo "\n✔️ Registros procesados correctamente: $procesados";
    echo "\n⚠️ Registros omitidos (formato no reconocido): $omitidos";
    echo "\n❌ Errores en actualización: $errores";
    echo "\n🎉 Proceso completado.\n";

} catch (PDOException $e) {
    echo "\n🚨 Error en la base de datos: " . $e->getMessage() . "\n";
} catch (Exception $e) {
    echo "\n🚨 Error general: " . $e->getMessage() . "\n";
}