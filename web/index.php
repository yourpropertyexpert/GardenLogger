<!DOCTYPE html>
<html lang="en">

<head>
  <title>Bootstrap 4 Example</title>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.1.0/css/bootstrap.min.css">
  <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.0/umd/popper.min.js"></script>
  <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.1.0/js/bootstrap.min.js"></script>
</head>

<body>


  <div class="jumbotron">
    <h1>GardenLogger</h1>
    <p>Gardenlogger is a website (that runs on a web server on the Internet)
       that accepts temperature readings (from sensors attached to a Raspberry Pi) in the home...</p>
</div>

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
$dbdatabasename = "GardenWeb";
$dbusername = "website";
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


$sql = "SELECT * FROM Readings ORDER BY SENSOR, ReadingTimeDate DESC;";

if(!$result = $conn->query($sql)){
    die('There was an error running the query [' . $db->error . ']');
  }
else {

  $LastSensor="";

  echo "<table class='table-striped'>";
  while($row = $result->fetch_assoc()){

    if ($LastSensor==$row['Sensor']) {
      echo "<tr>";
      echo "<td/>";
      }
    else {
      echo "<tr class='table-primary'>";
      echo "<td>";
      echo $row['Sensor'];
      echo "</td><td/><td/></tr><tr><td/>";
      }
    echo "<td>";
    echo $row['ReadingTimeDate'];
    echo "</td>";
    echo "<td>";
    echo $row['Reading'];
    echo "</td>";
    echo "</tr>";
    $LastSensor=$row['Sensor'];
    }
  echo "</table>";
  }




$conn->close();
?>
</body>
