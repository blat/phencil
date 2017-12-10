<?php

namespace Phencil;

use FastRoute\Dispatcher;
use FastRoute\RouteCollector;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;

class App
{
    private $_options;
    private $_request;
    private $_routes;

    public function __construct($options = [])
    {
        $this->_options = $options;
        $this->_routes  = [];

        //---------------------------------------------------------------------------
        // Init request

        $this->_request = Request::createFromGlobals();
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

        $routeInfo = $dispatcher->dispatch($this->_request->getMethod(), $this->_request->getPathInfo());
        switch ($routeInfo[0]) {
            case Dispatcher::FOUND:
                $handler = new Handler($this);
                $callback = $routeInfo[1];
                $vars = $routeInfo[2];
                $content = call_user_func_array($callback->bindTo($handler), $vars);
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
        $this->_sendResponse($response);
    }

    public function redirect($url)
    {
        $response = new RedirectResponse($url);
        $this->_sendResponse($response);
    }

    public function sendFile($file, $filename = null)
    {
        $response = new BinaryFileResponse($file);
        if ($filename) {
            $response->setContentDisposition(ResponseHeaderBag::DISPOSITION_ATTACHMENT, $filename);
        }
        $this->_sendResponse($response);
    }

    private function _sendResponse($response)
    {
        $response->prepare($this->_request)->send();
        exit;
    }

    public function getParam($name)
    {
        $value = null;
        if ($this->_request->query->has($name)) {
            $value = $this->_request->query->get($name); // $_GET
        } else if ($this->_request->request->has($name)) {
            $value = $this->_request->request->get($name); // $_POST
        }
        return $value;
    }

    public function getFile($name)
    {
        return $this->_request->files->get($name); // $_FILES
    }

    public function getOption($name)
    {
        if (isset($this->_options[$name])) {
            return $this->_options[$name];
        }
    }

}
