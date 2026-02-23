<?php
// 1. Cabeceras de seguridad y permisos (CORS)
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header("Content-Type: application/json; charset=UTF-8");

// 2. ATENDER LA PETICIÓN "INVISIBLE" (OPTIONS)
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

// 3. Verificar que la petición real sea POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(["success" => false, "mensaje" => "Método no permitido"]);
    exit;
}

// Importar configuración de BD
$config = require 'config.php';
$db = $config['db'];

// 4. Leer los datos enviados
$json_input = file_get_contents("php://input");
$data = json_decode($json_input, true);

// Validamos que los datos hayan llegado bien
if (!$data || !isset($data['carrito']) || empty($data['carrito'])) {
    echo json_encode(["success" => false, "mensaje" => "No se recibieron datos del carrito."]);
    exit;
}

$carrito = $data['carrito'];
$total = $data['total'] ?? 0;
$admin = $data['admin'] ?? 'Admin';

try {
    $pdo = new PDO("mysql:host={$db['host']};dbname={$db['name']};charset=utf8", $db['user'], $db['pass']);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // --- INICIO DE LA TRANSACCIÓN ---
    $pdo->beginTransaction();

    // A. Crear la Venta General
    $stmt = $pdo->prepare("INSERT INTO ventas (total, usuario_admin, fecha) VALUES (?, ?, NOW())");
    $stmt->execute([$total, $admin]);
    $ventaId = $pdo->lastInsertId();

    // B. Procesar cada producto del carrito
    foreach ($carrito as $item) {
        $id = $item['id'];
        $cantidad = $item['cantidad'];
        $precio = $item['precio'];

        // Verificar Stock actual
        $stmtStock = $pdo->prepare("SELECT stock, nombre FROM productos WHERE id = ? FOR UPDATE");
        $stmtStock->execute([$id]);
        $productoReal = $stmtStock->fetch(PDO::FETCH_ASSOC);

        if (!$productoReal) {
            throw new Exception("El producto ID $id no existe en la base de datos.");
        }
        if ($productoReal['stock'] < $cantidad) {
            throw new Exception("Sin stock suficiente de: " . $productoReal['nombre']);
        }

        // Descontar Stock
        $stmtUpdate = $pdo->prepare("UPDATE productos SET stock = stock - ? WHERE id = ?");
        $stmtUpdate->execute([$cantidad, $id]);

        // Guardar el renglón en detalle_ventas
        $stmtDetalle = $pdo->prepare("INSERT INTO detalle_ventas (venta_id, producto_id, cantidad, precio_unitario) VALUES (?, ?, ?, ?)");
        $stmtDetalle->execute([$ventaId, $id, $cantidad, $precio]);
    }

    // --- GUARDAR TODO (Si todo salió perfecto) ---
    $pdo->commit();

    echo json_encode(["success" => true, "mensaje" => "Venta registrada con éxito. N°: $ventaId"]);

} catch (Throwable $e) {
    // Si ALGO falla (Error SQL, nombre de tabla mal, etc), cancelamos la venta
    // Usamos Throwable para atrapar incluso errores fatales (500)
    if (isset($pdo) && $pdo->inTransaction()) {
        $pdo->rollBack();
    }
    
    // Devolvemos el error en forma de texto para que lo veas en la pantalla de Quasar
    echo json_encode([
        "success" => false, 
        "mensaje" => "Error interno en el servidor: " . $e->getMessage()
    ]);
}
?>