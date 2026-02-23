<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: Content-Type");
header("Content-Type: application/json; charset=UTF-8");

// Validar método HTTP
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    exit(json_encode(["success" => false, "mensaje" => "Método no permitido. Solo POST."]));
}

$config = require 'config.php';
$db = $config['db'];

// Recibimos los datos del carrito
$rawInput = file_get_contents("php://input");
$data = json_decode($rawInput, true);

if ($data === null) {
    http_response_code(400);
    exit(json_encode([
        "success" => false,
        "mensaje" => "JSON inválido",
        "debug" => [
            "input_recibido" => substr($rawInput, 0, 200),
            "json_error" => json_last_error_msg()
        ]
    ]));
}

if (!is_array($data)) {
    http_response_code(400);
    exit(json_encode(["success" => false, "mensaje" => "Datos inválidos. Se esperaba JSON array."]));
}

$carrito = $data['carrito'] ?? [];
$total = isset($data['total']) ? floatval($data['total']) : 0;
$admin = $data['admin'] ?? 'Admin';

// Validar carrito
if (!is_array($carrito)) {
    http_response_code(400);
    exit(json_encode(["success" => false, "mensaje" => "Carrito debe ser un array"]));
}

if (empty($carrito)) {
    http_response_code(400);
    exit(json_encode(["success" => false, "mensaje" => "El carrito está vacío"]));
}

// Validar cada item
$erroresItem = [];
foreach ($carrito as $index => $item) {
    if (!isset($item['id']) || !isset($item['cantidad']) || !isset($item['precio'])) {
        $erroresItem[] = "Item $index: faltan campos (id, cantidad, precio)";
    }
    $id = isset($item['id']) ? intval($item['id']) : 0;
    $cantidad = isset($item['cantidad']) ? intval($item['cantidad']) : 0;
    $precio = isset($item['precio']) ? floatval($item['precio']) : 0;
    
    if ($id <= 0) {
        $erroresItem[] = "Item $index: id debe ser > 0";
    }
    if ($cantidad <= 0) {
        $erroresItem[] = "Item $index: cantidad debe ser > 0";
    }
    if ($precio < 0) {
        $erroresItem[] = "Item $index: precio no puede ser negativo";
    }
}

if (!empty($erroresItem)) {
    http_response_code(400);
    exit(json_encode([
        "success" => false,
        "mensaje" => "Validación de carrito fallida",
        "errores" => $erroresItem
    ]));
}

if ($total <= 0) {
    http_response_code(400);
    exit(json_encode(["success" => false, "mensaje" => "Total debe ser mayor a 0"]));
}

try {
    $pdo = new PDO(
        "mysql:host={$db['host']};dbname={$db['name']};charset=utf8",
        $db['user'],
        $db['pass']
    );
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // --- INICIO DE LA TRANSACCIÓN (Modo Seguro) ---
    $pdo->beginTransaction();

    // 1. Crear la Venta
    $stmt = $pdo->prepare("INSERT INTO ventas (total, usuario_admin, fecha, estado) VALUES (?, ?, NOW(), 'completada')");
    $stmt->execute([$total, $admin]);
    $ventaId = $pdo->lastInsertId();

    // 2. Procesar cada producto
    foreach ($carrito as $item) {
        $id = isset($item['id']) ? intval($item['id']) : 0;
        $cantidad = isset($item['cantidad']) ? intval($item['cantidad']) : 0;
        $precio = isset($item['precio']) ? floatval($item['precio']) : 0;
        $cantidad = isset($item['cantidad']) ? intval($item['cantidad']) : 0;
        $precio = isset($item['precio']) ? floatval($item['precio']) : 0;

        // Validar datos
        if ($id <= 0) {
            throw new Exception("ID de producto inválido");
        }
        if ($cantidad <= 0) {
            throw new Exception("Cantidad debe ser mayor a 0");
        }
        if ($precio < 0) {
            throw new Exception("Precio inválido");
        }

        // A. Verificar Stock actual
        $stmtStock = $pdo->prepare("SELECT stock, nombre FROM productos WHERE id = ? FOR UPDATE");
        $stmtStock->execute([$id]);
        $productoReal = $stmtStock->fetch(PDO::FETCH_ASSOC);

        if (!$productoReal) {
            throw new Exception("El producto ID $id no existe.");
        }
        if ($productoReal['stock'] < $cantidad) {
            throw new Exception("Stock insuficiente para: " . $productoReal['nombre'] . " (Disponible: " . $productoReal['stock'] . ", Solicitado: " . $cantidad . ")");
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

    echo json_encode([
        "success" => true,
        "mensaje" => "Venta registrada con éxito",
        "ventaId" => $ventaId,
        "total" => $total,
        "cantidad_items" => count($carrito)
    ]);

} catch (Exception $e) {
    // Si algo falló, deshacemos todo
    if (isset($pdo)) {
        try {
            $pdo->rollBack();
        } catch (Exception $rollbackError) {
            // Ignorar error en rollback
        }
    }
    
    http_response_code(400);
    echo json_encode([
        "success" => false,
        "mensaje" => "Error al registrar la venta: " . $e->getMessage()
    ]);
} catch (PDOException $e) {
    http_response_code(500);
    error_log("Error DB (registrar_venta): " . $e->getMessage());
    echo json_encode([
        "success" => false,
        "mensaje" => "Error en la base de datos"
    ]);
}
?>
