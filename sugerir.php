<?php
$errores = [];
$sugerencia = [
    'nombre' => '',
    'categoria' => '',
    'descripcion' => '',
    'imagen' => '',
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $sugerencia['nombre'] = isset($_POST['nombre']) ? trim($_POST['nombre']) : '';
    $sugerencia['categoria'] = isset($_POST['categoria']) ? trim($_POST['categoria']) : '';
    $sugerencia['descripcion'] = isset($_POST['descripcion']) ? trim($_POST['descripcion']) : '';
    $sugerencia['imagen'] = isset($_POST['imagen']) ? trim($_POST['imagen']) : '';

    if (empty($sugerencia['nombre']))      $errores['nombre'] = 'El nombre es obligatorio.';
    if (empty($sugerencia['categoria']))   $errores['categoria'] = 'La categoría es obligatoria.';
    if (empty($sugerencia['descripcion'])) $errores['descripcion'] = 'La descripción es obligatoria.';
    if (empty($sugerencia['imagen']))      $errores['imagen'] = 'La URL de la imagen es obligatoria.';
}

// Devolvemos los datos
return [
    'errores' => $errores,
    'sugerencia' => $sugerencia,
];
