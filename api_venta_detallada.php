<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: Content-Type");
header("Content-Type: application/json; charset=UTF-8");

error_reporting(0); // Evitar mostrar errores de PHP

$errores = [];

try {
    $config = require 'config.php';
    if (!isset($config['db'])) {
        throw new Exception("No hay configuración de base de datos");
    }
    $dbConfig = $config['db'];

    // Intentar conectar con diferentes charsets
    $dsn = "mysql:host={$dbConfig['host']};dbname={$dbConfig['name']};charset=utf8";
    $pdo = new PDO(
        $dsn,
        $dbConfig['user'],
        $dbConfig['pass'],
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
        ]
    );

    $limit = max(1, intval($_GET['limit'] ?? 50));
    $offset = max(0, intval($_GET['offset'] ?? 0));

    // Verificar si existen las tablas
    $checkTables = $pdo->query("
        SELECT TABLE_NAME 
        FROM INFORMATION_SCHEMA.TABLES 
        WHERE TABLE_SCHEMA = '{$dbConfig['name']}' 
        AND TABLE_NAME IN ('detalle_ventas', 'ventas', 'productos')
    ");
    $tablesExist = $checkTables->fetchAll(PDO::FETCH_COLUMN);
    
    if (!in_array('detalle_ventas', $tablesExist)) {
        throw new Exception("Tabla 'detalle_ventas' no encontrada");
    }

    // Obtener datos con mejor manejo
    $sql = "
        SELECT 
            dv.id,
            dv.venta_id,
            dv.cantidad,
            dv.precio_unitario,
            v.fecha,
            v.total,
            p.nombre,
            p.marca,
            p.categoria,
            p.codigo
        FROM detalle_ventas dv
        LEFT JOIN ventas v ON dv.venta_id = v.id
        LEFT JOIN productos p ON dv.producto_id = p.id
        ORDER BY v.fecha DESC, dv.id DESC
        LIMIT " . (int)$limit . " OFFSET " . (int)$offset;

    $resultado = $pdo->query($sql);
    $datos = $resultado->fetchAll();

    // Contar total
    $countSql = "SELECT COUNT(*) as total FROM detalle_ventas";
    $countStmt = $pdo->prepare($countSql);
    $countStmt->execute();
    $countRow = $countStmt->fetch();
    $total = intval($countRow ? $countRow['total'] : 0);

    // Reformatear datos
    $datosFormateados = [];
    foreach ($datos as $row) {
        // Calcular subtotal
        $subtotal = floatval($row['cantidad']) * floatval($row['precio_unitario']);
        
        $datosFormateados[] = [
            'fecha' => $row['fecha'] ?? null,
            'producto_nombre' => $row['nombre'] ?? 'N/A',
            'marca' => $row['marca'] ?? 'N/A',
            'categoria' => $row['categoria'] ?? 'N/A',
            'codigo' => $row['codigo'] ?? 'N/A',
            'cantidad' => intval($row['cantidad']),
            'precio_unitario' => floatval($row['precio_unitario']),
            'subtotal' => $subtotal,
            'venta_monto_total' => floatval($row['total'] ?? 0)
        ];
    }

    http_response_code(200);
    echo json_encode([
        "success" => true,
        "data" => $datosFormateados,
        "columns" => [
            "fecha",
            "producto_nombre",
            "marca",
            "categoria",
            "codigo",
            "cantidad",
            "precio_unitario",
            "subtotal",
            "venta_monto_total"
        ],
        "total" => $total,
        "limit" => $limit,
        "offset" => $offset
    ]);

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode([
        "success" => false,
        "error" => "Error de BD: " . $e->getMessage(),
        "code" => $e->getCode()
    ]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        "success" => false,
        "error" => $e->getMessage()
    ]);
}
?>
