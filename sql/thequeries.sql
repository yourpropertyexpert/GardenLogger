Current readings:

SELECT
  SensorNames.SensorName as SensorName, Reading, ReadingTimeDate
  FROM SensorNames, Readings
  INNER JOIN
  (SELECT Sensor, Max(ReadingTimeDate) AS MostRecentTimeStamp
  FROM Readings GROUP BY Sensor) MostRecents
  ON MostRecents.Sensor=Readings.Sensor
  WHERE SensorNames.Sensor = Readings.Sensor
  AND Readings.ReadingTimeDate=MostRecents.MostRecentTimeStamp
  ORDER BY SensorName
;


ALL - Last 24 hours:

SELECT
  IFNULL(SensorNames.SensorName, Readings.Sensor) AS Sensor,
  ReadingTimeDate,
  Reading
  FROM Readings
  LEFT JOIN SensorNames
  ON Readings.Sensor=SensorNames.Sensor
  WHERE ReadingTimeDate >= NOW() - INTERVAL 1 DAY
  ORDER BY SENSOR, Readings.ReadingTimeDate DESC
;
