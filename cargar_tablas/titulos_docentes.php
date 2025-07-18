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

    // 1. Extraer datos de SIU (nombre docente + nombre título)
    $query = "
        SELECT DISTINCT 
            CONCAT(dh01.desc_appat, ' ', dh01.desc_apmat, ' ', dh01.desc_nombr) AS nombre_docente,
            dh33.desc_titul AS nombre_titulo
        FROM mapuche.dh01 AS dh01
        LEFT JOIN mapuche.dh06 AS dh06 ON dh06.nro_legaj = dh01.nro_legaj
        LEFT JOIN mapuche.dh33 AS dh33 ON dh33.codc_titul = dh06.codc_titul
        WHERE dh33.desc_titul IS NOT NULL
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
        
        // Buscar ID del título por descripción
        $stmt_titulo = $conn_cubetera->prepare(
            "SELECT id_titulo FROM mapuche.titulos WHERE desc_titulo = ?"
        );
        $stmt_titulo->execute([$rel['nombre_titulo']]);
        $id_titulo = $stmt_titulo->fetchColumn();
        
        if ($id_docente && $id_titulo) {
            $stmt_insert = $conn_cubetera->prepare(
                "INSERT INTO mapuche.titulo_docente (id_docente, id_titulo) VALUES (?, ?)"
            );
            $stmt_insert->execute([$id_docente, $id_titulo]);
            $insertados++;
        } else {
            echo "⚠️ Omitido: Docente '{$rel['nombre_docente']}' o título '{$rel['nombre_titulo']}' no encontrado\n";
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