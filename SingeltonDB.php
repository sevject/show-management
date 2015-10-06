<?php
/**
 * Created by PhpStorm.
 * User: tina_hammer
 * Date: 27.08.14
 * Time: 10:40
 */

use show_management\Config\SystemConfig;

require_once(__DIR__ . '/config/.SystemConfig.php');
require_once(__DIR__ . '/DB/mysql.inc.php');


class SingeltonDB
{

    private static $uniqueSQLCon;

    public static function generalDBConnection()
    {
        if (!isset(self::$uniqueSQLCon)) {
            $sql = new \sDb(
                SystemConfig::DB_HOST,
                SystemConfig::DB_USER,
                SystemConfig::DB_PASS,
                SystemConfig::DB_NAME
            );
            self::$uniqueSQLCon = $sql;
        }

        return self::$uniqueSQLCon;
    }


}