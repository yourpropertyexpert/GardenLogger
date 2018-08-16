<!DOCTYPE html>
<html lang="en">

<head>
  <title>GardenLogger</title>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.1.0/css/bootstrap.min.css">
  <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.0/umd/popper.min.js"></script>
  <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.1.0/js/bootstrap.min.js"></script>
  <script src="http://code.highcharts.com/highcharts.js"></script>

</head>

<body>


  <div class="jumbotron">
    <h1>GardenLogger</h1>
    <p>Gardenlogger is a website (that runs on a web server on the Internet)
       that accepts temperature readings (from sensors attached to a Raspberry Pi) in the home...</p>
</div>

<nav class="navbar navbar-expand-sm bg-dark navbar-dark fixed-top">

  <ul class="navbar-nav">
    <li class="nav-item">
      <a class="nav-link" href="index.php">Home</a>
    </li>
    <li class="nav-item dropdown">
      <a class="nav-link dropdown-toggle" href="#" id="navbardrop" data-toggle="dropdown">
        Recent
      </a>
      <div class="dropdown-menu">
        <a class="dropdown-item" href="recent.php?Duration=1h">Hour</a>
        <a class="dropdown-item" href="recent.php?Duration=12h">12 hours</a>
        <a class="dropdown-item" href="recent.php?Duration=24h">Day</a>
        <a class="dropdown-item" href="recent.php?Duration=7d">Week</a>
        <a class="dropdown-item" href="recent.php?Duration=1mo">Month</a>
      </div>
    </li>
    <li class="nav-item">
      <a class="nav-link .active" href="table.php">All readings</a>
    </li>
  </ul>
</nav>

<?php

// You'll need to replace the following with the credentials for your DATABASE
// There is complexity about using localhost for Mysql connections,
// so for Docker on Mac (which I use), it's better to use host.docker.internal

// You will note that I'm explicitly including the credentials here, rather than sharing
// them with pushreading. This is to allow this page to use a database user with R/O access,
// and the Pi website to use a database user with Read-Write access.

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


echo '<div class="container">
      <h2>Chart</h2>
      <div id="chartcontainer"/>
      </div>';



$sql = "SELECT * FROM Readings ORDER BY SENSOR, ReadingTimeDate DESC ;";

$sql ="SELECT
  IFNULL(SensorNames.SensorName, Readings.Sensor) AS Sensor,
  ReadingTimeDate,
  Reading
  FROM Readings
  LEFT JOIN SensorNames
  ON Readings.Sensor=SensorNames.Sensor
  ORDER BY SENSOR, Readings.ReadingTimeDate DESC
  ;
  ";
if(!$result = $conn->query($sql)){
    die('There was an error running the query [' . $db->error . ']');
  }
else {

  $CategoriesArray=[];
  $DataArray=[];
  $PreviousSensor="";

  echo "<div class='container'>";
  echo "<h2>Table</h2>";
  echo "<table class='table-striped'>";
  while($row = $result->fetch_assoc()){

    if ($PreviousSensor==$row['Sensor']) {
      echo "<tr>";
      echo "<td/>";
      }
    else {
      echo "<tr class='table-primary'>";
      echo "<td>";
      echo $row['Sensor'];
      $CategoriesArray[]=$row['Sensor'];
      echo "</td><td/><td/></tr><tr><td/>";
      }
    echo "<td>";
    echo $row['ReadingTimeDate'];
    echo "</td>";
    echo "<td>";
    $DataArray[$row['Sensor']][]="[".(1000*strtotime($row['ReadingTimeDate'])).",".$row['Reading']."]";
    echo $row['Reading'];
    echo "</td>";
    echo "</tr>";
    $PreviousSensor=$row['Sensor'];
    }

  echo "</table>";
  echo "</div>";

  $CategoriesString="['".implode("','", $CategoriesArray)."']";

  $DataString='[';

  foreach ($DataArray as $key => $value) {
    if($DataString!='[')
      $DataString.=',';
    $DataString.="{name: '";
    $DataString.=$key;
    $DataString.="',";
    $DataString.='data: [';
    $DataString.=implode(",",$value);
    $DataString.="]}";
  }

  $DataString.="]";
    echo
    "<script>
    $(function () {
        var myChart = Highcharts.chart('chartcontainer', {
            chart: {
                type: 'line'
            },
            title: {
                text: 'Temperatures over time'
            },
            xAxis: {
              type: 'datetime'
            },
            yAxis: {
                title: {
                    text: ''
                }
            },
            series: $DataString
        });
    });
    </script>";
  }

$conn->close();


// print_r($DataString);

?>



</body>
