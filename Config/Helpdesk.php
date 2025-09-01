<?php
/**
 * @package EvolutionScript
 * @author: EvolutionScript S.A.C.
 * @Copyright (c) 2010 - 2020, EvolutionScript.com
 * @link http://www.evolutionscript.com
 */

namespace Config;


use CodeIgniter\Config\BaseConfig;

class Helpdesk extends BaseConfig
{
    #Database host
    public static string $DB_HOST = 'db';

    #Database username
    public static string $DB_USER = 'helpdesk';

    #Database password
    public static string $DB_PASSWORD = 'helpdesk';

    #Database name
    public static string $DB_NAME = 'db_helpdesk';

    #Database table prefix
    public static string $DB_PREFIX = 'hdz_';

    #Database port, do not change it if you are not sure
    public static int $DB_PORT = 3306;

    #URL of your helpdesk
    public static string $SITE_URL = 'http://localhost';

    #Upload path for images used in HTML editor and logo
    const UPLOAD_PATH = FCPATH.'upload';

    #Default helpdesk language
    const DEFAULT_LANG = 'en';

    #URI name to access to staff panel. Ex: staff / then you can access in http://helpdesk.com/staff
    const STAFF_URI = 'staff';

    /**
     * Carga variables desde el entorno (Docker, .env, etc.)
     */
    public static function loadEnv(): void
    {
        self::$DB_HOST     = getenv('DB_HOST') ?: self::$DB_HOST;
        self::$DB_USER     = getenv('DB_USER') ?: self::$DB_USER;
        self::$DB_PASSWORD = getenv('DB_PASSWORD') ?: self::$DB_PASSWORD;
        self::$DB_NAME     = getenv('DB_NAME') ?: self::$DB_NAME;
        self::$DB_PREFIX   = getenv('DB_PREFIX') ?: self::$DB_PREFIX;
        self::$DB_PORT     = getenv('DB_PORT') ? (int)getenv('DB_PORT') : self::$DB_PORT;

        self::$SITE_URL    = getenv('SITE_URL') ?: self::$SITE_URL;
    }
}
