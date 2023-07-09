<?php

namespace TeamPortal\RouteObjects;

use TeamPortal\Gateways\WordPressGateway;
use GuzzleHttp\Psr7\Response;
use Slim\Routing\RouteCollectorProxy;
use UnauthorizedException;
use UnexpectedValueException;

abstract class CrudRoute
{
    function FillBody(Response $response, $data)
    {
        if (is_null($data)) return $response;

        $jsonData = json_encode($data);
        if ($data === false) {
            $response->getBody()->write($data);
        } else {
            $response->getBody()->write($jsonData);
        }

        return $response;
    }

    function MergeInputObjects(array $queryParameters, array $postBody): object
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

    function Authorize(WordPressGateway $wordPressGateway, ?int $role)
    {
        $user = $wordPressGateway->GetUser();
        switch ($role) {
            case null;
            case 0:
                $isAuthorized = true;
                break;
            case 1:
                $isAuthorized = $user !== null;
                break;
            case 2:
                $isAuthorized = $wordPressGateway->IsBarcie($user);
                break;
            case 3:
                $isAuthorized =  $wordPressGateway->IsScheidsrechter($user);
                break;
            case 4:
                $isAuthorized =  $wordPressGateway->IsTeamcoordinator($user);
                break;
            case 5:
                $isAuthorized =  $wordPressGateway->IsWebcie($user);
                break;
            default:
                $isAuthorized = false;
        }

        if (!$isAuthorized) {
            if ($user !== null) {
                throw new UnexpectedValueException("Je bent hier niet voor geautoriseerd");
            } else {
                throw new UnauthorizedException();
            }
        }
    }

    abstract function RegisterAction(RouteCollectorProxy $group);
}
