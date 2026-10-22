<?php

namespace TeamPortal\Common;

use UnexpectedValueException;

class Curl
{
    function SendRequest(Request $request): HttpResponse
    {
        $timeout = 5;
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $request->url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
        curl_setopt($ch, CURLOPT_TIMEOUT, $timeout);

        curl_setopt($ch, CURLOPT_HEADER, 1);

        if ($request->body) {
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $request->body);
        }

        if ($request->headers) {
            curl_setopt($ch, CURLOPT_HTTPHEADER, $request->headers);
        }

        $rawResponse = curl_exec($ch);

        if ($rawResponse === false) {
            $error = curl_error($ch);
            curl_close($ch);
            throw new UnexpectedValueException(
                "Fout bij het uitvoeren van het request: $error",
            );
        }

        $response = new HttpResponse($rawResponse);

        curl_close($ch);

        return $response;
    }
}
