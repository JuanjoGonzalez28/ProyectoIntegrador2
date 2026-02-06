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

/* ESTADO */
if ($accion === 'estado') {
    $res = $conexion->query("SELECT modo FROM gala WHERE id = 1");
    $gala = $res->fetch_assoc();
    echo json_encode(['ok' => true, 'modo' => $gala['modo']]);
    exit;
}

/* CAMBIAR MODO */
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

/* CREAR SECCION */
if ($accion === 'crearSeccion') {
    $titulo = trim($_POST['titulo'] ?? '');
    $fecha = trim($_POST['fecha'] ?? '');
    $hora = trim($_POST['hora'] ?? '');
    $sala = trim($_POST['sala'] ?? '');
    $descripcion = trim($_POST['descripcion'] ?? '');

    $errors = [];

    if ($titulo === '') $errors['titulo'] = 'Título obligatorio';
    if ($fecha === '') $errors['fecha'] = 'Fecha obligatoria';
    if ($hora === '') $errors['hora'] = 'Hora obligatoria';
    if ($sala === '') $errors['sala'] = 'Sala obligatoria';
    if ($descripcion === '') $errors['descripcion'] = 'Descripción obligatoria';

    if (!empty($errors)) {
        echo json_encode(['ok' => false, 'errors' => $errors]);
        exit;
    }

    // Comprobar conflicto (misma fecha, hora y sala)
    $check = $conexion->prepare("
        SELECT id_seccion FROM gala_secciones
        WHERE fecha = ? AND hora = ? AND sala = ?
    ");
    $check->bind_param("sss", $fecha, $hora, $sala);
    $check->execute();
    $res = $check->get_result();
    if ($res->num_rows > 0) {
        echo json_encode(['ok' => false, 'errors' => ['hora' => 'Ya existe una sección en esa sala, fecha y hora']]);
        exit;
    }

    $stmt = $conexion->prepare("
        INSERT INTO gala_secciones (titulo, fecha, hora, sala, descripcion)
        VALUES (?, ?, ?, ?, ?)
    ");
    $stmt->bind_param("sssss", $titulo, $fecha, $hora, $sala, $descripcion);
    if ($stmt->execute()) {
        echo json_encode(['ok' => true]);
    } else {
        echo json_encode(['ok' => false, 'error' => 'Error al guardar la sección']);
    }
    exit;
}

/* EDITAR SECCION */
if ($accion === 'editarSeccion') {
    $id = intval($_POST['id'] ?? 0);
    $titulo = trim($_POST['titulo'] ?? '');
    $fecha = trim($_POST['fecha'] ?? '');
    $hora = trim($_POST['hora'] ?? '');
    $sala = trim($_POST['sala'] ?? '');
    $descripcion = trim($_POST['descripcion'] ?? '');

    $errors = [];
    if ($id <= 0) $errors['id'] = 'Id inválido';
    if ($titulo === '') $errors['titulo'] = 'Título obligatorio';
    if ($fecha === '') $errors['fecha'] = 'Fecha obligatoria';
    if ($hora === '') $errors['hora'] = 'Hora obligatoria';
    if ($sala === '') $errors['sala'] = 'Sala obligatoria';
    if ($descripcion === '') $errors['descripcion'] = 'Descripción obligatoria';

    if (!empty($errors)) {
        echo json_encode(['ok' => false, 'errors' => $errors]);
        exit;
    }

    // Comprobar conflicto excluyendo la propia sección
    $check = $conexion->prepare("
        SELECT id_seccion FROM gala_secciones
        WHERE fecha = ? AND hora = ? AND sala = ? AND id_seccion <> ?
    ");
    $check->bind_param("sssi", $fecha, $hora, $sala, $id);
    $check->execute();
    $res = $check->get_result();
    if ($res->num_rows > 0) {
        echo json_encode(['ok' => false, 'errors' => ['hora' => 'Conflicto con otra sección en esa sala, fecha y hora']]);
        exit;
    }

    $stmt = $conexion->prepare("
        UPDATE gala_secciones
        SET titulo = ?, fecha = ?, hora = ?, sala = ?, descripcion = ?
        WHERE id_seccion = ?
    ");
    $stmt->bind_param("sssssi", $titulo, $fecha, $hora, $sala, $descripcion, $id);
    if ($stmt->execute()) {
        echo json_encode(['ok' => true]);
    } else {
        echo json_encode(['ok' => false, 'error' => 'Error al actualizar la sección']);
    }
    exit;
}

/* BORRAR SECCION */
if ($accion === 'borrarSeccion') {
    $id = intval($_GET['id'] ?? 0);
    if ($id <= 0) {
        echo json_encode(['ok' => false, 'error' => 'Id inválido']);
        exit;
    }
    $stmt = $conexion->prepare("DELETE FROM gala_secciones WHERE id_seccion = ?");
    $stmt->bind_param("i", $id);
    if ($stmt->execute()) echo json_encode(['ok' => true]);
    else echo json_encode(['ok' => false, 'error' => 'Error al borrar']);
    exit;
}

/* LISTAR SECCIONES */
if ($accion === 'listarSecciones') {
    $res = $conexion->query("SELECT * FROM gala_secciones ORDER BY fecha, hora");
    $secciones = $res->fetch_all(MYSQLI_ASSOC);
    echo json_encode(['ok' => true, 'secciones' => $secciones]);
    exit;
}

/* GUARDAR RESUMEN */
if ($accion === 'guardarResumen') {
    $stmt = $conexion->prepare("UPDATE gala SET texto_resumen = ? WHERE id = 1");
    $stmt->bind_param("s", $_POST['texto']);
    $stmt->execute();
    echo json_encode(['ok' => true]);
    exit;
}

/* SUBIR IMAGEN */
if ($accion === 'subirImagen') {
    if (!isset($_FILES['imagen'])) {
        echo json_encode(['ok' => false, 'error' => 'No llega imagen']);
        exit;
    }
    $dir = "../../uploads/";
    if (!is_dir($dir)) mkdir($dir, 0777, true);
    $nombre = uniqid() . "_" . basename($_FILES['imagen']['name']);
    if (!move_uploaded_file($_FILES['imagen']['tmp_name'], $dir . $nombre)) {
        echo json_encode(['ok' => false, 'error' => 'Error moviendo archivo']);
        exit;
    }
    $stmt = $conexion->prepare("INSERT INTO gala_imagenes (ruta) VALUES (?)");
    $stmt->bind_param("s", $nombre);
    $stmt->execute();
    echo json_encode(['ok' => true]);
    exit;
}

echo json_encode(['ok' => false, 'error' => 'Acción no válida']);
exit;
