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
    <li class="nav-item .active">
      <a class="nav-link" href="recent.php">Last 24 hours</a>
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


$AveragesArray=[];

// Get the data for the last 24 hours

$sql ="SELECT
  IFNULL(SensorNames.SensorName, Readings.Sensor) AS Sensor,
  ReadingTimeDate,
  Reading
  FROM Readings
  LEFT JOIN SensorNames
  ON Readings.Sensor=SensorNames.Sensor
  WHERE ReadingTimeDate >= NOW() - INTERVAL 1 DAY
  ORDER BY SENSOR, Readings.ReadingTimeDate DESC
  ;
  ";
if(!$result = $conn->query($sql)){
    die('There was an error running the query [' . $db->error . ']');
  }
else {

  $CategoriesArray=[];
  $DataArray=[];
  $AveragesArray=[];

  while($row = $result->fetch_assoc()){
    $CategoriesArray[]=$row['Sensor'];
    $DataArray[$row['Sensor']][]="[".(1000*strtotime($row['ReadingTimeDate'])).",".$row['Reading']."]";
    if(!isset($AveragesArray[$row['Sensor']]))
      {
        $AveragesArray[$row['Sensor']]['Max']=-10000;
        $AveragesArray[$row['Sensor']]['Min']=10000;
        $AveragesArray[$row['Sensor']]['Sum'] = 0;
        $AveragesArray[$row['Sensor']]['Count']=0;
      }

    $AveragesArray[$row['Sensor']]['Max'] = max($AveragesArray[$row['Sensor']]['Max'], $row['Reading']);
    $AveragesArray[$row['Sensor']]['Min'] = min($AveragesArray[$row['Sensor']]['Min'], $row['Reading']);
    $AveragesArray[$row['Sensor']]['Sum'] = ($AveragesArray[$row['Sensor']]['Sum']) + $row['Reading'];
    $AveragesArray[$row['Sensor']]['Count'] = ($AveragesArray[$row['Sensor']]['Count']) + 1;

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

  }


  // SQL to select the "most recent temperatures"
  // This relies on the fact that the most recent time/date is the "Max" of ReadingTimeDate

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


// Loop through the results, creating a Bootstrap "card" for each, using a mix of this and the array we made earlier.


  echo '<div class="container">';

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
            echo "<h3> Latest: ".$row['Reading']."</h3>";
            $theseaverages=$AveragesArray[$row['SensorName']];
            echo "<p>Max: ".$theseaverages['Max']."</p>";
            echo "<p>Average: ";
            echo round(100*$theseaverages['Sum']/$theseaverages['Count'])/100; // Round to 2dp
            echo "</p>";
            echo "<p>Min: ".$theseaverages['Min']."</p>";

//            debugging
//            print_r ($theseaverages);

            echo "</div>\r\n";
            echo "<div class='card-footer'><p>Latest at ";
            echo $row['ReadingTimeDate'];
            echo "</p></div>\r\n";
            echo "</div>\r\n";

          }

          echo "</div>\r\n";
        }
  echo "</div>";

  // Finally, output the chart

  echo '<h2>Chart</h2>
          <div id="chartcontainer"/>
          </div>';




  echo
  "<script>
  $(function () {
      var myChart = Highcharts.chart('chartcontainer', {
          chart: {
              type: 'line'
          },
          title: {
              text: 'Temperatures over last 24 hours'
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


$conn->close();


// print_r($AveragesArray);

?>



</body>