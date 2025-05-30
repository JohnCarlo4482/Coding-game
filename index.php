<?php
session_start();
require_once 'config.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

// Load user progress if not already loaded
if (!isset($_SESSION['current_level']) || !isset($_SESSION['score'])) {
    $stmt = $pdo->prepare("SELECT current_level, score FROM user_progress WHERE user_id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $progress = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($progress) {
        $_SESSION['current_level'] = $progress['current_level'];
        $_SESSION['score'] = $progress['score'];
    } else {
        // Initialize new user progress
        $_SESSION['current_level'] = 0;
        $_SESSION['score'] = 0;
        $stmt = $pdo->prepare("INSERT INTO user_progress (user_id, current_level, score) VALUES (?, 0, 0)");
        $stmt->execute([$_SESSION['user_id']]);
    }
}

// Get current challenge
$stmt = $pdo->prepare("SELECT * FROM challenges WHERE id = ?");
$stmt->execute([$_SESSION['current_level'] + 1]);
$challenge = $stmt->fetch(PDO::FETCH_ASSOC);

// Get total number of challenges
$stmt = $pdo->query("SELECT COUNT(*) FROM challenges");
$totalChallenges = $stmt->fetchColumn();

// Handle form submission
$message = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['check_solution'])) {
        $userCode = trim($_POST['code_input']);
        $correctCode = trim($challenge['correct_code']);
        
        // Normalize code for comparison
        $userCode = preg_replace('/\s+/', ' ', $userCode);
        $correctCode = preg_replace('/\s+/', ' ', $correctCode);
        
        if ($userCode === $correctCode) {
            // Calculate points based on difficulty
            $points = $challenge['difficulty'] === 'easy' ? 10 : 
                     ($challenge['difficulty'] === 'medium' ? 20 : 30);
            
            $_SESSION['score'] += $points;
            
            // Save progress
            $stmt = $pdo->prepare("UPDATE user_progress SET score = ?, current_level = ? WHERE user_id = ?");
            $stmt->execute([$_SESSION['score'], $_SESSION['current_level'] + 1, $_SESSION['user_id']]);
            
            // Check if game is complete
            if ($_SESSION['current_level'] >= $totalChallenges - 1) {
                $_SESSION['game_complete'] = true;
            } else {
                $_SESSION['current_level']++;
                header('Location: index.php');
                exit;
            }
        } else {
            $message = '<div class="error">Try again! Your solution is not quite right.</div>';
        }
    } elseif (isset($_POST['restart'])) {
        $_SESSION['current_level'] = 0;
        $_SESSION['score'] = 0;
        $_SESSION['game_complete'] = false;
        
        // Save reset progress
        $stmt = $pdo->prepare("UPDATE user_progress SET score = 0, current_level = 0 WHERE user_id = ?");
        $stmt->execute([$_SESSION['user_id']]);
        
        header('Location: index.php');
        exit;
    } elseif (isset($_POST['previous']) && $_SESSION['current_level'] > 0) {
        $_SESSION['current_level']--;
        
        // Save progress
        $stmt = $pdo->prepare("UPDATE user_progress SET current_level = ? WHERE user_id = ?");
        $stmt->execute([$_SESSION['current_level'], $_SESSION['user_id']]);
        
        header('Location: index.php');
        exit;
    } elseif (isset($_POST['next']) && $_SESSION['current_level'] < $totalChallenges - 1) {
        $_SESSION['current_level']++;
        
        // Save progress
        $stmt = $pdo->prepare("UPDATE user_progress SET current_level = ? WHERE user_id = ?");
        $stmt->execute([$_SESSION['current_level'], $_SESSION['user_id']]);
        
        header('Location: index.php');
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Avengers Bug Buster - Debug and Learn</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <header class="header">
        <div class="header-content">
            <div class="logo">
                <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="bug-icon">
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
            <div class="user-info">
                <div class="welcome">Welcome, <?php echo htmlspecialchars($_SESSION['username']); ?></div>
                <div class="score">Score: <span id="score"><?php echo $_SESSION['score']; ?></span></div>
                <a href="logout.php" class="btn btn-secondary logout-btn">Logout</a>
            </div>
        </div>
    </header>

    <main class="main">
        <?php if (isset($_SESSION['game_complete']) && $_SESSION['game_complete']): ?>
            <div id="completion-screen" class="completion-screen">
                <h2>Mission Accomplished! 🎉</h2>
                <p>You've saved the code universe with a score of <span id="final-score"><?php echo $_SESSION['score']; ?></span>!</p>
                <form method="POST">
                    <button type="submit" name="restart" class="btn btn-primary">New Mission</button>
                </form>
            </div>
        <?php else: ?>
            <div id="game-container" class="game-container">
                <div class="challenge-header">
                    <h2>Level <span id="level"><?php echo $_SESSION['current_level'] + 1; ?></span></h2>
                    <span id="difficulty" class="difficulty-badge" data-difficulty="<?php echo htmlspecialchars($challenge['difficulty']); ?>">
                        <?php echo htmlspecialchars($challenge['difficulty']); ?>
                    </span>
                </div>
                
                <p id="description" class="description"><?php echo htmlspecialchars($challenge['description']); ?></p>
                
                <form method="POST">
                    <div class="code-editor">
                        <textarea id="code-input" name="code_input" spellcheck="false"><?php echo htmlspecialchars($challenge['buggy_code']); ?></textarea>
                    </div>
                    
                    <div class="button-container">
                        <div class="navigation-buttons">
                            <button type="submit" name="previous" class="btn btn-secondary" <?php echo $_SESSION['current_level'] <= 0 ? 'disabled' : ''; ?>>Previous</button>
                            <button type="submit" name="next" class="btn btn-secondary" <?php echo $_SESSION['current_level'] >= $totalChallenges - 1 ? 'disabled' : ''; ?>>Next</button>
                        </div>
                        <div>
                            <button type="button" id="hint-btn" class="btn btn-purple">Need a hint?</button>
                            <button type="button" id="show-answer-btn" class="btn btn-secondary">Show Answer</button>
                            <button type="submit" name="check_solution" class="btn btn-primary">Check Solution</button>
                        </div>
                    </div>
                </form>
                
                <?php if ($message): ?>
                    <?php echo $message; ?>
                <?php endif; ?>
                
                <div id="hint-container" class="hint-container hidden">
                    <?php echo htmlspecialchars($challenge['hint']); ?>
                </div>

                <div id="answer-container" class="answer-container hidden">
                    <h3>Solution:</h3>
                    <pre><code><?php echo htmlspecialchars($challenge['correct_code']); ?></code></pre>
                </div>
            </div>
        <?php endif; ?>
    </main>

    <script>
        document.getElementById('hint-btn').addEventListener('click', function() {
            document.getElementById('hint-container').classList.toggle('hidden');
        });

        document.getElementById('show-answer-btn').addEventListener('click', function() {
            document.getElementById('answer-container').classList.toggle('hidden');
        });

        // Add tab support in code editor
        document.getElementById('code-input').addEventListener('keydown', function(e) {
            if (e.key === 'Tab') {
                e.preventDefault();
                const start = this.selectionStart;
                const end = this.selectionEnd;
                const spaces = '  ';
                
                this.value = 
                    this.value.substring(0, start) +
                    spaces +
                    this.value.substring(end);
                
                this.selectionStart = this.selectionEnd = start + spaces.length;
            }
        });
    </script>
</body>
</html>