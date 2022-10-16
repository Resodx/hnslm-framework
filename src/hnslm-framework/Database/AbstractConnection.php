<?php

namespace Framework\Database;

use PDO;

abstract class AbstractConnection
{

    protected PDO $conn;
    protected array $config;

    public function __construct()
    {
        try {
            $this->conn = new PDO($this->getDsn(), $this->config['username'], $this->config['password']);
            return $this->conn;
        } catch (PDOException $e) {
            echo "Erro: " . $e->getCode() . "\r\nMensagem: " . $e->getMessage();
        }
    }

    public function __destruct()
    {
        foreach (get_object_vars($this) as $var => $value)
            unset($this->$var);
    }

    public function getConn(): PDO
    {
        return $this->conn;
    }

    public function getDsn()
    {
        $this->config = $this->loadConfig();
        return $this->config['driver'] . ":host=" . $this->config['host'] . ";port=" . $this->config['port'] . ";dbname=" . $this->config['database'];
    }

    protected abstract function loadConfig();
}
