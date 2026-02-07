<?php
session_start();
header("Content-Type: application/json");
require "../../config/database.php";

if (!isset($_SESSION['id_usuario'])) {
    echo json_encode(['ok' => false, 'error' => 'No autorizado']);
    exit;
}

$id = intval($_SESSION['id_usuario']);

/* DATOS PERSONALES + INSCRIPCIÓN */
$datos = $conexion->query("
    SELECT 
        p.usuario,
        i.email,
        i.dni,
        i.expediente,
        i.estado,
        i.motivo_rechazo,
        i.id_inscripcion
    FROM participantes p
    LEFT JOIN inscripciones i ON i.id_usuario = p.id_usuario
    WHERE p.id_usuario = $id
")->fetch_assoc();

/* PREMIO (SI EXISTE) */
$premios = [];
if (!empty($datos['id_inscripcion'])) {
    $res = $conexion->query("
        SELECT pr.nombre
        FROM premios_ganadores pg
        JOIN premios pr ON pr.id_premio = pg.id_premio
        WHERE pg.id_inscripcion = {$datos['id_inscripcion']}
    ");
    $premios = $res->fetch_all(MYSQLI_ASSOC);
}

echo json_encode([
    'ok' => true,
    'datos' => $datos,
    'premios' => $premios
]);
