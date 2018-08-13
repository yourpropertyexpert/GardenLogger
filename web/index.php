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
    <li class="nav-item">
      <a class="nav-link" href="table.php">All readings</a>
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


// SQL to select the "most recent temperatures"
// This relies on the fact that the most recent time/date is the "Max" of ReadingTimeDate

echo '<div class="container">';

        $sql = "
        SELECT SensorNames.SensorName as SensorName, Reading, ReadingTimeDate
        FROM SensorNames, Readings
        INNER JOIN
        (SELECT Sensor, Max(ReadingTimeDate) AS MostRecentTimeStamp
        FROM Readings GROUP BY Sensor) MostRecents
        ON MostRecents.Sensor=Readings.Sensor
        WHERE SensorNames.Sensor = Readings.Sensor
        AND Readings.ReadingTimeDate=MostRecents.MostRecentTimeStamp
        ORDER BY SensorName
        ;
        ";


        if(!$result = $conn->query($sql)){
          die('There was an error running the query [' . $db->error . ']');
        }
        else {
          echo "<div class='card-columns'>\r\n";

          while($row = $result->fetch_assoc())
          {
            echo "<div class=card>\r\n";
            echo "<div class='card-header'>";
            echo $row['SensorName'];
            echo "</div>\r\n";
            echo "<div class='card-body'>";
            echo $row['Reading'];
            echo "</div>\r\n";
            echo "<div class='card-footer'>";
            echo $row['ReadingTimeDate'];
            echo "</div>\r\n";
            echo "</div>\r\n";

          }

          echo "</div>\r\n";
        }
echo "</div>";


// Now the chart
// This code is duplicated into the "table.php", and probably ought to be pulled into an include


echo '<h2>Chart</h2>
        <div id="chartcontainer"/>
        </div>';


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

  while($row = $result->fetch_assoc()){

    $CategoriesArray[]=$row['Sensor'];
    $DataArray[$row['Sensor']][]="[".(1000*strtotime($row['ReadingTimeDate'])).",".$row['Reading']."]";
    }


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
