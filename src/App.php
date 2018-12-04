<?php

namespace Tiny;

use FastRoute\Dispatcher;
use FastRoute\RouteCollector;
use Monolog\Formatter\LineFormatter;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use function FastRoute\simpleDispatcher;

class App
{
    /**
     * @var Route[]
     */
    private $routes;

    public function __construct()
    {
        $this->routes = [];
    }

    public function get($route, $callable)
    {
        $this->routes[] = new Route('GET', $route, $callable);
    }

    public function post($route, $callable)
    {
        $this->routes[] = new Route('POST', $route, $callable);
    }

    public function run()
    {
        $accessLog = LoggerFactory::getLogger('access', Logger::INFO);
        $errorLog = LoggerFactory::getLogger('error', Logger::ERROR);

        $dispatcher = simpleDispatcher(function (RouteCollector $r) {
            foreach ($this->routes as $route) {
                $r->addRoute($route->getMethod(), $route->getRoute(), $route->getCallable());
            }
        });

        $request = Request::createFromGlobals();
        $method = $request->getMethod();
        $uri = $request->getPathInfo();

        $routeInfo = $dispatcher->dispatch($method, $uri);
        switch ($routeInfo[0]) {
            case Dispatcher::NOT_FOUND:
                $accessLog->addInfo(sprintf('%s %s: 404 Not Found', $method, $uri));
                $response = new Response('Not found', 404);
                break;
            case Dispatcher::METHOD_NOT_ALLOWED:
                $accessLog->addInfo(sprintf('%s %s: 405 Not Allowed', $method, $uri));
                $response = new Response('Method not allowed', 405);
                $response->headers->set('Allow', implode(', ', $routeInfo[1]));
                break;
            default:
                $accessLog->addInfo(sprintf('%s %s: 200 OK', $method, $uri));
                $handler = $routeInfo[1];
                $request->attributes->add($routeInfo[2]);
                try {
                    $response = $handler($request);
                } catch (\Exception $e) {
                    $errorLog->addError(sprintf('Exception in request handler: %s', $e->getMessage()), array('exception' => $e));
                    $response = new Response('Internal Server Error', 500);
                }
        }

        $response->prepare($request);
        $response->send();
    }
}