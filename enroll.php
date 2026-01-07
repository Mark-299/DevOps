
<?php
SESSION_START();
include 'config/plugins.php';
require_once __DIR__ . '/config/dbcon.php';
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
<?php
if (isset($_SESSION['status'])){
    echo '<div class="container mt-4 mb-4 alert alert-success" role="alert">
    '.$_SESSION['status'].'</div>';
    unset($_SESSION['status']);
}
?>
<!--yung enrollment form section-->

<div class="container shadow rounded-3 p-4 my-4">
  <form action="config/AddStudent.php" method="POST">
    <p class="mb-4" style="font-size: 2rem;">Select your appointment schedule</p>
    <table class="table table-bordered">
    <thead>
      <tr>
        <th>Date</th>
        <th>Time</th>
        <th>Slots</th>
        <th>Action</th>

      </tr>
    </thead>
    <tbody>
      <?php
        $stmt = $conn->prepare("SELECT id, date, start_time, end_time, slots FROM appointments WHERE date >= CURDATE() ORDER BY date, start_time");
        $stmt->execute();
        $res = $stmt->get_result();
        $rows = $res ? $res->fetch_all(MYSQLI_ASSOC) : [];
        $hasAvailable = false;
        foreach ($rows as $r) { if (intval($r['slots']) > 0) { $hasAvailable = true; break; } }

        if (count($rows) > 0) {
          foreach ($rows as $row) {
            $fmtDate = date("F j, Y | l", strtotime($row['date']));
            $fmtStart = date("h:i A", strtotime($row['start_time']));
            $fmtEnd = date("h:i A", strtotime($row['end_time']));
            $slots = intval($row['slots']);
            $disabled = $slots <= 0 ? 'disabled' : '';
            $badge = $slots <= 0 ? '<span class="badge bg-danger">Full</span>' : '<span class="badge bg-success">' . $slots . ' slot' . ($slots>1? 's':'') . '</span>';
            $requiredAttr = $hasAvailable ? 'required' : '';
            echo '<tr>';
            echo '<td>' . htmlspecialchars($fmtDate) . '</td>';
            echo '<td>' . htmlspecialchars($fmtStart) . ' - ' . htmlspecialchars($fmtEnd) . '</td>';
            echo '<td>' . $badge . '</td>';
            echo '<td><input type="radio" name="appointmentID" value="' . intval($row['id']) . '" data-date="' . htmlspecialchars($row['date']) . '" data-start="' . htmlspecialchars($row['start_time']) . '" data-end="' . htmlspecialchars($row['end_time']) . '" ' . $disabled . ' ' . $requiredAttr . '></td>';
            echo '</tr>'; 
          }
        } else {
          echo '<tr><td colspan="4">No appointments available.</td></tr>';
        }
      ?>
    </tbody>
  </table>
  <input type="hidden" name="appointment_date" id="appointment_date" value="">
  <input type="hidden" name="time" id="appointment_time" value="">
</div>
<script>
(function(){
  function setAppointmentFields(){
    var sel = document.querySelector('input[name="appointmentID"]:checked');
    var dateEl = document.getElementById('appointment_date');
    var timeEl = document.getElementById('appointment_time');
    if (!dateEl || !timeEl) return;
    function to12h(t){ if (!t) return ''; var parts = t.split(':'); var hh = parseInt(parts[0],10); var mm = (parts[1]||'00').split(':')[0]; if (isNaN(hh)) return t; var am = hh >= 12 ? 'PM' : 'AM'; hh = hh % 12; if (hh === 0) hh = 12; return hh + ':' + (mm.length===1? '0'+mm:mm) + ' ' + am; }
    if (sel) {
      dateEl.value = sel.getAttribute('data-date') || '';
      var s = sel.getAttribute('data-start') || '';
      var e = sel.getAttribute('data-end') || '';
      // store human-friendly range like "1:00 PM - 2:00 PM"
      if (s && e) {
        timeEl.value = to12h(s) + ' - ' + to12h(e);
      } else if (s) {
        timeEl.value = to12h(s);
      } else {
        timeEl.value = '';
      }
    } else {
      dateEl.value = '';
      timeEl.value = '';
    }
  }
  document.querySelectorAll('input[name="appointmentID"]').forEach(function(r){
    r.addEventListener('change', setAppointmentFields);
  });
  // initialize on load (in case browser persisted selection)
  setAppointmentFields();
  // integrate with confirmation submit button (set fields before final submit)
  var confirmBtn = document.getElementById('confirmSubmit');
  if (confirmBtn) {
    confirmBtn.removeEventListener && confirmBtn.removeEventListener('click', null);
    confirmBtn.addEventListener('click', function(){
      setAppointmentFields();
      var form = document.querySelector('form');
      if (form.checkValidity()) {
        form.submit();
      } else {
        form.reportValidity();
      }
    });
  }
})();
</script>

