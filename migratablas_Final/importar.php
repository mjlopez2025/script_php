<?php

CONST CSV_FILE_GUARANI = 'doc_de_guarani.csv';
CONST CSV_FILE_MAPUCHE = 'doc_de_mapuche.csv';
CONST DELIMITER = ','; 
$LINES = str_repeat('-', 80)."\n";

// Configuración de la conexión a PostgreSQL
$host = 'localhost';
$dbname = 'tablas';
$user = 'postgres';
$password = '13082019';

$conn = new PDO("pgsql:host=$host;dbname=$dbname", $user, $password);
$conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

require_once "./paso1.php"; // verificar y crear tabla si no existe
require_once "./paso2.php"; // importar csv a tablas
require_once "./paso3.php"; // duplicar registros con segundo docente
require_once "./paso6.php";   //Importada la tabla docentes_mapuche
require_once "./paso7.php";   // Crear tabla combinada y poblarla
require_once "./paso8.php";   // Exportar a xlsx


// Cerrar conexión
$conn = null;

