<?php


namespace OJSXml;


interface Database
{
    public function query($query);
    public function resultset();
    public function rowCount();
    public function execute();
    public function bind($param, $value, $type = null);


}