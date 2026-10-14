<?php
// Página de productos.
// IMPORTANTE: cambia estos nombres por los de tu tabla real (DESCRIBE productos).

$titulo = "Productos";
$tabla = "productos";
$clave = "id";               // clave primaria

// Campos del formulario => tipo de input (text, number, date, email)
$campos = [
    "nombre" => "text",
    "precio" => "number",
    "stock"  => "number"
];

$ordenarPor = "precio";      // criterio de Bubble Sort por defecto

require "gestion.php";
