<?php
// Configuración de la base de datos
define('DB_HOST', 'db');
define('DB_USER', 'taskhive_user');
define('DB_PASSWORD', 'userpassword');
define('DB_NAME', 'taskhive');

// Configuración de LDAP
define('LDAP_HOST', 'ldap');
define('LDAP_PORT', 389);
define('LDAP_DN', 'dc=taskhive,dc=local');
define('LDAP_USER', 'cn=admin,dc=taskhive,dc=local');
define('LDAP_PASSWORD', 'adminpassword');
?>