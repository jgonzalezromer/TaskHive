<?php
// public/index.php

require_once __DIR__ . '/../app/Controllers/AuthController.php';
require_once __DIR__ . '/../app/Controllers/UsuarioController.php';

session_start();

$authController = new AuthController();
$usuarioController = new UsuarioController();

// Verifica si el usuario está intentando acceder a la página de registro
$esRegistro = isset($_GET['action']) && $_GET['action'] === 'registro';

// Verifica si el usuario está intentando acceder al dashboard
$esDashboard = isset($_GET['action']) && $_GET['action'] === 'dashboard';

// Verifica si el usuario está intentando crear un proyecto
$esCrearProyecto = isset($_GET['action']) && $_GET['action'] === 'crearProyecto';

// Verifica si el usuario está intentando ver un proyecto
$esVerProyecto = isset($_GET['action']) && $_GET['action'] === 'verProyecto';

// Verifica si el usuario está intentando crear una tarea
$esCrearTarea = isset($_GET['action']) && $_GET['action'] === 'crearTarea';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'login':
                $authController->login();
                break;

            case 'registro':
                $authController->registro();
                break;

            case 'crearProyecto':
                $usuarioController->crearProyecto();
                break;

            case 'crearTarea':
                $usuarioController->crearTarea();
                break;

            default:
                echo "Acción no válida.";
                break;
        }
    } else {
        echo "Acción no especificada.";
    }
} else {
    // Mostrar la página de registro, login, dashboard o ver proyecto según la acción
    if ($esRegistro) {
        include __DIR__ . '/../app/Views/auth/register.html';
    } elseif ($esDashboard) {
        $usuarioController->dashboard();
    } elseif ($esCrearProyecto) {
        $usuarioController->crearProyecto();
    } elseif ($esVerProyecto) {
        $proyecto_id = $_GET['id'] ?? '';
        $usuarioController->verProyecto($proyecto_id);
    } elseif ($esCrearTarea) {
        $usuarioController->crearTarea();
    } else {
        include __DIR__ . '/../app/Views/auth/login.html';
    }
}
?>