<!--Yung account creation section-->
<div class="container shadow rounded-3 p-4 my-4">
    <p class="mb-4" style="font-size: 2rem;">I. Create Your Student Portal Account</p>
      <div class="mb-3">
        <label for="username" class="form-label">Username:</label>
        <div class="input-group">
          <input type="text" class="form-control" id="Username" name="username" placeholder="Username" required>
          <div class="input-group-text">@Student</div>
        </div>
      </div>
        <div class="mb-3">
            <label for="email" class="form-label">Email address:</label>
            <input type="email" class="form-control" id="email" name="email" placeholder="name@example.com" required>
        </div>
        <div class="mb-3">
            <label for="password" class="form-label">Password:</label>
            <input type="password" class="form-control" id="password" name="password" placeholder="Enter your password" required>
        </div>
</div>

<!--Yung educational attainment section-->
<div class="container shadow rounded-3 p-4 my-4">
    <p class="mb-4" style="font-size: 2rem;">II. Educational Attainment</p>
      <label class="form-label" for="form6Example1">Elementary:</label>
      <div class="row mb-4">
    <div class="col-8">
      <div data-mdb-input-init class="form-outline">
        <input type="text" id="elemName" name="elemName" class="form-control" placeholder="School Name"/ required>
      </div>
    </div>
    <div class="col">
      <div data-mdb-input-init class="form-outline">
        <input type="text" id="elemYear" name="elemYear" maxlength="4" class="form-control" placeholder="Year Graduated"/ required>
      </div>
    </div>
  </div>
  <label class="form-label" for="form6Example1">Junior High School:</label>
  <div class="row mb-4">
    <div class="col-8">
      <div data-mdb-input-init class="form-outline">
        <input type="text" id="juniorName" name="juniorName" class="form-control" placeholder="School Name"/ required>
      </div>
    </div>
    <div class="col">
      <div data-mdb-input-init class="form-outline">
        <input type="text" id="juniorYear" name="juniorYear" maxlength="4" class="form-control" placeholder="Year Graduated"/ required>
      </div>
    </div>
  </div>
  <label class="form-label" for="form6Example1">Senior High School:</label>
  <div class="row mb-4">
    <div class="col-8">
      <div data-mdb-input-init class="form-outline">
        <input type="text" id="seniorName" name="seniorName" class="form-control" placeholder="School Name"/ required>
      </div>
    </div>
    <div class="col">
      <div data-mdb-input-init class="form-outline">
        <input type="text" id="seniorYear" name="seniorYear" maxlength="4" class="form-control" placeholder="Year Graduated"/ required>
      </div>
    </div>
  </div>
</div>

