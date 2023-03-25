<?php

namespace VolgPhp\Tails;

class Connection {

    static $connection;

    private function __construct(){}

    public static function get()
    {
        return self::$connection;
    }

    public static function open($connectionName = "default")
    {
        $thereIsNoOpenConn = empty(self::$connection) == true;

        if($thereIsNoOpenConn)
        {
            $connParams = parse_ini_file(__DIR__ . "/config/Connections/{$connectionName}.ini");

            $dbHostname = isset($connParams["dbHostname"]) ? $connParams["dbHostname"] : "";
            $dbName     = isset($connParams["dbName"])     ? $connParams["dbName"] : "";
            $dbUser     = isset($connParams["dbUser"])     ? $connParams["dbUser"] : "";
            $dbPass     = isset($connParams["dbPass"])     ? $connParams["dbPass"] : "";
            $dbType     = isset($connParams["dbType"])     ? $connParams["dbType"] : "";
            $dbPort     = isset($connParams["dbPort"])     ? $connParams["dbPort"] : "";

            $conn = new \PDO(
                "{$dbType}:host={$dbHostname};dbname={$dbName};port={$dbPort}",
                $dbUser, 
                $dbPass, 
                [\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION]
            );

            self::$connection = $conn;
        }

        return self::$connection;
    }

    public static function close()
    {
        self::$connection = NULL;       
    }
}