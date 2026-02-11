<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: Content-Type");
header("Content-Type: application/json; charset=UTF-8");

$config = require 'config.php';
$dbConfig = $config['db'];
$uploadConfig = $config['upload'];

// --- VALIDACIÓN DE MÉTODO HTTP ---
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(["success" => false, "mensaje" => "Método no permitido. Solo POST."]);
    exit;
}

// Leemos el JSON del cuerpo de la petición
$data = json_decode(file_get_contents("php://input"), true) ?? [];
$accion = $_GET['accion'] ?? '';

// --- VALIDACIÓN DE ACCIÓN AL INICIO ---
$accionesValidas = ['eliminar', 'editar'];
if (!in_array($accion, $accionesValidas)) {
    http_response_code(400);
    echo json_encode(["success" => false, "mensaje" => "Acción no reconocida"]);
    exit;
}

// --- FUNCIÓN DE AUDITORÍA (opcional, no bloquea si falla) ---
function registrarAuditoria($pdo, $accion, $tabla, $registroId, $detalles) {
    try {
        // Verificar si la tabla existe
        $checkTable = $pdo->query("SHOW TABLES LIKE 'auditoria'");
        if ($checkTable && $checkTable->rowCount() === 0) {
            // Tabla no existe, ignorar auditoría silenciosamente
            return;
        }

        $timestamp = date('Y-m-d H:i:s');
        $ip = $_SERVER['REMOTE_ADDR'] ?? 'DESCONOCIDA';
        $userAgent = substr($_SERVER['HTTP_USER_AGENT'] ?? 'DESCONOCIDO', 0, 255);

        $sql = "INSERT INTO auditoria (timestamp, accion, tabla, registro_id, detalles, ip_origen, user_agent) 
                VALUES (?, ?, ?, ?, ?, ?, ?)";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$timestamp, $accion, $tabla, $registroId, $detalles, $ip, $userAgent]);
    } catch (Exception $e) {
        // Ignorar errores de auditoría silenciosamente para no bloquear la acción principal
        error_log("Advertencia: No se pudo registrar auditoría: " . $e->getMessage());
    }
}

