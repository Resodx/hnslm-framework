<?php

namespace Framework\MVC\Controller;

use Exception;
use ReflectionClass;
use stdClass;
use Framework\MVC\Model\AbstractModel;

abstract class AbstractController
{

    protected AbstractModel $model;
    protected stdClass $class;
    protected array $request;

    public function __construct(AbstractModel $model)
    {
        $this->request = $_REQUEST;
        $this->model = $model;

        $this->request = match($_SERVER['REQUEST_METHOD']) {
            'GET' => $_GET,
            'POST' => $_POST,
            'PUT','DELETE' => $this->parseInput(),
            default => throw new Exception('Invalid Request', 400)
 
        };

    }

    public function __destruct()
    {
        foreach (get_object_vars($this) as $var => $value)
            unset($this->$var);
    }

    public function list()
    {

        header('Content-Type: application/json');

        try {

            $data = $this->model->all();

            http_response_code(200);

            return [
                'success' => true,
                'data' => $data ?: null
            ];
        } catch (Exception $e) {
            http_response_code(500);
            return [
                'success' => false,
                'message' => $e->getMessage(),
                'code' => $e->getCode(),
            ];
        }
    }

    public function add()
    {
        header('Content-Type: application/json');

        try {

            $params = array_filter($this->request, function ($key) {
                return in_array($key, $this->model->columns);
            }, ARRAY_FILTER_USE_KEY);

            $model = $this->model->create($params);

            http_response_code(201);

            return [
                'success' => true,
                'data' => $model ?: null,
                'message' => (new ReflectionClass($this->model))->getShortName() . " added successfully"
            ];
        } catch (Exception $e) {
            http_response_code(500);
            return [
                'success' => false,
                'message' => $e->getMessage(),
                'code' => $e->getCode(),
            ];
        }
    }

    public function search()
    {
        header('Content-Type: application/json');

        try {

            $params = array_filter($this->request, function ($key) {
                return in_array($key, $this->model->columns);
            }, ARRAY_FILTER_USE_KEY);

            if (!array_key_exists($this->model->primary_key, $_GET)) {
                throw new Exception("Error when searching a " . (new ReflectionClass($this->model))->getShortName() . ". Missing parameters.", 500);
            }

            $model = $this->model->find($_GET[$this->model->primary_key]);
            http_response_code(200);

            return [
                'success' => true,
                'data' => $model ?: null
            ];
        } catch (Exception $e) {
            http_response_code(500);

            return [
                'success' => false,
                'message' => $e->getMessage(),
                'code' => $e->getCode(),
            ];
        }
    }

    public function update()
    {

        header('Content-Type: application/json');

        $params = array_filter($this->request, function ($key) {
            return in_array($key, $this->model->columns);
        }, ARRAY_FILTER_USE_KEY);

        try {

            if (!array_key_exists($this->model->primary_key, $_GET)) {
                throw new Exception("Error when updating a " . (new ReflectionClass($this->model))->getShortName() . ". Missing parameters.", 500);
            }

            $this->model->update($_GET[$this->model->primary_key], $params);

            http_response_code(200);

            return [
                'success' => true,
                'data' => $this->model->find($_GET[$this->model->primary_key]) ?: null,
                'message' => (new ReflectionClass($this->model))->getShortName() . " updated successfully"
            ];
        } catch (Exception $e) {
            http_response_code(500);

            return [
                'success' => false,
                'message' => $e->getMessage(),
                'code' => $e->getCode(),
            ];
        }
    }

    public function remove()
    {

        header('Content-Type: application/json');

        try {

            $params = array_filter($this->request, function ($key) {
                return in_array($key, $this->model->columns);
            }, ARRAY_FILTER_USE_KEY);

            if (!$this->model->delete($_GET[$this->model->primary_key])) {
                throw new Exception("Error when deleting a " . (new ReflectionClass($this->model))->getShortName() . ". Missing parameters.", 500);
            }

            http_response_code(202);

            return [
                'success' => true,
                'message' => (new ReflectionClass($this->model))->getShortName() . " removed successfully"
            ];
        } catch (Exception $e) {
            http_response_code(500);

            return [
                'success' => false,
                'message' => $e->getMessage(),
                'code' => $e->getCode(),
            ];
        }
    }

    protected function render($class, $layout)
    {
        $this->class->page = $class;
        if (file_exists("../App/View/" . $layout . ".phtml")) {
            require_once "../App/View/" . $layout . ".phtml";
        } else {
            $this->loadContent();
        }
    }

    private function loadContent()
    {
        $class = strtolower(str_replace('App\\Controller\\', '', get_class($this)));

        if (file_exists("../App/View/" . $class . "/" . $this->class->page . ".phtml")) {
            require_once "../App/View/" . $class . "/" . $this->class->page . ".phtml";
        } else {
            http_response_code(404);
            exit;
        }
    }

    function parseInput()
    {
        $input = file_get_contents('php://input');
        json_decode($input);
        if(json_decode($input) && json_last_error() === JSON_ERROR_NONE) {
            $input = json_decode($input, true);
        } else {
            parse_str(file_get_contents('php://input'), $input);
        }
        return $input;
    }

}
