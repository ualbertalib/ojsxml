<?php


namespace OJSXml;


class DatabaseFactory
{


    public function makeDB($DB_TYPE,$SQLite_Location)
    {
        if( strtolower($DB_TYPE) == 'mysql' ){
            return new MySQL();
        }else{
            return new SQLiteDB($SQLite_Location);
        }
    }

}