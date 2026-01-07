<?php
SESSION_START();  
include 'config/plugins.php';
require_once __DIR__ . '/config/site.php';
?>
<style>
@keyframes popIn {
  0% {
    transform: scale(0.9);
    opacity: 0;
  }
  100% {
    transform: scale(1);
    opacity: 1;
  }
}
.modal-content {
  animation: popIn 0.3s ease-out;
}
</style>
<!--Eto yung navbar-->
<nav class="navbar navbar-expand-sm bg-light navbar-light" style="box-shadow: 0 2px 4px rgba(0,0,0,0.1); position: sticky; top: 0; z-index: 1000;">
  <div class="container-fluid container py-4">
    <?php
      $logoPath = __DIR__ . '/' . $SITE_LOGO;
      $logoUrl = htmlspecialchars($SITE_LOGO);
      if (file_exists($logoPath)) { $logoUrl .= '?v=' . filemtime($logoPath); }
    ?>
    <a class="navbar-brand" href="index.php"><img src="<?= $logoUrl ?>" alt="Logo" style="height:40px; width:auto; object-fit:contain;"></a>
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#collapsibleNavbar">
      <span class="navbar-toggler-icon"></span>
    </button>
    <div class="collapse navbar-collapse" id="collapsibleNavbar">
      <ul class="navbar-nav me-auto mb-2 mb-lg-0">
        <li class="nav-item">
          <a class="nav-link fs-6" style="width: 4rem;" href="index.php">Home</a>
        </li>
        <li class="nav-item">
          <a class="nav-link fs-6" style="width: 6rem;" href="contact.php">Contact Us</a>
        </li>
        <li class="nav-item">
          <a class="nav-link fs-6" style="width: 6rem;" href="enroll.php">Enroll Now</a>
        </li>  
      </ul>
        <button type="button" class="btn btn-outline-primary rounded-pill px-4" data-bs-toggle="modal" data-bs-target="#loginModal">LOGIN</button>
    </div>
  </div>
</nav>



  <div class=" py-5 px-3 h-50">
    <div class="row d-flex justify-content-center align-items-center h-100">
      <div class="col-xl-10">
        <div class="card rounded-3 text-black" style="box-shadow: 0 15px 17px rgba(0,0,0,0.1);">
          <div class="row g-0">
            <div class="col-lg-7">
              <div class="card-body p-md-5 mx-md-4">

                <div>
                  <h3 class="mt-1 mb-3 pb-1">Contact Us!</h3>
                </div>

                <form action="config/addContact.php" method="POST">
                    <!-- Name input -->
                    <div data-mdb-input-init class="form-outline ">
                      <label class="form-label" for="name">Name:</label>
                      <input type="text" id="name" name="name" class="form-control" placeholder="Enter your name" required />
                    </div>

                    <!-- Email input -->
                    <div data-mdb-input-init class="form-outline ">
                      <label class="form-label" for="email">Email address:</label>
                      <input type="email" id="email" name="email" class="form-control" placeholder="Enter your email address" required />
                    </div>

                    <!-- Message input -->
                    <div data-mdb-input-init class="form-outline ">
                      <label class="form-label" for="message">Message:</label>
                      <textarea class="form-control" id="message" name="message" rows="4" placeholder="Enter your message" required></textarea>
                    </div>

                    <label class="form-label" for="captcha">Captcha:</label>
                    <div class="row mb-1">
                      <div class="col">
                        <div data-mdb-input-init class="form-outline">
                          <p id="captchaDisplay" class="fw-bold fs-5"></p>
                        </div>
                      </div>
                      <div class="col">
                        <div data-mdb-input-init class="form-outline">
                          <input type="text" id="captchaInput" name="captcha" class="form-control" placeholder="Enter code" required/>
                        </div>
                      </div>
                    </div>
                    <div id="captchaError" class="text-danger mt-2" style="display: none;">Captcha code is incorrect. Please try again.</div>
                    <?php
                    if (isset($_SESSION['success'])){
                    echo '<div class="mt-2 text-success"><h6>'.$_SESSION['success'].'</h6></div>';
                    unset($_SESSION['success']);
                    }?>
                    <!-- Submit button -->
                    <button id="sendBtn" data-mdb-ripple-init type="button" class="btn btn-primary btn-block mt-3 ps-3 pe-3">Send</button>
                  </form>

              </div>
            </div>
            <div class="col-lg-5 d-flex align-items-end justify-content-end bg-gradient-custom">
              <div class="text-white ps-5 py-4 p-md-5 mx-md-1">
                <p class="mb-0" style="font-size: 16px; font-weight: bold;">
                  <i class="fa-solid fa-school"></i> <?= htmlspecialchars($SITE_INST) ?><br><br>
                  <i class="fa-solid fa-location-dot"></i> <?= htmlspecialchars($SITE_LOCATION) ?><br><br>
                  <i class="fa-solid fa-envelope"></i> <?= htmlspecialchars($SITE_EMAIL) ?><br><br>
                  <i class="fa-solid fa-phone"></i> <?= htmlspecialchars($SITE_PHONE) ?><br><br>
                  <i class="fa-solid fa-tty"></i> <?= htmlspecialchars($SITE_TTY) ?>
                </p>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>

