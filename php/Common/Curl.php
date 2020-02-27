<?php

namespace TeamPortal\Common;

class Curl
{
    function SendRequest(Request $request): HttpResponse
    {
        $timeout = 5;
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $request->url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout);

        curl_setopt($ch, CURLOPT_HEADER, 1);

        if ($request->body) {
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $request->body);
        }

        if ($request->headers) {
            curl_setopt($ch, CURLOPT_HTTPHEADER, $request->headers);
        }

        $response = new HttpResponse(curl_exec($ch));

        if (curl_errno($ch)) {
            echo 'Error:' . curl_error($ch);
        }
        curl_close($ch);

        return $response;
    }
}
