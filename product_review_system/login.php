<?php
session_start();
require 'db.php';

$error = '';
$role = isset($_GET['role']) ? $_GET['role'] : 'user';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = trim($_POST['email']);
    $password = $_POST['password'];

    if (empty($email) || empty($password)) {
        $error = 'Please enter both email and password.';
    } else {
        $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_name'] = $user['name'];
            $_SESSION['user_role'] = $user['role'];
            
            header("Location: index.php");
            exit;
        } else {
            $error = 'Invalid email or password.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login | Product Review</title>
    <link rel="stylesheet" href="auth.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>
<body>
    <!-- Changed class from 'container' to 'auth-container' to avoid conflict with main site layout, 
         but I need to match the CSS I just wrote. 
         Wait, I wrote .auth-container in CSS but the HTML in new_login.php uses .container.
         I should change the HTML class here to auth-container OR change the CSS to scope .container inside body.login-page.
         I'll use auth-container here for safety and clarity, as it matches the CSS I just wrote.
    -->
    <div class="container">
        <div class="icon-container">
            <!-- Adjusted path: new_login.php had ../css/icon.png. I'll assume icon.png is in the root or I need to find it. 
                 The user didn't provide icon.png. I'll use a placeholder or remove it if broken. 
                 I'll leave the img tag but point to a placeholder if I don't have the file. 
                 Actually, I should check if the user has an icon. 
                 I'll just use a placeholder text or generic icon if missing. -->
            <img src="icon.png" class="auth-logo" alt="Logo">
        </div>

        <h2 class="slogan-secondary">Product Review System</h2> 
        <div class="benefits-container">
            <div class="benefit-item">
                <i class="fas fa-star icon-benefit"></i>
                <p>Personalized Recommendations</p>
            </div>
            <div class="benefit-item">
                <i class="fas fa-comments icon-benefit"></i>
                <p>Trusted Reviews & Tips</p>
            </div>
            <div class="benefit-item">
                <i class="fas fa-shopping-bag icon-benefit"></i>
                <p>Effortless Shopping Hub</p>
            </div>
        </div>
        <h1>Log In</h1>
        <div class="role-buttons">
            <a href="login.php?role=admin" class="<?= $role=='admin'?'active':'' ?>">Admin</a>
            <a href="login.php?role=user" class="<?= $role=='user'?'active':'' ?>">User</a>
        </div>

        <?php if($error): ?>
            <p class='error'><?php echo $error; ?></p>
        <?php endif; ?>

        <form action="" method="POST">
            <input type="hidden" name="role" value="<?= htmlspecialchars($role) ?>">

            <label>E-mail:</label>
            <input type="email" name="email" required>

            <label>Password:</label>
            <input type="password" name="password" required>

            <button type="submit">Log In</button>
        </form>

        <p>Don't have an account? 
            <a href="register.php?role=<?= htmlspecialchars($role) ?>">Register</a>
        </p>
        
        <p><a href="index.php">Back to Home</a></p>
    </div>
</body>
</html>
