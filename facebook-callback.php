<?php
session_start();
require_once '../config.php';

if (isset($_GET['code'])) {
    $code = $_GET['code'];
    
    // Exchange code for token
    $response = file_get_contents('https://graph.facebook.com/v12.0/oauth/access_token?' . http_build_query([
        'client_id' => 'your_facebook_app_id',
        'client_secret' => 'your_facebook_app_secret',
        'redirect_uri' => 'http://localhost/auth/facebook-callback.php',
        'code' => $code
    ]));
    
    $token = json_decode($response)->access_token;
    
    // Get user info sa user
    $userInfo = file_get_contents('https://graph.facebook.com/me?fields=id,email&access_token=' . $token);
    $userData = json_decode($userInfo);
    
    // Check if user exists
    $stmt = $pdo->prepare("SELECT * FROM users WHERE social_id = ? AND social_provider = 'facebook'");
    $stmt->execute([$userData->id]);
    $user = $stmt->fetch();
    
    if (!$user) {
        // Create new user
        $stmt = $pdo->prepare("INSERT INTO users (username, password, social_id, social_provider) VALUES (?, '', ?, 'facebook')");
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