<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");

$config = require 'config.php';
$dbConfig = $config['db'];

try {
    $pdo = new PDO(
        "mysql:host={$dbConfig['host']};dbname={$dbConfig['name']};charset=utf8",
        $dbConfig['user'],
        $dbConfig['pass']
    );
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $query = $pdo->query("SELECT id, nombre, codigo, precio, stock, imagen, descripcion, categoria, marca FROM productos ORDER BY id DESC");
    $productos = $query->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode($productos);

} catch (PDOException $e) {
    error_log("Error DB (api): " . $e->getMessage());
    echo json_encode(["error" => "Error al obtener productos"]);
} catch (Exception $e) {
    error_log("Error (api): " . $e->getMessage());
    echo json_encode(["error" => "Error en el servidor"]);
}
?>