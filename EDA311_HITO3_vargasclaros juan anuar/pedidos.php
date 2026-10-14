<?php

$titulo = "Pedidos";
$tabla = "pedidos";
$clave = "id";             

$campos = [
    "cliente_id" => "number",   
    "fecha"      => "date",
    "total"      => "number"
];

$ordenarPor = "fecha";      

require "gestion.php";
