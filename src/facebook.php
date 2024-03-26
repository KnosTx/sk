<?php

require __DIR__.'/vendor/autoload.php';

use Dotenv\Dotenv;
use Facebook\Facebook;

class FacebookAuth {
    protected $fb;

    public function __construct() {
        // Load environment variables from .env file
        $dotenv = Dotenv::createImmutable(__DIR__);
        $dotenv->load();

        // Initialize Facebook SDK
        $this->fb = new Facebook([
            'app_id' => $_ENV['FACEBOOK_APP_ID'],
            'app_secret' => $_ENV['FACEBOOK_APP_SECRET'],
            'default_graph_version' => 'v12.0',
        ]);
    }

    public function getLoginUrl() {
        // Generate login URL
        $helper = $this->fb->getRedirectLoginHelper();
        $loginUrl = $helper->getLoginUrl($_ENV['FACEBOOK_REDIRECT_URI'], ['email']);

        return $loginUrl;
    }

    public function authenticate($code) {
        // Authenticate with Facebook
        $helper = $this->fb->getRedirectLoginHelper();
        try {
            $accessToken = $helper->getAccessToken();
        } catch(\Facebook\Exceptions\FacebookResponseException $e) {
            // When Facebook returns an error
            echo 'Graph returned an error: ' . $e->getMessage();
            exit;
        } catch(\Facebook\Exceptions\FacebookSDKException $e) {
            // When validation fails or other local issues
            echo 'Facebook SDK returned an error: ' . $e->getMessage();
            exit;
        }

        if (!isset($accessToken)) {
            if ($helper->getError()) {
                // User denied the login request
                echo "<script>alert('User denied the login request');</script>";
            } else {
                // The state does not match. You can ignore this error since it will be caught in the callback
                echo "<script>alert('The state does not match');</script>";
            }
            return;
        }

        // Logged in
        echo "<script>alert('Logged in!');</script>";

        // Use the access token to get user details
        $oAuth2Client = $this->fb->getOAuth2Client();
        $tokenMetadata = $oAuth2Client->debugToken($accessToken);
        $userId = $tokenMetadata->getUserId();

        try {
            // Get user details
            $response = $this->fb->get("/$userId?fields=id,name,email", $accessToken);
            $userNode = $response->getGraphNode();
            $userData = $userNode->asArray();
            return $userData;
        } catch(\Facebook\Exceptions\FacebookResponseException $e) {
            // When Facebook returns an error
            echo 'Graph returned an error: ' . $e->getMessage();
            exit;
        } catch(\Facebook\Exceptions\FacebookSDKException $e) {
            // When validation fails or other local issues
            echo 'Facebook SDK returned an error: ' . $e->getMessage();
            exit;
        }
    }
}

// Example usage:
$facebookAuth = new FacebookAuth();

// Get login URL
$loginUrl = $facebookAuth->getLoginUrl();
echo "Login URL: $loginUrl\n";

// Simulate receiving authentication code (e.g., from redirect URI)
$code = $_GET['code'];

// Authenticate with Facebook
$userData = $facebookAuth->authenticate($code);
echo "User Data: " . print_r($userData, true) . "\n";

?>
