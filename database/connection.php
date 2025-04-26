<?php

// Without using ENV variables since we haven't tackled it yet.
$hostname = "localhost";
$username = $password = "root";
$dbname = "lostfounddb";

$conn = new mysqli($hostname, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Error: $conn->connect_error");
}

?>