<?php

// You'll need to replace the following with the credentials for your DATABASE
// There is complexity about using localhost for Mysql connections,
// so for Docker on Mac (which I use), it's better to use host.docker.internal

$dbservername = "host.docker.internal";
$dbdatabasename = "gardenlogger";
$dbusername = "username";
$dbpassword = "password";

// Create connection

$conn = new mysqli($dbservername, $dbusername, $dbpassword);
// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}


// INSERT VALUES ....

// In normal use, no human will ever see this output, but
// the response will be shown on screen for people running the client manually
// so it's good practice to output something to help debug.


$sql = "INSERT INTO $dbdatabasename VALUES (1,NOW(), 1)";
if ($conn->query($sql) === TRUE) {
    echo "<p>Updated OK.</p>";
} else {
    echo "<p>Error updating</p>: " . $conn->error;
}

$conn->close();
?>
