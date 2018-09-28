<?php
include "TeamPortal.class.php";

$app = new TeamPortal();
$action = $_SERVER['QUERY_STRING']['action'];
if (method_exists($app, $action)) {
    $app->{$action}();
} else {
    $app->unknownAction($action);
}
$app->unknownAction($action);
