<?php
// Replace these with your actual credentials
$client->setClientId('901386860956-676625qo48eo4heih5vkr40qusr5q3uf.apps.googleusercontent.com');
$redirectUri = 'http://localhost/III%20-%20BINS/elective/callback';
$state = bin2hex(random_bytes(16)); // Generate a random state parameter

// OAuth 2.0 authorization URL
$authUrl = 'https://accounts.google.com/o/oauth2/v2/auth' . '?' . http_build_query([
    'response_type' => 'code',
    'client_id' => $clientId,
    'redirect_uri' => $redirectUri,
    'scope' => 'email profile',
    'state' => $state,
    'access_type' => 'offline',
    'prompt' => 'consent',
]);

// Redirect the user to Google's OAuth 2.0 authorization server
header('Location: ' . $authUrl);
exit();
