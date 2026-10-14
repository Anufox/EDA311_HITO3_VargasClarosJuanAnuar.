<?php
// Página de clientes.
// IMPORTANTE: cambia estos nombres por los de tu tabla real (DESCRIBE clientes).

$titulo = "Clientes";
$tabla = "clientes";
$clave = "id";               // clave primaria

// Campos del formulario => tipo de input (text, number, date, email)
$campos = [
    "nombre"   => "text",
    "email"    => "email",
    "telefono" => "text"
];

$ordenarPor = "nombre";      // criterio de Bubble Sort por defecto

require "gestion.php";
