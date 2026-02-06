<?php
session_start();
header("Content-Type: application/json");
require "../../config/database.php";

/* =======================
   CREAR CATEGORÍA
======================= */
if ($_POST['accion'] === 'crear') {

    $stmt = $conexion->prepare("
        INSERT INTO categorias (nombre)
        VALUES (?)
    ");
    $stmt->bind_param("s", $_POST['nombre']);
    $stmt->execute();

    echo json_encode(['ok'=>true]);
    exit;
}

/* =======================
   LISTAR CATEGORÍAS
======================= */
if ($_GET['accion'] === 'listar') {

    $res = $conexion->query("SELECT * FROM categorias");

    echo json_encode([
        'ok'=>true,
        'categorias'=>$res->fetch_all(MYSQLI_ASSOC)
    ]);
    exit;
}

/* =======================
   ASIGNAR PREMIO
======================= */
if ($_POST['accion'] === 'asignar') {

    $stmt = $conexion->prepare("
        INSERT INTO premios (id_categoria, id_inscripcion, puesto)
        VALUES (?,?,?)
    ");
    $stmt->bind_param(
        "iis",
        $_POST['categoria'],
        $_POST['inscripcion'],
        $_POST['puesto']
    );
    $stmt->execute();

    echo json_encode(['ok'=>true]);
}
