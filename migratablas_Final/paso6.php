<?php
$csv_file = 'Docentes_mapuche.csv';

try {
    // 1. Crear la tabla si no existe
    $sql = "CREATE TABLE IF NOT EXISTS docentes (
        apellido_nombre VARCHAR(255) NOT NULL,
        nro_documento VARCHAR(20) NOT NULL,
        categoria VARCHAR(100),
        nro_cargo INT NOT NULL,
        dedicacion VARCHAR(50),
        estado VARCHAR(50)
    )";
    $conn->exec($sql);
    echo "Tabla 'docentes' creada o verificada.<br>";

    // 2. Importar datos del CSV
    if (($handle = fopen($csv_file, "r")) !== FALSE) {
        // Saltar la primera línea (cabeceras)
        fgetcsv($handle, 1000, ",");
        
        // Preparar la sentencia SQL para inserción
        $stmt = $conn->prepare("INSERT INTO docentes 
            (apellido_nombre, nro_documento, categoria, nro_cargo, dedicacion, estado) 
            VALUES (:apellido_nombre, :nro_documento, :categoria, :nro_cargo, :dedicacion, :estado)");

        $imported = 0;
        $skipped = 0;
        
        while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
            // Verificar que la línea tiene todos los campos necesarios (7 columnas en el CSV)
            if (count($data) >= 6) { // Necesitamos al menos 6 campos (el último campo no lo usamos)
                try {
                    // Limpiar el nombre: eliminar el número entre paréntesis y los paréntesis
                    $nombreLimpio = preg_replace('/\s*\(\d+\)$/', '', $data[0]);
                    
                    $stmt->execute([
                        ':apellido_nombre' => $nombreLimpio,
                        ':nro_documento' => $data[1],
                        ':categoria' => $data[2],
                        ':nro_cargo' => (int)$data[3], // Asumiendo que nro_cargo es un entero
                        ':dedicacion' => $data[4],
                        ':estado' => $data[5]
                    ]);
                    $imported++;
                } catch (PDOException $e) {
                    $skipped++;
                    // Opcional: registrar el error
                    error_log("Error al insertar línea: " . implode(", ", $data) . " - " . $e->getMessage());
                }
            } else {
                $skipped++;
                error_log("Línea incompleta: " . implode(", ", $data));
            }
        }
        fclose($handle);
        
        echo "Importación completada: $imported registros importados, $skipped registros omitidos.<br>";
    } else {
        echo "No se pudo abrir el archivo CSV.<br>";
    }
} catch(PDOException $e) {
    echo "Error: " . $e->getMessage();
}