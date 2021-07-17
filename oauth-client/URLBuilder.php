<?php

class URLBuilder {

    // Discord related urls

    public static function getDiscordOAuthLink() : string {
        $url = "https://discord.com/api/oauth2/authorize";
        $url .= "?response_type=code";
        $url .= "&client_id=".CLIENT_DISCORD_ID;
        $url .= "&scope=identify";
        $url .= "&state=".STATE;
        $url .= "&redirect_uri=https://localhost/discord-auth-success";
        $url .= "&prompt=consent";
    
        return $url;
    }



    // Twitch 

    public static function getTwitchLink() : string {
        // Authorization code grant
        $url = "https://id.twitch.tv/oauth2/authorize?";
        $url .= "client_id=".CLIENT_TWITCHID;
        $url .= "&scope=channel_read";
        $url .= "&response_type=code";
        $url .= "&state=".STATE;
        $url .= "&redirect_uri=https://localhost/twitchauth-success";
        
        return $url;
    }

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

    

    // Facebook

    public static function getFacebookLink() : string {
        // Authorization code grant
        $url = "https://www.facebook.com/v2.10/dialog/oauth?";
        $url .= "client_id=".CLIENT_FBID;
        $url .= "&scope=email";
        $url .= "&response_type=code";
        $url .= "&state=".STATE;
        $url .= "&redirect_uri=https://localhost/fbauth-success";
        
        return $url;
    }
    
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