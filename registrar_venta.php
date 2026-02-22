<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: Content-Type");
header("Content-Type: application/json; charset=UTF-8");

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    exit(json_encode(["success" => false, "mensaje" => "Método no permitido"]));
}

$config = require 'config.php';
$db = $config['db'];

// Recibimos los datos del carrito
$data = json_decode(file_get_contents("php://input"), true);
$carrito = $data['carrito'] ?? [];
$total = $data['total'] ?? 0;
$admin = $data['admin'] ?? 'Admin';

if (empty($carrito)) {
    exit(json_encode(["success" => false, "mensaje" => "El carrito está vacío"]));
}

try {
    $pdo = new PDO("mysql:host={$db['host']};dbname={$db['name']};charset=utf8", $db['user'], $db['pass']);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // --- INICIO DE LA TRANSACCIÓN (Modo Seguro) ---
    $pdo->beginTransaction();

    // 1. Crear la Venta
    $stmt = $pdo->prepare("INSERT INTO ventas (total, usuario_admin, fecha) VALUES (?, ?, NOW())");
    $stmt->execute([$total, $admin]);
    $ventaId = $pdo->lastInsertId();

    // 2. Procesar cada producto
    foreach ($carrito as $item) {
        $id = $item['id'];
        $cantidad = $item['cantidad'];
        $precio = $item['precio'];

        // A. Verificar Stock actual
        $stmtStock = $pdo->prepare("SELECT stock, nombre FROM productos WHERE id = ? FOR UPDATE");
        $stmtStock->execute([$id]);
        $productoReal = $stmtStock->fetch(PDO::FETCH_ASSOC);

        if (!$productoReal) {
            throw new Exception("El producto ID $id no existe.");
        }
        if ($productoReal['stock'] < $cantidad) {
            throw new Exception("Stock insuficiente para: " . $productoReal['nombre']);
        }

        // B. Descontar Stock
        $stmtUpdate = $pdo->prepare("UPDATE productos SET stock = stock - ? WHERE id = ?");
        $stmtUpdate->execute([$cantidad, $id]);

        // C. Guardar en Detalle
        $stmtDetalle = $pdo->prepare("INSERT INTO detalle_ventas (venta_id, producto_id, cantidad, precio_unitario) VALUES (?, ?, ?, ?)");
        $stmtDetalle->execute([$ventaId, $id, $cantidad, $precio]);
    }

    // --- CONFIRMAR TRANSACCIÓN ---
    $pdo->commit();

    echo json_encode(["success" => true, "mensaje" => "Venta registrada con éxito ID: $ventaId"]);

} catch (Exception $e) {
    // Si algo falló, deshacemos todo
    $pdo->rollBack();
    echo json_encode(["success" => false, "mensaje" => "Error: " . $e->getMessage()]);
}
?>