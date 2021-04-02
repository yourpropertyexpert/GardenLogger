<?php

/**
 * include file containing autoloaders
 *
 */

require '/var/www/vendor/autoload.php';

// Set up an autoloader for classes
spl_autoload_register(function ($className) {
    $className = str_replace("\\", DIRECTORY_SEPARATOR, $className);
    include_once $_SERVER['DOCUMENT_ROOT'] . '/classes/' . $className . '.php';
});
