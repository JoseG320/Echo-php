<?php
// Include Composer autoloader and set up authentication
require __DIR__ . '/../vendor/autoload.php';
$db = new PDO('mysql:host=localhost;dbname=echo-db;charset=utf8', 'root', '');
$auth = new \Delight\Auth\Auth($db);

session_start(); // Ensure session is started

?>

<nav>
  <ul>
  <li><a href="index.php"><strong>Echo - Share Music!</strong></a></li>
  </ul>
  <ul>
    <li><a href="#" class="contrast">About</a></li>
    <li><a href="home.php" class="contrast">Home</a></li>
    <?php if ($auth->isLoggedIn()): ?>
      <!-- If the user is logged in, show their email and a logout button -->
      <li><a href="logout.php" class="secondary button">Log Out</a></li>
    <?php else: ?>
      <!-- If the user is not logged in, show the login button -->
      <li><a href="login.php" class="secondary button">Log In</a></li>
    <?php endif; ?>
  </ul>
</nav>