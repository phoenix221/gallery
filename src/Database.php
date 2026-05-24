<?php

namespace App;

use mysqli;
use Exception;
use Monolog\Level;
use Monolog\Logger;
use Monolog\Handler\StreamHandler;

class Database
{
    private $host;
    private $database;
    private $username;
    private $password;
    private ?mysqli $connection = null;

    public function __construct(){
        $config = require './config.php';
        $this->host = $config['database']['DB_HOST'];
        $this->database = $config['database']['DB_NAME'];
        $this->username = $config['database']['DB_USER'];
        $this->password = $config['database']['DB_PASSWORD'];
    }
    public function connect(): ?mysqli
    {
        if ($this->connection !== null) {
            return $this->connection;
        }
        mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
        $log = new Logger('database');
        $log->pushHandler(new StreamHandler('logs/errors.log', Level::Error));

        try{
            $this->connection = new mysqli($this->host, $this->username, $this->password, $this->database);
            return $this->connection;
        }catch (Exception $e){
            $log->error($e->getMessage());
            return null;
        }
    }

    public function query(string $query, array $params, string $method) : array
    {
        $connection = $this->connect();
        $sql = $connection->prepare($query);
        if(!empty($params)){
            $types = $this->getParamsTypes($params);
            $sql->bind_param($types, ...$params);
        }
        $sql->execute();

        if($method == 'SELECT'){
            $result = $sql->get_result();

            if(!$result){
                return [];
            }
            return $result->fetch_all(MYSQLI_ASSOC);

        }else{
            return [];
        }
    }

    private function getParamsTypes(array $params) : string
    {
        $types = '';
        foreach ($params as $param) {
            switch (gettype($param)) {
                case 'integer':
                    $types .= 'i';
                    break;

                case 'double':
                    $types .= 'd';
                    break;

                case 'string':
                    $types .= 's';
                    break;

                default:
                    $types .= 'b';
            }
        }

        return $types;
    }

}



