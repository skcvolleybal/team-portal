<?php

if (DIRECTORY_SEPARATOR != '/') {
    error_reporting(E_ALL);
    ini_set("display_errors", 1);
}

set_include_path(get_include_path() . PATH_SEPARATOR . '.' . DIRECTORY_SEPARATOR . 'UseCases');
set_include_path(get_include_path() . PATH_SEPARATOR . '.' . DIRECTORY_SEPARATOR . 'Common');
set_include_path(get_include_path() . PATH_SEPARATOR . '.' . DIRECTORY_SEPARATOR . 'DomainEntities');
set_include_path(get_include_path() . PATH_SEPARATOR . '.' . DIRECTORY_SEPARATOR . 'Gateways');
set_include_path(get_include_path() . PATH_SEPARATOR . '.' . DIRECTORY_SEPARATOR . 'libs');

$http_referer = $_SERVER['HTTP_REFERER'] ?? "http://localhost:4200/";
if ($http_referer == "http://localhost:4200/") {
    $origin = "http://localhost:4200";
} else {
    $origin = "https://www.skcvolleybal.nl";
}

header("Access-Control-Allow-Origin: $origin");

if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    $allowedHeaders = "Content-Type";
    header("Access-Control-Allow-Origin: $origin");
    header("Access-Control-Allow-Credentials: true");
    header("Access-Control-Allow-Headers: $allowedHeaders");
    exit;
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
