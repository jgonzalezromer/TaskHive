<!--Database.php-->
<?php
// Función para a conexión coa base de datos
function Database(){
    // Credenciais para o inicio de sesion en mysql
    $servername = DB_HOST;
    $username = DB_USER;
    $password = DB_PASS;
    $database = DB_NAME;
    $port= DB_PORT;

    // Creamos a conexión con mysql
    $conn = new mysqli($servername, $username, $password,$database,$port);

    // Comprobar se hai un erro ao conectar 
    if ($conn->connect_error) { 
        // Con die detemos o script
        die("Connection failed: " . $conn->connect_error);
    }

    return $conn; // Devolvemos a conexión
}

// Función para cerrar a conexión coa base de datos
function DesconexionDB($conn){
    // Cerramos sesion con close()
    $conn->close();
}
?>