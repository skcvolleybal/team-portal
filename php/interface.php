<?php

error_reporting(E_ALL);
ini_set("display_errors", 1);

set_include_path(get_include_path() . PATH_SEPARATOR . '.' . DIRECTORY_SEPARATOR . 'UseCases');
set_include_path(get_include_path() . PATH_SEPARATOR . '.' . DIRECTORY_SEPARATOR . 'Common');
set_include_path(get_include_path() . PATH_SEPARATOR . '.' . DIRECTORY_SEPARATOR . 'DomainEntities');
set_include_path(get_include_path() . PATH_SEPARATOR . '.' . DIRECTORY_SEPARATOR . 'Gateways');
set_include_path(get_include_path() . PATH_SEPARATOR . '.' . DIRECTORY_SEPARATOR . 'libs');

if (DIRECTORY_SEPARATOR == '/') {
    header('Access-Control-Allow-Origin: https://www.skcvolleybal.nl');
} else {
    header('Access-Control-Allow-Origin: http://localhost:4200');
}

header('Access-Control-Allow-Credentials: true');

require_once "TeamPortal.php";

$app = new TeamPortal;

$queryString = $_SERVER['QUERY_STRING'];
if (empty($queryString)) {
    $app->NoAction();
}

parse_str($queryString, $parsedQueryString);
$action = $parsedQueryString['action'];

if (method_exists($app, $action)) {
    $app->{$action}();
} else {
    $app->unknownAction($action);
}
$app->unknownAction($action);
