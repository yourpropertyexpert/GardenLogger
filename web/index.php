<?php

namespace yourpropertyexpert;

require_once("./includes.php");

$head = new Template();
echo $head->render("menu", []);

$conn = new DB();
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
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

// Loop through the results, creating a Bootstrap "card" for each


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
          echo "<div class='card-body'><h3>";
          echo $row['Reading'];
          echo "</h3></div>\r\n";
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

$foot = new Template();
echo $foot->render("foot", []);
