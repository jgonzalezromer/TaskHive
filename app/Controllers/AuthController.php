<?php
// app/Controllers/AuthController.php

require_once __DIR__ . '/../Models/Auth.php';

class AuthController {
    private $auth;

    public function __construct() {
        $this->auth = new Auth();
    }

    /**
     * Maneja la solicitud de inicio de sesión.
     */
    public function login() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $email = $_POST['email'] ?? '';
            $password = $_POST['password'] ?? '';

            if (empty($email) || empty($password)) {
                $this->responderError("Todos los campos son obligatorios.");
                return;
            }

            $usuario = $this->auth->iniciarSesion($email, $password);

            if ($usuario) {
                session_start();
                $_SESSION['usuario'] = $usuario;  // Almacena el usuario en la sesión
                $this->responderExito("/index.php?action=dashboard");  // Redirige al dashboard
            } else {
                $this->responderError("Credenciales incorrectas.");
            }
        } else {
            $this->responderError("Método no permitido.");
        }
    }

    /**
     * Maneja la solicitud de registro.
     */
    public function registro() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $nombre = $_POST['nombre'] ?? '';
            $email = $_POST['email'] ?? '';
            $password = $_POST['password'] ?? '';

            if (empty($nombre) || empty($email) || empty($password)) {
                $this->responderError("Todos los campos son obligatorios.");
                return;
            }

            if ($this->auth->emailExiste($email)) {
                $this->responderError("El email ya está registrado.");
                return;
            }

            if ($this->auth->registrarUsuario($nombre, $email, $password)) {
                $this->responderExito("/index.php");  // Redirige al login
            } else {
                $this->responderError("Error en el registro.");
            }
        } else {
            $this->responderError("Método no permitido.");
        }
    }

    /**
     * Responde con un mensaje de éxito y redirige a una URL.
     */
    private function responderExito($url) {
        header("Location: $url");
        exit();
    }

    /**
     * Responde con un mensaje de error.
     */
    private function responderError($mensaje) {
        echo "<script>alert('$mensaje'); window.history.back();</script>";
        exit();
    }
}
?>