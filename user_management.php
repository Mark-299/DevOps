<?php
session_start();
include 'config/plugins.php';
require 'config/dbcon.php';
$status = '';
if (isset($_SESSION['status'])) { $status = $_SESSION['status']; unset($_SESSION['status']); }
$sql = "SELECT * FROM accounts";
$result = $conn->query($sql);
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Users Management</title>
<link rel="stylesheet" href="styles/users.css">
</head>
<body>

<?php include __DIR__ . '/sidebar.php'; ?>

<div class="main container">
    <h1 class="mb-0">Users Management</h1>
    <p>Manage administrative user accounts for the system.</p>
<?php if($status): ?>
  <div style="margin-bottom:10px;color:green;"><?php echo htmlspecialchars($status); ?></div>
<?php endif; ?>

    <table>
        <thead>
            <tr>
                <th>#</th>
                <th>Username</th>
                <th>Password</th>
                <th>
                    <button id="addBtn" class="btn btn-success fs-6 add"><i class="fa-solid fa-user-plus"></i></button>
                </th>
            </tr>
        </thead>
        <tbody>
<?php
if ($result && $result->num_rows > 0) {
  $i = 1;
  while($row = $result->fetch_assoc()) {
    $uid = (int)$row['id'];
    $uname = htmlspecialchars($row['username'], ENT_QUOTES);
    echo "<tr>";
    echo "<td>". $i++ ."</td>";
    echo "<td>". $uname ."</td>";
    echo "<td>********</td>";
    echo "<td>\n <button class='btn btn-primary edit' data-id='".$uid."' data-username='".$uname."'><i class='fa-solid fa-pen-to-square'></i></button>\n   <button class='btn btn-danger delete' data-id='".$uid."'><i class='fa-solid fa-trash'></i></button>\n                </td>";
    echo "</tr>";
  }
} else {
  echo "<tr><td colspan='4'>No users found.</td></tr>";
}
?>
        </tbody>
    </table>

    <!-- Add User Modal -->
    <div id="addModal" style="display:none; position:fixed; z-index:999; left:0; top:0; width:100%; height:100%; overflow:auto; background-color:rgba(0,0,0,0.4);">
      <div style="background:#fff; margin:10% auto; padding:20px; border-radius:6px; width:360px; box-shadow:0 2px 10px rgba(0,0,0,.3);">
        <span id="closeModal" style="float:right; cursor:pointer; font-size:20px;">&times;</span>
        <h2>Add User</h2>
        <form id="addUserForm" method="POST" action="config/addAccount.php">
          <div style="margin-bottom:10px;"><label>Username</label><br>
            <div style="display:flex; align-items:center;">
              <input required id="usernameInput" type="text" name="username" placeholder="username" style="flex:1; padding:8px; box-sizing:border-box;">
              <span style="margin-left:8px; padding:8px 10px; background:#f1f1f1; border:1px solid #ddd; border-radius:4px;">@admin</span>
            </div>
            <small style="color:#666;">Only enter the part before <code>@admin</code>.</small>
          </div>
          <div style="margin-bottom:10px;"><label>Password</label><br><input required type="password" name="password" id="addPassword" style="width:100%; padding:8px; box-sizing:border-box;"></div>
          <div style="text-align:right;"><button type="submit" class="btn add">Add</button></div>
        </form>
      </div>
    </div>

    <!-- Edit User Modal -->
    <div id="editModal" style="display:none; position:fixed; z-index:999; left:0; top:0; width:100%; height:100%; overflow:auto; background-color:rgba(0,0,0,0.4);">
      <div style="background:#fff; margin:10% auto; padding:20px; border-radius:6px; width:360px; box-shadow:0 2px 10px rgba(0,0,0,.3);">
        <span id="closeEdit" style="float:right; cursor:pointer; font-size:20px;">&times;</span>
        <h2>Edit User</h2>
        <form id="editUserForm" method="POST" action="config/editAccount.php">
          <input type="hidden" name="id" id="editId">
          <div style="margin-bottom:10px;"><label>Username</label><br>
            <input required id="editUsername" type="text" name="username" placeholder="username" style="width:100%; padding:8px; box-sizing:border-box;">
            <small style="color:#666;">Suffix <code>@admin</code> will be ensured.</small>
          </div>
          <div style="margin-bottom:10px;"><label>New Password (leave blank to keep)</label><br><input type="password" name="password" id="editPassword" style="width:100%; padding:8px; box-sizing:border-box;"></div>
          <div style="text-align:right;"><button type="submit" class="btn edit">Save</button></div>
        </form>
      </div>
    </div>

    <form id="deleteForm" method="POST" action="config/deleteAccount.php" style="display:none;"><input type="hidden" name="id" id="deleteId"></form>

    <script>
    document.getElementById('addBtn').addEventListener('click', function(){ document.getElementById('addModal').style.display = 'block'; document.getElementById('usernameInput').focus(); });
    document.getElementById('closeModal').addEventListener('click', function(){ document.getElementById('addModal').style.display = 'none'; });
    document.getElementById('closeEdit').addEventListener('click', function(){ document.getElementById('editModal').style.display = 'none'; });
    window.addEventListener('click', function(e){ if(e.target == document.getElementById('addModal')) document.getElementById('addModal').style.display = 'none'; if(e.target == document.getElementById('editModal')) document.getElementById('editModal').style.display = 'none'; });

    // Ensure the username submitted always ends with @admin (Add)
    document.getElementById('addUserForm').addEventListener('submit', function(e){
      var inp = document.getElementById('usernameInput');
      var v = inp.value.trim();
      if (!v) { e.preventDefault(); return; }
      if (!v.toLowerCase().endsWith('@admin')) {
        inp.value = v + '@admin';
      }
    });

    // Edit button handler — only attach to row edit buttons (exclude modal Save button)
    document.querySelectorAll('.btn.edit[data-id]').forEach(function(btn){
      btn.addEventListener('click', function(e){
        var id = this.getAttribute('data-id');
        var username = this.getAttribute('data-username') || '';
        // remove suffix for display
        if (username.toLowerCase().endsWith('@admin')) username = username.slice(0, -6);
        document.getElementById('editId').value = id;
        document.getElementById('editUsername').value = username;
        document.getElementById('editPassword').value = '';
        document.getElementById('editModal').style.display = 'block';
        document.getElementById('editUsername').focus();
      });
    });

    // Delete button handler (confirmation)
    document.querySelectorAll('.btn.delete').forEach(function(btn){
      btn.addEventListener('click', function(e){
        var id = this.getAttribute('data-id');
        if (confirm('Are you sure you want to delete this user?')) {
          document.getElementById('deleteId').value = id;
          document.getElementById('deleteForm').submit();
        }
      });
    });
    </script>
</div>

</body>
</html>
