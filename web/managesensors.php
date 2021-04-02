<?php

namespace yourpropertyexpert;

require_once("./includes.php");

$head = new Template();
echo $head->render("menu", []);

$sensornames = new SensorNames();
$pagebody = new Template();
$data = [];

if (array_key_exists("action", $_REQUEST) && $_REQUEST["action"] == "Edit") {
    if ($sensornames->upsertFromPost($_REQUEST)) {
        $data["updated"] = true;
    }
}

if (array_key_exists("action", $_REQUEST) && $_REQUEST["action"] == "Backfill") {
    $readings = new Readings();
    if ($readings->backfillFromPost($_REQUEST)) {
        $data["backfilled"] = true;
    }
}

$data["named"] = $sensornames->getNamedSensors();
$data["all"] = $sensornames->getAllSensors();
echo $pagebody->render("sensornames", $data);

$foot = new Template();
echo $foot->render("foot", []);
