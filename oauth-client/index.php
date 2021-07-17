<?php

require("URLBuilder.php");

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

// State : To protecte against CSRF attacks
const STATE = "fdzefzefze";

// OAuth Links used inside this SDK
const O_AUTH_APIS = [
    "homeMade" => "http://localhost:8081/auth?response_type=code",
    "discord" => "https://discord.com/api/oauth2/authorize?response_type=code",
    "twitch" => "https://id.twitch.tv/oauth2/authorize?response_type=code",
    "facebook" => "https://www.facebook.com/v2.10/dialog/oauth?response_type=code"
];

function handleLogin() {

    $homeMade = URLBuilder::getOAuthToken(O_AUTH_APIS["homeMade"], CLIENT_ID, "basic", null, STATE);
    $discordLink = URLBuilder::getOAuthToken(O_AUTH_APIS["discord"], CLIENT_DISCORD_ID, "identify", "https://localhost/discord-auth-success", STATE);
    $twitchLink = URLBuilder::getOAuthToken(O_AUTH_APIS["twitch"], CLIENT_TWITCHID, "channel_read", "https://localhost/twitchauth-success", STATE);
    $FbLink = URLBuilder::getOAuthToken(O_AUTH_APIS["facebook"], CLIENT_FBID, "email", "https://localhost/fbauth-success", STATE);

    $html = "<h1>Login with OAUTH</h1>
    <a href='.$homeMade.'>Se connecter avec Oauth fait maison</a><br>
    <a href='".$FbLink."'>Se connecter avec Facebook</a></br>
    <a href='".$twitchLink."' >Se connecter avec Twitch</a><br>
    <a href='".$discordLink."' >Se connecter avec Discord</a>";

    echo $html;
}




// Home made OAuth processes

function getUser($params) {
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

function handleError() {
    ["state" => $state] = $_GET;
    echo "{$state} : Request cancelled";
}

function handleSuccess() {
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

function handleFbSuccess() {
    ["state" => $state, "code" => $code] = $_GET;
    if ($state !== STATE) {
        throw new RuntimeException("{$state} : invalid state");
    }   

    $curl = curl_init();

    curl_setopt_array($curl, array(
    CURLOPT_URL => URLBuilder::getAccessTokenFacebook($code),
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



// Twitch process

function handleTwitchSuccess() {
    ["state" => $state, "code" => $code] = $_GET;
    if ($state !== STATE) {
        throw new RuntimeException("{$state} : invalid state");
    }    
    
    $curl = curl_init();

    curl_setopt_array($curl, array(
    CURLOPT_URL => URLBuilder::getAccessTokenTwitch($code),
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




// Custom router

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