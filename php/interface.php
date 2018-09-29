<?php

error_reporting(E_ALL);
ini_set("display_errors", 1);

set_include_path(get_include_path() . PATH_SEPARATOR . './UseCases');
set_include_path(get_include_path() . PATH_SEPARATOR . './Common');
set_include_path(get_include_path() . PATH_SEPARATOR . './DomainEntities');
set_include_path(get_include_path() . PATH_SEPARATOR . './Gateways');
set_include_path(get_include_path() . PATH_SEPARATOR . './libs');
set_include_path(get_include_path() . PATH_SEPARATOR . '/public_html');
set_include_path(get_include_path() . PATH_SEPARATOR . './Joomla');

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
