<?php require_once "conexion.php"; ?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Gestión Tienda Vargas</title>
  <link rel="stylesheet" href="estilos.css">
</head>
<body>
  <header>
    <h1>Gestion de Tienda Vargas</h1>
  </header>
  <main>
    <?php if ($conn): ?>
      <div class="estado ok">Conectado a la base de datos <strong><?= htmlspecialchars($bd) ?></strong>.</div>
    <?php else: ?>
      <div class="estado error">
        No se pudo conectar: <?= htmlspecialchars($errorConexion) ?>.<br>
        Revisa tus datos en conexion.php y verifica que estés en la red de la institución .
      </div>
    <?php endif; ?>

    <nav class="menu">
      <a href="clientes.php">
        <h2>Clientes</h2>
        <p>Registrar, consultar, modificar y eliminar clientes.</p>
      </a>
      <a href="pedidos.php">
        <h2>Pedidos</h2>
        <p>Registrar, consultar, modificar y eliminar pedidos.</p>
      </a>
      <a href="productos.php">
        <h2>Productos</h2>
        <p>Registrar, consultar, modificar y eliminar productos.</p>
      </a>
    </nav>
  </main>
</body>
</html>
