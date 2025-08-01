<?php
session_start();

// Limpia pero NO redirige aquí
$_SESSION = [];
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}
session_destroy();

// Solo responde con JSON
header('Content-Type: application/json');
echo json_encode(['success' => true]);
exit();
?>