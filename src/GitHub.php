<?php

require __DIR__.'/vendor/autoload.php';

use Dotenv\Dotenv;
use GuzzleHttp\Client as GuzzleClient;
use GuzzleHttp\Exception\ClientException;

class GitHubAuth {
    protected $client;

    public function __construct() {
        // Load environment variables from .env file
        $dotenv = Dotenv::createImmutable(__DIR__);
        $dotenv->load();

        // Initialize Guzzle HTTP client
        $this->client = new GuzzleClient([
            'base_uri' => 'https://github.com/login/oauth/',
        ]);
    }

    public function getLoginUrl() {
        // Generate login URL
        $params = [
            'client_id' => $_ENV['GITHUB_CLIENT_ID'],
            'redirect_uri' => $_ENV['GITHUB_REDIRECT_URI'],
            'scope' => 'user',
        ];
        $loginUrl = 'authorize?' . http_build_query($params);
        return $loginUrl;
    }

    public function authenticate($code) {
        // Authenticate with GitHub
        try {
            $response = $this->client->post('access_token', [
                'form_params' => [
                    'client_id' => $_ENV['GITHUB_CLIENT_ID'],
                    'client_secret' => $_ENV['GITHUB_CLIENT_SECRET'],
                    'code' => $code,
                    'redirect_uri' => $_ENV['GITHUB_REDIRECT_URI'],
                ],
            ]);
            $data = json_decode($response->getBody(), true);
            $accessToken = $data['access_token'];

            // Use access token to get user details
            $userResponse = $this->client->get('user', [
                'headers' => [
                    'Authorization' => 'token ' . $accessToken,
                    'Accept' => 'application/json',
                ],
            ]);
            $userData = json_decode($userResponse->getBody(), true);
            return $userData;
        } catch (ClientException $e) {
            // Handle errors
            echo 'Error: ' . $e->getMessage();
            exit;
        }
    }
}

?>
