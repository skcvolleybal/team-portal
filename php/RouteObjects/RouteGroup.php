<?php

use Slim\Routing\RouteCollectorProxy;

class RouteGroup
{
    public function __construct(string $url, array $routes, int $role = null)
    {
        $this->role = $role;
        $this->url = $url;
        $this->routes = $routes;
    }

    public function RegisterRoutes(RouteCollectorProxy $app)
    {
        $routes = $this->routes;
        $role = $this->role;

        $app->group($this->url, function (RouteCollectorProxy $group) use ($routes, $role) {
            foreach ($routes as $route) {
                $route->role = $route->role ?? $role;
                if ($route instanceof CrudRoute) {
                    $route->RegisterAction($group);
                } else if ($route instanceof RouteGroup) {
                    $route->RegisterRoutes($group);
                }
            }
        });
    }
}
