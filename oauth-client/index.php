<?php
// OATH
const CLIENT_ID = "client_60a3778e70ef02.05413444";
const CLIENT_SECRET = "cd989e9a4b572963e23fe39dc14c22bbceda0e60";

// Facebook
const CLIENT_FBID = "857084295218661";
const CLIENT_FBSECRET = "00daf47f2a321185e8eac2b2887da13a";

// Twitch
const CLIENT_TWITCHID = "0eoml14jrvzzwdfztbq29fhtml2xjg";
const CLIENT_TWITCHSECRET = "rtfj833leivnn52xulhd0pifsoe1ez";

// Discord
const CLIENT_DISCORD_ID = "865976478662787072";
const CLIENT_DISCORD_SECRET = "hjYqMBj76NilE8Jnfd_MTF1hgUfDgDAa";

const STATE = "fdzefzefze";

function handleLogin()
{
    // http://.../auth?response_type=code&client_id=...&scope=...&state=...
    echo "<h1>Login with OAUTH</h1>";
    
    // Oauth
    echo "<a href='http://localhost:8081/auth?response_type=code"
        . "&client_id=" . CLIENT_ID
        . "&scope=basic"
        . "&state=" . STATE . "'>Se connecter avec Oauth Server</a></br>";

    // Facebook
    echo "<a href='https://www.facebook.com/v2.10/dialog/oauth?response_type=code"
        . "&client_id=" . CLIENT_FBID
        . "&scope=email"
        . "&state=" . STATE
        . "&redirect_uri=https://localhost/fbauth-success'>Se connecter avec Facebook</a></br>";

    // Twitch
    echo "<a href='".getTwitchLink()."' >Se connecter avec Twitch</a><br>";

    // Discord
    echo "<a href='".getDiscordOAuthLink()."' >Se connecter avec Discord</a>";
}

function getUser($params)
{
    $url = "http://oauth-server:8081/token?client_id=" . CLIENT_ID . "&client_secret=" . CLIENT_SECRET . "&" . http_build_query($params);
    $result = file_get_contents($url);
    $result = json_decode($result, true);
    $token = $result['access_token'];

    $apiUrl = "http://oauth-server:8081/me";
    $context = stream_context_create([
        'http' => [
            'header' => 'Authorization: Bearer ' . $token
        ]
    ]);
    echo file_get_contents($apiUrl, false, $context);
}

function handleError()
{
    ["state" => $state] = $_GET;
    echo "{$state} : Request cancelled";
}

function handleSuccess()
{
    ["state" => $state, "code" => $code] = $_GET;
    if ($state !== STATE) {
        throw new RuntimeException("{$state} : invalid state");
    }
    // https://auth-server/token?grant_type=authorization_code&code=...&client_id=..&client_secret=...
    getUser([
        'grant_type' => "authorization_code",
        "code" => $code,
    ]);
}




// Facebook process

function handleFbSuccess()
{
    ["state" => $state, "code" => $code] = $_GET;
    if ($state !== STATE) {
        throw new RuntimeException("{$state} : invalid state");
    }
    https://auth-server/token?grant_type=authorization_code&code=...&client_id=..&client_secret=...
    $url = "https://graph.facebook.com/oauth/access_token?grant_type=authorization_code&code={$code}&client_id=" . CLIENT_FBID . "&client_secret=" . CLIENT_FBSECRET."&redirect_uri=https://localhost/fbauth-success";
    $result = file_get_contents($url);
    $resultDecoded = json_decode($result, true);
    ["access_token"=> $token] = $resultDecoded;
    $userUrl = "https://graph.facebook.com/me?fields=id,name,email";
    $context = stream_context_create([
        'http' => [
            'header' => 'Authorization: Bearer ' . $token
        ]
    ]);
    echo file_get_contents($userUrl, false, $context);
}




// Twitch process

