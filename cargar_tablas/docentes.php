<?php
// Configuración de conexiones (igual que antes)
$configProd = [
    'host' => 'localhost',
    'db'   => 'siu',
    'user' => 'postgres',
    'pass' => '13082019'
];

$configLocal = [
    'host' => 'localhost',
    'db'   => 'cubetera',
    'user' => 'postgres',
    'pass' => '13082019'
];

try {
    // Conexiones
    $prod = new PDO(
        "pgsql:host={$configProd['host']};dbname={$configProd['db']}", 
        $configProd['user'], 
        $configProd['pass']
    );
    
    $local = new PDO(
        "pgsql:host={$configLocal['host']};dbname={$configLocal['db']}", 
        $configLocal['user'], 
        $configLocal['pass']
    );

    // ⚠️⚠️⚠️ ¡PELIGRO! Esto borrará todos los datos existentes.
    $local->exec("TRUNCATE TABLE mapuche.docentes RESTART IDENTITY CASCADE");
    echo "✅ Tabla 'docentes' limpiada (reseteados los IDs autoincrementales).\n";

    // Consulta para obtener datos de producción (tu misma consulta)
$sql = "
    SELECT DISTINCT ON (dh01.nro_docum)
        dh01.desc_nombr AS nombre,
        CONCAT(dh01.desc_appat, ' ', dh01.desc_apmat) AS apellido,
        dh01.nro_docum AS dni,
        CONCAT(dh01.nro_cuil1, dh01.nro_cuil, dh01.nro_cuil2) AS cuil,
        dh01.nro_legaj as legajo,
        COALESCE(dha1.calle || ' ' || dha1.numero, 'Sin dirección') AS direccion,
        dh01.fec_nacim AS fecha_nacimiento,
        COALESCE(REGEXP_REPLACE(dha1.telefono, '[^0-9]', '', 'g'), '****') AS telefono,
        COALESCE(REGEXP_REPLACE(dha1.telefono_celular, '[^0-9]', '', 'g'), '****') AS telefono_celular,
        dh01.anioalta AS anio_alta,
        CASE
            WHEN dha1.correo_electronico IS NULL OR dha1.correo_electronico = ''
            THEN CONCAT('sin-correo-', dh01.nro_docum, '@undav.edu.ar')
            ELSE dha1.correo_electronico
        END AS correo_electronico
    FROM mapuche.dh01 AS dh01
    LEFT JOIN mapuche.dha1 AS dha1 ON dha1.nro_persona = dh01.nro_legaj
    WHERE dh01.nro_docum IS NOT NULL
    ORDER BY dh01.nro_docum, dh01.nro_legaj
";
    
    $stmt = $prod->query($sql);
    $docentes = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Preparar inserción (igual que antes)
    $insert = $local->prepare("
    INSERT INTO mapuche.docentes (
        nombres, apellidos, nro_dni, nro_cuil, nro_legajo,
        direccion, fecha_nacimiento, telefono, tel_celular,
        anio_alta, correo_electronico
    ) VALUES (
        :nombre, :apellido, :dni, :cuil, :legajo,
        :direccion, :fecha_nac, :tel_fijo, :tel_movil,
        :anio_alta, :correo
    )
    ON CONFLICT (correo_electronico) DO UPDATE SET
        nombres = EXCLUDED.nombres,
        apellidos = EXCLUDED.apellidos
    WHERE docentes.correo_electronico LIKE 'sin-correo-%'
");
    
    // Usar transacción para mayor seguridad
    $local->beginTransaction();
    
    try {
        foreach ($docentes as $docente) {
            $insert->execute([
                ':nombre' => $docente['nombre'],
                ':apellido' => $docente['apellido'],
                ':dni' => $docente['dni'],
                ':cuil' => $docente['cuil'],
                ':legajo' => $docente['legajo'],
                ':direccion' => $docente['direccion'],
                ':fecha_nac' => $docente['fecha_nacimiento'],
                ':tel_fijo' => $docente['telefono'],
                ':tel_movil' => $docente['telefono_celular'],
                ':anio_alta' => $docente['anio_alta'],
                ':correo' => $docente['correo_electronico']
            ]);
            
            echo "Migrado docente: {$docente['nombre']} {$docente['apellido']}\n";
        }
        
        $local->commit();
        echo "🎉 ¡Migración completada con éxito! Todos los datos fueron importados desde cero.\n";
        
    } catch (PDOException $e) {
        $local->rollBack();
        die("❌ Error durante la migración: " . $e->getMessage());
    }
    
} catch (PDOException $e) {
    die("❌ Error de conexión: " . $e->getMessage());
}