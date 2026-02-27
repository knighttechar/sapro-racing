<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: Content-Type");
header('Content-Type: application/json');

$config = require 'config.php';
$dbConfig = $config['db'];

$tipo = $_GET['tipo'] ?? 'dia'; // dia, semana, mes, año

try {
    $pdo = new PDO(
        "mysql:host={$dbConfig['host']};dbname={$dbConfig['name']};charset=utf8",
        $dbConfig['user'],
        $dbConfig['pass']
    );
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    switch ($tipo) {
        case 'dia':
            // Últimos 30 días
            $query = "SELECT 
                        DATE(v.fecha) as fecha,
                        COUNT(v.id) as total_ventas,
                        SUM(v.total) as monto_total
                      FROM ventas v
                      WHERE v.fecha >= DATE_SUB(NOW(), INTERVAL 30 DAY)
                      GROUP BY DATE(v.fecha)
                      ORDER BY v.fecha DESC";
            break;

        case 'semana':
            // Últimas 12 semanas
            $query = "SELECT 
                        WEEK(v.fecha) as semana,
                        YEAR(v.fecha) as año,
                        CONCAT('Semana ', WEEK(v.fecha)) as etiqueta,
                        COUNT(v.id) as total_ventas,
                        SUM(v.total) as monto_total,
                        MIN(v.fecha) as fecha_inicio,
                        MAX(v.fecha) as fecha_fin
                      FROM ventas v
                      WHERE v.fecha >= DATE_SUB(NOW(), INTERVAL 12 WEEK)
                      GROUP BY YEAR(v.fecha), WEEK(v.fecha)
                      ORDER BY v.fecha DESC";
            break;

        case 'mes':
            // Últimos 12 meses
            $query = "SELECT 
                        MONTH(v.fecha) as mes,
                        YEAR(v.fecha) as año,
                        DATE_FORMAT(v.fecha, '%b %Y') as etiqueta,
                        COUNT(v.id) as total_ventas,
                        SUM(v.total) as monto_total
                      FROM ventas v
                      WHERE v.fecha >= DATE_SUB(NOW(), INTERVAL 12 MONTH)
                      GROUP BY YEAR(v.fecha), MONTH(v.fecha)
                      ORDER BY v.fecha DESC";
            break;

        case 'año':
            // Últimos 5 años
            $query = "SELECT 
                        YEAR(v.fecha) as año,
                        COUNT(v.id) as total_ventas,
                        SUM(v.total) as monto_total
                      FROM ventas v
                      WHERE v.fecha >= DATE_SUB(NOW(), INTERVAL 5 YEAR)
                      GROUP BY YEAR(v.fecha)
                      ORDER BY v.fecha DESC";
            break;

        default:
            throw new Exception('Tipo de estadística no válido');
    }

    $stmt = $pdo->prepare($query);
    $stmt->execute();
    $datos = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Calcular totales generales
    $totalVentas = 0;
    $montoTotal = 0;
    foreach ($datos as $fila) {
        $totalVentas += $fila['total_ventas'];
        $montoTotal += $fila['monto_total'];
    }

    echo json_encode([
        'success' => true,
        'tipo' => $tipo,
        'datos' => $datos,
        'resumen' => [
            'total_ventas' => $totalVentas,
            'monto_total' => $montoTotal,
            'promedio_venta' => $totalVentas > 0 ? $montoTotal / $totalVentas : 0
        ]
    ]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
?>
