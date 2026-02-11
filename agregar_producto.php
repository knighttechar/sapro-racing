<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: Content-Type");
header("Content-Type: application/json; charset=UTF-8");

// --- VALIDACIÓN DE MÉTODO HTTP ---
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(["success" => false, "mensaje" => "Método no permitido. Solo POST."]);
    exit;
}

// --- VALIDACIÓN DE AUTORIZACIÓN ---
$isAdmin = $_POST['isAdmin'] ?? '';
if ($isAdmin !== 'true') {
    http_response_code(403);
    echo json_encode(["success" => false, "mensaje" => "No autorizado. Solo administradores pueden agregar productos."]);
    exit;
}

$config = require 'config.php';
$dbConfig = $config['db'];
$uploadConfig = $config['upload'];

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
    // --- VALIDACIÓN DE ENTRADA ---
    $nombre = trim($_POST['nombre'] ?? '');
    $codigo = trim($_POST['codigo'] ?? '');
    $precio = isset($_POST['precio']) ? floatval($_POST['precio']) : 0;
    $stock = isset($_POST['stock']) ? intval($_POST['stock']) : 0;
    $descripcion = trim($_POST['descripcion'] ?? '');
    $categoria = trim($_POST['categoria'] ?? 'Sin Categoría');
    $marca = trim($_POST['marca'] ?? 'Genérico');

    $errores = [];

    // Validar campos requeridos
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

    // --- CONEXIÓN A BD ---
    $pdo = new PDO(
        "mysql:host={$dbConfig['host']};dbname={$dbConfig['name']};charset=utf8",
        $dbConfig['user'],
        $dbConfig['pass']
    );
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Verificar si el código ya existe
    $stmtCheck = $pdo->prepare("SELECT id FROM productos WHERE codigo = ? LIMIT 1");
    $stmtCheck->execute([$codigo]);
    if ($stmtCheck->rowCount() > 0) {
        echo json_encode(["success" => false, "mensaje" => "El código de producto ya existe"]);
        exit;
    }

    try {
        // Iniciar transacción
        $pdo->beginTransaction();

        // --- PROCESAMIENTO DE IMAGEN ---
        $nombreImagen = 'default.jpg';

        if (isset($_FILES['imagen']) && $_FILES['imagen']['error'] === UPLOAD_ERR_OK) {
            $file = $_FILES['imagen'];
            
            // Validar tamaño
            if ($file['size'] > $uploadConfig['max_size']) {
                $pdo->rollBack();
                echo json_encode(["success" => false, "mensaje" => "La imagen supera el tamaño máximo permitido"]);
                exit;
            }

            // Validar MIME type
            $finfo = finfo_open(FILEINFO_MIME_TYPE);
            $mimeType = finfo_file($finfo, $file['tmp_name']);
            finfo_close($finfo);

            if (!in_array($mimeType, $uploadConfig['allowed_mimes'])) {
                $pdo->rollBack();
                echo json_encode(["success" => false, "mensaje" => "Formato de imagen no permitido"]);
                exit;
            }

            // Validar extensión
            $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
            if (!in_array($ext, $uploadConfig['allowed_ext'])) {
                $pdo->rollBack();
                echo json_encode(["success" => false, "mensaje" => "Extensión de archivo no permitida"]);
                exit;
            }

            // Crear directorio si no existe
            $uploadDir = $uploadConfig['dir'];
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0755, true);
            }

            // Generar nombre seguro
            $codigoLimpio = preg_replace("/[^a-zA-Z0-9_-]/", "", str_replace(' ', '_', $codigo));
            $nombreImagen = time() . "_" . $codigoLimpio . "." . $ext;
            $rutaDestino = $uploadDir . "/" . $nombreImagen;

            if (!move_uploaded_file($file['tmp_name'], $rutaDestino)) {
                $pdo->rollBack();
                echo json_encode(["success" => false, "mensaje" => "Error al guardar la imagen"]);
                exit;
            }
        }

        // --- INSERTAR PRODUCTO ---
        $sql = "INSERT INTO productos (nombre, codigo, precio, stock, descripcion, imagen, categoria, marca, created_at) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW())";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$nombre, $codigo, $precio, $stock, $descripcion, $nombreImagen, $categoria, $marca]);
        
        $productoId = $pdo->lastInsertId();

        // Confirmar transacción
        $pdo->commit();

        // Registrar en auditoría (FUERA de la transacción)
        registrarAuditoria($pdo, 'CREAR', 'productos', $productoId, "Nuevo producto: $nombre ($codigo)");

        echo json_encode([
            "success" => true,
            "mensaje" => "Producto guardado correctamente",
            "id" => $productoId
        ]);

    } catch (Exception $e) {
        $pdo->rollBack();
        error_log("Error en transacción de creación: " . $e->getMessage());
        echo json_encode(["success" => false, "mensaje" => "Error al guardar el producto"]);
    }

} catch (PDOException $e) {
    error_log("Error DB: " . $e->getMessage());
    echo json_encode(["success" => false, "mensaje" => "Error al guardar el producto"]);
} catch (Exception $e) {
    error_log("Error: " . $e->getMessage());
    echo json_encode(["success" => false, "mensaje" => "Error en el servidor"]);
}
?>