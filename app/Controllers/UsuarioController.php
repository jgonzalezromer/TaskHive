<?php
// app/Controllers/UsuarioController.php

require_once __DIR__ . '/../Models/UsuarioModel.php';

class UsuarioController {
    private $usuarioModel;

    public function __construct() {
        $this->usuarioModel = new UsuarioModel();
    }

    /**
     * Muestra el dashboard del usuario.
     */
    public function dashboard() {
        session_start();

        // Verifica si el usuario está autenticado
        if (!isset($_SESSION['usuario'])) {
            header("Location: /index.php");
            exit();
        }

        // Obtiene los datos del usuario desde la sesión
        $usuario = $_SESSION['usuario'];

        // Obtiene los proyectos del usuario
        $proyectos = $this->usuarioModel->obtenerProyectos($usuario['id']);

        // Incluye la vista del dashboard
        include __DIR__ . '/../Views/usuario/dashboard.html';
    }

    /**
     * Crea un nuevo proyecto.
     */
    public function crearProyecto() {
        session_start();

        // Verifica si el usuario está autenticado
        if (!isset($_SESSION['usuario'])) {
            header("Location: /index.php");
            exit();
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $nombre = $_POST['nombre'] ?? '';
            $descripcion = $_POST['descripcion'] ?? '';
            $usuario_id = $_SESSION['usuario']['id'];

            // Validación de campos
            if (empty($nombre) || empty($descripcion)) {
                echo "<script>alert('Todos los campos son obligatorios.'); window.history.back();</script>";
                exit();
            }

            // Crear el proyecto
            if ($this->usuarioModel->crearProyecto($nombre, $descripcion, $usuario_id)) {
                // Redirigir al dashboard después de crear el proyecto
                header("Location: /index.php?action=dashboard");
                exit();
            } else {
                echo "<script>alert('Error al crear el proyecto.'); window.history.back();</script>";
                exit();
            }
        } else {
            // Si no es POST, mostrar el formulario de creación de proyecto
            include __DIR__ . '/../Views/usuario/create_project.html';
        }
    }

    /**
     * Muestra los detalles de un proyecto.
     */
    public function verProyecto($proyecto_id) {
        session_start();

        // Verifica si el usuario está autenticado
        if (!isset($_SESSION['usuario'])) {
            header("Location: /index.php");
            exit();
        }

        // Validar el ID del proyecto
        if (empty($proyecto_id)) {
            echo "<script>alert('ID del proyecto no válido.'); window.history.back();</script>";
            exit();
        }

        // Obtiene las tareas del proyecto
        $tareas = $this->usuarioModel->obtenerTareas($proyecto_id);

        // Incluye la vista del proyecto
        include __DIR__ . '/../Views/usuario/projects.html';
    }

    /**
     * Crea una nueva tarea.
     */
    public function crearTarea() {
        session_start();

        // Verifica si el usuario está autenticado
        if (!isset($_SESSION['usuario'])) {
            header("Location: /index.php");
            exit();
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $nombre = $_POST['nombre'] ?? '';
            $descripcion = $_POST['descripcion'] ?? '';
            $proyecto_id = $_POST['proyecto_id'] ?? '';

            // Validación de campos
            if (empty($nombre) || empty($descripcion) || empty($proyecto_id)) {
                echo "<script>alert('Todos los campos son obligatorios.'); window.history.back();</script>";
                exit();
            }

            // Crear la tarea
            if ($this->usuarioModel->crearTarea($nombre, $descripcion, $proyecto_id)) {
                // Redirigir a la página del proyecto después de crear la tarea
                header("Location: /index.php?action=verProyecto&id=$proyecto_id");
                exit();
            } else {
                echo "<script>alert('Error al crear la tarea.'); window.history.back();</script>";
                exit();
            }
        } else {
            // Si no es POST, mostrar un mensaje de error
            echo "<script>alert('Método no permitido.'); window.history.back();</script>";
            exit();
        }
    }
}
?>