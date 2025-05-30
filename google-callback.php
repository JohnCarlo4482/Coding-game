<?php
session_start();
require_once '../config.php';

if (isset($_GET['code'])) {
    $code = $_GET['code'];
    
    // Exchange code for token
    $response = file_get_contents('https://oauth2.googleapis.com/token', false, stream_context_create([
        'http' => [
            'method' => 'POST',
            'header' => 'Content-Type: application/x-www-form-urlencoded',
            'content' => http_build_query([
                'code' => $code,
                'client_id' => 'your_google_client_id',
                'client_secret' => 'your_google_client_secret',
                'redirect_uri' => 'http://localhost/auth/google-callback.php',
                'grant_type' => 'authorization_code'
            ])
        ]
    ]));
    
    $token = json_decode($response)->access_token;
    
    // Get user info
    $userInfo = file_get_contents('https://www.googleapis.com/oauth2/v2/userinfo', false, stream_context_create([
        'http' => [
            'header' => "Authorization: Bearer $token"
        ]
    ]));
    
    $userData = json_decode($userInfo);
    
    // Check if user exists
    $stmt = $pdo->prepare("SELECT * FROM users WHERE social_id = ? AND social_provider = 'google'");
    $stmt->execute([$userData->id]);
    $user = $stmt->fetch();
    
    if (!$user) {
        // Create new user
        $stmt = $pdo->prepare("INSERT INTO users (username, password, social_id, social_provider) VALUES (?, '', ?, 'google')");
        $stmt->execute([$userData->email, $userData->id]);
        $userId = $pdo->lastInsertId();
        
        // Initialize progress
        $stmt = $pdo->prepare("INSERT INTO user_progress (user_id, current_level, score) VALUES (?, 0, 0)");
        $stmt->execute([$userId]);
        
        $_SESSION['user_id'] = $userId;
        $_SESSION['username'] = $userData->email;
    } else {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['username'] = $user['username'];
    }
    
    header('Location: ../index.php');
    exit;
}