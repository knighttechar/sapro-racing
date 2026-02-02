<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: Content-Type");
header("Content-Type: application/json; charset=UTF-8");

$host = "localhost";
$db   = "c2731928_sapro";
$user = "c2731928_sapro";
$pass = "basovi95RU";

try {
    $pdo = new PDO("mysql:host=$host;dbname=$db;charset=utf8", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    $nombre = $_POST['nombre'] ?? '';
    $codigo = $_POST['codigo'] ?? '';
    $precio = $_POST['precio'] ?? 0;
    $stock  = $_POST['stock'] ?? 0;
    $descripcion = $_POST['descripcion'] ?? ''; 
    $nombreImagen = 'default.jpg'; 

    if (isset($_FILES['imagen']) && $_FILES['imagen']['error'] === UPLOAD_ERR_OK) {
        $ext = pathinfo($_FILES['imagen']['name'], PATHINFO_EXTENSION);
        $codigoLimpio = preg_replace("/[^a-zA-Z0-9]/", "", $codigo);
        $nombreImagen = time() . "_" . $codigoLimpio . "." . $ext; 
        $rutaDestino = "imagenes/" . $nombreImagen;
        
        if (!is_dir('imagenes')) { mkdir('imagenes', 0777, true); }
        move_uploaded_file($_FILES['imagen']['tmp_name'], $rutaDestino);
    }

    // INSERT con descripción incluida
    $sql = "INSERT INTO productos (nombre, codigo, precio, stock, imagen, descripcion) VALUES (?, ?, ?, ?, ?, ?)";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$nombre, $codigo, $precio, $stock, $nombreImagen, $descripcion]);

    echo json_encode(["success" => true, "mensaje" => "Producto guardado"]);

} catch (Exception $e) {
    echo json_encode(["success" => false, "mensaje" => $e->getMessage()]);
}
?>