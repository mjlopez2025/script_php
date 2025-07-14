<?php
// Configuración del archivo CSV
define('DELIMITER', '|'); // El archivo usa | como separador
$skipRows = 1; // Saltar las primeras 2 filas (cabeceras)

try {
    echo $LINES;
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Preparar consulta SQL
    $sql = "INSERT INTO public.doc_de_mapuche (
                Apellido_y_Nombre, 
                Nro_de_Documento, 
                Dedicacion
            ) VALUES (
                :apellido_y_nombre, 
                :nro_documento, 
                :dedicacion
            )";

    $stmt = $conn->prepare($sql);

    // Abrir archivo CSV
    if (($handle = fopen(CSV_FILE_MAPUCHE, "r")) !== FALSE) {
        $rowCount = 0;
        $importedCount = 0;
        $errorCount = 0;

        echo "Iniciando importación desde ".CSV_FILE_MAPUCHE."...\n";

        while (($line = fgets($handle)) !== FALSE) {
            $rowCount++;
            
            // Saltar las primeras filas (cabeceras)
            if ($rowCount <= $skipRows) {
                continue;
            }
            
            // Separar manualmente por el delimitador |
            $data = explode('|', $line);
            
            // Eliminar saltos de línea y espacios en blanco
            $data = array_map('trim', $data);
            
            // Verificar que la fila tenga suficientes columnas
            if (count($data) < 3) {
                echo "Advertencia: Fila $rowCount no tiene suficientes columnas. Se omite.\n";
                $errorCount++;
                continue;
            }
            
            // Obtener datos
            $Apellido_y_Nombre = $data[0];
            $Nro_Documento = $data[1];
            $Dedicacion = $data[2];

        
            // Validar datos obligatorios
            if (empty($Apellido_y_Nombre) || empty($Nro_Documento)) {
                echo "Advertencia: Fila $rowCount tiene datos faltantes. Se omite.\n";
                $errorCount++;
                continue;
            }

            try {
                // Insertar datos en la base de datos
                $stmt->bindParam(':apellido_y_nombre', $Apellido_y_Nombre);
                $stmt->bindParam(':nro_documento', $Nro_Documento);
                $stmt->bindParam(':dedicacion', $Dedicacion);

                $stmt->execute();
                $importedCount++;
                
            } catch (PDOException $e) {
                echo "Error al insertar fila $rowCount: " . $e->getMessage() . "\n";
                $errorCount++;
            }
        }

        fclose($handle);
        
        // Resumen final
        echo "\nResultado de la importación:\n";
        echo "- Filas totales en CSV: " . ($rowCount - $skipRows) . "\n";
        echo "- Registros importados exitosamente: $importedCount\n";
        echo "- Registros con errores: $errorCount\n";

    } else {
        echo "Error: No se pudo abrir el archivo CSV.\n";
    }

} catch (PDOException $e) {
    echo "Error de base de datos: " . $e->getMessage() . "\n";
} catch (Exception $e) {
    echo "Error general: " . $e->getMessage() . "\n";
}