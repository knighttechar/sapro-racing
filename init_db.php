<?php
/**
 * Script de inicialización de base de datos
 * Crea todas las tablas necesarias si no existen
 * Accede a: https://site.com/init_db.php
 */

header("Content-Type: application/json; charset=UTF-8");

$config = require 'config.php';
$db = $config['db'];

try {
    $pdo = new PDO(
        "mysql:host={$db['host']};dbname={$db['name']};charset=utf8",
        $db['user'],
        $db['pass']
    );
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Leer el archivo SQL
    $sqlFile = file_get_contents('schema_auditoria.sql');
    
    // Dividir por punto y coma y ejecutar cada sentencia
    $statements = array_filter(
        array_map('trim', explode(';', $sqlFile)),
        function ($stmt) { return !empty($stmt); }
    );

    foreach ($statements as $sql) {
        try {
            $pdo->exec($sql);
        } catch (PDOException $e) {
            // Si la tabla ya existe, ignorar (está en el CREATE TABLE IF NOT EXISTS)
            if (strpos($e->getMessage(), 'already exists') === false) {
                throw $e;
            }
        }
    }

    echo json_encode([
        "success" => true,
        "mensaje" => "Base de datos inicializada correctamente",
        "detalles" => [
            "tablas_creadas" => [
                "productos",
                "usuarios",
                "ventas",
                "detalle_ventas",
                "auditoria"
            ]
        ]
    ]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        "success" => false,
        "mensaje" => "Error al inicializar la base de datos",
        "error" => $e->getMessage()
    ]);
}
?>
