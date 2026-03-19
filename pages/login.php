<?php
require_once '../includes/config.php';
require_once '../includes/functions.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $userFunctions = new UserFunctions();
    if ($userFunctions->login($_POST['username'], $_POST['password'])) {
        header('Location: dashboard.php');
        exit();
    } else {
        $error = 'Invalid username or password';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - <?php echo SITE_NAME; ?></title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <div class="container">
        <div class="card fade-in" style="max-width: 400px; margin: 100px auto;">
            <div class="card-header">
                <h2>Login</h2>
            </div>
            
            <?php if ($error): ?>
                <div style="background: rgba(255, 71, 87, 0.2); color: var(--danger); padding: 1rem; border-radius: 8px; margin-bottom: 1rem;">
                    <?php echo $error; ?>
                </div>
            <?php endif; ?>
            
            <form method="POST" action="">
                <div class="form-group">
                    <label for="username">Username or Email</label>
                    <input type="text" class="form-control" id="username" name="username" required>
                </div>
                
                <div class="form-group">
                    <label for="password">Password</label>
                    <input type="password" class="form-control" id="password" name="password" required>
                </div>
                
                <button type="submit" class="btn btn-primary" style="width: 100%;">Login</button>
            </form>
            
            <div style="text-align: center; margin-top: 1rem;">
                <a href="register.php" style="color: var(--accent-blue);">Don't have an account? Register</a>
            </div>
        </div>
    </div>
</body>
</html>
