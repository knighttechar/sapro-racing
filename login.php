<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: Content-Type");
header("Content-Type: application/json; charset=UTF-8");

$host = "localhost";
$db   = "c2731928_sapro";
$user = "c2731928_sapro";
$pass = "basovi95RU"; // La que ya te funcionó en api.php

$data = json_decode(file_get_contents("php://input"));

if (!empty($data->usuario) && !empty($data->password)) {
    try {
        $pdo = new PDO("mysql:host=$host;dbname=$db;charset=utf8", $user, $pass);
        $stmt = $pdo->prepare("SELECT id, nombre, password FROM usuarios WHERE usuario = ?");
        $stmt->execute([$data->usuario]);
        $usuario = $stmt->fetch(PDO::FETCH_ASSOC);

        // Por ahora comparamos texto plano. Luego usaremos password_verify
        if ($usuario && $data->password === $usuario['password']) {
            echo json_encode([
                "success" => true,
                "mensaje" => "Bienvenido " . $usuario['nombre'],
                "token" => "sapro_token_123" // Simulado por hoy
            ]);
        } else {
            echo json_encode(["success" => false, "mensaje" => "Credenciales incorrectas"]);
        }
    } catch (PDOException $e) {
        echo json_encode(["success" => false, "mensaje" => "Error de conexión"]);
    }
}
?>