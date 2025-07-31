<?php
// login_procesar.php

header('Content-Type: application/json');
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Conexión a la base de datos
$config = [
    'host'     => '172.16.1.58',
    'port'     => '5433',
    'dbname'   => 'sii',
    'user'     => 'mjlopez',
    'password' => '13082019'
];

try {
    $dsn = "pgsql:host={$config['host']};port={$config['port']};dbname={$config['dbname']}";
    $conn = new PDO($dsn, $config['user'], $config['password']);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Error de conexión a la base de datos']);
    exit;
}

// Obtener JSON del frontend
$input = json_decode(file_get_contents("php://input"), true);

if (!$input || empty($input['usuario']) || empty($input['password'])) {
    echo json_encode(['success' => false, 'message' => 'Faltan datos de usuario o contraseña']);
    exit;
}

$usuario = trim($input['usuario']);
$password = $input['password'];

// Verificar usuario
$sql = "SELECT password FROM usuarios WHERE usuario = :usuario LIMIT 1";
$stmt = $conn->prepare($sql);
$stmt->execute([':usuario' => $usuario]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if ($user && password_verify($password, $user['password'])) {
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'message' => 'Usuario o contraseña incorrectos']);
}
