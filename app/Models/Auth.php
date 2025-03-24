<?php
// app/Models/Auth.php

require_once 'Database.php';

class Auth {
    private $db;
    private $ldapServer = "ldap://192.168.8.120";  // Dirección del servidor LDAP
    private $ldapPort = 389;  // Puerto LDAP
    private $ldapDomain = "dominio.local";  // Dominio LDAP

    public function __construct() {
        $this->db = (new Database())->connect();
    }

    /**
     * Inicia sesión con las credenciales proporcionadas.
     */
    public function iniciarSesion($email, $password) {
        // Primero intentamos autenticar con LDAP
        if ($this->autenticarLDAP($email, $password)) {
            // Si la autenticación LDAP es exitosa, verificamos la base de datos
            $query = "SELECT * FROM usuario WHERE email = :email";
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':email', $email);
            $stmt->execute();

            $usuario = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($usuario) {
                return $usuario;
            }
        }

        return false;
    }

    /**
     * Verifica las credenciales con LDAP.
     */
    private function autenticarLDAP($email, $password) {
        $ldapConn = ldap_connect($this->ldapServer, $this->ldapPort);
        if (!$ldapConn) {
            return false;
        }

        ldap_set_option($ldapConn, LDAP_OPT_PROTOCOL_VERSION, 3);
        ldap_set_option($ldapConn, LDAP_OPT_REFERRALS, 0);

        $bindDn = "Administrator@$this->ldapDomain";  // El DN del administrador
        $bindPassword = "Prueba123";  // Contraseña del administrador LDAP

        // Conectar y autenticar con LDAP como administrador
        if (@ldap_bind($ldapConn, $bindDn, $bindPassword)) {
            // Construir el filtro de búsqueda para el correo electrónico
            $filter = "(mail=$email)";
            $result = ldap_search($ldapConn, "dc=dominio,dc=local", $filter);

            $entries = ldap_get_entries($ldapConn, $result);

            if ($entries["count"] > 0) {
                $dn = $entries[0]["dn"];
                // Intentamos hacer un bind con el DN encontrado y la contraseña proporcionada
                if (@ldap_bind($ldapConn, $dn, $password)) {
                    ldap_unbind($ldapConn);
                    return true;  // Autenticación exitosa
                }
            }
        }

        ldap_unbind($ldapConn);
        return false;
    }

    /**
     * Registra un nuevo usuario en la base de datos y en LDAP.
     */
    public function registrarUsuario($nombre, $email, $password) {
        // Almacena la contraseña en texto plano en la base de datos
        $query = "INSERT INTO usuario (nombre, email, password) VALUES (:nombre, :email, :password)";
        $stmt = $this->db->prepare($query);

        $stmt->bindParam(':nombre', $nombre);
        $stmt->bindParam(':email', $email);
        $stmt->bindParam(':password', $password);

        if ($stmt->execute()) {
            // Si el usuario se guarda en la base de datos, también lo agregamos a LDAP
            return $this->registrarUsuarioEnLDAP($nombre, $email, $password);
        }

        return false;
    }

    /**
     * Registra un nuevo usuario en el directorio LDAP.
     */
    private function registrarUsuarioEnLDAP($nombre, $email, $password) {
        $ldapConn = ldap_connect($this->ldapServer, $this->ldapPort);

        if (!$ldapConn) {
            return false;
        }

        ldap_set_option($ldapConn, LDAP_OPT_PROTOCOL_VERSION, 3);
        ldap_set_option($ldapConn, LDAP_OPT_REFERRALS, 0);

        $bindDn = "Administrator@$this->ldapDomain";
        $bindPassword = "Prueba123";

        if (@ldap_bind($ldapConn, $bindDn, $bindPassword)) {
            $dn = "uid=$email,ou=Users,dc=dominio,dc=local";  // Definimos el DN del usuario LDAP
            $entry = [
                "cn" => $nombre,
                "uid" => $nombre."@dominio.local",
                "mail" => $email,
                "userPassword" => $password,
                "objectClass" => ["top", "person", "organizationalPerson", "inetOrgPerson"]
            ];

            if (ldap_add($ldapConn, $dn, $entry)) {
                ldap_unbind($ldapConn);
                return true;  // Usuario agregado correctamente a LDAP
            }
        }

        ldap_unbind($ldapConn);
        return false;
    }

    /**
     * Verifica si un email ya está registrado en la base de datos.
     */
    public function emailExiste($email) {
        $query = "SELECT COUNT(*) FROM usuario WHERE email = :email";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':email', $email);
        $stmt->execute();

        return $stmt->fetchColumn() > 0;
    }

    // Nuevas funciones de gestión de usuarios LDAP (agregar, modificar, eliminar)
    
    /**
     * Modifica un usuario en LDAP.
     */
    public function modificarUsuarioLDAP($email, $atributos) {
        $ldapConn = ldap_connect($this->ldapServer, $this->ldapPort);

        if (!$ldapConn) {
            return false;
        }

        ldap_set_option($ldapConn, LDAP_OPT_PROTOCOL_VERSION, 3);
        ldap_set_option($ldapConn, LDAP_OPT_REFERRALS, 0);

        $bindDn = "Administrator@$this->ldapDomain";
        $bindPassword = "Prueba123";

        if (@ldap_bind($ldapConn, $bindDn, $bindPassword)) {
            $dn = "uid=$email,ou=Users,dc=dominio,dc=local";  // El DN del usuario

            if (ldap_modify($ldapConn, $dn, $atributos)) {
                ldap_unbind($ldapConn);
                return true;
            }
        }

        ldap_unbind($ldapConn);
        return false;
    }

    /**
     * Elimina un usuario en LDAP.
     */
    public function eliminarUsuarioLDAP($email) {
        $ldapConn = ldap_connect($this->ldapServer, $this->ldapPort);

        if (!$ldapConn) {
            return false;
        }

        ldap_set_option($ldapConn, LDAP_OPT_PROTOCOL_VERSION, 3);
        ldap_set_option($ldapConn, LDAP_OPT_REFERRALS, 0);

        $bindDn = "Administrator@$this->ldapDomain";
        $bindPassword = "Prueba123";

        if (@ldap_bind($ldapConn, $bindDn, $bindPassword)) {
            $dn = "uid=$email,ou=Users,dc=dominio,dc=local";  // El DN del usuario

            if (ldap_delete($ldapConn, $dn)) {
                ldap_unbind($ldapConn);
                return true;
            }
        }

        ldap_unbind($ldapConn);
        return false;
    }

    /**
     * Agrega un usuario a LDAP.
     */
    public function agregarUsuarioLDAP($nombre, $email, $password) {
        return $this->registrarUsuarioEnLDAP($nombre, $email, $password);
    }
}