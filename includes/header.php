<?php
if (session_status() === PHP_SESSION_NONE) session_start();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= $page_title ?? 'WanderLust - Explore the World' ?></title>
  <link rel="stylesheet" href="<?= $root ?? '' ?>css/style.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css">
</head>
<body>

<nav class="navbar" id="navbar">
  <a href="<?= $root ?? '' ?>index.php" class="nav-brand">✈ WanderLust</a>
  <div class="nav-links">
    <a href="<?= $root ?? '' ?>index.php">Home</a>
    <a href="<?= $root ?? '' ?>destinations.php">Destinations</a>
    <a href="<?= $root ?? '' ?>tools.php">AI Tools</a>
    <a href="<?= $root ?? '' ?>contact.php">Contact</a>
    <?php if (isset($_SESSION['user_id'])): ?>
      <a href="<?= $root ?? '' ?>dashboard.php">My Trips</a>
      <a href="<?= $root ?? '' ?>logout.php" class="nav-btn">Logout</a>
    <?php else: ?>
      <a href="<?= $root ?? '' ?>login.php">Login</a>
      <a href="<?= $root ?? '' ?>signup.php" class="nav-btn">Sign Up Free</a>
    <?php endif; ?>
  </div>
  <button class="nav-hamburger" onclick="toggleMenu(this)">
    <span></span><span></span><span></span>
  </button>
</nav>

<script>
window.addEventListener('scroll', () => {
  document.getElementById('navbar').classList.toggle('scrolled', window.scrollY > 50);
});
function toggleMenu(btn) {
  const links = document.querySelector('.nav-links');
  links.style.display = links.style.display === 'flex' ? 'none' : 'flex';
  links.style.flexDirection = 'column';
  links.style.position = 'absolute';
  links.style.top = '70px'; links.style.left = '0'; links.style.right = '0';
  links.style.background = 'white'; links.style.padding = '20px';
  links.style.boxShadow = '0 10px 30px rgba(0,0,0,0.1)';
}
</script>
