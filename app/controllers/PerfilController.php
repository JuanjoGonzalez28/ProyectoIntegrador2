<?php
session_start();
header("Content-Type: application/json");
require "../../config/database.php";

$id = $_SESSION['id_usuario'];

$res = $conexion->query("
    SELECT i.estado, c.nombre categoria, p.puesto
    FROM inscripciones i
    LEFT JOIN premios p ON p.id_inscripcion=i.id_inscripcion
    LEFT JOIN categorias c ON c.id_categoria=p.id_categoria
    WHERE i.id_usuario=$id
");

echo json_encode($res->fetch_assoc());
