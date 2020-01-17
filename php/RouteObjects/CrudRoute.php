<?php

use GuzzleHttp\Psr7\Response;
use Slim\Routing\RouteCollectorProxy;

abstract class CrudRoute
{
    function FillBody(Response $response, $data)
    {
        $response->getBody()->write(json_encode($data));
        return $response;
    }

    function MergeInputObjects(iterable $queryParameters, iterable $postBody): object
    {
        $result = (object) [];
        foreach ($queryParameters as $key => $value) {
            $result->$key = $value;
        }
        foreach ($postBody as $key => $value) {
            $result->$key = $value;
        }
        return $result;
    }

    function Authorize(JoomlaGateway $joomlaGateway, ?int $role)
    {
        $user = $joomlaGateway->GetUser();
        switch ($role) {
            case null;
            case 0:
                $isAuthorized = true;
                break;
            case 1:
                $isAuthorized = $user !== null;
                break;
            case 2:
                $isAuthorized = $joomlaGateway->isBarcie($user);
                break;
            case 3:
                $isAuthorized =  $joomlaGateway->isScheidsrechter($user);
                break;
            case 4:
                $isAuthorized =  $joomlaGateway->isTeamcoordinator($user);
                break;
            case 5:
                $isAuthorized =  $joomlaGateway->isWebcie($user);
                break;
            default:
                $isAuthorized = false;
        }

        if (!$isAuthorized) {
            throw new UnauthorizedException();
        }
    }

    abstract function RegisterAction(RouteCollectorProxy $group);
}
