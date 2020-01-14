<?php

use Slim\Routing\RouteCollectorProxy;

class RouteGroup
{
    public function __construct(string $url, array $routes)
    {
        $this->url = $url;
        $this->routes = $routes;
    }

    public function RegisterRoutes(RouteCollectorProxy $app)
    {
        $routes = $this->routes;
        $app->group($this->url, function (RouteCollectorProxy $group) use ($routes) {
            foreach ($routes as $route) {
                if ($route instanceof CrudRoute) {
                    $route->RegisterAction($group);
                } else if ($route instanceof RouteGroup) {
                    $route->RegisterRoutes($group);
                }
            }
        });
    }
}
