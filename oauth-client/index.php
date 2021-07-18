<?php

require("Helpers.php");

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

// Access Token used inside this SDK
const ACCESS_TOKEN_APIS = [
    "discord" => "https://discord.com/api/oauth2/token",
    "twitch" => "https://id.twitch.tv/oauth2/token",
    "facebook" => "https://graph.facebook.com/oauth/access_token"
];

// Successful redirect from APIs links
const SUCCESS_REDIRECT = [
    "discord" => "https://localhost/discord-auth-success",
    "twitch" => "https://localhost/twitchauth-success",
    "facebook" => "https://localhost/fbauth-success"
];

function handleLogin() {

    $homeMade = Helpers::urlBuilder(O_AUTH_APIS["homeMade"], [
        "client_id" => CLIENT_ID,
        "scope" => "basic",
        "state" => STATE
    ]);

    $FbLink = Helpers::urlBuilder(O_AUTH_APIS["facebook"], [
        "client_id" => CLIENT_FBID,
        "scope" => "email",
        "redirect_uri" => SUCCESS_REDIRECT["facebook"],
        "state" => STATE
    ]);

    $twitchLink = Helpers::urlBuilder(O_AUTH_APIS["twitch"], [
        "client_id" => CLIENT_TWITCHID,
        "scope" => "channel_read",
        "redirect_uri" => SUCCESS_REDIRECT["twitch"],
        "state" => STATE
    ]);

    $discordLink = Helpers::urlBuilder(O_AUTH_APIS["discord"], [
        "client_id" => CLIENT_DISCORD_ID,
        "scope" => "identify",
        "redirect_uri" => SUCCESS_REDIRECT["discord"],
        "state" => STATE
    ]);

    $html = "<h1>Login with OAUTH</h1>
    <a href='.$homeMade.'>Se connecter avec Oauth fait maison</a><br>
    <a href='".$FbLink."'>Se connecter avec Facebook</a></br>
    <a href='".$twitchLink."' >Se connecter avec Twitch</a><br>
    <a href='".$discordLink."' >Se connecter avec Discord</a><br>";

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






function handleFbSuccess() {
    ["state" => $state, "code" => $code] = $_GET;
    if ($state !== STATE) {
        throw new RuntimeException("{$state} : invalid state");
    }

    $baseUrl = ACCESS_TOKEN_APIS["facebook"];
    $queryParams = Helpers::queryParamsBuilder([
        "client_id" => CLIENT_FBID,
        "client_secret" => CLIENT_FBSECRET,
        "code" => $code,
        "grant_type" => "authorization_code",
        "redirect_uri" => SUCCESS_REDIRECT["facebook"]
    ]);

    echo Helpers::curl($baseUrl, $queryParams);
}






function handleTwitchSuccess() {
    ["state" => $state, "code" => $code] = $_GET;
    if ($state !== STATE) {
        throw new RuntimeException("{$state} : invalid state");
    }
    
    $baseUrl = ACCESS_TOKEN_APIS["twitch"];
    $queryParams = Helpers::queryParamsBuilder([
        "client_id" => CLIENT_TWITCHID,
        "client_secret" => CLIENT_TWITCHSECRET,
        "code" => $code,
        "grant_type" => "authorization_code",
        "redirect_uri" => SUCCESS_REDIRECT["twitch"]
    ]);

    echo Helpers::curl($baseUrl, $queryParams);
}





function handleDiscordSuccess() {
    ["state" => $state, "code" => $code] = $_GET;
    if ($state !== STATE) {
        throw new RuntimeException("{$state} : invalid discord state");
    }

    $baseUrl = ACCESS_TOKEN_APIS["discord"];
    $queryParams = Helpers::queryParamsBuilder([
        "client_id" => CLIENT_DISCORD_ID,
        "client_secret" => CLIENT_DISCORD_SECRET,
        "code" => $code,
        "grant_type" => "authorization_code",
        "redirect_uri" => SUCCESS_REDIRECT["discord"]
    ]);

    // Note : this one requires application/x-www-form-urlencoded as content-type according to the Discord documentation
    $headers = [
        'Content-Type: application/x-www-form-urlencoded',
        'Cookie: __dcfduid=7b4f678bb76e4fcc8813cdb6b85fe223'
    ];

    echo Helpers::curl($baseUrl, $queryParams, $headers);
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