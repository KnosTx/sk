<?php

require __DIR__.'/vendor/autoload.php';

use Dotenv\Dotenv;
use Google\Client as GoogleClient;

class GoogleAuth {
    protected $client;

    public function __construct() {
        // Load environment variables from .env file
        $dotenv = Dotenv::createImmutable(__DIR__);
        $dotenv->load();

        // Initialize Google client
        $this->client = new GoogleClient([
            'client_id' => $_ENV['GOOGLE_CLIENT_ID'],
            'client_secret' => $_ENV['GOOGLE_CLIENT_SECRET'],
            'redirect_uri' => $_ENV['GOOGLE_REDIRECT_URI']
        ]);
    }

    public function getAuthUrl() {
        // Generate authentication URL
        $authUrl = $this->client->createAuthUrl();
        return $authUrl;
    }

    public function authenticate($code) {
        // Exchange authorization code for access token
        $accessToken = $this->client->fetchAccessTokenWithAuthCode($code);

        // Set access token to client
        $this->client->setAccessToken($accessToken);

        // Get user info
        $oauth2 = new \Google_Service_Oauth2($this->client);
        $userInfo = $oauth2->userinfo->get();

        return $userInfo;
    }
}

// Example usage:
$googleAuth = new GoogleAuth();

// Get authentication URL
$authUrl = $googleAuth->getAuthUrl();
echo "Authentication URL: $authUrl\n";

// Simulate receiving authentication code (e.g., from redirect URI)
$code = $_GET['code'];

// Authenticate with Google
$userInfo = $googleAuth->authenticate($code);
echo "User Data: " . print_r($userInfo, true) . "\n";

?>
