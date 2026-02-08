<?php
session_start();
header("Content-Type: application/json; charset=UTF-8");

require "../../config/database.php";

/* SOLO ORGANIZADOR */
if (!isset($_SESSION['tipo']) || $_SESSION['tipo'] !== 'organizador') {
    echo json_encode(['ok' => false, 'error' => 'No autorizado']);
    exit;
}

$accion = $_GET['accion'] ?? '';

/* =========================
   ESTADO
========================= */
if ($accion === 'estado') {
    $res = $conexion->query("SELECT modo FROM gala WHERE id = 1");
    echo json_encode(['ok' => true, 'modo' => $res->fetch_assoc()['modo']]);
    exit;
}

/* =========================
   CAMBIAR MODO
========================= */
if ($accion === 'cambiarModo') {
    $res = $conexion->query("SELECT modo FROM gala WHERE id = 1");
    $actual = $res->fetch_assoc()['modo'];
    $nuevo = $actual === 'PRE' ? 'POST' : 'PRE';

    $stmt = $conexion->prepare("UPDATE gala SET modo = ? WHERE id = 1");
    $stmt->bind_param("s", $nuevo);
    $stmt->execute();

    echo json_encode(['ok' => true, 'modo' => $nuevo]);
    exit;
}

/* =========================
   CREAR SECCIÓN
========================= */
if ($accion === 'crearSeccion') {
    $titulo = trim($_POST['titulo'] ?? '');
    $hora = trim($_POST['hora'] ?? '');
    $sala = trim($_POST['sala'] ?? '');
    $descripcion = trim($_POST['descripcion'] ?? '');

    $errors = [];
    if ($titulo === '') $errors['titulo'] = 'Título obligatorio';
    if ($hora === '') $errors['hora'] = 'Hora obligatoria';
    if ($sala === '') $errors['sala'] = 'Sala obligatoria';
    if ($descripcion === '') $errors['descripcion'] = 'Descripción obligatoria';

    if ($errors) {
        echo json_encode(['ok' => false, 'errors' => $errors]);
        exit;
    }

    // Conflicto: misma hora + sala
    $check = $conexion->prepare("
        SELECT id FROM gala_secciones
        WHERE hora = ? AND sala = ?
    ");
    $check->bind_param("ss", $hora, $sala);
    $check->execute();
    if ($check->get_result()->num_rows > 0) {
        echo json_encode(['ok' => false, 'errors' => ['hora' => 'Ya existe una sección en esa sala y hora']]);
        exit;
    }

    $stmt = $conexion->prepare("
        INSERT INTO gala_secciones (titulo, hora, sala, descripcion)
        VALUES (?, ?, ?, ?)
    ");
    $stmt->bind_param("ssss", $titulo, $hora, $sala, $descripcion);
    $stmt->execute();

    echo json_encode(['ok' => true]);
    exit;
}

/* =========================
   LISTAR SECCIONES
========================= */
if ($accion === 'listarSecciones') {
    $res = $conexion->query("
        SELECT * FROM gala_secciones
        ORDER BY hora
    ");
    echo json_encode(['ok' => true, 'secciones' => $res->fetch_all(MYSQLI_ASSOC)]);
    exit;
}

/* =========================
   BORRAR SECCIÓN
========================= */
if ($accion === 'borrarSeccion') {
    $id = intval($_GET['id'] ?? 0);
    $stmt = $conexion->prepare("DELETE FROM gala_secciones WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();

    echo json_encode(['ok' => true]);
    exit;
}

/* =========================
   GUARDAR RESUMEN
========================= */
if ($accion === 'guardarResumen') {
    $stmt = $conexion->prepare("UPDATE gala SET texto_resumen = ? WHERE id = 1");
    $stmt->bind_param("s", $_POST['texto']);
    $stmt->execute();
    echo json_encode(['ok' => true]);
    exit;
}

/* =========================
   SUBIR IMAGEN
========================= */
if ($accion === 'subirImagen') {
    $dir = "../../uploads/";
    if (!is_dir($dir)) mkdir($dir, 0777, true);

    $nombre = uniqid() . "_" . basename($_FILES['imagen']['name']);
    move_uploaded_file($_FILES['imagen']['tmp_name'], $dir . $nombre);

    $stmt = $conexion->prepare("INSERT INTO gala_imagenes (ruta) VALUES (?)");
    $stmt->bind_param("s", $nombre);
    $stmt->execute();

    echo json_encode(['ok' => true]);
    exit;
}

echo json_encode(['ok' => false, 'error' => 'Acción no válida']);
exit;