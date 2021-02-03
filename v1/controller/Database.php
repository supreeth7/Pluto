<?php
require_once 'config.php';

class Database
{
    private static $readDBConnection;
    private static $writeDBConnection;

    public static function connectReadDatabase()
    {
        if (self::$readDBConnection == null) {
            self::$readDBConnection = new PDO('mysql:host='.$_ENV['HOST'].';dbname='.$_ENV['DB_NAME'], $_ENV['USER'], $_ENV['PASSWORD']);
            self::$readDBConnection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            self::$readDBConnection->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
        }

        return self::$readDBConnection;
    }

    public static function connectWriteDatabase()
    {
        if (self::$writeDBConnection == null) {
            self::$writeDBConnection = new PDO('mysql:host='.$_ENV['HOST'].';dbname='.$_ENV['DB_NAME'], $_ENV['USER'], $_ENV['PASSWORD']);
            self::$writeDBConnection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            self::$writeDBConnection->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
        }

        return self::$writeDBConnection;
    }
}
