<?php

    // Script para crear tabla combinada, poblar con datos del JOIN y limpiar duplicados exactos

    $createCombinedTable = "
    CREATE TABLE IF NOT EXISTS docentes_combinados (
        id SERIAL PRIMARY KEY,
        apellido_nombre_mapuche VARCHAR(255) NOT NULL,
        nro_documento_mapuche VARCHAR(20) NOT NULL,
        categoria_mapuche VARCHAR(100),
        nro_cargo_mapuche INT NOT NULL,  -- Nuevo campo
        dedicacion_mapuche VARCHAR(50),
        estado_mapuche VARCHAR(50),
        responsabilidad_academica_guarani VARCHAR(500),
        Propuesta_Formativa_Guarani VARCHAR(500),
        Periodo_Guarani VARCHAR(500),
        Actividad_Guarani VARCHAR(500),
        transversales VARCHAR(500),
        Codigo_Actividad_Guarani VARCHAR(100),  -- Nuevo campo
        Comision_Guarani VARCHAR(500),
        Cursados_Guarani VARCHAR(500)
    );";

    $truncateTable = "TRUNCATE TABLE docentes_combinados;";

   // 2. Script para insertar datos
$insertCombinedData = "
    INSERT INTO docentes_combinados (
        apellido_nombre_mapuche,
        nro_documento_mapuche,
        categoria_mapuche,
        nro_cargo_mapuche,
        dedicacion_mapuche,
        estado_mapuche,
        responsabilidad_academica_guarani,
        Propuesta_Formativa_Guarani,
        Periodo_Guarani,
        Actividad_Guarani,
        Codigo_Actividad_Guarani,
        Comision_Guarani,
        Cursados_Guarani,
        transversales
    )
    SELECT 
        d.apellido_nombre,
        d.nro_documento,
        d.categoria,
        d.nro_cargo,
        d.dedicacion,
        d.estado,
        COALESCE(g.responsabilidad_academica_guarani, 'Sin Informacion'),
        COALESCE(g.Propuesta_Formativa_Guarani, 'Sin Informacion'),
        COALESCE(g.Periodo_Guarani, 'Sin Informacion'),
        COALESCE(g.Actividad_Guarani, 'Sin Informacion'),
        COALESCE(g.Codigo_Actividad_Guarani, 'Sin Informacion'),
        COALESCE(g.Comision_Guarani, 'Sin Informacion'),
        COALESCE(g.Cursados_Guarani, 'Sin Informacion'),
        CASE 
            WHEN g.Codigo_Actividad_Guarani IN (
                'TSC01','TSC02','TSC03','TSC04','TSC05',
                'CA217','AB403','AB406','CA135','CA705',
                'CS004','CS016','CS02072','CS216','CS239','CS244',
                'IDIV1','INFV1','PT132','PT203','PT208','PT605','PT616',
                'PT703','PT721','SA256','SA257','SA304','SA312','SA406',
                'SA412','SA505','SA515','TA115','TA124','TRI01','TRL01'
            ) THEN 'Transversal'
            ELSE NULL
        END AS transversales
    FROM 
        docentes d
    LEFT JOIN 
        public.doc_de_guarani g ON d.nro_documento = g.docente_dni_guarani
    ORDER BY 
        CASE WHEN g.docente_dni_guarani IS NULL THEN 1 ELSE 0 END,
        d.apellido_nombre;
";

    // --- NUEVAS CONSULTAS ---
    $checkDuplicates = "
    SELECT 
        COUNT(*) as total_duplicates
    FROM (
        SELECT 
            apellido_nombre_mapuche,
            nro_documento_mapuche,
            categoria_mapuche,
            nro_cargo_mapuche,  -- Incluido en la verificación de duplicados
            dedicacion_mapuche,
            estado_mapuche,
            responsabilidad_academica_guarani,
            Propuesta_Formativa_Guarani,
            Periodo_Guarani,
            Actividad_Guarani,
            Codigo_Actividad_Guarani,  -- Incluido en la verificación de duplicados
            Comision_Guarani,
            Cursados_Guarani
        FROM docentes_combinados
        GROUP BY 
            apellido_nombre_mapuche,
            nro_documento_mapuche,
            categoria_mapuche,
            nro_cargo_mapuche,
            dedicacion_mapuche,
            estado_mapuche,
            responsabilidad_academica_guarani,
            Propuesta_Formativa_Guarani,
            Periodo_Guarani,
            Actividad_Guarani,
            Codigo_Actividad_Guarani,
            Comision_Guarani,
            Cursados_Guarani
        HAVING COUNT(*) > 1
    ) AS duplicates;
    ";

    $deleteDuplicates = "
    DELETE FROM docentes_combinados
    WHERE id NOT IN (
        SELECT MIN(id)
        FROM docentes_combinados
        GROUP BY 
            apellido_nombre_mapuche,
            nro_documento_mapuche,
            categoria_mapuche,
            nro_cargo_mapuche,
            dedicacion_mapuche,
            estado_mapuche,
            responsabilidad_academica_guarani,
            Propuesta_Formativa_Guarani,
            Periodo_Guarani,
            Actividad_Guarani,
            Codigo_Actividad_Guarani,
            Comision_Guarani,
            Cursados_Guarani
    );
    ";

    $countQuery = "SELECT COUNT(*) AS total FROM docentes_combinados;";

    try {
        // Paso 1: Crear/verificar tabla
        $conn->exec($createCombinedTable);
        echo "✅ Tabla docentes_combinados creada/verificada.\n";

        // Paso 2: Limpiar tabla si existía
        $conn->exec($truncateTable);
        echo "✅ Tabla truncada (datos antiguos eliminados).\n";

        // Paso 3: Insertar datos combinados
        $conn->exec($insertCombinedData);
        echo "✅ Datos combinados insertados.\n";

        // Paso 4: Verificar duplicados (ANTES de borrar)
        $stmt = $conn->query($checkDuplicates);
        $duplicates = $stmt->fetch(PDO::FETCH_ASSOC);
        echo "🔍 Registros duplicados encontrados: " . $duplicates['total_duplicates'] . "\n";

        // Paso 5: Eliminar duplicados (solo si existen)
        if ($duplicates['total_duplicates'] > 0) {
            $conn->exec($deleteDuplicates);
            echo "🧹 Duplicados eliminados.\n";
        } else {
            echo "👌 No hubo duplicados para eliminar.\n";
        }

        // Paso 6: Contar registros finales
        $stmt = $conn->query($countQuery);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        echo "📊 Total de registros FINALES en docentes_combinados: " . $result['total'] . "\n";

    } catch(PDOException $e) {
        echo "❌ Error en paso7: " . $e->getMessage() . "\n";
    }
?>