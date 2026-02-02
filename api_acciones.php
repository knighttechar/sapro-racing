<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: Content-Type");
header("Content-Type: application/json; charset=UTF-8");

$host = "localhost";
$db   = "c2731928_sapro";
$user = "c2731928_sapro";
$pass = "basovi95RU";

$data = json_decode(file_get_contents("php://input"), true);
$accion = $_GET['accion'] ?? '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$db;charset=utf8", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // --- ACCIÓN: ELIMINAR ---
    if ($accion === 'eliminar') {
        $id = $data['id'];
        
        // 1. Buscamos el nombre de la imagen para borrar el archivo físico
        $stmtImg = $pdo->prepare("SELECT imagen FROM productos WHERE id = ?");
        $stmtImg->execute([$id]);
        $img = $stmtImg->fetchColumn();
        
        // 2. Si existe imagen y no es la default, la borramos del disco
        if ($img && $img != 'default.jpg') {
            $rutaImg = "imagenes/" . $img;
            if (file_exists($rutaImg)) {
                unlink($rutaImg);
            }
        }

        // 3. Borramos el registro de la base de datos
        $stmt = $pdo->prepare("DELETE FROM productos WHERE id = ?");
        $stmt->execute([$id]);
        
        echo json_encode(["success" => true, "mensaje" => "Producto e imagen eliminados"]);
    }

    // --- ACCIÓN: EDITAR ---
    if ($accion === 'editar') {
        $id = $data['id'];
        $nombre = $data['nombre'];
        $codigo = $data['codigo'];
        $precio = $data['precio'];
        $stock = $data['stock'];
        $descripcion = $data['descripcion'] ?? '';

        $sql = "UPDATE productos SET nombre = ?, codigo = ?, precio = ?, stock = ?, descripcion = ? WHERE id = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$nombre, $codigo, $precio, $stock, $descripcion, $id]);

        echo json_encode(["success" => true, "mensaje" => "Producto actualizado"]);
    }

} catch (Exception $e) {
    echo json_encode(["success" => false, "error" => $e->getMessage()]);
}
?>