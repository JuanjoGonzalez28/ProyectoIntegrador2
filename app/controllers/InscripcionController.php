<?php
session_start();
header("Content-Type: application/json");
require "../../config/database.php";

/* =======================
   LISTAR CANDIDATURAS
======================= */
if ($_GET['accion'] === 'listar') {

    if (!isset($_SESSION['tipo']) || $_SESSION['tipo'] !== 'organizador') {
        echo json_encode(['ok'=>false,'error'=>'No autorizado']);
        exit;
    }

    $res = $conexion->query("
        SELECT i.*, p.usuario
        FROM inscripciones i
        JOIN participantes p ON p.id_participante = i.id_usuario
        ORDER BY i.fecha DESC
    ");

    echo json_encode([
        'ok'=>true,
        'candidaturas'=>$res->fetch_all(MYSQLI_ASSOC)
    ]);
    exit;
}

/* =======================
   ACEPTAR
======================= */
if ($_GET['accion'] === 'aceptar') {

    $id = intval($_GET['id']);

    $conexion->query("
        UPDATE inscripciones
        SET estado='ACEPTADO'
        WHERE id_inscripcion=$id
    ");

    echo json_encode(['ok'=>true]);
    exit;
}

/* =======================
   RECHAZAR
======================= */
if ($_GET['accion'] === 'rechazar') {

    $id = intval($_POST['id']);
    $motivo = $_POST['motivo'];

    $stmt = $conexion->prepare("
        UPDATE inscripciones
        SET estado='RECHAZADO', motivo_rechazo=?
        WHERE id_inscripcion=?
    ");
    $stmt->bind_param("si", $motivo, $id);
    $stmt->execute();

    echo json_encode(['ok'=>true]);
    exit;
}

/* =======================
   NOMINAR
======================= */
if ($_GET['accion'] === 'nominar') {

    $id = intval($_GET['id']);

    $conexion->query("
        UPDATE inscripciones
        SET estado='NOMINADO'
        WHERE id_inscripcion=$id
    ");

    echo json_encode(['ok'=>true]);
    exit;
}

/* =======================
   INSCRIPCIÓN ALUMNO
======================= */
if (!isset($_SESSION['id_usuario'])) {

    $hash = password_hash($_POST['contrasena'], PASSWORD_DEFAULT);

    $stmt = $conexion->prepare("
        INSERT INTO participantes (usuario, contrasena)
        VALUES (?,?)
    ");
    $stmt->bind_param("ss", $_POST['usuario'], $hash);
    $stmt->execute();

    $_SESSION['id_usuario'] = $conexion->insert_id;
    $_SESSION['tipo'] = 'participante';
}

$id_usuario = $_SESSION['id_usuario'];

$stmt = $conexion->prepare("
    INSERT INTO inscripciones
    (id_usuario, sinopsis, email, dni, expediente, video)
    VALUES (?,?,?,?,?,?)
");

$stmt->bind_param(
    "isssss",
    $id_usuario,
    $_POST['sinopsis'],
    $_POST['email'],
    $_POST['dni'],
    $_POST['expediente'],
    $_POST['video']
);

$stmt->execute();

echo json_encode(['ok'=>true]);
