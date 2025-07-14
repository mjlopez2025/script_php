<?php
// Configuración para mostrar todos los errores
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "🔍 Iniciando prueba de conexión y creación de tabla...\n\n";

try {
    // Establecer conexión
    $conn = new PDO($dsn, $remoteConfig['user'], $remoteConfig['password']);
    
    // Configurar PDO para que lance excepciones en errores
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "✅ Conexión exitosa a PostgreSQL\n\n";
    
    // 1. Verificar si la tabla ya existe
    echo "🔍 Verificando si la tabla docentes_mapuche existe...\n";
    $checkTableSQL = "SELECT EXISTS (
        SELECT 1 
        FROM information_schema.tables 
        WHERE table_schema = 'public' 
        AND table_name = 'docentes_mapuche'
    )";
    
    $tableExists = $conn->query($checkTableSQL)->fetchColumn();
    
    if ($tableExists) {
        echo "ℹ️ La tabla docentes_mapuche ya existe\n";
    } else {
        echo "ℹ️ La tabla docentes_mapuche no existe. Creándola...\n";
    }
    
    // 2. Crear tabla docentes_mapuche
    $createTableSQL = "
    CREATE TABLE IF NOT EXISTS docentes_mapuche (
        id SERIAL PRIMARY KEY,
        apellidonombre_desc VARCHAR(255),
        nro_documento BIGINT,
        categoria_desc VARCHAR(100),
        nro_cargo INTEGER,
        dedicacion_desc VARCHAR(50),
        estadodelcargo_desc VARCHAR(100),
        dependenciadesign_desc VARCHAR(255),
        anio_id INTEGER,
        mes_desc VARCHAR(20),
        persona_id INTEGER
    );
    ";
    
    echo "⚙️ Ejecutando sentencia CREATE TABLE...\n";
    $conn->exec($createTableSQL);
    echo "✅ Operación CREATE TABLE completada\n";
    
    // 3. Verificar nuevamente si la tabla existe
    $tableExistsNow = $conn->query($checkTableSQL)->fetchColumn();
    
    if ($tableExistsNow) {
        echo "\n🎉 ¡Tabla docentes_mapuche verificada con éxito!\n";
    } else {
        echo "\n⚠️ La tabla docentes_mapuche no se creó, pero no hubo errores. Verifica permisos.\n";
    }
    
} catch (PDOException $e) {
    die("\n❌ Error: " . $e->getMessage() . "\n");
}

// Verificar tablas existentes después de las operaciones
echo "\n🔍 Verificación final de tablas en la base de datos:\n";

$tables = $conn->query("
    SELECT table_name 
    FROM information_schema.tables 
    WHERE table_schema = 'public'
")->fetchAll(PDO::FETCH_COLUMN);

echo "Tablas existentes:\n";
print_r($tables);

// Mostrar 2 primeros registros de docentes_mapuche
echo "\n📊 Primeros 2 registros de docentes_mapuche:\n";
$result = $conn->query("SELECT * FROM docentes_mapuche LIMIT 2");
print_r($result->fetchAll(PDO::FETCH_ASSOC));

// Mostrar 2 primeros registros de docentes_guarani
echo "\n📊 Primeros 2 registros de docentes_guarani:\n";
$result = $conn->query("SELECT * FROM docentes_guarani LIMIT 2");
print_r($result->fetchAll(PDO::FETCH_ASSOC));

