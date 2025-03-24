<?php
require_once __DIR__ . '/../Models/Auth.php';

class AuthController {
    private $auth;

    public function __construct() {
        $this->auth = new Auth();
    }

    public function login() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $email = filter_var($_POST['email'] ?? '', FILTER_SANITIZE_EMAIL);
            $password = $_POST['password'] ?? '';
    
            if (empty($email) || empty($password)) {
                throw new Exception("Todos los campos son obligatorios.");
            }
    
            $usuario = $this->auth->iniciarSesion($email, $password);
    
            var_dump($usuario); // <-- Agregado para depurar el resultado
    
            if ($usuario) {
                $_SESSION['usuario'] = $usuario;
                header("Location: /index.php?action=dashboard");
                exit();
            } else {
                throw new Exception("Credenciales incorrectas.");
            }
        } else {
            throw new Exception("Método no permitido.");
        }
    }

    public function registro() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $nombre = filter_var($_POST['nombre'] ?? '', FILTER_SANITIZE_STRING);
            $email = filter_var($_POST['email'] ?? '', FILTER_SANITIZE_EMAIL);
            $password = $_POST['password'] ?? '';

            if (empty($nombre) || empty($email) || empty($password)) {
                throw new Exception("Todos los campos son obligatorios.");
            }

            if ($this->auth->emailExiste($email)) {
                throw new Exception("El email ya está registrado.");
            }

            $hashedPassword = password_hash($password, PASSWORD_BCRYPT);

            if ($this->auth->registrarUsuario($nombre, $email, $hashedPassword)) {
                header("Location: /index.php");
                exit();
            } else {
                throw new Exception("Error en el registro.");
            }
        } else {
            throw new Exception("Método no permitido.");
        }
    }
}