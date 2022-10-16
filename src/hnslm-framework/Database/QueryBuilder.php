<?php

namespace Framework\Database;

use Exception;
use Framework\Database\AbstractConnection;
use Framework\MVC\Model\AbstractModel;
use PDO;

class QueryBuilder
{
    private PDO $conn;
    private AbstractModel $model;
    // private PDOStatement $stmt;

    public function __construct(AbstractConnection $conn, AbstractModel $model)
    {
        $this->conn = $conn->getConn();
        $this->model = $model;
    }


    public function newQuery()
    {
        return $this->conn;
    }

    public function update(int $id, array $data, $assoc = true)
    {

        if (!$data) {
            throw new Exception('Insuficient parameters.', 500);
        }

        $query = "UPDATE {$this->model->table} SET ";

        $query .= implode(', ', array_map(function ($key) use ($data) {
            return "{$key} = :{$key}";
        }, array_keys($data)));

        $query .= " WHERE {$this->model->primary_key} = {$id}";
        $stmt = $this->conn->prepare($query);

        $stmt->execute($data);

        return $this->find($id, $assoc);
    }

    public function create(array $data, $assoc = true)
    {

        if (!$data) {
            throw new Exception('Insuficient parameters.', 503);
        }

        $columns = implode(', ', array_keys($data));
        $values = implode(', ', array_map(fn ($value) => ':' . $value, array_keys($data)));
        $query = "INSERT INTO " . $this->model->table . " ({$columns}) VALUES ({$values})";
        $stmt = $this->conn->prepare($query);

        $stmt->execute($data);

        return $this->find($this->conn->lastInsertId(), $assoc);
    }

    public function delete(int $id)
    {
        $query = "DELETE FROM {$this->model->table} WHERE {$this->model->primary_key} = {$id}";
        $stmt = $this->conn->prepare($query);
        return $stmt->execute();
    }

    public function all($assoc = true)
    {
        $query = "SELECT * FROM " . $this->model->table;
        $stmt = $this->conn->prepare($query);

        $stmt->execute();

        if ($assoc) {
            return $this->fetchList($stmt);
        } else {
            return $stmt;
        }
    }

    public function find(int $id, $assoc = true)
    {

        $stmt = $this->conn->prepare("SELECT * FROM " . $this->model->table .
            " WHERE " . $this->model->primary_key . " = :id");

        $stmt->execute([':id' => $id]);

        if ($assoc) {
            return $this->fetchArray($stmt);
        } else {
            return $stmt;
        }
    }

    public function where(array $filters = [], $assoc = true)
    {
        $query = "SELECT * FROM {$this->table} WHERE ";

        $filters = array_map(fn ($filter) => "{$filter[0]} = :{$filter[0]}", $filters);
        $query .= implode(' AND ', $filters);
        $stmt = $this->conn->prepare($query);

        $stmt->execute($filters);

        if ($assoc) {
            return $this->fetchList($stmt);
        } else {
            return $stmt;
        }
    }

    public function fetchObject($stmt)
    {
        if ($result = $stmt->fetchObject(PDO::FETCH_ASSOC)) {
            foreach (get_object_vars($result) as $var => $value)
                $this->$var = $value;
            return true;
        } else {
            return false;
        }
    }

    public function fetchArray($stmt)
    {
        if ($result = $stmt->fetch(PDO::FETCH_ASSOC)) {
            return $result;
        } else {
            return false;
        }
    }

    public function fetchList($stmt)
    {
        if ($result = $stmt->fetchAll(PDO::FETCH_ASSOC)) {
            return $result;
        } else {
            return false;
        }
    }


    public function numRows($stmt)
    {
        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
        return count($result);
    }
}
