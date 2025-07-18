<?php

// Tu consulta SQL
$sql = "
WITH sql_olap_ft AS (
    SELECT 
        ft_rendimiento.responsable_academica_id, 
        ft_rendimiento.propuesta_formativa_id, 
        ft_rendimiento.comision_id, 
        ft_rendimiento.periodo_id, 
        ft_rendimiento.docente_id, 
        ft_rendimiento.actividad_id, 
        d_anio.anio_id, 
        sum(ft_rendimiento.cantidad_registros_cursada) AS medida_cantidad_registros_cursada 
    FROM (SELECT * FROM guarani3.ft_rendimiento) ft_rendimiento 
    INNER JOIN public.d_anio ON d_anio.anio_id = ft_rendimiento.anio_academico 
    WHERE (d_anio.anio_id = '2024') AND (ft_rendimiento.cantidad_registros_cursada is not null) 
    GROUP BY 
        ft_rendimiento.responsable_academica_id,
        ft_rendimiento.propuesta_formativa_id,
        ft_rendimiento.comision_id,
        ft_rendimiento.periodo_id,
        ft_rendimiento.docente_id,
        ft_rendimiento.actividad_id,
        d_anio.anio_id 
    ORDER BY 
        ft_rendimiento.responsable_academica_id,
        ft_rendimiento.propuesta_formativa_id,
        ft_rendimiento.comision_id,
        ft_rendimiento.periodo_id,
        ft_rendimiento.docente_id,
        ft_rendimiento.actividad_id,
        d_anio.anio_id
) 
SELECT 
    d_responsable_academica.responsable_academica_desc, 
    d_propuesta_formativa.propuesta_formativa_desc, 
    d_comision.comision_desc, 
    v_periodo_anio_academico_x_tipo_periodo.periodo_desc, 
    d_docente.docente_desc, 
    d_actividad.actividad_desc, 
    sql_olap_ft.anio_id, 
    sum(sql_olap_ft.medida_cantidad_registros_cursada) AS cantidad_registros_cursada 
FROM sql_olap_ft 
INNER JOIN guarani3.d_responsable_academica d_responsable_academica ON d_responsable_academica.responsable_academica_id = sql_olap_ft.responsable_academica_id 
INNER JOIN guarani3.d_propuesta_formativa d_propuesta_formativa ON d_propuesta_formativa.propuesta_formativa_id = sql_olap_ft.propuesta_formativa_id 
INNER JOIN guarani3.d_comision d_comision ON d_comision.comision_id = sql_olap_ft.comision_id 
INNER JOIN guarani3.v_periodo_anio_academico_x_tipo_periodo v_periodo_anio_academico_x_tipo_periodo ON v_periodo_anio_academico_x_tipo_periodo.periodo_id = sql_olap_ft.periodo_id 
INNER JOIN guarani3.d_docente d_docente ON d_docente.docente_id = sql_olap_ft.docente_id 
INNER JOIN guarani3.d_actividad d_actividad ON d_actividad.actividad_id = sql_olap_ft.actividad_id 
WHERE (true) 
GROUP BY 
    d_responsable_academica.responsable_academica_desc,
    d_propuesta_formativa.propuesta_formativa_desc,
    d_comision.comision_desc,
    v_periodo_anio_academico_x_tipo_periodo.periodo_desc,
    d_docente.docente_desc,
    d_actividad.actividad_desc,
    sql_olap_ft.anio_id 
ORDER BY 
    d_responsable_academica.responsable_academica_desc,
    d_propuesta_formativa.propuesta_formativa_desc,
    d_comision.comision_desc,
    v_periodo_anio_academico_x_tipo_periodo.periodo_desc,
    d_docente.docente_desc,
    d_actividad.actividad_desc,
    sql_olap_ft.anio_id
";

try {
    // Crear conexión
    $conn = new PDO(
        "pgsql:host={$config_wichi['host']};port={$config_wichi['port']};dbname={$config_wichi['dbname']}",
        $config_wichi['user'],
        $config_wichi['password']
    );
    
    // Configurar el modo de error para que lance excepciones
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Ejecutar consulta
    $stmt = $conn->prepare($sql);
    $stmt->execute();
    
    // Obtener todos los resultados como array asociativo
    $resultados = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Mostrar información básica
    echo "=====================================\n";
    echo "Consulta ejecutada con éxito\n";
    echo "Total de registros obtenidos: " . count($resultados) . "\n";
    echo "=====================================\n\n";
    
    // Mostrar los primeros 3 registros con formato
    if(count($resultados) > 0) {
        echo "=== PRIMEROS 3 REGISTROS ===\n";
        
        for($i = 0; $i < min(3, count($resultados)); $i++) {
            echo "Registro #" . ($i+1) . ":\n";
            echo "----------------------------\n";
            
            foreach($resultados[$i] as $key => $value) {
                echo "- " . $key . ": " . (strlen($value) > 50 ? substr($value, 0, 50) . "..." : $value) . "\n";
            }
            
            echo "\n";
        }
    } else {
        echo "La consulta no devolvió ningún registro.\n";
    }
    
} catch(PDOException $e) {
    echo "Error de conexión: " . $e->getMessage();
}

?>