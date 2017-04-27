<?php
require __DIR__ . '/vendor/autoload.php';
// reading hidden clientId and clientSecret
require './appconf.php';
// we used session to store the state
session_start();
// create our IDCS OAuth2 provider
$provider = new \League\OAuth2\Client\Provider\GenericProvider([
    'clientId'                => APP_CLIENT_ID,    
    'clientSecret'            => APP_CLIENT_SECRET ,  
    'scopes'                  => 'urn:opc:idm:__myscopes__',
    'redirectUri'             => 'https://orcl.asia/idcs/',
    'urlAuthorize'            => 'https://idcsapac.idcshub.com/oauth2/v1/authorize',
    'urlAccessToken'          => 'https://idcsapac.idcshub.com/oauth2/v1/token',
    'urlResourceOwnerDetails' => 'https://idcsapac.idcshub.com/admin/v1/Me'
]);
 
// If we don't have an authorization code then get one
if (!isset($_GET['code'])) {
 
    // Fetch the authorization URL from the provider; this returns the
    $authorizationUrl = $provider->getAuthorizationUrl();
 
    // Get the state generated and store in session.
    $_SESSION['oauth2state'] = $provider->getState();
 
    // create the login button / URL
    echo "<html><body><h1>IDCS BYOA PHP Page</h1>";
    echo "<a href=\"$authorizationUrl\">Login with IDCS</a><br /><br />\n";
    echo "<div style='height:200px; width:600px; overflow:auto; border:3px solid green; padding: 5px'>";
    echo "<pre>\n";
    echo show_source("index.php");
    echo "</pre>\n";
    echo "</div>\n";
    echo "</body></html>";
    exit;
 
// user retrun from login, i.e. callbackUrl
// Check given state against previously stored one to mitigate CSRF attack
} elseif (empty($_GET['state']) || ($_GET['state'] !== $_SESSION['oauth2state'])) {
    unset($_SESSION['oauth2state']);
    exit('Invalid state');
} else {
    try {
        // Try to get an access token using the authorization code grant.
        // which is in the URL query string      
        $accessToken = $provider->getAccessToken('authorization_code', [
            'code' => $_GET['code']
        ]);
 
        // We have an access token, which we may use in authenticated
        // requests to get user info
 
        // Using the access token, we may look up details about the
        // resource owner.
        $resourceOwner = $provider->getResourceOwner($accessToken);
    $meami = $resourceOwner->toArray();
    echo "<h1>Hello " . $meami['displayName'] . "</h1><hr />\n";
    echo "<h2>User Info retrieve from IDCS</h2>";
        echo "<pre>\n";
    var_export($resourceOwner->toArray());
    echo "</pre>\n";
        echo "<div style='height:200px; width:600px; overflow:auto; border:3px solid green; padding: 5px'>";
        echo "<pre>\n";
        echo show_source("index.php");
        echo "</pre>\n";
        echo "</div>\n";
    } catch (\League\OAuth2\Client\Provider\Exception\IdentityProviderException $e) {
        // Failed to get the access token or user details.
        exit($e->getMessage());
    }
}
