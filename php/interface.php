<?php

use PHPMailer\PHPMailer\Exception;

function IncludeInPath($folder)
{
    set_include_path(get_include_path() . PATH_SEPARATOR . $folder);
}

function ExceptionHandler($error)
{
    $currentTime = (new DateTime())->format('Y-m-d H.i.s.u');
    if (!file_exists('errors')) {
        mkdir('errors');
    }
    $line = $error->getLine();
    $file = $error->getFile();
    $message = $error->getMessage();
    $output = "File (line $line): $file\nMessage: $message";
    file_put_contents("errors/$currentTime.txt", $output);

    header('HTTP/1.1 500 Internal Server Error');
    echo $message;
}

$configuration = include('./../configuration.php');
if (isset($configuration->displayErrors)) {
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
}

define('JPATH_BASE', $configuration->JPATH_BASE);
define('_JEXEC', 1);

require_once JPATH_BASE . '/includes/defines.php';
require_once JPATH_BASE . '/includes/framework.php';

IncludeInPath(dirname(__FILE__) . '/UseCases');
IncludeInPath(dirname(__FILE__) . '/Common');
IncludeInPath(dirname(__FILE__) . '/DomainEntities');
IncludeInPath(dirname(__FILE__) . '/Gateways');
IncludeInPath(dirname(__FILE__) . '/libs');
IncludeInPath(dirname(__FILE__) . '/shared');

$accessControlAllowOrigin = $configuration->accessControlAllowOrigin;

if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    $allowedHeaders = 'Content-Type';
    header("Access-Control-Allow-Origin: $accessControlAllowOrigin");
    header('Access-Control-Allow-Credentials: true');
    header("Access-Control-Allow-Headers: $allowedHeaders");
    exit;
}

header("Access-Control-Allow-Origin: $accessControlAllowOrigin");
header('Access-Control-Allow-Credentials: true');

require_once 'TeamPortal.php';

set_exception_handler('ExceptionHandler');


$app = new TeamPortal($configuration->database);

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
