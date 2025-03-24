<?php
// app/Models/Auth.php

require_once 'Database.php';

class Auth {
    private $db;

    public function __construct() {
        $this->db = (new Database())->connect();
    }

    /**
     * Inicia sesión con las credenciales proporcionadas usando LDAP.
     */
    public function iniciarSesion($username, $password) {
        // Configuración del servidor LDAP de Windows Server
        $ldap_host = "ldap://192.168.8.120l"; // Host del servidor LDAP
        $ldap_port = 389; // Puerto LDAP
        $ldap_dn = "DC=dominio,DC=local"; // DN base para búsquedas
        $ldap_user = "$username@dominio.local"; // Formato de usuario para Windows Server
        $ldap_password = $password; // Contraseña del usuario

        // Conexión al servidor LDAP
        $ldap_conn = ldap_connect($ldap_host, $ldap_port);
        if (!$ldap_conn) {
            return false; // Error al conectar al servidor LDAP
        }

        // Configurar opciones de LDAP
        ldap_set_option($ldap_conn, LDAP_OPT_PROTOCOL_VERSION, 3);
        ldap_set_option($ldap_conn, LDAP_OPT_REFERRALS, 0);

        // Intentar autenticación
        $bind = @ldap_bind($ldap_conn, $ldap_user, $ldap_password);
        if (!$bind) {
            return false; // Autenticación fallida
        }

        // Autenticación exitosa
        ldap_unbind($ldap_conn); // Cerrar conexión

        // Obtener detalles del usuario desde la base de datos (si es necesario)
        $query = "SELECT * FROM usuario WHERE username = :username";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':username', $username);
        $stmt->execute();

        $usuario = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($usuario) {
            return $usuario; // Devuelve los detalles del usuario desde la base de datos
        } else {
            // Si el usuario no existe en la base de datos, puedes registrarlo automáticamente
            return $this->registrarUsuario($username, $username . '@dominio.local', 'ldap_user');
        }
    }

    /**
     * Registra un nuevo usuario en la base de datos.
     */
    public function registrarUsuario($nombre, $email, $password) {
        // Configuración del servidor LDAP
        $ldap_host = "ldap://192.168.8.120"; // Host del servidor LDAP
        $ldap_port = 389; // Puerto LDAP
        $ldap_dn = "DC=dominio,DC=local"; // DN base para búsquedas
        $ldap_admin_user = "cn=admin,dc=dominio,dc=local"; // Usuario administrador de LDAP
        $ldap_admin_password = "Adminpassword123"; // Contraseña del administrador

        // Datos del nuevo usuario
        $new_user_cn = "$nombre"; // Nombre completo del usuario
        $new_user_dn = "CN=$new_user_cn,CN=Users,$ldap_dn"; // DN del nuevo usuario

        // Atributos del nuevo usuario
        $new_user_attributes = [
            "objectClass" => ["top", "person", "organizationalPerson", "user"], // Clases de objeto
            "cn" => $new_user_cn, // Nombre completo
            "sAMAccountName" => $nombre, // Nombre de usuario
            "userPrincipalName" => "$nombre@dominio.local", // UPN
            "displayName" => $new_user_cn, // Nombre para mostrar
            "givenName" => $nombre, // Nombre
            "mail" => $email, // Correo electrónico
            "userPassword" => $password, // Contraseña
            "accountExpires" => "0", // La cuenta no expira
            "userAccountControl" => "512" // Cuenta habilitada
        ];

        // Conectar al servidor LDAP
        $ldap_conn = ldap_connect($ldap_host, $ldap_port);
        if (!$ldap_conn) {
            throw new Exception("No se pudo conectar al servidor LDAP.");
        }

        // Configurar opciones de LDAP
        ldap_set_option($ldap_conn, LDAP_OPT_PROTOCOL_VERSION, 3);
        ldap_set_option($ldap_conn, LDAP_OPT_REFERRALS, 0);

        // Autenticar como administrador
        $bind = @ldap_bind($ldap_conn, $ldap_admin_user, $ldap_admin_password);
        if (!$bind) {
            throw new Exception("Error de autenticación del administrador.");
        }

        // Registrar el nuevo usuario
        $result = @ldap_add($ldap_conn, $new_user_dn, $new_user_attributes);
        if (!$result) {
            throw new Exception("Error al registrar el usuario: " . ldap_error($ldap_conn));
        }

        // Cerrar la conexión LDAP
        ldap_unbind($ldap_conn);

        return true; // Usuario registrado correctamente
    }

    /**
     * Verifica si un email ya está registrado.
     */
    public function emailExiste($email) {
        // Configuración del servidor LDAP de Windows Server
        $ldap_host = "ldap://192.168.8.120l"; // Host del servidor LDAP
        $ldap_port = 389; // Puerto LDAP
        $ldap_dn = "DC=dominio,DC=local"; // DN base para búsquedas
        $ldap_admin_user = "cn=admin,dc=dominio,dc=local"; // Usuario administrador de LDAP
        $ldap_admin_password = "Adminpassword123"; // Contraseña del administrador

        // Conexión al servidor LDAP
        $ldap_conn = ldap_connect($ldap_host, $ldap_port);
        if (!$ldap_conn) {
            return false; // Error al conectar al servidor LDAP
        }

        // Configurar opciones de LDAP
        ldap_set_option($ldap_conn, LDAP_OPT_PROTOCOL_VERSION, 3);
        ldap_set_option($ldap_conn, LDAP_OPT_REFERRALS, 0);

        // Autenticar como administrador para realizar operaciones de lectura
        $bind = @ldap_bind($ldap_conn, $ldap_admin_user, $ldap_admin_password);
        if (!$bind) {
            return false; // Error de autenticación del administrador
        }

        // Buscar el email en LDAP
        $filter = "(mail=$email)";
        $search = ldap_search($ldap_conn, $ldap_dn, $filter);
        $entries = ldap_get_entries($ldap_conn, $search);

        // Cerrar conexión
        ldap_unbind($ldap_conn);

        // Verificar si se encontraron resultados
        return $entries['count'] > 0;
    }
}
?>