<?php
// Configuración de conexiones (igual que antes)
$config_siu = [
    'host'     => 'localhost',
    'dbname'   => 'siu',
    'user'     => 'postgres',
    'password' => '13082019'
];

$config_cubetera = [
    'host'     => 'localhost',
    'dbname'   => 'cubetera',
    'user'     => 'postgres',
    'password' => '13082019'
];

try {
    // Conexiones
    $conn_siu = new PDO(
        "pgsql:host={$config_siu['host']};dbname={$config_siu['dbname']}",
        $config_siu['user'],
        $config_siu['password']
    );
    
    $conn_cubetera = new PDO(
        "pgsql:host={$config_cubetera['host']};dbname={$config_cubetera['dbname']}",
        $config_cubetera['user'],
        $config_cubetera['password']
    );

    // 1. Extraer datos de SIU (docente + dedicación)
    $query = "
        SELECT DISTINCT 
            CONCAT(dh01.desc_appat, ' ', dh01.desc_apmat, ' ', dh01.desc_nombr) AS nombre_docente,
            dh31.desc_dedic AS nombre_dedicacion
        FROM mapuche.dh01 AS dh01
        LEFT JOIN mapuche.dhr2 AS dhr2 ON dhr2.nro_legaj = dh01.nro_legaj
        LEFT JOIN mapuche.dh31 AS dh31 ON dh31.codc_dedic = dhr2.codc_dedic
        WHERE dh31.desc_dedic IS NOT NULL
    ";
    
    $stmt = $conn_siu->query($query);
    $relaciones = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // 2. Insertar en CUBETERA (buscando por nombres)
    $conn_cubetera->beginTransaction();
    $insertados = 0;
    $omitidos = 0;

    foreach ($relaciones as $rel) {
        // Buscar ID del docente por nombre completo
        $stmt_docente = $conn_cubetera->prepare(
            "SELECT id_docente FROM mapuche.docentes 
             WHERE CONCAT(apellidos, ' ', nombres) = ?"
        );
        $stmt_docente->execute([$rel['nombre_docente']]);
        $id_docente = $stmt_docente->fetchColumn();
        
        // Buscar ID de la dedicación por descripción
        $stmt_dedicacion = $conn_cubetera->prepare(
            "SELECT id_dedicacion FROM mapuche.dedicaciones WHERE desc_dedicacion = ?"
        );
        $stmt_dedicacion->execute([$rel['nombre_dedicacion']]);
        $id_dedicacion = $stmt_dedicacion->fetchColumn();
        
        if ($id_docente && $id_dedicacion) {
            $stmt_insert = $conn_cubetera->prepare(
                "INSERT INTO mapuche.dedicacion_docente (id_docente, id_dedicacion) VALUES (?, ?)"
            );
            $stmt_insert->execute([$id_docente, $id_dedicacion]);
            $insertados++;
        } else {
            echo "⚠️ Omitido: Docente '{$rel['nombre_docente']}' o dedicación '{$rel['nombre_dedicacion']}' no encontrado\n";
            $omitidos++;
        }
    }
    
    $conn_cubetera->commit();
    echo "✅ ¡Proceso completado! \n";
    echo "📊 Insertados: $insertados relaciones | Omitidos: $omitidos\n";

} catch (PDOException $e) {
    $conn_cubetera->rollBack();
    die("❌ Error: " . $e->getMessage());
} finally {
    $conn_siu = null;
    $conn_cubetera = null;
}
?>