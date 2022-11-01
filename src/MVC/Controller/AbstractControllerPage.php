<?php

namespace Framework\MVC\Controller;

use Exception;
use stdClass;

abstract class AbstractControllerPage
{

    protected stdClass $view;
    protected array $request;

    public function __construct()
    {
        $this->view = new stdClass();

        $this->request = match ($_SERVER['REQUEST_METHOD']) {
            'GET' => $_GET,
            'POST' => $_POST,
            'PUT', 'DELETE' => $this->parseInput(),
            default => throw new Exception('Invalid Request', 400)
        };
    }

    public function __destruct()
    {
        foreach (get_object_vars($this) as $var => $value)
            unset($this->$var);
    }

    protected function render($view, $layout)
    {
        $this->view->page = $view;
        if (file_exists("../App/View/" . $layout . ".phtml")) {
            require_once "../App/View/" . $layout . ".phtml";
        } else {
            $this->loadContent();
        }
    }

    private function loadContent()
    {
        $view = strtolower(str_replace('App\\Controller\\', '', get_class($this)));

        if (file_exists("../App/View/" . $view . "/" . $this->view->page . ".phtml")) {
            require_once "../App/View/" . $view . "/" . $this->view->page . ".phtml";
        } else {
            http_response_code(404);
            exit;
        }
    }

    function parseInput()
    {
        $input = file_get_contents('php://input');
        json_decode($input);
        if (json_decode($input) && json_last_error() === JSON_ERROR_NONE) {
            $input = json_decode($input, true);
        } else {
            parse_str(file_get_contents('php://input'), $input);
        }
        return $input;
    }
}
