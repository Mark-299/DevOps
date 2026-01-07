<?php
SESSION_START();
include 'config/plugins.php';
require 'config/dbcon.php';

// Check if username is provided
if (!isset($_GET['username']) || empty($_GET['username'])) {
    die("Invalid student.");
}

$username = $conn->real_escape_string($_GET['username']);

// ==========================
// GET STUDENT INFO
// ==========================
$student_sql = "SELECT firstname, lastname, course, year, section FROM enroll WHERE username='$username' LIMIT 1";
$student_result = $conn->query($student_sql);
if (!$student_result || $student_result->num_rows === 0) die("Student not found.");
$student = $student_result->fetch_assoc();

// ==========================
// SAVE GRADES IF POSTED
// ==========================
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['grades']) && is_array($_POST['grades'])) {
    foreach ($_POST['grades'] as $data) {
        $subject = trim($conn->real_escape_string($data['subject'] ?? ''));
        if ($subject === '') continue;

        $instructor = trim($conn->real_escape_string($data['instructor'] ?? ''));
        $prelim = isset($data['prelim']) && $data['prelim'] !== '' ? floatval($data['prelim']) : null;
        $midterm = isset($data['midterm']) && $data['midterm'] !== '' ? floatval($data['midterm']) : null;
        $finals = isset($data['finals']) && $data['finals'] !== '' ? floatval($data['finals']) : null;

        $gradesEntered = array_filter([$prelim, $midterm, $finals], fn($v) => $v !== null);
        $average = !empty($gradesEntered) ? round(array_sum($gradesEntered) / count($gradesEntered), 2) : null;
        $remarks = $average !== null ? ($average >= 75 ? 'Passed' : 'Failed') : '';

        $check_sql = "SELECT id FROM grades WHERE username='$username' AND subject='$subject'";
        $check_res = $conn->query($check_sql);

        if ($check_res && $check_res->num_rows > 0) {
            $update_sql = "
                UPDATE grades
                SET instructor='$instructor', 
                    prelim=" . ($prelim !== null ? $prelim : "NULL") . ",
                    midterm=" . ($midterm !== null ? $midterm : "NULL") . ",
                    finals=" . ($finals !== null ? $finals : "NULL") . ",
                    average=" . ($average !== null ? $average : "NULL") . ",
                    remarks='$remarks'
                WHERE username='$username' AND subject='$subject'
            ";
            $conn->query($update_sql);
        } else {
            $insert_sql = "
                INSERT INTO grades (username, subject, instructor, prelim, midterm, finals, average, remarks)
                VALUES (
                    '$username',
                    '$subject',
                    '$instructor',
                    " . ($prelim !== null ? $prelim : "NULL") . ",
                    " . ($midterm !== null ? $midterm : "NULL") . ",
                    " . ($finals !== null ? $finals : "NULL") . ",
                    " . ($average !== null ? $average : "NULL") . ",
                    '$remarks'
                )
            ";
            $conn->query($insert_sql);
        }
    }

    echo "<div class='alert alert-success'>Grades saved successfully!</div>";
}

// ==========================
// FETCH GRADES
// ==========================
$grades_sql = "SELECT subject, instructor, prelim, midterm, finals, average, remarks FROM grades WHERE username='$username' ORDER BY subject";
$grades_result = $conn->query($grades_sql);

$grades = [];
if ($grades_result && $grades_result->num_rows > 0) {
    while ($row = $grades_result->fetch_assoc()) $grades[] = $row;
}
?>

<?php include __DIR__ . '/sidebar.php'; ?>

<div class="container my-4">
    <h1>Student Grades</h1>

    <!-- STUDENT INFO -->
    <div class="card mb-4">
        <div class="card-body">
            <h5 class="card-title"><?= htmlspecialchars($student['lastname'] . ', ' . $student['firstname']) ?></h5>
            <p class="mb-1"><strong>Username:</strong> <?= htmlspecialchars($username) ?></p>
            <p class="mb-1"><strong>Course:</strong> <?= htmlspecialchars($student['course']) ?></p>
            <p class="mb-1"><strong>Year:</strong> <?= htmlspecialchars($student['year']) ?></p>
            <p class="mb-0"><strong>Section:</strong> <?= htmlspecialchars($student['section'] ?? '-') ?></p>
        </div>
    </div>

    <!-- GRADES FORM -->
    <form method="post">
        <div class="card">
            <div class="card-body">
                <h5 class="card-title">Grades</h5>

                <table class="table table-striped table-bordered" id="gradesTable">
                    <thead class="table-dark">
                        <tr>
                            <th>Subject</th>
                            <th>Instructor</th>
                            <th>Prelim</th>
                            <th>Midterm</th>
                            <th>Finals</th>
                            <th>Average</th>
                            <th>Remarks</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($grades)): ?>
                            <?php foreach ($grades as $row): ?>
                                <tr>
                                    <td><?= htmlspecialchars($row['subject']) ?></td>
                                    <td><?= htmlspecialchars($row['instructor']) ?></td>
                                    <td><?= $row['prelim'] ?? '' ?></td>
                                    <td><?= $row['midterm'] ?? '' ?></td>
                                    <td><?= $row['finals'] ?? '' ?></td>
                                    <td><?= $row['average'] ?? '' ?></td>
                                    <td><?= htmlspecialchars($row['remarks']) ?></td>
                                    <td><!-- no action for existing grades --></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td><input type="text" name="grades[][subject]" class="form-control"></td>
                                <td><input type="text" name="grades[][instructor]" class="form-control"></td>
                                <td><input type="number" step="0.01" name="grades[][prelim]" class="form-control"></td>
                                <td><input type="number" step="0.01" name="grades[][midterm]" class="form-control"></td>
                                <td><input type="number" step="0.01" name="grades[][finals]" class="form-control"></td>
                                <td></td>
                                <td></td>
                                <td><button type="button" class="btn btn-danger btn-sm removeRow">Remove</button></td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>

                <button type="button" class="btn btn-info mb-2" id="addRow">Add Subject</button>
                <br>
                <button type="submit" class="btn btn-success">Save Grades</button>
                <a href="javascript:history.back()" class="btn btn-secondary">← Back</a>
            </div>
        </div>
    </form>
</div>

<script>
// Add new row dynamically
document.getElementById('addRow').addEventListener('click', function() {
    const tbody = document.getElementById('gradesTable').getElementsByTagName('tbody')[0];
    const row = tbody.insertRow();
    row.innerHTML = `
        <td><input type="text" name="grades[][subject]" class="form-control"></td>
        <td><input type="text" name="grades[][instructor]" class="form-control"></td>
        <td><input type="number" step="0.01" name="grades[][prelim]" class="form-control gradeInput"></td>
        <td><input type="number" step="0.01" name="grades[][midterm]" class="form-control gradeInput"></td>
        <td><input type="number" step="0.01" name="grades[][finals]" class="form-control gradeInput"></td>
        <td></td>
        <td></td>
        <td><button type="button" class="btn btn-danger btn-sm removeRow">Remove</button></td>
    `;
});

// Remove row
document.addEventListener('click', function(e) {
    if (e.target.classList.contains('removeRow')) {
        e.target.closest('tr').remove();
    }
});
</script>
