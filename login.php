<?php
$page_title = "Login - WanderLust";
session_start();
include 'db_connect.php';
if (isLoggedIn()) redirect('dashboard.php');

$error = '';
if (isset($_POST['submit'])) {
    $login = trim(mysqli_real_escape_string($conn, $_POST['login']));
    $pass  = $_POST['password'];
    $sql   = "SELECT * FROM users WHERE email='$login' OR username='$login'";
    $res   = mysqli_query($conn, $sql);
    if (mysqli_num_rows($res) > 0) {
        $user = mysqli_fetch_assoc($res);
        if (password_verify($pass, $user['password'])) {
            $_SESSION['user_id']   = $user['id'];
            $_SESSION['user']      = $user['username'];
            $_SESSION['user_name'] = $user['name'] ?? $user['username'];
            $_SESSION['role']      = $user['role'];
            redirect($user['role'] === 'admin' ? 'admin/dashboard.php' : 'dashboard.php');
        } else { $error = 'Wrong password. Try again.'; }
    } else { $error = 'Account not found. Please sign up first.'; }
}
include 'includes/header.php';
?>
<div class="auth-page">
  <div class="auth-card">
    <h2>Welcome Back ✈️</h2>
    <p class="sub">Login to plan your next adventure</p>
    <?php if ($error): ?><div class="alert alert-error"><?= $error ?></div><?php endif; ?>
    <form method="POST">
      <div class="form-group">
        <label>Email or Username</label>
        <input type="text" name="login" placeholder="you@email.com" required>
      </div>
      <div class="form-group">
        <label>Password</label>
        <input type="password" name="password" placeholder="Your password" required>
      </div>
      <button class="auth-submit" name="submit">Login</button>
    </form>
    <p class="auth-link">Don't have an account? <a href="signup.php">Sign Up Free</a></p>
  </div>
</div>
<?php include 'includes/footer.php'; ?>
