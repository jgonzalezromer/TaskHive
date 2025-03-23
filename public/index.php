<?php
// public/index.php

require_once __DIR__ . '/../app/Controllers/AuthController.php';
require_once __DIR__ . '/../app/Controllers/UsuarioController.php';

session_start();

$authController = new AuthController();
$usuarioController = new UsuarioController();

// Verifica la acción solicitada
$action = $_POST['action'] ?? $_GET['action'] ?? '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Manejar solicitudes POST
    switch ($action) {
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
        case 'eliminarTarea':
            $usuarioController->eliminarTarea();
            break;
        case 'eliminarProyecto':
            $usuarioController->eliminarProyecto();
            break;
        default:
            echo "Acción no válida.";
            break;
    }
} else {
    // Manejar solicitudes GET
    switch ($action) {
        case 'dashboard':
            $usuarioController->dashboard();
            break;
        case 'verProyecto':
            $proyecto_id = $_GET['id'] ?? '';
            $usuarioController->verProyecto($proyecto_id);
            break;
        case 'crearProyecto':
            $usuarioController->crearProyecto();
            break;
        case 'login':
            include __DIR__ . '/../app/Views/auth/login.html';
            break;
        case 'registro':
            include __DIR__ . '/../app/Views/auth/register.html';
            break;
        default:
            include __DIR__ . '/../app/Views/auth/login.html';
            break;
    }
}
?>