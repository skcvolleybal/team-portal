<?php

use Slim\Routing\RouteCollectorProxy;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class DeleteRoute extends CrudRoute
{
    public function __construct(string $route, string $interactor, int $role = null)
    {
        $this->route = $route;
        $this->interactor = $interactor;
        $this->role = $role;
    }

    public function RegisterAction(RouteCollectorProxy $group)
    {
        $interactor = $this->interactor;
        $route = $this;

        $group->delete($this->route, function (Request $request, Response $response, array $args) use ($interactor, $route) {
            $joomlaGateway = $this->get(JoomlaGateway::class);
            $route->Authorize($joomlaGateway, $route->role);

            $interactor  = $this->get($interactor);
            $body = $request->getQueryParams();
            $input = $route->MergeInputObjects($body, $args);
            $data = $interactor->Execute($input);
            return $route->FillBody($response, $data);
        });
    }
}
