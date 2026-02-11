<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: Content-Type");
header("Content-Type: application/json; charset=UTF-8");

$config = require 'config.php';
$dbConfig = $config['db'];

$data = json_decode(file_get_contents("php://input"), true) ?? [];
$usuario = trim($data['usuario'] ?? '');
$password = $data['password'] ?? '';

// Validación de entrada
if (empty($usuario) || empty($password)) {
    echo json_encode(["success" => false, "mensaje" => "Usuario y contraseña son requeridos"]);
    exit;
}

try {
    $pdo = new PDO(
        "mysql:host={$dbConfig['host']};dbname={$dbConfig['name']};charset=utf8",
        $dbConfig['user'],
        $dbConfig['pass']
    );
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $stmt = $pdo->prepare("SELECT id, nombre, password FROM usuarios WHERE usuario = ? LIMIT 1");
    $stmt->execute([$usuario]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$user) {
        // No revelar si el usuario existe
        echo json_encode(["success" => false, "mensaje" => "Credenciales incorrectas"]);
        exit;
    }

    // Validar contraseña
    // Verificar si la contraseña está hasheada (comienza con $2y$, $2a$, $2b$ = bcrypt)
    $passwordValida = false;
    if (strpos($user['password'], '$2') === 0 || strpos($user['password'], '$argon2') === 0) {
        // Está hasheada, usar password_verify
        $passwordValida = password_verify($password, $user['password']);
    } else {
        // Comparación de texto plano (NO RECOMENDADO - cambiar a hasheado)
        $passwordValida = ($password === $user['password']);
    }

    if ($passwordValida) {
        // Generar token único (más seguro que hardcodeado)
        $token = bin2hex(random_bytes(32));

        echo json_encode([
            "success" => true,
            "mensaje" => "Bienvenido " . htmlspecialchars($user['nombre']),
            "id" => $user['id'],
            "nombre" => htmlspecialchars($user['nombre']),
            "token" => $token
        ]);
    } else {
        echo json_encode(["success" => false, "mensaje" => "Credenciales incorrectas"]);
    }

} catch (PDOException $e) {
    error_log("Error DB (login): " . $e->getMessage());
    echo json_encode(["success" => false, "mensaje" => "Error en el servidor"]);
} catch (Exception $e) {
    error_log("Error (login): " . $e->getMessage());
    echo json_encode(["success" => false, "mensaje" => "Error en el servidor"]);
}
?>