function handleTwitchSuccess() 
{
    ["state" => $state, "code" => $code] = $_GET;
    if ($state !== STATE) {
        throw new RuntimeException("{$state} : invalid state");
    }    
    
    $curl = curl_init();

    curl_setopt_array($curl, array(
    CURLOPT_URL => accessTokenTwitch($code),
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_ENCODING => '',
    CURLOPT_MAXREDIRS => 10,
    CURLOPT_TIMEOUT => 0,
    CURLOPT_FOLLOWLOCATION => true,
    CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
    CURLOPT_CUSTOMREQUEST => 'POST',
    ));

    $response = curl_exec($curl);

    curl_close($curl);
    echo $response;
    
}

function getTwitchLink() : string {
    // Authorization code grant
    $url = "https://id.twitch.tv/oauth2/authorize?";
    $url .= "client_id=".CLIENT_TWITCHID;
    $url .= "&scope=channel_read";
    $url .= "&response_type=code";
    $url .= "&state=".STATE;
    $url .= "&redirect_uri=https://localhost/twitchauth-success";
    
    return $url;
}

function accessTokenTwitch($code) : string {
    //accessTokenTwitch
    $url = 'https://id.twitch.tv/oauth2/token?';
    $url .= "client_id=".CLIENT_TWITCHID;
    $url .= "&client_secret=".CLIENT_TWITCHSECRET;
    $url .= "&code=$code";
    $url .= "&grant_type=authorization_code";
    $url .= "&redirect_uri=https://localhost/twitchauth-success";

    return $url;
}




// Discord process

function handleDiscordSuccess() {
    ["state" => $state, "code" => $code] = $_GET;
    if ($state !== STATE) {
        throw new RuntimeException("{$state} : invalid discord state");
    }

    $curl = curl_init();

    curl_setopt_array($curl, array(
    CURLOPT_URL => 'https://discord.com/api/oauth2/token',
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_ENCODING => '',
    CURLOPT_MAXREDIRS => 10,
    CURLOPT_TIMEOUT => 0,
    CURLOPT_FOLLOWLOCATION => true,
    CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
    CURLOPT_CUSTOMREQUEST => 'POST',
    CURLOPT_POSTFIELDS => 'client_id='.CLIENT_DISCORD_ID.'&client_secret='.CLIENT_DISCORD_SECRET.'&grant_type=authorization_code&code='.$code.'&redirect_uri=https%3A%2F%2Flocalhost%2Fdiscord-auth-success',
    CURLOPT_HTTPHEADER => array(
        'Content-Type: application/x-www-form-urlencoded',
        'Cookie: __dcfduid=7b4f678bb76e4fcc8813cdb6b85fe223'
    ),
    ));

    $response = curl_exec($curl);

    curl_close($curl);
    echo $response;
}


function getDiscordOAuthLink() : string {
    
    // Authorization code grant
    $url = "https://discord.com/api/oauth2/authorize";
    $url .= "?response_type=code";
    $url .= "&client_id=".CLIENT_DISCORD_ID;
    $url .= "&scope=identify";
    $url .= "&state=".STATE;
    $url .= "&redirect_uri=https://localhost/discord-auth-success";
    $url .= "&prompt=consent";

    return $url;
}





/**
 * AUTH CODE WORKFLOW
 * => Generate link (/login)
 * => Get Code (/auth-success)
 * => Exchange Code <> Token (/auth-success)
 * => Exchange Token <> User info (/auth-success)
 */

$route = strtok($_SERVER["REQUEST_URI"], "?");
switch ($route) {
    case '/login':
        handleLogin();
        break;
    case '/auth-success':
        handleSuccess();
        break;
    case '/fbauth-success':
        handleFbSuccess();
        break;
    case '/twitchauth-success':
        handleTwitchSuccess();
        break;
    case '/discord-auth-success':
        handleDiscordSuccess();
        break;
    case '/auth-cancel':
        handleError();
        break;
    case '/password':
        if ($_SERVER['REQUEST_METHOD'] === "GET") {
            echo '<form method="POST">';
            echo '<input name="username">';
            echo '<input name="password">';
            echo '<input type="submit" value="Submit">';
            echo '</form>';
        } else {
            ["username" => $username, "password" => $password] = $_POST;
            getUser([
                'grant_type' => "password",
                "username" => $username,
                "password" => $password
            ]);
        }
        break;
    default:
        http_response_code(404);
        break;
}