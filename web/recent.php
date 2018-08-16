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
        <a class="dropdown-item" href="recent.php?Duration=1y">Year</a>
        <a class="dropdown-item" href="recent.php?Duration=100y">All time</a>

      </div>
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


switch($_GET['Duration']) {

  case '1h':
  $DurationString="1 HOUR";
  $DurationDisplay = "hour";
  break;


  case '12h':
  $DurationString="12 HOUR";
  $DurationDisplay = "12 hours";
  break;

  case '24h':
  $DurationString="1 DAY";
  $DurationDisplay = "24 hours";
  break;

  case '7d':
  $DurationString="7 DAY";
  $DurationDisplay = "week";
  break;

  case '1mo':
  $DurationString="1 MONTH";
  $DurationDisplay = "month";
  break;

  case '1y':
  $DurationString="1 YEAR";
  $DurationDisplay = "year";
  break;

  case '100y':
  $DurationString="100 YEAR";
  $DurationDisplay = "lifetime of readings";
  break;

  default:
    $DurationString="1 DAY";
    $DurationDisplay = "day";
}

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

// Get the data for the period

$sql ="SELECT
  IFNULL(SensorNames.SensorName, Readings.Sensor) AS Sensor,
  ReadingTimeDate,
  Reading
  FROM Readings
  LEFT JOIN SensorNames
  ON Readings.Sensor=SensorNames.Sensor
  WHERE ReadingTimeDate >= NOW() - INTERVAL $DurationString
  ORDER BY SENSOR, Readings.ReadingTimeDate DESC
  ;
  ";
if(!$result = $conn->query($sql)){
    die('There was an error running the query [' . $db->error . ']');
  }
else {

  $DataArray=[];
  $AveragesArray=[];

  while($row = $result->fetch_assoc()){
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
  echo '<h2>Metrics over the last ';
  echo $DurationDisplay;
  echo '</h2>
          <div id="metricschartcontainer"/>
          </div>';
  echo '</div>';


  echo '<div class="container">';

        if(!$result = $conn->query($sql)){
          die('There was an error running the query [' . $db->error . ']');
        }
        else {
          echo "<h2>Last $DurationDisplay</h2>";
          echo "<div class='card-columns'>\r\n";

          while($row = $result->fetch_assoc())
          {
            // The variable "theseaverages" is set first, then the AveragesArray Updated
            // then the variable is reset. The reason for this is to get arround PHP processing
            // issues where keys are themselves values out of arrays
            $theseaverages=$AveragesArray[$row['SensorName']];
            $AveragesArray[$row['SensorName']]['Current']=$row['Reading'];
            $AveragesArray[$row['SensorName']]['RoundedAverage']=round(100*$theseaverages['Sum']/$theseaverages['Count'])/100;
            $AveragesArray[$row['SensorName']]['UnroundedAverage']=$theseaverages['Sum']/$theseaverages['Count'];
            $theseaverages=$AveragesArray[$row['SensorName']];

            echo "<div class=card>\r\n";
            echo "<div class='card-header'>";
            echo $row['SensorName'];
            echo "</div>\r\n";
            echo "<div class='card-body'>";
            echo "<h3> Latest: ".$theseaverages['Current']."</h3>";
            echo "<p>Max: ".$theseaverages['Max']."</p>";
            echo "<p>Average: ".$theseaverages['RoundedAverage']."</p>";
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



  // Output the "last period chart"



  echo '<div class="container">';
  echo '<h2>Readings over the last ';
  echo $DurationDisplay;
  echo '</h2>
          <div id="timeserieschartcontainer"/>
          </div>';
  echo '</div>';


  echo
  "<script>
  $(function () {
      var myChart = Highcharts.chart('timeserieschartcontainer', {
          chart: {
              type: 'line'
          },
          title: {
              text: 'Temperatures over last $DurationDisplay'
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



      $SensorsArray=[];
      $RangeArray=[];
      $AverageArray=[];
      $CurrentArray=[];

      foreach ($AveragesArray as $key => $value) {

// Stuff to format the metrics string goes here

      $SensorsArray[]=$key;
      $RangeArray[]=($value['Min'].",".$value['Max']);
      $AverageArray[]=(($value['RoundedAverage']-0.05).",".($value['RoundedAverage']+0.05));
      $CurrentArray[]=(($value['Current']-0.05).",".($value['Current']+0.05));

      echo "<div>";
      print_r($key);
      echo "<br/>";
      print_r($value);
      echo "<hr/></div>";

      }

      $SensorsString="['";
      $SensorsString.=implode("','",$SensorsArray);
      $SensorsString.="']";

      $RangeString="[[";
      $RangeString.=implode("],[",$RangeArray);
      $RangeString.="]]";

      $AverageString="[[";
      $AverageString.=implode("],[",$AverageArray);
      $AverageString.="]]";

      $CurrentString="[[";
      $CurrentString.=implode("],[",$CurrentArray);
      $CurrentString.="]]";




  echo
  "<script src='https://code.highcharts.com/highcharts-more.js'></script>
  <script>
  $(function () {
      var myChart = Highcharts.chart('metricschartcontainer', {
          chart: {
            type: 'column'
          },
          title: {
              text: 'Metrics over last $DurationDisplay'
          },
          xAxis: {
            categories: $SensorsString
          },
          yAxis: {
              title: {
                  text: 'Hello'
              }
          },
          series: [
            {
              type: 'columnrange',
              name: 'Range',
              color: 'green',
              stacking: 'normal',
              data: $RangeString
            },
            {
              name: 'Average',
              type: 'columnrange',
              color: 'yellow',
              borderWidth: 2,
              borderColor: 'yellow',
              stacking: 'normal',
              data: $AverageString
            },
            {
              name: 'Current',
              type: 'columnrange',
              color: 'blue',
              stacking: '',
              borderWidth: 2,
              borderColor: 'blue',
              data: $CurrentString
            }
          ]
      });
  });
  </script>";


$conn->close();
  // print_r($SensorsString);
  // echo "<hr/>";
  print_r($CurrentString);
  // echo "<hr/>";

?>



</body>