try {
    // --- CONEXIÓN A BD ---
    $pdo = new PDO(
        "mysql:host={$dbConfig['host']};dbname={$dbConfig['name']};charset=utf8",
        $dbConfig['user'],
        $dbConfig['pass']
    );
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // --- VALIDACIÓN DE AUTORIZACIÓN ---
    // Verificar que el usuario sea admin
    $esAdmin = ($data['isAdmin'] === true || $data['isAdmin'] === 'true');
    $tokenAdmin = $data['token'] ?? null;
    
    // Si no viene isAdmin del frontend, verificar con token (opcional)
    if (!$esAdmin && !empty($tokenAdmin)) {
        // Aquí podrías validar el token contra BD si lo guardaste
        // Por ahora, solo verificamos que venga la flag isAdmin del frontend
    }
    
    if (!$esAdmin && empty($tokenAdmin)) {
        http_response_code(403);
        echo json_encode(["success" => false, "mensaje" => "No autorizado. Solo administradores pueden realizar esta acción."]);
        exit;
    }

    // --- ACCIÓN: ELIMINAR ---
    if ($accion === 'eliminar') {
        $id = isset($data['id']) ? intval($data['id']) : 0;

        if (!$id || $id <= 0) {
            echo json_encode(["success" => false, "mensaje" => "ID inválido"]);
            exit;
        }

        try {
            // Iniciar transacción
            $pdo->beginTransaction();

            // 1. Obtener imagen y datos del producto
            $stmtImg = $pdo->prepare("SELECT imagen FROM productos WHERE id = ? LIMIT 1");
            $stmtImg->execute([$id]);
            $img = $stmtImg->fetchColumn();

            if ($img === false) {
                $pdo->rollBack();
                echo json_encode(["success" => false, "mensaje" => "Producto no encontrado"]);
                exit;
            }

            // 2. Eliminar registro
            $stmt = $pdo->prepare("DELETE FROM productos WHERE id = ?");
            $stmt->execute([$id]);
            $filasAfectadas = $stmt->rowCount();

            // 3. Si se eliminó correctamente, eliminar imagen
            if ($filasAfectadas > 0 && $img !== 'default.jpg') {
                $rutaImg = $uploadConfig['dir'] . "/" . basename($img);
                if (file_exists($rutaImg)) {
                    if (!@unlink($rutaImg)) {
                        error_log("Advertencia: No se pudo eliminar imagen: $rutaImg");
                    }
                }
            }

            // Confirmar transacción
            $pdo->commit();

            // 4. Registrar acción en auditoría (FUERA de la transacción)
            registrarAuditoria($pdo, 'ELIMINAR', 'productos', $id, $img);

            echo json_encode([
                "success" => true,
                "mensaje" => "Producto eliminado correctamente",
                "filasAfectadas" => $filasAfectadas
            ]);
            exit;

        } catch (Exception $e) {
            $pdo->rollBack();
            error_log("Error en transacción de eliminación: " . $e->getMessage());
            echo json_encode(["success" => false, "mensaje" => "Error al eliminar el producto"]);
            exit;
        }
    }

    // --- ACCIÓN: EDITAR ---
    if ($accion === 'editar') {
        $id = isset($data['id']) ? intval($data['id']) : 0;
        $nombre = trim($data['nombre'] ?? '');
        $codigo = trim($data['codigo'] ?? '');
        $precio = isset($data['precio']) ? floatval($data['precio']) : 0;
        $stock = isset($data['stock']) ? intval($data['stock']) : 0;
        $descripcion = trim($data['descripcion'] ?? '');
        $categoria = trim($data['categoria'] ?? 'Sin Categoría');
        $marca = trim($data['marca'] ?? 'Genérico');

        // Validación
        $errores = [];
        if (!$id || $id <= 0) $errores[] = "ID inválido";
        if (empty($nombre)) $errores[] = "El nombre es requerido";
        if (empty($codigo)) $errores[] = "El código es requerido";
        if ($precio < 0) $errores[] = "El precio no puede ser negativo";
        if ($stock < 0) $errores[] = "El stock no puede ser negativo";
        if (strlen($nombre) > 100) $errores[] = "El nombre es muy largo (máx. 100 caracteres)";
        if (strlen($codigo) > 50) $errores[] = "El código es muy largo (máx. 50 caracteres)";

        if (!empty($errores)) {
            echo json_encode(["success" => false, "mensaje" => implode(", ", $errores)]);
            exit;
        }

        try {
            // Iniciar transacción
            $pdo->beginTransaction();

            // 1. Verificar que el producto existe
            $stmtCheck = $pdo->prepare("SELECT id, nombre, codigo FROM productos WHERE id = ? LIMIT 1");
            $stmtCheck->execute([$id]);
            $productoAnterior = $stmtCheck->fetch(PDO::FETCH_ASSOC);

            if (!$productoAnterior) {
                $pdo->rollBack();
                echo json_encode(["success" => false, "mensaje" => "Producto no encontrado"]);
                exit;
            }

            // 2. Verificar que el código no esté duplicado en otro producto
            if ($codigo !== $productoAnterior['codigo']) {
                $stmtDup = $pdo->prepare("SELECT id FROM productos WHERE codigo = ? LIMIT 1");
                $stmtDup->execute([$codigo]);
                if ($stmtDup->rowCount() > 0) {
                    $pdo->rollBack();
                    echo json_encode(["success" => false, "mensaje" => "El código ya existe en otro producto"]);
                    exit;
                }
            }

            // 3. Actualizar
            $sql = "UPDATE productos SET nombre = ?, codigo = ?, precio = ?, stock = ?, descripcion = ?, categoria = ?, marca = ?, updated_at = NOW() WHERE id = ?";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$nombre, $codigo, $precio, $stock, $descripcion, $categoria, $marca, $id]);
            $filasAfectadas = $stmt->rowCount();

            // Confirmar transacción
            $pdo->commit();

            // 4. Registrar cambios en auditoría (FUERA de la transacción)
            $cambios = [];
            if ($nombre !== $productoAnterior['nombre']) {
                $cambios[] = "nombre: '{$productoAnterior['nombre']}' → '{$nombre}'";
            }
            if ($codigo !== $productoAnterior['codigo']) {
                $cambios[] = "código: '{$productoAnterior['codigo']}' → '{$codigo}'";
            }
            registrarAuditoria($pdo, 'EDITAR', 'productos', $id, implode("; ", $cambios));

            echo json_encode([
                "success" => true,
                "mensaje" => "Producto actualizado correctamente",
                "filasAfectadas" => $filasAfectadas
            ]);
            exit;

        } catch (Exception $e) {
            $pdo->rollBack();
            error_log("Error en transacción de edición: " . $e->getMessage());
            echo json_encode(["success" => false, "mensaje" => "Error al actualizar el producto"]);
            exit;
        }
    }

} catch (PDOException $e) {
    error_log("Error DB (api_acciones): " . $e->getMessage());
    echo json_encode(["success" => false, "mensaje" => "Error al procesar la solicitud"]);
} catch (Exception $e) {
    error_log("Error (api_acciones): " . $e->getMessage());
    echo json_encode(["success" => false, "mensaje" => "Error en el servidor"]);
}
?>