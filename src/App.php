<?php

namespace Phencil;

use FastRoute\Dispatcher;
use FastRoute\RouteCollector;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class App
{
    private $_options;
    private $_routes;

    public function __construct($options = [])
    {
        $this->_options = $options;
        $this->_routes  = [];
    }

    public function __call($method, $args)
    {
        if (!in_array(strtoupper($method), ['GET', 'POST', 'PUT', 'PATCH', 'DELETE', 'HEAD'])) {
            throw new \Exception("Method `$method` is not allowed");
        }

        if (count($args) != 2) {
            throw new \Exception("Invalid parameters");
        }

        list($route, $handler) = $args;

        if (!is_callable($handler)) {
            throw new \Exception("Invalid handler");
        }

        $this->_routes[$method][$route] = $handler;
    }

    public function run()
    {
        //---------------------------------------------------------------------------
        // Init request

        $request = Request::createFromGlobals();

        //---------------------------------------------------------------------------
        // Init router

        $routes = $this->_routes;
        $dispatcher =  \FastRoute\simpleDispatcher(function (RouteCollector $r) use ($routes) {
            foreach ($routes as $method => $data) {
                foreach ($data as $route => $handler) {
                    $r->$method($route, $handler);
                }
            }
        });

        //---------------------------------------------------------------------------
        // Dispatch

        $routeInfo = $dispatcher->dispatch($request->getMethod(), $request->getPathInfo());
        switch ($routeInfo[0]) {
            case Dispatcher::FOUND:
                $view = new View($this->_options);
                $handler = $routeInfo[1];
                $vars = $routeInfo[2];
                $content = call_user_func_array($handler->bindTo($view), $vars);
                $statusCode = Response::HTTP_OK;
                break;
            case Dispatcher::NOT_FOUND:
                $content = "404 Not Found";
                $statusCode = Response::HTTP_NOT_FOUND;
                break;
            case Dispatcher::METHOD_NOT_ALLOWED:
                $content = "405 Method Not Allowed";
                $statusCode = Response::HTTP_METHOD_NOT_ALLOWED;
                break;
        }

        //---------------------------------------------------------------------------
        // Send response

        $response = new Response($content, $statusCode);
        $response->prepare($request)->send();
    }
}
