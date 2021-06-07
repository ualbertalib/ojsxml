<?php


namespace OJSXml;


use DateTime;
use DateTimeZone;

class Logger {

    public static array $messages = [];

    private static string $_fileName = '';

    /**
     * @throws \Exception
     */
    public static function __constructStatic() {
        $tz = 'America/Vancouver';
        $timestamp = time();
        $dt = new DateTime("now", new DateTimeZone($tz)); //first argument "must" be a string
        $dt->setTimestamp($timestamp); //adjust the object to correct timestamp
        self::$_fileName = $dt->format('YmdHi') . '_log.txt';
    }

    public static function print(string $message) {
        array_push(self::$messages, $message);
        echo $message . PHP_EOL;
    }


    public static function writeOut($command, $user) {
        $file = fopen(Config::get('logLocation') . '/' . $command . '_' . $user . '_'. self::$_fileName, 'w') or die('Unable to open file');
        fwrite($file, self::formatToString(self::$messages));
        fclose($file);
    }

    /**
     * @param $string
     * @return string
     */
    private static function formatToString(array $messages) : string {
        $returner = '';

        foreach ($messages as $message) {
            $returner .= $message . PHP_EOL;
        }

        return $returner;
    }
}

Logger::__constructStatic();
