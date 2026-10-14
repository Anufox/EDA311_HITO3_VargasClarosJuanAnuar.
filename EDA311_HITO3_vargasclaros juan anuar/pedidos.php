<?php
// Página de pedidos.
// IMPORTANTE: cambia estos nombres por los de tu tabla real (DESCRIBE pedidos).

$titulo = "Pedidos";
$tabla = "pedidos";
$clave = "id";               // clave primaria

// Campos del formulario => tipo de input (text, number, date, email)
$campos = [
    "cliente_id" => "number",    // clave foránea hacia clientes
    "fecha"      => "date",
    "total"      => "number"
];

$ordenarPor = "fecha";       // criterio de Bubble Sort por defecto

require "gestion.php";
