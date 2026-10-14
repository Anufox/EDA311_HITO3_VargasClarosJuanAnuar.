<?php
// Lista doblemente enlazada hecha desde cero.

// Cada nodo guarda un registro y apunta al anterior y al siguiente.
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

// La lista sabe cuál es el primer nodo (cabeza) y el último (cola).
class ListaDoble {
    public $cabeza = null;
    public $cola = null;
    public $tamano = 0;

    // Agrega un registro al final de la lista.
    public function insertar($dato) {
        $nuevo = new Nodo($dato);

        if ($this->cabeza === null) {
            // Lista vacía: el nuevo nodo es la cabeza y la cola
            $this->cabeza = $nuevo;
            $this->cola = $nuevo;
        } else {
            // Se enlaza el nuevo nodo con el último
            $nuevo->anterior = $this->cola;
            $this->cola->siguiente = $nuevo;
            $this->cola = $nuevo;
        }
        $this->tamano++;
    }

    // Recorre la lista desde la cabeza y devuelve los nodos para mostrarlos.
    public function recorrer() {
        $nodos = [];
        $actual = $this->cabeza;
        while ($actual !== null) {
            $nodos[] = $actual;
            $actual = $actual->siguiente;
        }
        return $nodos;
    }

    // Lee todos los registros de una tabla de MySQL y los guarda en la lista.
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

    // Bubble Sort directo sobre la lista (sin pasarla a arreglo).
    // Compara cada nodo con el siguiente y si están al revés intercambia sus datos.
    // Repite hasta que en una vuelta completa ya no haya intercambios.
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
                    // Intercambiar los datos de los dos nodos
                    $temporal = $actual->dato;
                    $actual->dato = $siguiente->dato;
                    $siguiente->dato = $temporal;
                    $huboCambio = true;
                }
                $actual = $actual->siguiente;
            }
        } while ($huboCambio);
    }

    // Si los dos valores son números se comparan como números, si no como texto.
    private function comparar($a, $b) {
        if (is_numeric($a) && is_numeric($b)) {
            return $a <=> $b;
        }
        return strcasecmp((string)$a, (string)$b);
    }
}
