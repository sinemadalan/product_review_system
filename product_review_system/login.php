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
    <title>Login | Vetta</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="auth.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body>
    <div class="container">
        <div class="icon-container">
            <img src="icon.png" class="auth-logo" alt="Logo">
        </div>

        <h2 class="slogan-secondary">Vetta</h2> 
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
            <script>
                document.addEventListener('DOMContentLoaded', function() {
                    Swal.fire({
                        icon: 'error',
                        title: 'Login Failed',
                        text: '<?php echo $error; ?>',
                        confirmButtonColor: '#3085d6',
                        confirmButtonText: 'Try Again'
                    });
                });
            </script>
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