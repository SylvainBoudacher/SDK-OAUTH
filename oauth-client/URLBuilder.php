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

}