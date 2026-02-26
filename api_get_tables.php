<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: Content-Type");
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

    $tabla = $_GET['tabla'] ?? null;
    $limit = $_GET['limit'] ?? 50;
    $offset = $_GET['offset'] ?? 0;

    // Sanitizar tabla para evitar SQL injection
    $tablasPermitidas = ['ventas', 'detalle_ventas'];
    
    if (!$tabla || !in_array($tabla, $tablasPermitidas)) {
        http_response_code(400);
        echo json_encode(["error" => "Tabla no válida"]);
        exit;
    }

    // Obtener datos de la tabla (nombres de tabla no pueden ser parámetros)
    $query = $pdo->query("SELECT * FROM `$tabla` LIMIT " . (int)$limit . " OFFSET " . (int)$offset);
    $datos = $query->fetchAll(PDO::FETCH_ASSOC);

    // Obtener total de registros
    $countQuery = $pdo->query("SELECT COUNT(*) as total FROM `$tabla`");
    $total = $countQuery->fetch(PDO::FETCH_ASSOC)['total'];

    // Obtener names de columnas
    $columnsQuery = $pdo->query("DESCRIBE `$tabla`");
    $columnas = $columnsQuery->fetchAll(PDO::FETCH_ASSOC);
    $columnNames = array_map(function($col) { return $col['Field']; }, $columnas);

    echo json_encode([
        "success" => true,
        "data" => $datos,
        "columns" => $columnNames,
        "total" => $total,
        "limit" => (int)$limit,
        "offset" => (int)$offset
    ]);

} catch (PDOException $e) {
    http_response_code(500);
    error_log("Error DB (api_get_tables): " . $e->getMessage());
    echo json_encode(["error" => "Error al obtener datos de la tabla"]);
} catch (Exception $e) {
    http_response_code(500);
    error_log("Error (api_get_tables): " . $e->getMessage());
    echo json_encode(["error" => "Error en el servidor"]);
}
?>
