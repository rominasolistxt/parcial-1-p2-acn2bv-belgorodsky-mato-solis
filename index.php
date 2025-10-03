<?php
include("datos.php");

//
// GET
//
$q = isset($_GET['q']) ? trim($_GET['q']) : '';
$categoria = isset($_GET['categoria']) ? trim($_GET['categoria']) : '';
$tema = (isset($_GET['tema']) && strtolower($_GET['tema']) === 'oscuro') ? 'oscuro' : 'claro';

// Filtrado basico
$filtrados = [];
foreach ($items as $item) {
    $okNombre = ($q === '') || (stripos($item['nombre'], $q) !== false);
    $okCat = ($categoria === '') || (strcasecmp($item['categoria'], $categoria) === 0);
    if ($okNombre && $okCat) $filtrados[] = $item;
}

// URL helper
function urlConParametros($params)
{
    $base = strtok($_SERVER['REQUEST_URI'], '?');
    $todos = array_merge($_GET, $params);
    foreach ($todos as $k => $v) {
        if ($v === null) unset($todos[$k]);
    }
    $qs = http_build_query($todos);
    return $base . ($qs ? '?' . $qs : '');
}

// Categorias para el <select> del filtro
$cats = [];
foreach ($items as $it) {
    if (!in_array($it['categoria'], $cats)) $cats[] = $it['categoria'];
}
sort($cats);

//
// POST
//
$errores = [];
$sugerencia = null;
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $procesarPost = include("sugerir.php");
    $errores = $procesarPost['errores'];
    $sugerencia = $procesarPost['sugerencia'];
}
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8" />
    <title>Top Series Netflix AR 🇦🇷</title>
    <link rel="stylesheet" href="./css/style.css">
</head>

<body class="tema-<?php echo htmlspecialchars($tema); ?>">
    <header>
        <h1>Top Series Netflix Argentina 🇦🇷</h1>
        <div class="tema-switch">
            <a href="<?php echo htmlspecialchars(urlConParametros(['tema' => 'claro'])); ?>" class="<?php echo ($tema === 'claro') ? 'activo' : ''; ?>">Claro</a>
            <a href="<?php echo htmlspecialchars(urlConParametros(['tema' => 'oscuro'])); ?>" class="<?php echo ($tema === 'oscuro') ? 'activo' : ''; ?>">Oscuro</a>
        </div>
    </header>

    <div class="container">
        <!-- Filtros existentes (GET) -->
        <form method="GET" class="filtros">
            <input type="hidden" name="tema" value="<?php echo htmlspecialchars($tema); ?>">
            <input type="text" name="q" placeholder="Buscar nombre..." value="<?php echo htmlspecialchars($q); ?>">
            <select name="categoria">
                <option value="">Todas</option>
                <?php foreach ($cats as $cat): ?>
                    <option value="<?php echo htmlspecialchars($cat); ?>" <?php echo (strcasecmp($cat, $categoria) === 0) ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($cat); ?>
                    </option>
                <?php endforeach; ?>
            </select>
            <button type="submit">Filtrar</button>
        </form>

        <?php if (count($filtrados) === 0): ?>
            <div class="no-result">No se encontraron resultados.</div>
        <?php else: ?>
            <div class="grid">
                <?php foreach ($filtrados as $it): ?>
                    <article class="card">
                        <img src="<?php echo htmlspecialchars($it['imagen']); ?>" alt="<?php echo htmlspecialchars($it['nombre']); ?>">
                        <div class="content">
                            <h3><?php echo htmlspecialchars($it['nombre']); ?></h3>
                            <p><?php echo htmlspecialchars($it['descripcion']); ?></p>
                            <span class="badge"><?php echo htmlspecialchars($it['categoria']); ?></span>
                        </div>
                    </article>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <!-- Sugerir nuevo ítem (POST) -->
        <section class="sugerir">
            <h2>Sugerir una nueva serie</h2>

            <?php if ($_SERVER['REQUEST_METHOD'] === 'POST' && empty($errores)): ?>
                <!-- Confirmación si pasó la validación backend -->
                <div class="alert ok">¡Gracias! Recibimos tu sugerencia.</div>

                <article class="card">
                    <img src="<?php echo htmlspecialchars($sugerencia['imagen']); ?>" alt="<?php echo htmlspecialchars($sugerencia['nombre']); ?>">
                    <div class="content">
                        <h3><?php echo htmlspecialchars($sugerencia['nombre']); ?></h3>
                        <p><?php echo htmlspecialchars($sugerencia['descripcion']); ?></p>
                        <span class="badge"><?php echo htmlspecialchars($sugerencia['categoria']); ?></span>
                    </div>
                </article>
            <?php else: ?>
                <!-- Si hay errores backend, los muestro -->
                <?php if (!empty($errores)): ?>
                    <div class="alert error">
                        <ul>
                            <?php foreach ($errores as $msg): ?>
                                <li><?php echo htmlspecialchars($msg); ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                <?php endif; ?>

                <!-- Formulario POST: required (frontend) + if(empty(...)) (backend) -->
                <form method="POST" class="form-sugerir">
                    <div class="row">
                        <label for="nombre">Nombre*</label>
                        <input id="nombre" name="nombre" type="text" required
                            value="<?php echo htmlspecialchars($sugerencia['nombre']); ?>">
                        <!-- Mensaje frontend: el navegador mostrará el “campo requerido” por el atributo required -->
                    </div>

                    <div class="row">
                        <label for="categoria">Categoría*</label>
                        <input id="categoria" name="categoria" type="text" list="categorias" required
                            value="<?php echo htmlspecialchars($sugerencia['categoria']); ?>">
                        <datalist id="categorias">
                            <?php foreach ($cats as $cat): ?>
                                <option value="<?php echo htmlspecialchars($cat); ?>"><?php echo htmlspecialchars($cat); ?></option>
                            <?php endforeach; ?>
                        </datalist>
                    </div>

                    <div class="row">
                        <label for="descripcion">Descripción*</label>
                        <textarea id="descripcion" name="descripcion" rows="3" required><?php echo htmlspecialchars($sugerencia['descripcion']); ?></textarea>
                    </div>

                    <div class="row">
                        <label for="imagen">URL de imagen*</label>
                        <input id="imagen" name="imagen" type="url" required
                            placeholder="https://..."
                            value="<?php echo htmlspecialchars($sugerencia['imagen']); ?>">
                    </div>

                    <button type="submit">Enviar sugerencia</button>
                </form>
            <?php endif; ?>
        </section>
    </div>
</body>

</html>