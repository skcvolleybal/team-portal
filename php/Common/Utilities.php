<?php
setlocale(LC_ALL, 'nl_NL');

function IsNullOrEmpty($obj)
{
    return !$obj || empty($obj);
}

// function GetPostValues()
// {
//     $postData = file_get_contents('php://input');
//     if (empty($postData)) {
//         return null;
//     }

//     return json_decode($postData);
// }

// function GetQueryParameters()
// {
//     parse_str($_SERVER['QUERY_STRING'], $query_array);
//     return (object) $query_array;
// }





// function GetYmdDate($timestamp = null)
// {
//     $timestamp = $timestamp ?? time();
//     return date("Y-m-d", $timestamp);
// }



// function GetQueryStringParamater($name)
// {
//     $queryString = $_SERVER['QUERY_STRING'];
//     if (empty($queryString)) {
//         return null;
//     }

//     parse_str($queryString, $parsedQueryString);
//     if (isset($parsedQueryString[$name])) {
//         return $parsedQueryString[$name];
//     }

//     return null;
// }










// function SanitizeQueryString($url)
// {
//     $url = explode('?', $url);
//     $parts = explode('&', $url[1]);
//     $newParts = [];
//     foreach ($parts as $part) {
//         $params = explode('=', $part);
//         $newParts[] = $params[0] . '=' . rawurlencode($params[1]);
//     }
//     return $url[0] . '?' . implode('&', $newParts);
// }

// function SendPost($url, $post_fields, $headers = null)
// {
//     $ch = curl_init();
//     $timeout = 5;
//     curl_setopt($ch, CURLOPT_URL, $url);
//     if ($post_fields && !empty($post_fields)) {
//         curl_setopt($ch, CURLOPT_POST, 1);
//         curl_setopt($ch, CURLOPT_POSTFIELDS, $post_fields);
//     }
//     if ($headers && !empty($headers)) {
//         curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
//     }
//     curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
//     curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
//     curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
//     curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
//     $data = curl_exec($ch);

//     if (curl_errno($ch)) {
//         echo 'Error:' . curl_error($ch);
//     }
//     curl_close($ch);
//     return $data;
// }

// function GetConfigValue($key)
// {
//     $config = JFactory::getConfig();
//     return $config->get($key);
// }

// function GetKlasse($poule)
// {
//     if (strlen($poule) !== 3) {
//         throw new InvalidArgumentException('Poule $poule is niet valide');
//     }
//     if ($poule[1] == 'P') {
//         return 'Promotieklasse';
//     } else if (is_numeric($poule[1])) {
//         return $poule[1] . 'e klasse';
//     }
// }
