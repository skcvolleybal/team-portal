<?php

namespace TeamPortal\RouteObjects;

use TeamPortal\Gateways\WordPressGateway;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Routing\RouteCollectorProxy;
use TeamPortal\RouteObjects\CrudRoute;

class PostRoute extends CrudRoute
{

    public string $route;
    public string $interactor;
    public ?int $role;

    
    public function __construct($route, $interactor, int $role = null)
    {
        $this->role = $role;
        $this->route = $route;
        $this->interactor = $interactor;
    }

    public function RegisterAction(RouteCollectorProxy $group)
    {
        $interactor = $this->interactor;
        $route = $this;

        $group->post($this->route, function (Request $request, Response $response, array $args) use ($interactor, $route) {
            $wordPressGateway = $this->get(WordPressGateway::class);
            $route->Authorize($wordPressGateway, $route->role);

            $interactor = $this->get($interactor);
            $body = $request->getParsedBody();
            $input = $route->MergeInputObjects($body, $args);
            $data = $interactor->Execute($input);
            return $route->FillBody($response, $data);
        });
    }
}
