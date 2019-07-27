<?php

function IncludeInPath($folder)
{
    set_include_path(get_include_path() . PATH_SEPARATOR . $folder);
}

if (DIRECTORY_SEPARATOR != '/') {
    error_reporting(E_ALL);
    ini_set("display_errors", 1);
}

if (!defined('JPATH_BASE')) {
    if (DIRECTORY_SEPARATOR == '/') {
        define('JPATH_BASE', '/home/deb105013n2/domains/skcvolleybal.nl/public_html/');
    } else {
        define('JPATH_BASE', "C:\\Users\\jonat\\joomla-website");
    }
}

if (!defined('_JEXEC')) {
    define('_JEXEC', 1);
}
require_once JPATH_BASE . '/includes/defines.php';
require_once JPATH_BASE . '/includes/framework.php';

IncludeInPath(dirname(__FILE__) . DIRECTORY_SEPARATOR . 'UseCases');
IncludeInPath(dirname(__FILE__) . DIRECTORY_SEPARATOR . 'Common');
IncludeInPath(dirname(__FILE__) . DIRECTORY_SEPARATOR . 'DomainEntities');
IncludeInPath(dirname(__FILE__) . DIRECTORY_SEPARATOR . 'Gateways');
IncludeInPath(dirname(__FILE__) . DIRECTORY_SEPARATOR . 'libs');

$http_referer = $_SERVER['HTTP_REFERER'] ?? "http://localhost:4200/";
if ($http_referer == "http://localhost:4200/") {
    $origin = "http://localhost:4200";
} else {
    $origin = "https://www.skcvolleybal.nl";
}

if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    $allowedHeaders = "Content-Type";
    header("Access-Control-Allow-Origin: $origin");
    header("Access-Control-Allow-Credentials: true");
    header("Access-Control-Allow-Headers: $allowedHeaders");
    exit;
}

header("Access-Control-Allow-Origin: $origin");
header('Access-Control-Allow-Credentials: true');

require_once "TeamPortal.php";

$app = new TeamPortal;

$queryString = $_SERVER['QUERY_STRING'];
if (empty($queryString)) {
    $app->NoAction();
}

parse_str($queryString, $parsedQueryString);
if (!isset($parsedQueryString['action'])) {
    $app->NoAction();
}
$action = $parsedQueryString['action'];

if (method_exists($app, $action)) {
    $app->{$action}();
} else {
    $app->unknownAction($action);
}
