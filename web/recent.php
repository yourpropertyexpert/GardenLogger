<?php

namespace yourpropertyexpert;

require_once("./includes.php");

$head = new Template();
echo $head->render("menu", []);

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

  case '4h':
  $DurationString="4 HOUR";
  $DurationDisplay = "4 hours";
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

$conn = new DB();
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$AveragesArray=array();

// Get the data for the period

$sql ="SELECT
  Sensor,
  ReadingTimeDate,
  Reading
  FROM Readings
  WHERE ReadingTimeDate >= NOW() - INTERVAL $DurationString
  ORDER BY SENSOR, Readings.ReadingTimeDate
  ;
  ";

if(!$result = $conn->query($sql)){
    die('There was an error running the query [' . $db->error . ']');
  }
else {

  $DataArray=array();
  $AveragesArray=array();

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
    $AveragesArray[$row['Sensor']]['Current'] = $row['Reading'];
    }

    foreach ($AveragesArray as $key => $value) {
        $sum = $AveragesArray[$key]['Sum'];
        $count = $AveragesArray[$key]['Count'];
        $average = round($sum / $count, 1);
        $AveragesArray[$key]['Average']=$average;
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


  // Create the container for the metrics chart

  echo '<div class="container">';
  echo '<h2>Metrics over the last ';
  echo $DurationDisplay;
  echo '</h2>
          <div id="metricschartcontainer"/>
          </div>';
  echo '</div>';

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



      $SensorsArray=array();
      $RangeArray=array();
      $AverageArray=array();
      $CurrentArray=array();

      // To make HighCharts display the mixed graph correctly,
      // we have to take the values and turn them into ranges
      foreach ($AveragesArray as $key => $value) {
      $SensorsArray[]=$key;
      $RangeArray[]=($value['Min'].",".$value['Max']);
      $AverageArray[]=(($value['Average']-0.05).",".($value['Average']+0.05));
      $CurrentArray[]=(($value['Current']-0.05).",".($value['Current']+0.05));

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
$foot = new Template();
echo $foot->render("foot", []);
