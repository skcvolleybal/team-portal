<?php

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class DeleteRoute extends CrudRoute
{
    public function __construct($route, $interactor)
    {
        $this->route = $route;
        $this->interactor = $interactor;
    }

    public function RegisterAction(RouteCollectorProxy $group)
    {
        $interactor = $this->interactor;
        $crudRoute = $this;
        $group->get($this->route, function (Request $request, Response $response, iterable $args) use ($interactor, $crudRoute) {
            $interactor  = $this->get($interactor);
            $data = $interactor->Execute($args);
            return $crudRoute->FillBody($response, $data);
        });
    }
}
