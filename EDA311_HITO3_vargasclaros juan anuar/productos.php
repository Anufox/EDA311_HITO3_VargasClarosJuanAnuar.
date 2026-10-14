<?php
$titulo = "Productos";
$tabla = "productos";
$clave = "id";               
$campos = [
    "nombre" => "text",
    "precio" => "number",
    "stock"  => "number"
];

$ordenarPor = "precio";     

require "gestion.php";
