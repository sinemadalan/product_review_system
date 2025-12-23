<?php
session_start();
require 'db.php';

$error = '';
$success = '';
$role = isset($_GET['role']) ? $_GET['role'] : 'user';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = trim($_POST['username']); 
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password']; 

    if (empty($name) || empty($email) || empty($password) || empty($confirm_password)) {
        $error = 'All fields are required.';
    } elseif ($password !== $confirm_password) {
        $error = 'Passwords do not match.';
    } else {
        $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->execute([$email]);
        if ($stmt->fetch()) {
            $error = 'Email already registered.';
        } else {
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            $reg_role = (isset($_REQUEST['role']) && $_REQUEST['role'] === 'admin') ? 'admin' : 'user';
            
            $stmt = $pdo->prepare("INSERT INTO users (name, email, password, role) VALUES (?, ?, ?, ?)");
            if ($stmt->execute([$name, $email, $hashed_password, $reg_role])) {
                $success = 'Registration successful! You can now <a href="login.php">login</a>.';
            } else {
                $error = 'Something went wrong. Please try again.';
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register | Vetta</title>
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
        <h1>Register for <?= htmlspecialchars(ucfirst($role)) ?> </h1>
        
        <?php if($error): ?>
            <script>
                document.addEventListener('DOMContentLoaded', function() {
                    Swal.fire({
                        icon: 'error',
                        title: 'Registration Failed',
                        text: '<?php echo $error; ?>',
                        confirmButtonColor: '#d33',
                        confirmButtonText: 'OK'
                    });
                });
            </script>
        <?php endif; ?>
        
        <?php if($success): ?>
            <script>
                document.addEventListener('DOMContentLoaded', function() {
                    Swal.fire({
                        icon: 'success',
                        title: 'Registration Successful!',
                        html: 'You can now <a href="login.php?role=<?= htmlspecialchars($role) ?>" style="color:#3085d6; font-weight:bold;">login</a>.',
                        confirmButtonColor: '#3085d6',
                        confirmButtonText: 'Go to Login'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            window.location.href = 'login.php?role=<?= htmlspecialchars($role) ?>';
                        }
                    });
                });
            </script>
        <?php endif; ?>

        <form action="register.php?role=<?= htmlspecialchars($role) ?>" method="POST">
            <input type="hidden" name="role" value="<?= htmlspecialchars($role) ?>">

            <label>Username:</label>
            <input type="text" name="username" required>

            <label>E-mail:</label>
            <input type="email" name="email" required>

            <label>Password:</label>
            <input type="password" name="password" required>

            <!-- Added Confirm Password field to match backend requirement -->
            <label>Confirm Password:</label>
            <input type="password" name="confirm_password" required>

            <button type="submit">Register</button>
        </form>

        <p>Already have an account? 
            <a href="login.php?role=<?= htmlspecialchars($role) ?>">Log In</a>
        </p>
        
        <p><a href="index.php">Back to Home</a></p>
    </div>
</body>
</html>
