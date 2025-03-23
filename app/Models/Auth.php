<?php
// app/Models/Auth.php

require_once 'Database.php';

class Auth {
    private $db;

    public function __construct() {
        $this->db = (new Database())->connect();
    }

    /**
     * Inicia sesión con las credenciales proporcionadas.
     */
    public function iniciarSesion($email, $password) {
        $query = "SELECT * FROM usuario WHERE email = :email";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':email', $email);
        $stmt->execute();

        $usuario = $stmt->fetch(PDO::FETCH_ASSOC);

        // Compara la contraseña en texto plano
        if ($usuario && $password === $usuario['password']) {
            return $usuario;
        }

        return false;
    }

    /**
     * Registra un nuevo usuario en la base de datos.
     */
    public function registrarUsuario($nombre, $email, $password) {
        // Almacena la contraseña en texto plano
        $query = "INSERT INTO usuario (nombre, email, password) VALUES (:nombre, :email, :password)";
        $stmt = $this->db->prepare($query);

        $stmt->bindParam(':nombre', $nombre);
        $stmt->bindParam(':email', $email);
        $stmt->bindParam(':password', $password);

        return $stmt->execute();
    }

    /**
     * Verifica si un email ya está registrado.
     */
    public function emailExiste($email) {
        $query = "SELECT COUNT(*) FROM usuario WHERE email = :email";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':email', $email);
        $stmt->execute();

        return $stmt->fetchColumn() > 0;
    }
}
?>