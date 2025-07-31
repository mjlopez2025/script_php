<?php

CONST CSV_FILE_GUARANI = 'Docente_Guarani.csv';
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
require_once "./paso2.php"; // 
require_once "./paso3.php"; // separa los registros con dos o mas docentes
require_once "./paso4.php"; // limpieza de registros
require_once "./paso5.php"; 
 
 


// Cerrar conexión
$conn = null;

