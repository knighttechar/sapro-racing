<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");

$host = "localhost";
$db   = "c2731928_sapro";
$user = "c2731928_sapro";
$pass = "basovi95RU"; 

try {
    $pdo = new PDO("mysql:host=$host;dbname=$db;charset=utf8", $user, $pass);
    // Agregamos descripcion al SELECT
    $query = $pdo->query("SELECT id, nombre, codigo, precio, stock, imagen, descripcion FROM productos");
    $productos = $query->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode($productos);
} catch (PDOException $e) {
    echo json_encode(["error" => $e->getMessage()]);
}
?>