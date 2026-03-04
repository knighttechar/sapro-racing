<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

header("Content-Type: application/json; charset=UTF-8");

try {
    $config = require 'config.php';
    $dbConfig = $config['db'];

    $pdo = new PDO(
        "mysql:host={$dbConfig['host']};dbname={$dbConfig['name']};charset=utf8",
        $dbConfig['user'],
        $dbConfig['pass']
    );
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Listar todas las tablas
    $query = "SELECT TABLE_NAME FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_SCHEMA = ?";
    $stmt = $pdo->prepare($query);
    $stmt->execute([$dbConfig['name']]);
    $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);

    // Contar registros en cada tabla importante
    $counts = [];
    foreach (['ventas', 'detalle_ventas', 'productos', 'detalleventas'] as $table) {
        if (in_array($table, $tables)) {
            $countStmt = $pdo->query("SELECT COUNT(*) FROM `$table`");
            $counts[$table] = $countStmt->fetchColumn();
        }
    }

    echo json_encode([
        "success" => true,
        "all_tables" => $tables,
        "counts" => $counts,
        "db_name" => $dbConfig['name'],
        "db_host" => $dbConfig['host']
    ], JSON_PRETTY_PRINT);

} catch (Exception $e) {
    echo json_encode([
        "success" => false,
        "error" => $e->getMessage(),
        "file" => $e->getFile(),
        "line" => $e->getLine()
    ], JSON_PRETTY_PRINT);
}
?>
