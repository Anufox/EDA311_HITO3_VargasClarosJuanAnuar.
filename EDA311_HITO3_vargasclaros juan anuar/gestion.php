<?php
require_once __DIR__ . '/conexion.php';
require_once __DIR__ . '/ListaDoble.php';

function h($v): string { return htmlspecialchars((string)($v ?? ''), ENT_QUOTES, 'UTF-8'); }
function qi(string $v): string { return '`' . str_replace('`', '``', $v) . '`'; }
function mensaje(string $m, string $tipo='ok'): void {
    header('Location: ' . basename($_SERVER['PHP_SELF']) . '?mensaje=' . urlencode($m) . '&tipo=' . urlencode($tipo)); exit;
}

$columnas = []; $errores = []; $registros = []; $lista = new ListaDoble();
$clavePrimaria = null; $registroEditar = null; $campoOrden = ''; $direccion = 'asc';
$permitidas = ['clientes', 'pedidos', 'productos'];
if (!isset($tabla) || !in_array($tabla, $permitidas, true)) die('Tabla no permitida.');

if (!$conn) {
    $errorConexion = $errorConexion ?? 'No se pudo conectar con MySQL.';
} else {
    $res = $conn->query('SHOW FULL COLUMNS FROM ' . qi($tabla));
    if (!$res) $errores[] = 'No se pudo leer la tabla ' . $tabla . ': ' . $conn->error;
    else {
        while ($c = $res->fetch_assoc()) {
            $columnas[$c['Field']] = $c;
            if (($c['Key'] ?? '') === 'PRI' && $clavePrimaria === null) $clavePrimaria = $c['Field'];
        }
        $res->free();
    }
    if (!$columnas) $errores[] = "No se encontró la tabla '$tabla'. Verifica el nombre de la base de datos y los permisos.";
    elseif ($clavePrimaria === null) $errores[] = "La tabla '$tabla' no tiene una clave primaria detectable.";

    if ($_SERVER['REQUEST_METHOD'] === 'POST' && !$errores) {
        $accion = $_POST['accion'] ?? '';
        $id = (string)($_POST['id'] ?? '');
        if ($accion === 'eliminar') {
            $st = $conn->prepare('DELETE FROM ' . qi($tabla) . ' WHERE ' . qi($clavePrimaria) . ' = ?');
            if (!$st) $errores[] = 'Error al preparar eliminación: ' . $conn->error;
            else {
                $st->bind_param('s', $id);
                if ($st->execute()) { $st->close(); mensaje('Registro eliminado correctamente.'); }
                $errores[] = 'No se pudo eliminar. Puede existir una relación con otra tabla. ' . $st->error; $st->close();
            }
        } elseif ($accion === 'guardar') {
            $editar = ($_POST['modo'] ?? '') === 'editar'; $datos = []; $tipos = '';
            foreach ($columnas as $nombre => $col) {
                if ($nombre === $clavePrimaria && (stripos($col['Extra'] ?? '', 'auto_increment') !== false || $editar)) continue;
                if (!array_key_exists($nombre, $_POST)) continue;
                $v = trim((string)$_POST[$nombre]);
                if ($v === '' && ($col['Null'] ?? '') === 'YES') $v = null;
                $datos[$nombre] = $v;
                $tipo = strtolower($col['Type'] ?? '');
                $tipos .= (preg_match('/int|decimal|float|double|numeric|bit/', $tipo) ? 'd' : 's');
            }
            if (!$datos) $errores[] = 'No se recibieron campos para guardar.';
            else {
                if ($editar) {
                    $sets = []; foreach (array_keys($datos) as $n) $sets[] = qi($n) . ' = ?';
                    $sql = 'UPDATE ' . qi($tabla) . ' SET ' . implode(', ', $sets) . ' WHERE ' . qi($clavePrimaria) . ' = ?';
                    $st = $conn->prepare($sql);
                    if (!$st) $errores[] = 'Error al preparar modificación: ' . $conn->error;
                    else {
                        $tipos .= 's'; $params = array_values($datos); $params[] = $id;
                        $st->bind_param($tipos, ...$params);
                        if ($st->execute()) { $st->close(); mensaje('Registro actualizado correctamente.'); }
                        $errores[] = 'No se pudo modificar: ' . $st->error; $st->close();
                    }
                } else {
                    $colsSQL = array_map('qi', array_keys($datos)); $marks = implode(', ', array_fill(0, count($datos), '?'));
                    $st = $conn->prepare('INSERT INTO ' . qi($tabla) . ' (' . implode(', ', $colsSQL) . ') VALUES (' . $marks . ')');
                    if (!$st) $errores[] = 'Error al preparar registro: ' . $conn->error;
                    else {
                        $params = array_values($datos); $st->bind_param($tipos, ...$params);
                        if ($st->execute()) { $st->close(); mensaje('Registro guardado correctamente.'); }
                        $errores[] = 'No se pudo registrar: ' . $st->error; $st->close();
                    }
                }
            }
        }
    }

    if (!$errores && isset($_GET['editar'])) {
        $st = $conn->prepare('SELECT * FROM ' . qi($tabla) . ' WHERE ' . qi($clavePrimaria) . ' = ? LIMIT 1');
        if ($st) {
            $idEdit = (string)$_GET['editar']; $st->bind_param('s', $idEdit);
            if ($st->execute()) $registroEditar = $st->get_result()->fetch_assoc() ?: null;
            $st->close();
        }
    }
    if (!$errores) {
        $campoOrden = (string)($_GET['orden'] ?? ($ordenarPor ?? $clavePrimaria));
        if (!isset($columnas[$campoOrden])) $campoOrden = $clavePrimaria;
        $direccion = (($_GET['dir'] ?? 'asc') === 'desc') ? 'desc' : 'asc';
        $res = $conn->query('SELECT * FROM ' . qi($tabla));
        if (!$res) $errores[] = 'Error al consultar los registros: ' . $conn->error;
        else {
            while ($fila = $res->fetch_assoc()) $lista->insertar($fila);
            $res->free();
            $lista->ordenarBurbuja($campoOrden, $direccion === 'asc');
            foreach ($lista->recorrer() as $nodo) $registros[] = $nodo->dato;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="es"><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1">
<title><?= h($titulo ?? $tabla) ?> | Tienda Vargas</title><link rel="stylesheet" href="estilos.css"></head>
<body><header><h1>Gestión de <?= h($titulo ?? $tabla) ?></h1><p>PHP, MySQL, lista doblemente enlazada y Bubble Sort</p></header><main>
<p><a href="index.php">← Volver al inicio</a></p>
<?php if (!$conn): ?><div class="estado error"><strong>Error de conexión:</strong> <?= h($errorConexion ?? 'No se pudo conectar.') ?><br>Verifica host, puerto, usuario, contraseña, base de datos y acceso a la red del docente.</div>
<?php else: ?><div class="estado ok">Conectado a la base de datos <strong><?= h($bd) ?></strong>.</div><?php endif; ?>
<?php if (isset($_GET['mensaje'])): ?><div class="estado <?= h($_GET['tipo'] ?? 'ok') ?>"><?= h($_GET['mensaje']) ?></div><?php endif; ?>
<?php foreach ($errores as $e): ?><div class="estado error"><?= h($e) ?></div><?php endforeach; ?>
<?php if ($conn && !$errores): ?>
<section class="panel"><h2><?= $registroEditar ? 'Modificar registro' : 'Registrar nuevo registro' ?></h2>
<form method="post"><input type="hidden" name="accion" value="guardar"><input type="hidden" name="modo" value="<?= $registroEditar ? 'editar' : 'nuevo' ?>">
<?php if ($registroEditar): ?><input type="hidden" name="id" value="<?= h($registroEditar[$clavePrimaria]) ?>"><?php endif; ?>
<div class="form-grid">
<?php foreach ($columnas as $nombre => $col):
    $auto = stripos($col['Extra'] ?? '', 'auto_increment') !== false;
    if (($auto && !$registroEditar) || ($registroEditar && $nombre === $clavePrimaria)) continue;
    $sqlTipo = strtolower($col['Type'] ?? ''); $inputTipo = 'text';
    if (preg_match('/int|decimal|float|double|numeric/', $sqlTipo)) $inputTipo = 'number';
    if (strpos($sqlTipo, 'date') === 0 && strpos($sqlTipo, 'datetime') !== 0) $inputTipo = 'date';
    if (strpos($sqlTipo, 'datetime') === 0 || strpos($sqlTipo, 'timestamp') === 0) $inputTipo = 'datetime-local';
    $valor = $registroEditar[$nombre] ?? '';
    if ($inputTipo === 'datetime-local' && $valor) $valor = str_replace(' ', 'T', substr((string)$valor, 0, 16));
    $requerido = (($col['Null'] ?? 'YES') === 'NO' && ($col['Default'] ?? null) === null && !$auto);
?>
<label><span><?= h($nombre) ?><?= $requerido ? ' *' : '' ?></span>
<?php if (preg_match('/^enum\((.*)\)$/i', $sqlTipo, $m)): preg_match_all("/'((?:\\\\'|[^'])*)'/", $m[1], $opciones); ?>
<select name="<?= h($nombre) ?>" <?= $requerido ? 'required' : '' ?>><?php if (!$requerido): ?><option value="">-- Seleccionar --</option><?php endif; ?><?php foreach ($opciones[1] as $op): $op = stripslashes($op); ?><option value="<?= h($op) ?>" <?= ((string)$valor === $op) ? 'selected' : '' ?>><?= h($op) ?></option><?php endforeach; ?></select>
<?php elseif (strpos($sqlTipo, 'text') !== false): ?><textarea name="<?= h($nombre) ?>" <?= $requerido ? 'required' : '' ?>><?= h($valor) ?></textarea>
<?php else: ?><input type="<?= h($inputTipo) ?>" name="<?= h($nombre) ?>" value="<?= h($valor) ?>" <?= $inputTipo === 'number' ? 'step="any"' : '' ?> <?= $requerido ? 'required' : '' ?>><?php endif; ?>
<small><?= h($col['Type']) ?><?= ($col['Null'] ?? 'YES') === 'YES' ? ' · opcional' : ' · obligatorio' ?></small></label>
<?php endforeach; ?></div><button type="submit"><?= $registroEditar ? 'Guardar cambios' : 'Registrar' ?></button>
<?php if ($registroEditar): ?><a class="boton-secundario" href="<?= h(basename($_SERVER['PHP_SELF'])) ?>">Cancelar</a><?php endif; ?></form></section>
<section class="panel"><h2>Registros existentes</h2><p>Cargados en la lista doblemente enlazada: <strong><?= count($registros) ?></strong></p>
<form method="get" class="orden-form"><label>Ordenar por <select name="orden"><?php foreach ($columnas as $n => $c): ?><option value="<?= h($n) ?>" <?= $campoOrden === $n ? 'selected' : '' ?>><?= h($n) ?></option><?php endforeach; ?></select></label>
<label>Dirección <select name="dir"><option value="asc" <?= $direccion === 'asc' ? 'selected' : '' ?>>Ascendente</option><option value="desc" <?= $direccion === 'desc' ? 'selected' : '' ?>>Descendente</option></select></label><button type="submit">Ordenar con Bubble Sort</button></form>
<div class="tabla-contenedor"><table><thead><tr><?php foreach ($columnas as $n => $c): ?><th><?= h($n) ?></th><?php endforeach; ?><th>Acciones</th></tr></thead><tbody>
<?php if (!$registros): ?><tr><td colspan="<?= count($columnas)+1 ?>">No hay registros.</td></tr><?php else: foreach ($registros as $fila): ?><tr><?php foreach ($columnas as $n => $c): ?><td><?= h($fila[$n] ?? '') ?></td><?php endforeach; ?><td class="acciones"><a href="?editar=<?= urlencode((string)$fila[$clavePrimaria]) ?>">Modificar</a><form method="post" onsubmit="return confirm('¿Seguro que quieres eliminar este registro?');"><input type="hidden" name="accion" value="eliminar"><input type="hidden" name="id" value="<?= h($fila[$clavePrimaria]) ?>"><button class="peligro" type="submit">Eliminar</button></form></td></tr><?php endforeach; endif; ?>
</tbody></table></div></section><?php endif; ?></main></body></html>
