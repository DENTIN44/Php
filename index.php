
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
      <h3><a href="#" class="shop-button">Home</a></h3>

      <ul>
        <li><a href="about.php">Sobre</a></li>
        <li><a href="services-list.php">Services</a></li>
        <li><a href="views/register.php">Register</a></li>
        <li><a href="ForgotPassword.php">Email-reset</a></li>
      </ul>
    </nav>

    <div class="main-content">
      <img src="Assets/images/shutterstock.jpg" alt="img">
    
    </div>

  <div class="footer-container">
    <!-- Footer sections -->
    <?php for ($i = 0; $i < 3; $i++): ?>

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
</body>
</html>

<!-- User table section -->


<?php require 'footer.php'; ?>
