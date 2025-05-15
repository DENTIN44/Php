<?php
// Start the session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Handle logout
if (isset($_GET['logout']) && $_GET['logout'] == 'true') {
    // Destroy the session
    session_unset();
    session_destroy();

    // Redirect to the login page (or home page)
    header('Location: views/login.php'); // Redirect to login or home page
    exit;
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>My Shop</title>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
  <link rel="stylesheet" href="Assets/STYLES.css">
</head>
<body>
  <div id="content">
    <nav>
      <h3><a href="/" class="shop-button">Home</a></h3>

      <ul>
          <li><a href="about.php">Sobre</a></li>
          <li><a href="users-list.php">Users</a></li>

          <?php if (isset($_SESSION['user'])): // Check if user is logged in ?>
              <!-- User is logged in -->
              <li><a href="profile.php">Profile</a></li>
              <!-- Log out link with inline PHP logic -->
              <li><a href="index.php?logout=true">Logout</a></li> 
          <?php else: ?>
              <!-- User is not logged in -->
              <li><a href="views/register.php">Register</a></li>
              <li><a href="views/login.php">Login</a></li>
          <?php endif; ?>

          <li><a href="ForgotPassword.php">Email-reset</a></li>
      </ul>
    </nav>

    <div class="main-content">
      <img src="Assets/images/shutterstock.jpg" alt="img">
    </div>

    <div class="footer-container">
      <!-- Footer sections -->
      <?php for ($i = 0; $i < 3; $i++): ?>
        <!-- You can add footer content here -->
      <?php endfor; ?>

    <footer>
      <p>© WW 2025. All rights reserved.</p>
    </footer>

    <div class="back-btn"><i class="fas fa-arrow-up"></i></div>

    <script>
      // Add/remove shadow on search form
      const searchForm = document.getElementById('search-form');
      document.addEventListener('click', (e) => {
        if (searchForm.contains(e.target)) {
          searchForm.classList.add('shadow-active');
        } else {
          searchForm.classList.remove('shadow-active');
        }
      });

      // Back to Top button logic
      const backBtn = document.querySelector('.back-btn');
      window.addEventListener('scroll', () => {
        backBtn.classList.toggle('active', window.scrollY > 100);
      });

      backBtn.addEventListener('click', () => {
        window.scrollTo({ top: 0, behavior: 'smooth' });
      });
    </script>
  </div>

  <?php require 'footer.php'; ?>
</body>
</html>
