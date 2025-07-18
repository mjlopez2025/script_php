<?php
// Script para crear tabla combinada y poblar con datos combinados

$createNuevaTabla = "
CREATE TABLE IF NOT EXISTS nueva_tabla (
    id SERIAL PRIMARY KEY,
    apellido_nombre_mapuche VARCHAR(255),
    dni_mapuche VARCHAR(20),
    categoria_mapuche VARCHAR(100),
    nro_cargo_mapuche VARCHAR(50),  
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

// 1. Verificar/crear la tabla destino
try {
    $conn->exec($createNuevaTabla);
    echo "✅ Tabla 'nueva_tabla' creada/verificada.\n";
    
    // Verificar si existen datos en las tablas fuente
    $countMapuche = $conn->query("SELECT COUNT(*) FROM docentes_mapuche")->fetchColumn();
    $countCombinados = $conn->query("SELECT COUNT(*) FROM docentes_combinados")->fetchColumn();
    
    echo "📊 Registros en docentes_mapuche: $countMapuche\n";
    echo "📊 Registros en docentes_combinados: $countCombinados\n";
    
    if ($countMapuche == 0 || $countCombinados == 0) {
        die("❌ Error: Una de las tablas fuente está vacía");
    }
    
} catch(PDOException $e) {
    die("❌ Error al crear tabla: " . $e->getMessage());
}

// 2. Consulta de combinación
$insertCombinedData = "
INSERT INTO nueva_tabla (
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
SELECT 
    m.apellido_nombre_mapuche, 
    m.dni_mapuche,             
    m.categoria_mapuche,       
    m.nro_cargo_mapuche,       
    m.dedicacion_mapuche,      
    m.estado_mapuche,          
    m.dependencia_designada_mapuche,
    d.responsabilidad_academica_guarani,
    d.propuesta_formativa_guarani,
    d.periodo_guarani,
    d.actividad_guarani,
    d.transversales,
    d.codigo_actividad_guarani,
    d.comision_guarani,
    d.cursando_guarani
FROM 
    docentes_mapuche AS m       
INNER JOIN                       
    docentes_combinados AS d ON TRIM(m.dni_mapuche) = TRIM(d.dni_mapuche)
";

try {
    // Limpiar tabla nueva ANTES de insertar
    $conn->exec("TRUNCATE TABLE nueva_tabla;");
    echo "✅ Tabla 'nueva_tabla' truncada.\n";
    
    // Insertar datos combinados con transacción
    $conn->beginTransaction();
    $affectedRows = $conn->exec($insertCombinedData);
    
    // Verificar si la inserción fue exitosa
    if ($affectedRows === false) {
        throw new PDOException("Error al ejecutar la inserción");
    }
    
    // Eliminar duplicados exactos
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
    )";
    
    $duplicatesRemoved = $conn->exec($deleteDuplicates);
    $conn->commit();
    
    echo "******************************************************\n";
    echo "✅ $affectedRows registros insertados en 'nueva_tabla'.\n";
    echo "♻️ $duplicatesRemoved registros duplicados y eliminados.\n";
    echo "******************************************************\n";
    
    // Verificación final
    $stmt = $conn->query("
        SELECT COUNT(*) AS total, 
               COUNT(DISTINCT dni_mapuche) AS dni_unicos 
        FROM nueva_tabla
    ");
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    
    echo "📊 Estadísticas finales:\n";
    echo "   - Total registros: " . $result['total'] . "\n";
    echo "   - DNIs únicos: " . $result['dni_unicos'] . "\n";
    

} catch(PDOException $e) {
    if (isset($conn) && $conn->inTransaction()) {
        $conn->rollBack();
    }
    echo "❌ Error en la combinación de datos: " . $e->getMessage() . "\n";
    
    $errorInfo = $conn->errorInfo();
    echo "🔧 Detalles técnicos:\n";
    print_r($errorInfo);
    
    error_log("Error en combinación: " . print_r($errorInfo, true));
}
?>