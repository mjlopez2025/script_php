<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

try {


    // 3. Verificar existencia de tablas LOCALES (opcional)
    $tablasRequeridas = ['ft_personal_cerrada', 'd_categoriascargo', 'd_persona'];
    foreach ($tablasRequeridas as $tabla) {
        $exists = $connw->query("SELECT to_regclass('{$config_wichi['esquema_principal']}.$tabla')")->fetchColumn();
        if (!$exists) {
            die("❌ Tabla requerida no encontrada: {$config_wichi['esquema_principal']}.$tabla");
        }
    }
    echo "🔍 Todas las tablas requeridas existen en el esquema {$config_wichi['esquema_principal']}\n";

    // 4. Consulta OLAP con esquemas EXPLÍCITOS
    echo "⚙️ Ejecutando consulta OLAP...\n";
    $olapQuery = "
    WITH sql_olap_ft AS (
        SELECT 
            pc.persona_id, 
            pc.categoria_id, 
            pc.nro_cargo, 
            pc.estadodelcargo_id, 
            pc.dependenciadesigncargo_id, 
            pc.anio_id, 
            pc.mes_id, 
            pc.persona_id AS medida_persona_id 
        FROM {$config_wichi['esquema_principal']}.ft_personal_cerrada pc
        INNER JOIN {$config_wichi['esquema_principal']}.d_categoriascargo cc ON cc.categoria_id = pc.categoria_id 
        WHERE (cc.escalafon_desc = 'Docente') 
        GROUP BY pc.persona_id, pc.categoria_id, pc.nro_cargo,
                pc.estadodelcargo_id, pc.dependenciadesigncargo_id,
                pc.anio_id, pc.mes_id 
    ) 
    SELECT 
        p.apellidonombre_desc, 
        p.nro_documento, 
        cc.categoria_desc, 
        sof.nro_cargo, 
        cc.dedicacion_desc, 
        ec.estadodelcargo_desc, 
        dd.dependenciadesign_desc, 
        a.anio_id, 
        m.mes_desc, 
        COUNT(DISTINCT sof.medida_persona_id) AS persona_id 
    FROM sql_olap_ft sof
    INNER JOIN {$config_wichi['esquema_principal']}.d_persona p ON p.persona_id = sof.persona_id 
    INNER JOIN {$config_wichi['esquema_principal']}.d_categoriascargo cc ON cc.categoria_id = sof.categoria_id 
    INNER JOIN {$config_wichi['esquema_principal']}.d_estadodelcargo ec ON ec.estadodelcargo_id = sof.estadodelcargo_id 
    INNER JOIN {$config_wichi['esquema_principal']}.d_dependenciadesig dd ON dd.dependenciadesign_id = sof.dependenciadesigncargo_id 
    INNER JOIN public.d_anio a ON a.anio_id = sof.anio_id 
    INNER JOIN public.d_mes m ON m.mes_id = sof.mes_id 
    WHERE (true) 
    GROUP BY p.apellidonombre_desc, p.nro_documento, cc.categoria_desc,
            sof.nro_cargo, cc.dedicacion_desc, ec.estadodelcargo_desc,
            dd.dependenciadesign_desc, a.anio_id, m.mes_desc
    ";

    $localStmt = $connw->query($olapQuery);
    $totalRegistros = 0;

    
    // 5. Preparar inserción en REMOTO
    $insertQuery = "
    INSERT INTO docentes_mapuche (
        apellidonombre_desc,
        nro_documento,
        categoria_desc,
        nro_cargo,
        dedicacion_desc,
        estadodelcargo_desc,
        dependenciadesign_desc,
        anio_id,
        mes_desc,
        persona_id
    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
    ";
    $remoteStmt = $conn->prepare($insertQuery);

    // 6. Procesamiento por lotes
    echo "⏳ Transfiriendo datos...\n";
    $conn->beginTransaction();


    $conn->query("TRUNCATE TABLE docentes_mapuche");


    while ($row = $localStmt->fetch(PDO::FETCH_ASSOC)) {
        $remoteStmt->execute([
            $row['apellidonombre_desc'],
            $row['nro_documento'],
            $row['categoria_desc'],
            $row['nro_cargo'],
            $row['dedicacion_desc'],
            $row['estadodelcargo_desc'],
            $row['dependenciadesign_desc'],
            $row['anio_id'],
            $row['mes_desc'],
            $row['persona_id']
        ]);
        $totalRegistros++;

        if ($totalRegistros % 100 == 0) {
            echo "📦 $totalRegistros registros transferidos...\n";
        }
    }

    $conn->commit();
    echo "\n✅ Transferencia completada. Total registros: $totalRegistros\n";

    // 7. Verificación
    $count = $conn->query("SELECT COUNT(*) FROM docentes_mapuche")->fetchColumn();
    echo "📊 Total registros en tabla mapuche: $count\n";

} catch (PDOException $e) {
    if (isset($conn) && $conn->inTransaction()) {
        $conn->rollBack();
    }
    echo "\n❌ Error: " . $e->getMessage() . "\n";

    
    
    // Mostrar la consulta que falló para diagnóstico
    if (isset($olapQuery)) {
        echo "\nConsulta problemática:\n" . substr($olapQuery, 0, 500) . "...\n";
    }
} finally {
    if (isset($connw)) $connw = null;
    if (isset($conn)) $conn = null;
    echo "\n🔌 Conexiones cerradas\n";
}

?>