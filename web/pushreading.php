<?php

// You'll need to replace the following with the credentials for your DATABASE
// There is complexity about using localhost for Mysql connections,
// so for Docker on Mac (which I use), it's better to use host.docker.internal

// You will note that I'm explicitly including the credentials here, rather than sharing
// them with index. This is to allow this page to use a database user with R/W access,
// and the human-facing one to use a database user with Read-Only access.

// In production, it is better to move the credentials out to the environment rather than
// having them in the code.



$dbservername = "localhost";
$dbdatabasename = "GardenWeb";
$dbusername = "root";
$dbpassword = "mypassword";
$ValidationExpected="secret";  // Change this to a Validation secret to match the one the Pi is sending


// Create connection

$conn = new mysqli($dbservername, $dbusername, $dbpassword, $dbdatabasename);
// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}


// We need to escape this to ensure that it's not going to give us SQL injection problems

$SensorID=$_GET['SensorID'];
$SensorID=mysqli_real_escape_string($conn,$SensorID);


// We don't need to escape the code, because all we do is compare it
$ValidationReceived=$_GET['Code'];

// We don't need to escape the reading, because we're going type check it
$SensorReading=$_GET['Reading'];


// INSERT VALUES ....

// In normal use, no human will ever see this output, but
// the response will be shown on screen for people running the client manually
// so it's good practice to output something to help debug.

if ($ValidationExpected!=$ValidationReceived) {
  die("<p>Validation code missing or invalid</p>");

}

if (!is_numeric($SensorReading)) {
  die("<p>Reading appeared bogus</p>");
}


if (!is_numeric($SensorReading)) {
  die("<p>Reading appeared bogus</p>");
}


if ($SensorReading > 75) {
    die("<p>Reading too high</p>");
}

$SensorName = $SensorID;

$sql = "SELECT SensorName from SensorNames where Sensor='$SensorID';";

$result=$conn->query($sql);
if ($result->num_rows==1) {
    $row=$result->fetch_assoc();
    $SensorName=$row["SensorName"];
}


$sql = "INSERT
  INTO Readings (Sensor, ReadingTimeDate, Reading)
  VALUES ('$SensorName', NOW(), $SensorReading)
  ";

if ($conn->query($sql) === TRUE) {
    echo "Updated reading of $SensorReading for sensor $SensorName ($SensorID) OK.";


} else {
    echo "<p>Error updating</p>: " . $conn->error;
}

$conn->close();
?>
