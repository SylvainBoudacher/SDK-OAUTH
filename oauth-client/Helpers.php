<?php

class Helpers {

    public static function urlBuilder(string $baseUrl, array $params) : string {
        $queryParams = '';

        // Note : replacement fro str_contains() from Php8.
        $hasAlreadyFirstParam = strpos($baseUrl, '?');
        
        $index = 0;
        foreach ($params as $paramName => $paramValue) {
            $separator = ($index === 0 && $hasAlreadyFirstParam === false) ? '?' : '&';
            $queryParams .= $separator . $paramName . '=' . $paramValue;
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

}