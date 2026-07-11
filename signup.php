<?php
$page_title = "Sign Up - WanderLust";
session_start();
include 'db_connect.php';
if (isLoggedIn()) redirect('dashboard.php');

$error = ''; $success = '';
if (isset($_POST['submit'])) {
    $name  = trim(mysqli_real_escape_string($conn, $_POST['name']));
    $uname = trim(mysqli_real_escape_string($conn, $_POST['username']));
    $email = trim(mysqli_real_escape_string($conn, $_POST['email']));
    $pass  = $_POST['password'];
    $cpass = $_POST['confirm_password'];

    if (!$name || !$uname || !$email || !$pass) { $error = 'All fields are required.'; }
    elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) { $error = 'Invalid email format.'; }
    elseif (strlen($pass) < 6) { $error = 'Password must be at least 6 characters.'; }
    elseif ($pass !== $cpass) { $error = 'Passwords do not match.'; }
    else {
        $check = mysqli_query($conn, "SELECT id FROM users WHERE email='$email' OR username='$uname'");
        if (mysqli_num_rows($check) > 0) {
            $error = 'Email or username already exists.';
        } else {
            $hash = password_hash($pass, PASSWORD_DEFAULT);
            mysqli_query($conn, "INSERT INTO users (username, name, email, password) VALUES ('$uname','$name','$email','$hash')");
            $success = 'Account created! <a href="login.php">Login now →</a>';
        }
    }
}
include 'includes/header.php';
?>
<div class="auth-page">
  <div class="auth-card">
    <h2>Join WanderLust 🌍</h2>
    <p class="sub">Create your free account and start planning smarter trips</p>
    <?php if ($error): ?><div class="alert alert-error"><?= $error ?></div><?php endif; ?>
    <?php if ($success): ?><div class="alert alert-success"><?= $success ?></div><?php endif; ?>
    <form method="POST">
      <div class="form-group"><label>Full Name</label><input type="text" name="name" placeholder="Your name" required></div>
      <div class="form-group"><label>Username</label><input type="text" name="username" placeholder="cool_traveller" required></div>
      <div class="form-group"><label>Email</label><input type="email" name="email" placeholder="you@email.com" required></div>
      <div class="form-group"><label>Password</label><input type="password" name="password" placeholder="Min 6 characters" required></div>
      <div class="form-group"><label>Confirm Password</label><input type="password" name="confirm_password" placeholder="Repeat password" required></div>
      <button class="auth-submit" name="submit">Create Free Account</button>
    </form>
    <p class="auth-link">Already have an account? <a href="login.php">Login</a></p>
  </div>
</div>
<?php include 'includes/footer.php'; ?>