<!--Yung personal information section-->
<div class="container shadow rounded-3 p-4 my-4">
    <p class="mb-3" style="font-size: 2rem;">III. Enrollment Form</p>
    <p class="text-center mb-3" style="font-size: 1.5rem;">Student's Personal Information</p>
    <label for="name" class="form-label">Student Name:</label>
    <div class="row mb-3">
    <div class="col">
      <div data-mdb-input-init class="form-outline">
        <input type="text" id="lastname" name="lastname" class="form-control" placeholder="Last Name"/ required>
      </div>
    </div>
    <div class="col">
      <div data-mdb-input-init class="form-outline">
        <input type="text" id="firstname" name="firstname" class="form-control" placeholder="First Name"/ required>
      </div>
    </div>
    <div class="col">
      <div data-mdb-input-init class="form-outline">
        <input type="text" id="middlename" name="middlename" class="form-control" placeholder="Middle Name"/ required>
      </div>
    </div>
  </div>

  <div class="row mb-3">
    <div class="col">
      <div data-mdb-input-init class="form-outline">
        <label for="sex" class="form-label">Sex:</label>
        <select id="sex" name="sex" class="form-select" required>
          <option selected disabled>Choose...</option>
          <option>Male</option>
          <option>Female</option>
        </select>
      </div>
    </div>
    <div class="col">
      <div data-mdb-input-init class="form-outline">
        <label for="date" class="form-label">Date of Birth:</label>
        <input type="date" id="dob" name="dob" class="form-control" required/>
      </div>
    </div>
  </div>

  <label for="Number" class="form-label">Contact Number:</label>
    <div class="input-group mb-3">
      <div class="input-group-text">+63</div>
      <input type="text" class="form-control" id="phoneNumber" name="phoneNumber" placeholder="9XXXXXXXXX" required>
    </div>

    <label for="address" class="form-label">Home Address:</label>
      <textarea type="text" class="form-control" id="address" name="address" placeholder="Enter Full Address" required></textarea>

    <div class="row mb-3 mt-3">
    <div class="col">
      <div data-mdb-input-init class="form-outline">
        <label for="Gname" class="form-label">Guardian' Name:</label>
        <input type="text" id="guardianName" name="guardianName" class="form-control" placeholder="Enter Guardian's Full Name" required/>
      </div>
    </div>
    <div class="col">
      <div data-mdb-input-init class="form-outline">
        <label for="Gnumber" class="form-label">Guardian's Contact Number:</label>
        <div class="input-group">
          <div class="input-group-text">+63</div>
          <input type="text" class="form-control" id="guardianPhoneNumber" maxlength="10" name="guardianPhoneNumber" placeholder="9XXXXXXXXX" required>
        </div>
      </div>
    </div>
  </div>

  <label for="Gaddress" class="form-label">Guardian's Home Address:</label>
      <textarea type="text" class="form-control" id="guardianAddress" name="guardianAddress" placeholder="Enter Full Address of Guardian" required></textarea>
    <hr>
    <p class="text-center mb-3" style="font-size: 1.5rem;">Student's Enrollment</p>
    <div class="row mb-3">
    <div class="col">
      <div data-mdb-input-init class="form-outline">
        <label for="course" class="form-label">Course you want to enroll:</label>
        <select id="course" name="course" class="form-select" required>
          <option selected disabled>Choose...</option>
          <option>BS Information Technology</option>
          <option>BS Computer Science</option>
          <option>BS Computer Engineering</option>
        </select>
      </div>
    </div>
    <div class="col">
      <div data-mdb-input-init class="form-outline">
        <label for="year" class="form-label">Year Level you want to enroll:</label>
        <select id="year" name="year" class="form-select" required>
          <option selected disabled>Choose...</option>
          <option>First Year</option>
          <option>Second Year</option>
          <option>Third Year</option>
          <option>Fourth Year</option>
        </select>
      </div>
    </div>
    <div class="col">
      <div data-mdb-input-init class="form-outline">
        <label for="section" class="form-label">Section</label>
        <select id="section" name="section" class="form-select" required>
          <option selected disabled>Choose...</option>
          <option>A</option>
          <option>B</option>
          <option>C</option>
        </select>
      </div>
    </div>
      </div>
    </div>

