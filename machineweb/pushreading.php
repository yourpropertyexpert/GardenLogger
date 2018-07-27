<?php

// You'll need to replace the following with the credentials for your DATABASE
// There is complexity about using localhost for Mysql connections,
// so for Docker on Mac (which I use), it's better to use host.docker.internal

// You will note that I'm explicitly including the credentials here, rather than sharing
// them with /site. This is to allow this page to use a database user with R/W access,
// and the /site one to use a database user with Read-Only access.


// In production, it is better to move the credentials out to the environment rather than 
// having them in the code.


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
