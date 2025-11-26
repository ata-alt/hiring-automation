<?php
session_start();

// ==================== CONFIGURATION ====================
define('ADMIN_USERNAME', 'admin');
define('ADMIN_PASSWORD', 'FCI_Assessment_2025!');  // CHANGE THIS to your secure password

// ==================== CHECK IF LOGGED IN ====================
function checkAuth() {
    if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
        showLoginForm();
        exit;
    }
}

// ==================== HANDLE LOGIN ====================
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['login'])) {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';
    
    if ($username === ADMIN_USERNAME && $password === ADMIN_PASSWORD) {
        $_SESSION['admin_logged_in'] = true;
        $_SESSION['admin_username'] = $username;
        $_SESSION['login_time'] = time();
        
        // Redirect back to the page they were trying to access
        $redirect = $_POST['redirect'] ?? 'view_results.php';
        header("Location: $redirect");
        exit;
    } else {
        $error = "Invalid username or password!";
    }
}

// ==================== HANDLE LOGOUT ====================
if (isset($_GET['logout'])) {
    session_destroy();
    header("Location: view_results.php");
    exit;
}

// ==================== SHOW LOGIN FORM ====================
function showLoginForm() {
    global $error;
    $current_page = basename($_SERVER['PHP_SELF']);
    ?>
    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Admin Login - IT Assessment</title>
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
        <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
        <style>
            body {
                background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
                min-height: 100vh;
                display: flex;
                align-items: center;
                justify-content: center;
                font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            }
            .login-container {
                background: white;
                border-radius: 15px;
                box-shadow: 0 10px 40px rgba(0,0,0,0.3);
                padding: 40px;
                max-width: 450px;
                width: 100%;
            }
            .login-header {
                text-align: center;
                margin-bottom: 30px;
            }
            .login-header i {
                font-size: 4em;
                color: #667eea;
                margin-bottom: 20px;
            }
            .login-header h2 {
                color: #333;
                font-weight: bold;
            }
            .form-control:focus {
                border-color: #667eea;
                box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.25);
            }
            .btn-login {
                background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
                border: none;
                padding: 12px;
                font-size: 1.1em;
                font-weight: bold;
            }
            .btn-login:hover {
                opacity: 0.9;
            }
        </style>
    </head>
    <body>
        <div class="login-container">
            <div class="login-header">
                <i class="fas fa-shield-alt"></i>
                <h2>Admin Access Required</h2>
                <p class="text-muted">IT Assessment Dashboard</p>
            </div>

            <?php if (isset($error)): ?>
            <div class="alert alert-danger">
                <i class="fas fa-exclamation-triangle"></i> <?php echo $error; ?>
            </div>
            <?php endif; ?>

            <form method="POST" action="auth.php">
                <input type="hidden" name="redirect" value="<?php echo htmlspecialchars($current_page); ?>">
                
                <div class="mb-3">
                    <label class="form-label">
                        <i class="fas fa-user"></i> Username
                    </label>
                    <input type="text" name="username" class="form-control" placeholder="Enter username" required autofocus>
                </div>

                <div class="mb-4">
                    <label class="form-label">
                        <i class="fas fa-lock"></i> Password
                    </label>
                    <input type="password" name="password" class="form-control" placeholder="Enter password" required>
                </div>

                <button type="submit" name="login" class="btn btn-primary w-100 btn-login">
                    <i class="fas fa-sign-in-alt"></i> Login to Dashboard
                </button>
            </form>

            <div class="text-center mt-4">
                <small class="text-muted">
                    <i class="fas fa-info-circle"></i> Contact admin if you forgot credentials
                </small>
            </div>
        </div>
    </body>
    </html>
    <?php
}
?>