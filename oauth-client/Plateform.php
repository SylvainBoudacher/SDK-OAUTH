<?php

class Plateform {

    private $name;
    
    private $urlOAuth;
    private $urlToken;

    private $clientId;
    private $clientSecret;
    
    private $state;

    private $redirectUri;
    

    public function __construct($name, $urlOAuth, $urlToken, $clientId, $clientSecret, $redirectUri, $state) {
        $this->name = $name;
        $this->urlOAuth = $urlOAuth;
        $this->urlToken = $urlToken;
        $this->clientId = $clientId;
        $this->clientSecret = $clientSecret;
        $this->redirectUri = $redirectUri;
        $this->state = $state;
    }

    public function getClientId() : string {
        return $this->clientId;
    }

    public function getClientSecret() : string {
        return $this->clientId;
    }

    public function getOAuthUrl(string $scope) : string {
        $url = Helpers::urlBuilder($this->urlOAuth, [
            "client_id" => $this->clientId,
            "scope" => $scope,
            "redirect_uri" => $this->redirectUri,
            "state" => $this->state
        ]);

        return $url;
    }

    public function getUrlToken() : string {
        return $this->urlToken;
    }

    public function getTokenParams(string $code) : string {
        return Helpers::queryParamsBuilder([
            "client_id" => $this->clientId,
            "client_secret" => $this->clientSecret,
            "code" => $code,
            "grant_type" => "authorization_code",
            "redirect_uri" => $this->redirectUri
        ]);
    }
}