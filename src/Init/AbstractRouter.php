<?php


namespace Framework\Init;

abstract class AbstractRouter
{

    private $routes;

    public function __construct()
    {
        $this->initRoutes();
        $this->run($this->getUrl());
    }

    public function __destruct()
    {
        foreach (get_object_vars($this) as $var => $value)
            unset($this->$var);
    }

    protected abstract function initRoutes();

    public function getRoutes()
    {
        return $this->routes;
    }


    public function setRoutes(array $routes)
    {
        $this->routes = $routes;
    }


    protected function run($url)
    {
        $pageExists = false;
        foreach ($this->getRoutes() as $key => $route) {
            if ($url == $route['route']) {
                $pageExists = true;
                $controller_path = $route['controller'];
                $model_path = $route['model'];
                $controller = new $controller_path($route['model'] ? new $model_path : null);
                $action = $route['action'];
                exit($controller->$action());
            }
        }
        if (!$pageExists) {
            http_response_code(404);
            exit;
        }
    }

    protected function getUrl()
    {
        return parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
    }
}
