<?php
// app/Models/UsuarioModel.php

require_once 'Database.php';

class UsuarioModel {
    private $db;

    public function __construct() {
        $this->db = (new Database())->connect();
    }

    /**
     * Obtiene los proyectos asociados a un usuario.
     */
    public function obtenerProyectos($usuario_id) {
        $query = "SELECT * FROM proyectos WHERE usuario_id = :usuario_id";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':usuario_id', $usuario_id);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Crea un nuevo proyecto.
     */
    public function crearProyecto($nombre, $descripcion, $usuario_id) {
        $query = "INSERT INTO proyectos (nombre, descripcion, usuario_id) VALUES (:nombre, :descripcion, :usuario_id)";
        $stmt = $this->db->prepare($query);

        $stmt->bindParam(':nombre', $nombre);
        $stmt->bindParam(':descripcion', $descripcion);
        $stmt->bindParam(':usuario_id', $usuario_id);

        return $stmt->execute();
    }

    /**
     * Obtiene las tareas asociadas a un proyecto.
     */
    public function obtenerTareas($proyecto_id) {
        $query = "SELECT * FROM tareas WHERE proyecto_id = :proyecto_id";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':proyecto_id', $proyecto_id);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Crea una nueva tarea.
     */
    public function crearTarea($nombre, $descripcion, $proyecto_id) {
        $query = "INSERT INTO tareas (nombre, descripcion, proyecto_id) VALUES (:nombre, :descripcion, :proyecto_id)";
        $stmt = $this->db->prepare($query);

        $stmt->bindParam(':nombre', $nombre);
        $stmt->bindParam(':descripcion', $descripcion);
        $stmt->bindParam(':proyecto_id', $proyecto_id);

        return $stmt->execute();
    }
}
?>