<!--Yung information section-->
  <div class="container shadow rounded-3 p-4 my-4">
    <h6 style="font-size: 1.5rem;">Data Privacy Notice</h6>
    <p class="mb-5" style="font-size: 1.1rem;">Before you submit any personal information to our website, please take a moment to read 
      this data privacy notice. We are committed to protecting your personal information and ensuring that your privacy is respected. 
      We comply with the Data Privacy Act of the Philippines and other applicable data protection laws.</p>

    <h6 style="font-size: 1.5rem;">What personal information do we collect?</h6>
    <p class="mb-5" style="font-size: 1.1rem;">We may collect personal information such as your name, email address, phone number, 
      and other details that you provide when you fill out a form or interact with our website.</p>

    <h6 style="font-size: 1.5rem;">How do we use your personal information?</h6>
    <p class="mb-5" style="font-size: 1.1rem;">We may use your personal information to provide you with the services or information 
      that you have requested, to respond to your inquiries, and to improve our website and services. We may also use your personal 
      information for other purposes that are compatible with the original purpose of collection or as required by law.</p>
    
    <h6 style="font-size: 1.5rem;">Do we share your personal information?</h6>
    <p class="mb-5" style="font-size: 1.1rem;">We do not sell, trade, or otherwise transfer your personal information to outside parties 
      unless we provide you with advance notice or as required by law.</p>
    
    <h6 style="font-size: 1.5rem;">How do we protect your personal information?</h6>
    <p class="mb-5" style="font-size: 1.1rem;">We implement a variety of security measures to protect your personal information from unauthorized 
      access, use, or disclosure. We use industry-standard encryption technology and other reasonable measures to safeguard your personal information.</p>

    <h6 style="font-size: 1.5rem;">What are your rights?</h6>
    <p class="mb-5" style="font-size: 1.1rem;">You have the right to access, correct, and delete your personal information that we have collected.
       You may also withdraw your consent to our processing of your personal information at any time. To exercise your rights, please contact us using the 
       contact details provided on our website.</p>

    <h6 style="font-size: 1.5rem;">Changes to this notice</h6>
    <p class="mb-5" style="font-size: 1.1rem;">We may update this data privacy notice from time to time. Any changes will be posted on our website, and the 
      revised notice will apply to personal information collected after the date it is posted.</p>

    <h6 style="font-size: 1.5rem;">Contact us</h6>
    <p class="mb-5" style="font-size: 1.1rem;">If you have any questions or concerns about our data privacy practices, please contact us by clicking this 
      <a href="contact.php" class="text-decoration-underline">link</a></p>
    
    <button id="submitBtn" type="button" class="btn btn-primary">Submit</button>
    </form>
</div>
</div>


<!-- Footer -->
<footer class="bg-dark text-center text-lg-start mt-5">
  <div class="container p-5">
    <div class="row">
      <div class="col-lg-9 col-md-12 mb-4 mb-md-0">
        <h5 class="text-uppercase text-light"><?= htmlspecialchars(
          $SITE_INST ?: 'Institute'
        ) ?></h5>
        <p class="text-light">Empowering education for a brighter future.</p>
      </div>
      <div class="col-lg-3 col-md-12 mb-4 mb-md-0">
        <h5 class="text-uppercase text-light">Contact</h5>
        <p class="text-light"><br><i class="fa-solid fa-school"></i> <?= htmlspecialchars($SITE_INST) ?><br><br><i class="fa-solid fa-location-dot"></i> <?= htmlspecialchars($SITE_LOCATION) ?><br><br>
          <i class="fa-solid fa-envelope"></i> <?= htmlspecialchars($SITE_EMAIL) ?><br><br><i class="fa-solid fa-phone"></i> <?= htmlspecialchars($SITE_PHONE) ?><br><br><i class="fa-solid fa-tty"></i> <?= htmlspecialchars($SITE_TTY) ?>
        </p>
      </div>
    </div>
  </div>
  <div class="text-center text-light p-3" style="background-color: rgba(0, 0, 0, 0.2);">
    © <?= date('Y') ?> Copyright: <?= htmlspecialchars($SITE_INST) ?>
  </div>
</footer>

    <!-- Confirmation Modal -->
<div class="modal fade" id="confirmModal" tabindex="-1" aria-labelledby="confirmModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="confirmModalLabel">Confirm Submission</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        Are you sure you want to submit the enrollment form?
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">No</button>
        <button type="button" class="btn btn-primary" id="confirmSubmit">Yes</button>
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

  // Handle submit button click to show confirmation modal
  document.getElementById('submitBtn').addEventListener('click', function() {
    const modal = new bootstrap.Modal(document.getElementById('confirmModal'));
    modal.show();
  });

  // Handle confirmation to submit the form
  document.getElementById('confirmSubmit').addEventListener('click', function() {
    const form = document.querySelector('form');
    if (form.checkValidity()) {
      form.submit();
    } else {
      form.reportValidity();
    }
  });
</script>