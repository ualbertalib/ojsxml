<?php


namespace OJSXml;

use \SQLite3;

class SQLiteDB implements Database
{

    private $stmt;

    private $error;


    private $handle;
    private $rowcount;

    function __construct($location){

        try{
            $this->handle = new SQLite3($location);

        } catch(Exception $e){
            $this->error = $e->getMessage();
        }
    }

    public function query($query){


        $this->stmt  = $this->handle->prepare($query);

    }

    /**
     * Binds the parameters of the query
     * @param String $param is the placeholder value that we will be using in our SQL statement, example :name.
     * @param String $value is the actual value that we want to bind to the placeholder, example “John Smith”.
     * @param null $type is the datatype of the parameter, example string.
     */
    public function bind($param, $value, $type = null){
        if (is_null($type)) {
            switch (true) {
                case is_int($value):
                    $type = SQLITE3_INTEGER;
                    break;
                case is_bool($value):
                    $type = SQLITE3_INTEGER;
                    break;
                case is_null($value):
                    $type = SQLITE3_NULL;
                    break;
                default:
                    $type = SQLITE3_TEXT;
            }
        }
        $this->stmt->bindValue($param, $value, $type);
    }

    /**
     * @description The next method we will be look at is the PDOStatement::execute. The execute method executes the prepared statement.
     * @return mixed
     */
    public function execute(){
        return $this->stmt->execute();
    }

    public function single($query){

        return $this->handle->querySingle($query, true);
    }

    public function resultset(){
        $result = $this->execute();
        $row = array();

        while ( $res = $result->fetchArray(SQLITE3_ASSOC)){
            $row[] = $res;
            $this->rowcount++;
        }

        return $row;
    }

    public function rowCount(){
        return $this->rowCount();
    }




}