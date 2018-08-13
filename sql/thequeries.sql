Most recent readings:


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
