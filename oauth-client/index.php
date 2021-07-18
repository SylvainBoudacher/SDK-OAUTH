<?php

require_once __DIR__ . '/Helpers.php';
require_once __DIR__ . '/Plateform.php';

// State : To protecte against CSRF attacks
$state = Helpers::generateRandomString(64);

$twitch = new Plateform(
    "twitch",
    "https://id.twitch.tv/oauth2/authorize?response_type=code",
    "https://id.twitch.tv/oauth2/token",
    "0eoml14jrvzzwdfztbq29fhtml2xjg",
    "rtfj833leivnn52xulhd0pifsoe1ez",
    "https://localhost/twitchauth-success",
    $state
);

$discord = new Plateform(
    "discord",
    "https://discord.com/api/oauth2/authorize?response_type=code",
    "https://discord.com/api/oauth2/token",
    "865976478662787072",
    "hjYqMBj76NilE8Jnfd_MTF1hgUfDgDAa",
    "https://localhost/discord-auth-success",
    $state
);

$facebook = new Plateform(
    "facebook",
    "https://www.facebook.com/v2.10/dialog/oauth?response_type=code",
    "https://graph.facebook.com/oauth/access_token",
    "496351738294763",
    "9213a3eaaf13e6f78f792bef0a5a0d37",
    "https://localhost/fbauth-success",
    $state
);

$oAuthServer = new Plateform(
    "oAuthServer",
    "http://localhost:8081/auth?response_type=code",
    null,
    "client_60a3778e70ef02.05413444",
    "cd989e9a4b572963e23fe39dc14c22bbceda0e60",
    null,
    $state
);

function handleLogin() {
    global $twitch, $discord, $facebook, $oAuthServer;

    $twitchLink = $twitch->getOAuthUrl("channel_read");
    $discordLink = $discord->getOAuthUrl("identify");
    $facebookLink = $facebook->getOAuthUrl("email");
    $oAuthServerLink = $oAuthServer->getOAuthUrl("basic");

    $html = "<h1>Login with OAUTH</h1>
    <a href='.$oAuthServerLink.'>Se connecter avec Oauth fait maison</a><br>
    <a href='".$facebookLink."'>Se connecter avec Facebook</a></br>
    <a href='".$twitchLink."' >Se connecter avec Twitch</a><br>
    <a href='".$discordLink."' >Se connecter avec Discord</a><br>";

    echo $html;
}



// oAuthServer processes
function getUser($params) {
    global $oAuthServer;

    $url = "http://oauth-server:8081/token?client_id=" . $oAuthServer->getClientId() . "&client_secret=" . $oAuthServer->getClientSecret() . "&" . http_build_query($params);
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
    global $state;
    if ($state !== $state) {
        throw new RuntimeException("{$state} : invalid state");
    }
    // https://auth-server/token?grant_type=authorization_code&code=...&client_id=..&client_secret=...
    getUser([
        'grant_type' => "authorization_code",
        "code" => $code,
    ]);
}



// Facebook processes
function handleFbSuccess() {
    ["state" => $state, "code" => $code] = $_GET;
    global $state, $facebook;
    if ($state !== $state) {
        throw new RuntimeException("{$state} : invalid state");
    }
    $baseUrl = $facebook->getUrlToken();
    $queryParams = $facebook->getTokenParams($code);
    echo Helpers::curl($baseUrl, $queryParams);
}

// Twitch processes
function handleTwitchSuccess() {
    ["state" => $state, "code" => $code] = $_GET;
    global $state, $twitch;
    if ($state !== $state) {
        throw new RuntimeException("{$state} : invalid state");
    }
    $baseUrl = $twitch->getUrlToken();
    $queryParams = $twitch->getTokenParams($code);
    echo Helpers::curl($baseUrl, $queryParams);
}

// DiscordProcesses
function handleDiscordSuccess() {
    ["state" => $state, "code" => $code] = $_GET;
    global $discord, $state;
    if ($state !== $state) {
        throw new RuntimeException("{$state} : invalid discord state");
    }
    
    $baseUrl = $discord->getUrlToken();
    $queryParams = $discord->getTokenParams($code);
    $headers = [
        'Content-Type: application/x-www-form-urlencoded',
        'Cookie: __dcfduid=7b4f678bb76e4fcc8813cdb6b85fe223'
    ];

    // Note : this one requires application/x-www-form-urlencoded as content-type according to the Discord documentation
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