<?php
session_start();
require_once 'config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'];
    $password = $_POST['password'];

    $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ? AND password = ?");
    $stmt->execute([$username, md5($password)]);
    $user = $stmt->fetch();

    if ($user) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['username'] = $user['username'];
        header('Location: index.php');
        exit;
    } else {
        $error = "Invalid username or password";
    }
}

// If already logged in, redirect to index
if (isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit;
}

// Simple OAuth URLs
$googleLoginUrl = "https://accounts.google.com/o/oauth2/v2/auth?" . http_build_query([
    'client_id' => 'your_google_client_id',
    'redirect_uri' => 'http://localhost/auth/google-callback.php',
    'response_type' => 'code',
    'scope' => 'email profile'
]);

$facebookLoginUrl = "https://www.facebook.com/v12.0/dialog/oauth?" . http_build_query([
    'client_id' => 'your_facebook_app_id',
    'redirect_uri' => 'http://localhost/auth/facebook-callback.php',
    'scope' => 'email'
]);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Avengers Bug Buster</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <div class="login-container">
        <div class="login-box">
            <div class="logo">
                <svg xmlns="http://www.w3.org/2000/svg" width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="bug-icon">
                    <path d="m8 2 1.88 1.88"></path>
                    <path d="M14.12 3.88 16 2"></path>
                    <path d="M9 7.13v-1a3.003 3.003 0 1 1 6 0v1"></path>
                    <path d="M12 20c-3.3 0-6-2.7-6-6v-3a4 4 0 0 1 4-4h4a4 4 0 0 1 4 4v3c0 3.3-2.7 6-6 6"></path>
                    <path d="M12 20v-9"></path>
                    <path d="M6.53 9C4.6 8.8 3 7.1 3 5"></path>
                    <path d="M6.53 15c-1.93-.2-3.53-1.9-3.53-4"></path>
                    <path d="M17.47 9c1.93-.2 3.53-1.9 3.53-4"></path>
                    <path d="M17.47 15c1.93-.2 3.53-1.9 3.53-4"></path>
                </svg>
                <h1>Avengers Bug Buster</h1>
            </div>
            <?php if (isset($error)): ?>
                <div class="error"><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>
            <form method="POST" class="login-form">
                <div class="form-group">
                    <label for="username">Username</label>
                    <input type="text" id="username" name="username" required>
                </div>
                <div class="form-group">
                    <label for="password">Password</label>
                    <input type="password" id="password" name="password" required>
                </div>
                <button type="submit" class="btn btn-primary login-btn">Login</button>
                
                <div class="social-login">
                    <a href="<?php echo htmlspecialchars($googleLoginUrl); ?>" class="btn btn-google">
                        Sign in with Google
                    </a>
                    <a href="<?php echo htmlspecialchars($facebookLoginUrl); ?>" class="btn btn-facebook">
                        Sign in with Facebook
                    </a>
                </div>

                <div class="form-footer">
                    Don't have an account? <a href="register.php" class="link">Create one here</a>
                </div>
            </form>
        </div>
    </div>
</body>
</html>