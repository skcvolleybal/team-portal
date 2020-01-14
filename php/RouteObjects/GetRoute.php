<?php

use Slim\Routing\RouteCollectorProxy;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class GetRoute extends CrudRoute
{
    public function __construct(string $route, string $interactor)
    {
        $this->route = $route;
        $this->interactor = $interactor;
    }

    public function RegisterAction(RouteCollectorProxy $group)
    {
        $interactor = $this->interactor;
        $route = $this;
        $group->get($this->route, function (Request $request, Response $response, iterable $args) use ($interactor, $route) {
            $interactor  = $this->get($interactor);
            $body = $request->getQueryParams();
            $input = $route->MergeInputObjects($body, $args);
            $data = $interactor->Execute($input);
            return $route->FillBody($response, $data);
        });
    }
}
