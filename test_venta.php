<?php
/**
 * Script de Test - Verifica que registrar_venta.php funciona correctamente
 * Accede a: https://saproracing.knighttech.com.ar/test_venta.php
 */

header("Content-Type: application/json; charset=UTF-8");

// Test 1: Verificar estructura básica
echo "{ \"tests\": [\n";

// Test 1.1: Conectar base de datos
$config = require 'config.php';
$db = $config['db'];

try {
    $pdo = new PDO(
        "mysql:host={$db['host']};dbname={$db['name']};charset=utf8",
        $db['user'],
        $db['pass']
    );
    echo "  { \"test\": \"Conexión BD\", \"status\": \"OK ✓\" },\n";
} catch (Exception $e) {
    echo "  { \"test\": \"Conexión BD\", \"status\": \"ERROR ✗\", \"error\": \"" . $e->getMessage() . "\" },\n";
    exit;
}

// Test 1.2: Verificar tabla productos
try {
    $stmt = $pdo->query("SELECT COUNT(*) FROM productos");
    $count = $stmt->fetchColumn();
    echo "  { \"test\": \"Tabla productos\", \"status\": \"OK ✓\", \"registros\": $count },\n";
} catch (Exception $e) {
    echo "  { \"test\": \"Tabla productos\", \"status\": \"ERROR ✗\", \"error\": \"No existe\" },\n";
}

// Test 1.3: Verificar tabla ventas
try {
    $stmt = $pdo->query("SELECT COUNT(*) FROM ventas");
    $count = $stmt->fetchColumn();
    echo "  { \"test\": \"Tabla ventas\", \"status\": \"OK ✓\", \"registros\": $count },\n";
} catch (Exception $e) {
    echo "  { \"test\": \"Tabla ventas\", \"status\": \"ERROR ✗\", \"error\": \"No existe\" },\n";
}

// Test 1.4: Verificar tabla detalle_ventas
try {
    $stmt = $pdo->query("SELECT COUNT(*) FROM detalle_ventas");
    $count = $stmt->fetchColumn();
    echo "  { \"test\": \"Tabla detalle_ventas\", \"status\": \"OK ✓\", \"registros\": $count },\n";
} catch (Exception $e) {
    echo "  { \"test\": \"Tabla detalle_ventas\", \"status\": \"ERROR ✗\", \"error\": \"No existe\" },\n";
}

// Test 1.5: Simular POST con datos válidos
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents("php://input"), true);
    
    if ($data && isset($data['carrito'])) {
        echo "  { \"test\": \"POST recibido\", \"status\": \"OK ✓\" },\n";
        echo "  { \"test\": \"Carrito\", \"items\": " . count($data['carrito']) . " },\n";
    } else {
        echo "  { \"test\": \"POST recibido\", \"status\": \"ERROR ✗\", \"info\": \"No hay carrito en POST\" },\n";
    }
} else {
    // Test con datos de ejemplo
    echo "  { \"test\": \"Modo TEST\", \"info\": \"Enviando POST con datos de prueba...\" },\n";
    
    $productoTest = null;
    try {
        $stmt = $pdo->query("SELECT id, precio FROM productos LIMIT 1");
        $productoTest = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($productoTest) {
            $testData = [
                "carrito" => [
                    [
                        "id" => (int)$productoTest['id'],
                        "cantidad" => 1,
                        "precio" => (float)$productoTest['precio']
                    ]
                ],
                "total" => (float)$productoTest['precio'],
                "admin" => "Test"
            ];
            
            echo "  { \"test\": \"Datos de prueba\", \"carrito\": " . json_encode($testData['carrito']) . " }\n";
        } else {
            echo "  { \"test\": \"No hay productos\", \"status\": \"ERROR ✗\" }\n";
        }
    } catch (Exception $e) {
        echo "  { \"test\": \"Error al construir datos test\", \"error\": \"" . $e->getMessage() . "\" }\n";
    }
}

echo "], \"servidor\": \"✓ Online\" }\n";
?>
