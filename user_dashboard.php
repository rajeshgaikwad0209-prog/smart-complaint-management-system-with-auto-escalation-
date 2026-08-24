<?php
session_start();
require_once 'includes/config.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$userId = $_SESSION['user_id'];
$userName = $_SESSION['user_name'];

$stmt = $conn->prepare("SELECT * FROM complaints WHERE user_id = ? ORDER BY date_submitted DESC");
$stmt->bind_param("i", $userId);
$stmt->execute();
$complaints = $stmt->get_result();
$total = $complaints->num_rows;

function statusBadge($status) {
    $map = ['Pending' => 'badge-pending', 'In Progress' => 'badge-progress', 'Resolved' => 'badge-resolved'];
    $class = $map[$status] ?? 'badge-pending';
    return "<span class=\"badge $class\">$status</span>";
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>My Complaints | Smart Complaint Management System</title>
<link rel="stylesheet" href="assets/css/style.css">
</head>
<body>

<div class="topbar">
  <div class="brand"><span class="dot"></span> Smart Complaint Desk</div>
  <a href="logout.php">Log Out</a>
</div>

<div class="page-wide">

  <div class="card" style="margin-bottom:24px; display:flex; align-items:center; justify-content:space-between; flex-wrap:wrap; gap:16px;">
    <div>
      <h1 style="margin:0 0 4px;">Welcome, <?php echo htmlspecialchars($userName); ?> 👋</h1>
      <p class="subtitle" style="margin:0;">Report and track your complaints from one place.</p>
    </div>
    <a href="report_incident.php" class="btn btn-primary" style="width:auto;">+ Report Incident</a>
  </div>

  <div class="stats" style="grid-template-columns: 1fr;">
    <div class="stat-card">
      <div class="label">My Complaints</div>
      <div class="value"><?php echo $total; ?></div>
    </div>
  </div>

  <div class="table-card">
    <table>
      <thead>
        <tr>
          <th>Complaint</th>
          <th>Category</th>
          <th>Date</th>
          <th>Status</th>
        </tr>
      </thead>
      <tbody>
        <?php if ($total === 0): ?>
          <tr><td colspan="4" style="text-align:center; color:#64748B; padding:48px 20px;">
            No complaints yet. Click "+ Report Incident" above to submit your first one.
          </td></tr>
        <?php endif; ?>

        <?php while ($row = $complaints->fetch_assoc()): ?>
          <tr class="<?php echo $row['is_escalated'] ? 'row-escalated' : ''; ?>">
            <td data-label="Complaint">
              <?php echo htmlspecialchars($row['description']); ?>
              <?php if ($row['is_escalated']): ?>
                <div style="margin-top:6px;">
                  <span class="badge badge-escalated"><span class="pulse"></span> Escalated</span>
                </div>
              <?php endif; ?>
            </td>
            <td data-label="Category"><span class="category-tag"><?php echo htmlspecialchars($row['category']); ?></span></td>
            <td data-label="Date"><?php echo date('d M Y', strtotime($row['date_submitted'])); ?></td>
            <td data-label="Status"><?php echo statusBadge($row['status']); ?></td>
          </tr>
        <?php endwhile; ?>
      </tbody>
    </table>
  </div>

</div>

</body>
</html>
<?php $stmt->close(); $conn->close(); ?>
