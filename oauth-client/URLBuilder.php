<?php

class URLBuilder {

    // Global O Auth token url builder
    public static function getOAuthToken($baseUrl, $clientID, $scope, $redirectURI = null, $state = null) {
        $url = $baseUrl . '&client_id=' . $clientID . '&scope=' . $scope;
        $url .= !empty($redirectURI) ? '&redirect_uri=' . $redirectURI : '';
        $url .= !empty($state) ? '&state='.$state : '';
        return $url;
    }

    // Twitch tocken
    public static function getAccessTokenTwitch($code) : string {
        //accessTokenTwitch
        $url = 'https://id.twitch.tv/oauth2/token?';
        $url .= "client_id=".CLIENT_TWITCHID;
        $url .= "&client_secret=".CLIENT_TWITCHSECRET;
        $url .= "&code=$code";
        $url .= "&grant_type=authorization_code";
        $url .= "&redirect_uri=https://localhost/twitchauth-success";
    
        return $url;
    }


    // Facebook tocken
    
    public static function getAccessTokenFacebook($code) : string {
        //accessTokenTwitch
        $url = 'https://graph.facebook.com/oauth/access_token?';
        $url .= "client_id=".CLIENT_FBID;
        $url .= "&client_secret=".CLIENT_FBSECRET;
        $url .= "&code=$code";
        $url .= "&grant_type=authorization_code";
        $url .= "&redirect_uri=https://localhost/fbauth-success&grant_type=authorization_code";
    
        return $url;
    }
}