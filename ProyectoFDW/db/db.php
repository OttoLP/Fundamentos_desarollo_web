<?php

$host = "127.0.0.1";
$user = "root";
$password = "";
$dbname = "bar";
$port = 3307;

$conn = mysqli_connect($host, $user, $password, $dbname, $port);

if (!$conn) {
    die("Error de conexión: " . mysqli_connect_error());
}

?>