<?php

class Helpers {

    public static function urlBuilder(string $baseUrl, array $params) : string {
        $queryParams = '';

        // Note : replacement fro str_contains() from Php8.
        $hasAlreadyFirstParam = strpos($baseUrl, '?');
        
        $index = 0;
        foreach ($params as $paramName => $paramValue) {
            $separator = ($index === 0 && $hasAlreadyFirstParam === false) ? '?' : '&';

            if (!empty($paramValue)) {
                $queryParams .= $separator . $paramName . '=' . $paramValue;
            }
            $index++;
        }

        return $baseUrl . $queryParams; 
    }

    public static function queryParamsBuilder(array $params) : string {
        $queryParams = '';

        $index = 0;
        foreach ($params as $paramName => $paramValue) {
            $separator = ($index === 0) ? '' : '&';
            $queryParams .= $separator . $paramName . '=' . $paramValue;
            $index++;
        }
        
        return $queryParams;
    }

    public static function curl(string $baseUrl, string $params, array $headers = null) : string {
        $fullURL = $baseUrl.'?'.$params;

        $curl = curl_init();

        $curlParams = [
            CURLOPT_URL => empty($headers) ? $fullURL : $baseUrl,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
        ];

        if (!empty($headers)) {
            $curlParams[CURLOPT_POSTFIELDS] = $params;
            $curlParams[CURLOPT_HTTPHEADER] = $headers;
        }

        curl_setopt_array($curl, $curlParams);
        $response = curl_exec($curl);
    
        curl_close($curl);

        return $response;
    }

    public static function debug($data, string $dataLabel = '') : void {
        $label = !empty($dataLabel) ? "### ". $dataLabel . "###" : '';
        
        echo "<br>".$label."<br>";
        echo "<br><pre>". print_r($data) ."</pre><br>";
    }

}