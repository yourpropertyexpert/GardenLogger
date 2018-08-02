<?php

// You'll need to replace the following with the credentials for your DATABASE
// There is complexity about using localhost for Mysql connections,
// so for Docker on Mac (which I use), it's better to use host.docker.internal

// You will note that I'm explicitly including the credentials here, rather than sharing
// them with index. This is to allow this page to use a database user with R/W access,
// and the human-facing one to use a database user with Read-Only access.

// In production, it is better to move the credentials out to the environment rather than
// having them in the code.


$dbservername = "host.docker.internal";
$dbdatabasename = "GardenWeb";
$dbusername = "rasp";
$dbpassword = "rasprasp";

// Create connection

$conn = new mysqli($dbservername, $dbusername, $dbpassword, $dbdatabasename);
// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}



$SensorID=$_GET['SensorID'];
$SensorReading=$_GET['Reading'];

// INSERT VALUES ....

// In normal use, no human will ever see this output, but
// the response will be shown on screen for people running the client manually
// so it's good practice to output something to help debug.


$sql = "INSERT
  INTO Readings (Sensor, ReadingTimeDate, Reading)
  VALUES ('$SensorID', NOW(), $SensorReading)
  ";

if ($conn->query($sql) === TRUE) {
    echo "Updated reading of $SensorReading for sensor $SensorID OK.";


} else {
    echo "<p>Error updating</p>: " . $conn->error;
}

$conn->close();
?>
