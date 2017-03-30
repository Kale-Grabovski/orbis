<?php

namespace App\Components;

use PDO;
use PDOException;

/**
 * Database class to manage connection
 */
class DB
{
    /**
     * Connection instance
     * @var
     */
    private static $instance;

    /**
     * Creates instance of the database connection
     *
     * @param string $driver
     * @param string $host
     * @param string $user
     * @param string $password
     * @param string $database
     */
    public static function init(string $driver, string $host, string $user, string $password, string $database)
    {
        try {
            self::$instance = new PDO("$driver:host=$host;dbname=$database", $user, $password);
        } catch (PDOException $e) {
            throw $e;
        }
    }

    /**
     * Returns instance of the database connection
     *
     * @return PDO
     */
    public static function getInstance() : PDO
    {
        return self::$instance;
    }

    /**
     * Prevents creating instance of the class
     */
    private function __construct() {}

    /**
     * Prevents creating instance of the class
     */
    private function __clone() {}
}
