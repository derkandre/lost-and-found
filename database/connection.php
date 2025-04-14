<?php

$hostname = "localhost";
$username = $password = "root";
$dbname = "lostfounddb";

$conn = new mysqli($hostname, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Error: $conn->connect_error");
}

?>