<?php

namespace Framework\MVC\Model;

use Framework\Database\AbstractConnection;
use Framework\Database\QueryBuilder;

abstract class AbstractModel
{
    protected ?AbstractConnection $conn;
    protected string $table;
    protected string $primary_key;
    protected array $columns;

    public function __construct(AbstractConnection $conn = null)
    {
        $this->conn = $conn;
        $this->populate();
    }

    public function &__get($property)
    {
        if (property_exists($this, $property)) {
            return $this->$property;
        } else {
            throw new \Exception("Invalid Property: '" . $property . "' in class '" . get_class($this) . "'");
        }
    }


    public function __set($property, $value)
    {
        if (property_exists($this, $property)) {
            $this->$property = $value;
        } else {
            throw new \Exception("Invalid Property: '" . $property . "' in class '" . get_class($this) . "'");
        }
    }

    public function newQuery()
    {
        return new QueryBuilder($this->conn, $this);
    }

    public function create(array $data)
    {
        return $this->newQuery()->create($data);
    }

    public function all()
    {
        return $this->newQuery()->all();
    }

    public function find(int $id)
    {
        return $this->newQuery()->find($id);
    }

    public function update(int $id, array $data)
    {
        return $this->newQuery()->update($id, $data);
    }

    public function delete($id)
    {
        return $this->newQuery()->delete($id);
    }

    public function where($column, $operator, $value)
    {
        return $this->newQuery()->where($column, $operator, $value);
    }

    public function customQuery($query, $data = [])
    {
        return $this->newQuery()->custom($query, $data);
    }
    

    protected abstract function populate();
}
