<?php
// Configuración para mostrar todos los errores
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Configuración de conexión al servidor
$config_sii = [
    'host'     => '172.16.1.58',
    'port'     => '5432',
    'dbname'   => 'sii',
    'user'     => 'postgres',
    'password' => 'postgres'
];

// Crear string de conexión DSN
$dsn = "pgsql:host={$config_sii['host']};port={$config_sii['port']};dbname={$config_sii['dbname']}";

echo "=============================================\n";
echo "SCRIPT PARA CREAR TABLA DOCENTES_GUARANI\n";
echo "=============================================\n";
echo "\nIniciando procesamiento....\n";

try {
    // Establecer conexión
    $conn = new PDO($dsn, $config_sii['user'], $config_sii['password']);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "✅ Conexión exitosa a PostgreSQL\n";
    echo "Servidor: {$config_sii['host']}:{$config_sii['port']}\n";
    echo "Base de datos: {$config_sii['dbname']}\n\n";

    echo "Verificando existencia de la tabla Docentes_Guarani...\n";
    echo "****************************************************\n";
    
    // Consulta para verificar si la tabla existe
    $checkTable = "SELECT EXISTS (SELECT 1 FROM information_schema.tables WHERE table_schema = 'public' AND table_name = 'Docentes_Guarani')";
    $stmt = $conn->query($checkTable);
    $tableExists = $stmt->fetchColumn();

    if (!$tableExists) {
        echo "=============================================================\n";
        echo "Paso 1. La tabla no existe. Creando tabla Docentes_Guarani...\n";
        echo "=============================================================\n";
        
        // SQL para crear la tabla
        $createTable = "CREATE TABLE public.Docentes_Guarani (
                        responsabilidad_academica_guarani varchar(500) NULL,
                        Propuesta_Formativa_Guarani varchar(500) NULL,
                        Comision_Guarani varchar(500) NULL,
                        Anio_guarani varchar (500) NULL,
                        Periodo_Guarani varchar(500) NULL,
                        Docente_Guarani varchar(500) NULL,
                        Tipo_Documento varchar(500) NULL,
                        Num_Documento varchar(500) NULL,
                        Codigo_guarani  varchar(500) NULL,
                        Actividad_Guarani varchar(500) NULL,
                        Cursados_Guarani varchar(500) NULL,
                        ape_nom1_Guarani varchar(500) NULL,
                        tipo_doc1_Guarani varchar(500) NULL,
                        num_doc1_Guarani varchar(500) NULL,
                        ape_nom2_Guarani varchar(500) NULL,
                        tipo_doc2_Guarani varchar(500) NULL,
                        num_doc2_Guarani varchar(500) NULL,
                        ape_nom3_Guarani varchar(500) NULL,
                        tipo_doc3_Guarani varchar(500) NULL,
                        num_doc3_Guarani varchar(500) NULL,
                        ape_nom4_Guarani varchar(500) NULL,
                        tipo_doc4_Guarani varchar(500) NULL,
                        num_doc4_Guarani varchar(500) NULL,
                        ape_nom5_Guarani varchar(500) NULL,
                        tipo_doc5_Guarani varchar(500) NULL,
                        num_doc5_Guarani varchar(500) NULL
                       )";
        
        $alterTable = "ALTER TABLE public.Docentes_Guarani ADD COLUMN id SERIAL PRIMARY KEY";

        // Ejecutar creación de tabla
        $conn->exec($createTable);
        echo "✅ Tabla Docentes_Guarani creada exitosamente.\n";

        // Agregar columna ID como PRIMARY KEY
        $conn->exec($alterTable);
        echo "✅ Columna ID agregada como PRIMARY KEY.\n";

    } else {
        echo "ℹ️ La tabla Docentes_Guarani ya existe. No se realizaron cambios.\n";
    }

    // Verificación final
    echo "\n🔍 Verificación final:\n";
    $tables = $conn->query("SELECT table_name FROM information_schema.tables WHERE table_schema = 'public'")->fetchAll(PDO::FETCH_COLUMN);
    echo "Tablas existentes en la base de datos:\n";
    print_r($tables);

} catch (PDOException $e) {
    echo "\n❌ Error: " . $e->getMessage() . "\n";
    if (strpos($e->getMessage(), 'already exists') !== false) {
        echo "ℹ️ La tabla Docentes_Guarani ya existe en la base de datos.\n";
    }
}

echo "\nProceso completado.\n";
?>