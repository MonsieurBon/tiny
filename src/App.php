<?php
namespace Tiny;

use FastRoute\Dispatcher;
use FastRoute\RouteCollector;
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
        $dispatcher = simpleDispatcher(function(RouteCollector $r) {
           foreach ($this->routes as $route) {
               $r->addRoute($route->getMethod(), $route->getRoute(), $route->getCallable());
           }
        }, [
            'cacheFile' => __DIR__.'/../../var/cache/route.cache'
        ]);

        $request = Request::createFromGlobals();

        $method = $request->getMethod();
        $uri = $request->getPathInfo();

        $routeInfo = $dispatcher->dispatch($method, $uri);
        switch ($routeInfo[0]) {
            case Dispatcher::NOT_FOUND:
                $response = new Response('Not found', 404);
                break;
            case Dispatcher::METHOD_NOT_ALLOWED:
                $response = new Response('Method not allowed', 405);
                $response->headers->set('Allow', implode(', ', $routeInfo[1]));
                break;
            default:
                $handler = $routeInfo[1];
                $request->attributes->add($routeInfo[2]);
                $response = $handler($request);
        }

        $response->prepare($request);
        $response->send();
    }
}