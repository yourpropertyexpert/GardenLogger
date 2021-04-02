<?php

/**
 * Readings class
 *
 * @copyright Mark Harrison Ltd 2021
 */

namespace yourpropertyexpert;

/**
 * Readings class to encapsulate access credentials
 */
class Readings
{
    private $conn;

    /**
     * Constructs the Readings class, setting up a DB object for internal use
     */
    public function __construct()
    {
        $this->conn = new DB();
    }

    /**
     * Backfills from known values
     */
    public function backfill($oldname, $newname)
    {
        $sql = "UPDATE Readings SET Sensor = ? WHERE Sensor = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("ss", $newname, $oldname);
        $stmt->execute();
        return true;
    }

    /**
     * Backfills from REQUEST (normally a POST)
     */
    public function backfillFromPost($post)
    {
        if (!array_key_exists("oldname", $post)) {
            return false;
        }
        if (!array_key_exists("sensorname", $post)) {
            return false;
        }
        $this->backfill($post["oldname"], $post["sensorname"]);
        return true;
    }
}
