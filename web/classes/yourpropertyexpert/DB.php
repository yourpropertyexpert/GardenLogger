<?php

/**
 * DB class
 *
 * @copyright Mark Harrison Ltd 2020
 */

namespace yourpropertyexpert;

use mysqli;

/**
 * DB class to encapsulate access credentials
 */
class DB extends mysqli
{
    /**
     * Constructs the DB class
     */
    public function __construct()
    {
        $servername = getenv('DBSERVER');
        $username = getenv('DBUSER');
        $password = getenv('DBPASSWORD');
        $dbname =  getenv('DBNAME');

        parent::__construct($servername, $username, $password, $dbname);
    }
}
