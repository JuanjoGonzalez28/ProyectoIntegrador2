<?php
session_start();
header("Content-Type: application/json");
require "../../config/database.php";

/* =========================
   CONSULTA ESTADO
========================= */
if ($_GET['accion'] ?? '' === 'estado') {

    $respuesta = [
        'tieneSesion' => isset($_SESSION['id_usuario']),
        'total' => 0
    ];

    if (isset($_SESSION['id_usuario'])) {
        $id = $_SESSION['id_usuario'];
        $res = $conexion->query("SELECT COUNT(*) total FROM inscripciones WHERE id_usuario=$id");
        $respuesta['total'] = $res->fetch_assoc()['total'];
    }

    echo json_encode($respuesta);
    exit;
}

/* =========================
   GUARDAR INSCRIPCIÓN
========================= */

// Si no hay sesión → crear usuario
if (!isset($_SESSION['id_usuario'])) {

    if (empty($_POST['nombre_responsable']) || empty($_POST['contrasena'])) {
        echo json_encode(['ok'=>false,'error'=>'Datos de usuario incompletos']);
        exit;
    }

    $hash = password_hash($_POST['contrasena'], PASSWORD_DEFAULT);

    $stmt = $conexion->prepare("
        INSERT INTO participantes (usuario, contrasena)
        VALUES (?,?)
    ");
    $stmt->bind_param("ss", $_POST['nombre_responsable'], $hash);
    $stmt->execute();

    $_SESSION['id_usuario'] = $conexion->insert_id;
    $_SESSION['usuario'] = $_POST['nombre_responsable'];
    $_SESSION['tipo'] = 'participante';
}

$id_usuario = $_SESSION['id_usuario'];

/* Máximo 2 candidaturas */
$res = $conexion->query("SELECT COUNT(*) total FROM inscripciones WHERE id_usuario=$id_usuario");
if ($res->fetch_assoc()['total'] >= 2) {
    echo json_encode(['ok'=>false,'error'=>'Has alcanzado el máximo de candidaturas']);
    exit;
}

/* Guardar archivos */
$dir = "../../uploads/";
@mkdir($dir, 0777, true);

$ficha = $dir . uniqid() . "_" . $_FILES['ficha']['name'];
$cartel = $dir . uniqid() . "_" . $_FILES['cartel']['name'];

move_uploaded_file($_FILES['ficha']['tmp_name'], $ficha);
move_uploaded_file($_FILES['cartel']['tmp_name'], $cartel);

/* Insertar inscripción */
$stmt = $conexion->prepare("
    INSERT INTO inscripciones
    (id_usuario, ficha, cartel, sinopsis, email, dni, expediente, video)
    VALUES (?,?,?,?,?,?,?,?)
");

$stmt->bind_param(
    "isssssss",
    $id_usuario,
    $ficha,
    $cartel,
    $_POST['sinopsis'],
    $_POST['email'] ,
    $_POST['dni'] ,
    $_POST['expediente'] ,
    $_POST['video']
);

$stmt->execute();

echo json_encode(['ok'=>true]);
