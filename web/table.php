<?php

namespace yourpropertyexpert;

require_once("./includes.php");

$head = new Template();

echo $head->render("menu", []);
// You'll need to replace the following with the credentials for your DATABASE
// There is complexity about using localhost for Mysql connections,
// so for Docker on Mac (which I use), it's better to use docker.internal

// You will note that I'm explicitly including the credentials here, rather than sharing
// them with pushreading. This is to allow this page to use a database user with R/O access,
// and the Pi website to use a database user with Read-Write access.

// In production, it is better to move the credentials out to the environment rather than
// having them in the code.


$dbservername = "db";
$dbdatabasename = "GardenWeb";
$dbusername = "root";
$dbpassword = "my_secret_pw_shh";

// Create connection

$conn = new \mysqli($dbservername, $dbusername, $dbpassword, $dbdatabasename);
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
