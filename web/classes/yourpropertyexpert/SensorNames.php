<?php

/**
 * SensorNames class
 *
 * @copyright Mark Harrison Ltd 2021
 */

namespace yourpropertyexpert;

/**
 * SensorNames class to encapsulate access credentials
 */
class SensorNames
{
    private $conn;

    /**
     * Constructs the SensorNames class, setting up a DB object for internal use
     */
    public function __construct()
    {
        $this->conn = new DB();
    }

    /**
     * Gets the sensors for which names have been configured
     * @return array of arrays, containing the sensor ID and display name
     */
    public function getNamedSensors()
    {
        $sql = "SELECT * FROM SensorNames";
        try {
            $stmt = $this->conn->prepare($sql);
            $stmt->execute();
            return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        } catch (\Error $ex) {
            error_log($ex);
            return ["success" => false, "error" => "Database error"];
        }
    }

    public function getNamedSensorsKeyed()
    {
        $return = [];
        foreach ($this->getNamedSensors() as $thisone) {
            $return[$thisone["Sensor"]] = $thisone;
        }
        return $return;
    }

    /**
     * Gets all sensors
     * @return array of strings, being the sensor ID
     */
    public function getAllSensors()
    {
        $sql = "SELECT DISTINCT(Sensor) FROM Readings";
        try {
            $stmt = $this->conn->prepare($sql);
            $stmt->execute();
            $raw = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        } catch (\Error $ex) {
            error_log($ex);
            return ["success" => false, "error" => "Database error"];
        }

        $named = $this->getNamedSensorsKeyed();
        $return = [];
        foreach ($raw as $thisone) {
            if (array_key_exists($thisone["Sensor"], $named)) {
                $thisone["SensorName"] = $named[$thisone["Sensor"]]["SensorName"];
            }
            $return[] = $thisone;
        }
        return $return;
    }

    /**
     * Updates from known values
     */
    public function upsertSensorName($sensor, $sensorname)
    {
        $sql = "INSERT INTO SensorNames (Sensor, SensorName)
            VALUES (?, ?)
            ON DUPLICATE KEY UPDATE SensorName = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("sss", $sensor, $sensorname, $sensorname);
        $stmt->execute();
        return true;
    }

    /**
     * Updates from a _REQUEST (normally POST)
     */
    public function upsertFromPost($post)
    {
        if (!array_key_exists("sensor", $post)) {
            return false;
        }
        if (!array_key_exists("sensorname", $post)) {
            return false;
        }
        return $this->upsertSensorName($post["sensor"], $post["sensorname"]);
    }
}
