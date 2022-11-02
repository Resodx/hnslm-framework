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
                $model = array_key_exists('model', $route) ? new $route['model'] : null;
                $controller = new $controller_path($model);
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
