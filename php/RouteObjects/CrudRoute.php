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

    abstract function RegisterAction(RouteCollectorProxy $group);
}
