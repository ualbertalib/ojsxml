<?php


namespace OJSXml;


class Config {
    static private $data;

    static public function load($configFile) {
        self::$data = parse_ini_file($configFile);
    }

    static public function get($key) {
        return self::$data[$key];
    }

}