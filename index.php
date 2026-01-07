<?php
SESSION_START();
include 'config/plugins.php';
require_once __DIR__ . '/config/site.php';
require_once __DIR__ . '/config/dbcon.php';
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

<?php
// Show carousel if covers are available
$coversFile = __DIR__ . '/config/covers.json';
$covers = [];
if (file_exists($coversFile)) { $covers = json_decode(file_get_contents($coversFile), true) ?? []; }
?>
<div class="container-main">
<?php if (count($covers) > 0): ?>
  <div id="homeCarousel" class="carousel slide mb-3" data-bs-ride="carousel" style="--bs-carousel-height: 360px;">
    <div class="carousel-indicators">
      <?php foreach ($covers as $i => $entry): ?>
        <button type="button" data-bs-target="#homeCarousel" data-bs-slide-to="<?= $i ?>" class="<?= $i===0? 'active' : '' ?>" aria-current="<?= $i===0? 'true':'false' ?>" aria-label="Slide <?= $i+1 ?>"></button>
      <?php endforeach; ?>
    </div>
    <div class="carousel-inner">
      <?php foreach ($covers as $i => $entry):
        if (is_string($entry)) { $cpath = $entry; $title = ''; $caption=''; } else { $cpath = $entry['path'] ?? ''; $title = $entry['title'] ?? ''; $caption = $entry['caption'] ?? ''; }
        $p = __DIR__ . '/' . $cpath;
        $url = htmlspecialchars($cpath);
        if (file_exists($p)) { $url .= '?v=' . filemtime($p); }
      ?>
      <div class="carousel-item <?= $i===0? 'active':'' ?>">
        <div style="background-image:url('<?= $url ?>'); background-size:cover; background-position:center; height:360px; position:relative;">
          <?php if ($title || $caption): ?>
          <div class="carousel-caption d-none d-md-block" style="background: rgba(0,0,0,0.35); padding:12px; border-radius:6px; left:10%; right:10%; bottom:30px;">
            <?php if ($title): ?><h5><?= htmlspecialchars($title) ?></h5><?php endif; ?>
            <?php if ($caption): ?><p><?= htmlspecialchars($caption) ?></p><?php endif; ?>
          </div>
          <?php endif; ?>
        </div>
      </div>
      <?php endforeach; ?>
    </div>
    <button class="carousel-control-prev" type="button" data-bs-target="#homeCarousel" data-bs-slide="prev">
      <span class="carousel-control-prev-icon" aria-hidden="true"></span>
      <span class="visually-hidden">Previous</span>
    </button>
    <button class="carousel-control-next" type="button" data-bs-target="#homeCarousel" data-bs-slide="next">
      <span class="carousel-control-next-icon" aria-hidden="true"></span>
      <span class="visually-hidden">Next</span>
    </button>
  </div>
<?php else: ?>
  <div class="hero-section text-center mb-1" style="background-color: #0dcaf0; background-size: cover; padding: 25px; color: #ffffff;">
      <h1 style="font-size: 2.5rem; font-weight: 350;">Welcome</h1>
      <p style="font-size: 1.2rem;">Your webpage is ready to be set up. If you are the admin, click start and setup the website through content management section.</p>
      <a type="button" class="btn btn-outline-light px-3" data-bs-toggle="modal" data-bs-target="#loginModal">Start</a>
  </div>
<?php endif; ?>
</div>

<!-- Home cards section -->
<div class="row mx-0 p-4">
<?php
  $cardsRes = $conn->query("SELECT * FROM home_cards WHERE status = 1 ORDER BY sort_order ASC, id ASC");
  $count = 0;
  while ($row = $cardsRes->fetch_assoc()) {
    $count++;
    $title = htmlspecialchars($row['title']);
    $desc = htmlspecialchars($row['description']);
    echo "<div class=\"col-sm-4\">";
    if (!empty($row['image_path'])) {
      $ip = $row['image_path'];
      $pfile = __DIR__ . '/' . $ip;
      $iurl = htmlspecialchars($ip);
      if (file_exists($pfile)) { $iurl .= '?v=' . filemtime($pfile); }
      echo "<img src=\"$iurl\" style=\"width:100%; height:180px; object-fit:cover; margin-bottom:10px;\">";
    }
    echo "<h1 style=\"font-size: 1.9rem; font-weight: 300;\">$title</h1>";
    echo "<p style=\"font-size: 1.2rem; font-weight: 300; text-align: justify;\">$desc</p>";
    echo "</div>";
  }
  // if no cards in db, fallback to static messages
  if ($count === 0) {
?>  
<?php } ?>

<?php
// Render all feature cards from DB if available
$fRes = $conn->query("SELECT * FROM feature_card ORDER BY id ASC");
if ($fRes && $fRes->num_rows > 0) {
  while ($f = $fRes->fetch_assoc()) {
    $fHeader = htmlspecialchars($f['header']);
    $fTitle = htmlspecialchars($f['title']);
    $fBody = nl2br(htmlspecialchars($f['body']));
    $fFooter = htmlspecialchars($f['footer']);
    $fBg = htmlspecialchars($f['bg_color'] ?: '#ffffff');
    // pick readable text color
    $hex = ltrim($fBg, '#'); if (strlen($hex) === 3) { $hex = $hex[0].$hex[0].$hex[1].$hex[1].$hex[2].$hex[2]; }
    $r = hexdec(substr($hex,0,2)); $g = hexdec(substr($hex,2,2)); $b = hexdec(substr($hex,4,2));
    $brightness = (($r * 299) + ($g * 587) + ($b * 114)) / 1000;
    $fText = $brightness > 128 ? '#000000' : '#ffffff';
    echo "<div class=\"card text-center mb-3\" style=\"background:$fBg; color:$fText;\">";
    echo "<div class=\"card-header\">$fHeader</div>";
    echo "<div class=\"card-body\"><h5 class=\"card-title\">$fTitle</h5><p class=\"card-text\">$fBody</p></div>";
    echo "<div class=\"card-footer text-muted\" style=\"color:$fText; opacity:0.9\">$fFooter</div>";
    echo "</div>";
    echo "<hr>";
  }
}
?>

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
</script>