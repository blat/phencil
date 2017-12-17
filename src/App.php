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
    private static $_options;
    private $_request;
    private $_routes;

    public function __construct($options = [])
    {
        self::$_options = $options;
        $this->_routes  = [];

        if (!empty($options['sessions'])) session_start();

        if (!empty($options['database'])) {

            //---------------------------------------------------------------------------
            // Init ORM

            $capsule = new \Illuminate\Database\Capsule\Manager;
            $capsule->addConnection($options['database']);
            $capsule->setAsGlobal();
            $capsule->bootEloquent();
        }

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
                break;
            case Dispatcher::NOT_FOUND:
                $this->error(Response::HTTP_NOT_FOUND, "404 Not Found");
                break;
            case Dispatcher::METHOD_NOT_ALLOWED:
                $this->error(Response::HTTP_METHOD_NOT_ALLOWED, "405 Method Not Allowed");
                break;
        }

        //---------------------------------------------------------------------------
        // Send response

        $response = new Response($content, Response::HTTP_OK);
        $this->_sendResponse($response);
    }

    public function redirect($url)
    {
        $response = new RedirectResponse($url);
        $this->_sendResponse($response);
    }

    public function error($code, $message = '')
    {
        $response = new Response($message, $code);
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

    public static function getOption($name)
    {
        if (isset(self::$_options[$name])) {
            return self::$_options[$name];
        }
    }

}
