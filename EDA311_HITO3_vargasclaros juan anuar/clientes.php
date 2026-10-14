<?php

$titulo = "Clientes";
$tabla = "clientes";
$clave = "id";               
$campos = [
    "nombre"   => "text",
    "email"    => "email",
    "telefono" => "text"
];

$ordenarPor = "nombre";     

require "gestion.php";