<!--Yung login modal-->
<div class="modal p-4" id="loginModal" tabindex="-1" aria-labelledby="loginModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
        <div class="wrapper">
        <div class="logo">
            <?php
              $logoPath = __DIR__ . '/' . $SITE_LOGO;
              $logoUrl = htmlspecialchars($SITE_LOGO);
              if (file_exists($logoPath)) { $logoUrl .= '?v=' . filemtime($logoPath); }
            ?>
            <img src="<?= $logoUrl ?>" alt="">
        </div>
        <div class="text-center mt-4 name">
            LOGIN
        </div>
        <form class="p-3 mt-3" action="config/loginAuth.php" method="POST">
            <div class="form-field d-flex align-items-center">
                <span class="far fa-user"></span>
                <input autocomplete="off" type="text" name="username" id="username" placeholder="Username">
            </div>
            <div class="form-field d-flex align-items-center">
                <span class="fas fa-key"></span>
                <input type="password" name="password" class="password" id="pwd" placeholder="Password">
                <i class="fa-solid fa-eye me-3 fs-5 cursor-pointer" id="icon"></i>
            </div>
            <?php
                if (isset($_SESSION['error'])){
                    echo '<div class="mb-2" style="color: red;"><h6>'.$_SESSION['error'].'</h6></div>';
                    unset($_SESSION['error']);
                }
                ?>
            <button class="btn mt-3">Login</button>
        </form>
        <div class="text-center fs-6 mb-3">
           <a type="button" class="text-decoration-underline" data-bs-dismiss="modal">Close</a>
        </div>
    </div>
</div>

<script defer>
    const passwordInput = document.querySelector(".form-field .password");
    const eyeIcon = document.querySelector("#icon");

    eyeIcon.addEventListener("click", () => {
    // Toggle the password input type between "password" and "text"
    passwordInput.type = passwordInput.type === "password" ? "text" : "password";
    // Update the eye icon class based on the password input type
    eyeIcon.className = `fa-solid fa-eye${passwordInput.type === "password" ? "fa-solid fa-eye me-3 fs-5 cursor-pointer" : "-slash me-3 fs-5 cursor-pointer"}`;
  });

  // Generate random captcha
  let captchaCode = Math.floor(100000 + Math.random() * 900000).toString();
  document.getElementById('captchaDisplay').textContent = captchaCode;

  // Handle send button click
  document.getElementById('sendBtn').addEventListener('click', function() {
    const captchaInput = document.getElementById('captchaInput').value;
    const errorDiv = document.getElementById('captchaError');
    if (captchaInput === captchaCode) {
      errorDiv.style.display = 'none';
      document.querySelector('form').submit();
    } else {
      errorDiv.style.display = 'block';
      // Regenerate captcha on failure
      captchaCode = Math.floor(100000 + Math.random() * 900000).toString();
      document.getElementById('captchaDisplay').textContent = captchaCode;
      document.getElementById('captchaInput').value = '';
    }
  });
</script>