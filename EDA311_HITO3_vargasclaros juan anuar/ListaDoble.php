<?php

class Nodo {
    public $dato;
    public $anterior;
    public $siguiente;

    public function __construct($dato) {
        $this->dato = $dato;
        $this->anterior = null;
        $this->siguiente = null;
    }
}

class ListaDoble {
    public $cabeza = null;
    public $cola = null;
    public $tamano = 0;

    public function insertar($dato) {
        $nuevo = new Nodo($dato);

        if ($this->cabeza === null) {
    
            $this->cabeza = $nuevo;
            $this->cola = $nuevo;
        } else {

            $nuevo->anterior = $this->cola;
            $this->cola->siguiente = $nuevo;
            $this->cola = $nuevo;
        }
        $this->tamano++;
    }

    public function recorrer() {
        $nodos = [];
        $actual = $this->cabeza;
        while ($actual !== null) {
            $nodos[] = $actual;
            $actual = $actual->siguiente;
        }
        return $nodos;
    }

    public function cargarDesdeMySQL($conn, $tabla) {
        $resultado = $conn->query("SELECT * FROM $tabla");
        if (!$resultado) {
            return false;
        }
        while ($fila = $resultado->fetch_assoc()) {
            $this->insertar($fila);
        }
        return true;
    }


    public function ordenarBurbuja($campo, $ascendente = true) {
        if ($this->cabeza === null) {
            return;
        }

        do {
            $huboCambio = false;
            $actual = $this->cabeza;

            while ($actual->siguiente !== null) {
                $siguiente = $actual->siguiente;
                $resultado = $this->comparar($actual->dato[$campo], $siguiente->dato[$campo]);

                if (!$ascendente) {
                    $resultado = -$resultado;
                }

                if ($resultado > 0) {
                    $temporal = $actual->dato;
                    $actual->dato = $siguiente->dato;
                    $siguiente->dato = $temporal;
                    $huboCambio = true;
                }
                $actual = $actual->siguiente;
            }
        } while ($huboCambio);
    }


    private function comparar($a, $b) {
        if (is_numeric($a) && is_numeric($b)) {
            return $a <=> $b;
        }
        return strcasecmp((string)$a, (string)$b);
    }
}
