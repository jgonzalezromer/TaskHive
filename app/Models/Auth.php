<?php
// app/Models/Auth.php

class Auth {
    private $ldap_host = "ldap://192.168.8.120";
    private $ldap_port = 389;
    private $ldap_dn = "DC=dominio,DC=local";
    private $ldap_admin_user = "cn=Administrator,dc=dominio,dc=local";
    private $ldap_admin_password = "Prueba123";

    public function iniciarSesion($email, $password) {
        $ldap_user = "$email@dominio.local";
        $ldap_conn = ldap_connect($this->ldap_host, $this->ldap_port);

        if (!$ldap_conn) {
            throw new Exception("No se pudo conectar al servidor LDAP.");
        }

        ldap_set_option($ldap_conn, LDAP_OPT_PROTOCOL_VERSION, 3);
        ldap_set_option($ldap_conn, LDAP_OPT_REFERRALS, 0);

        if (!@ldap_bind($ldap_conn, $ldap_user, $password)) {
            throw new Exception("Credenciales incorrectas.");
        }

        $filter = "(mail=$email)";
        $search = ldap_search($ldap_conn, $this->ldap_dn, $filter);
        $entries = ldap_get_entries($ldap_conn, $search);
        ldap_unbind($ldap_conn);

        if ($entries['count'] > 0) {
            return [
                'nombre' => $entries[0]['cn'][0],
                'email' => $entries[0]['mail'][0],
            ];
        }

        return false;
    }

    public function registrarUsuario($nombre, $email, $password) {
        $ldap_conn = ldap_connect($this->ldap_host, $this->ldap_port);

        if (!$ldap_conn) {
            throw new Exception("No se pudo conectar al servidor LDAP.");
        }

        ldap_set_option($ldap_conn, LDAP_OPT_PROTOCOL_VERSION, 3);
        ldap_set_option($ldap_conn, LDAP_OPT_REFERRALS, 0);

        if (!@ldap_bind($ldap_conn, $this->ldap_admin_user, $this->ldap_admin_password)) {
            throw new Exception("Error de autenticación del administrador.");
        }

        $new_user_cn = $nombre;
        $new_user_dn = "CN=$new_user_cn,CN=Users," . $this->ldap_dn;

        $hashed_password = password_hash($password, PASSWORD_DEFAULT);

        $new_user_attributes = [
            "objectClass" => ["top", "person", "organizationalPerson", "user"],
            "cn" => $new_user_cn,
            "sAMAccountName" => $nombre,
            "userPrincipalName" => "$nombre@dominio.local",
            "displayName" => $new_user_cn,
            "givenName" => $nombre,
            "mail" => $email,
            "userPassword" => $hashed_password,
            "accountExpires" => "0",
            "userAccountControl" => "512"
        ];

        if (!@ldap_add($ldap_conn, $new_user_dn, $new_user_attributes)) {
            throw new Exception("Error al registrar el usuario: " . ldap_error($ldap_conn));
        }

        ldap_unbind($ldap_conn);
        return true;
    }

    public function emailExiste($email) {
        $ldap_conn = ldap_connect($this->ldap_host, $this->ldap_port);

        if (!$ldap_conn) {
            throw new Exception("No se pudo conectar al servidor LDAP.");
        }

        ldap_set_option($ldap_conn, LDAP_OPT_PROTOCOL_VERSION, 3);
        ldap_set_option($ldap_conn, LDAP_OPT_REFERRALS, 0);

        if (!@ldap_bind($ldap_conn, $this->ldap_admin_user, $this->ldap_admin_password)) {
            throw new Exception("Error de autenticación del administrador.");
        }

        $filter = "(mail=$email)";
        $search = ldap_search($ldap_conn, $this->ldap_dn, $filter);
        $entries = ldap_get_entries($ldap_conn, $search);
        ldap_unbind($ldap_conn);

        return $entries['count'] > 0;
    }
}
?>