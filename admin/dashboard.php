<?php
session_start();
require_once '../includes/config.php';

// Require login
if (!isset($_SESSION['admin_id'])) {
    header('Location: login.php');
    exit;
}

// ---------- AUTO-ESCALATION LOGIC ----------
// Any complaint still "Pending" for more than 3 days gets flagged as escalated.
// This runs automatically every time the dashboard loads.
$escalateDays = 3;
$conn->query("
    UPDATE complaints
    SET is_escalated = 1
    WHERE status = 'Pending'
      AND is_escalated = 0
      AND date_submitted <= DATE_SUB(NOW(), INTERVAL $escalateDays DAY)
");

// ---------- DEMO: force-escalate a complaint instantly (for testing/demo only) ----------
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['force_escalate_id'])) {
    $id = (int) $_POST['force_escalate_id'];
    $stmt = $conn->prepare("UPDATE complaints SET is_escalated = 1, date_submitted = DATE_SUB(NOW(), INTERVAL 4 DAY) WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $stmt->close();
    header('Location: dashboard.php');
    exit;
}

// ---------- HANDLE STATUS UPDATES ----------
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['complaint_id'], $_POST['new_status'])) {
    $id = (int) $_POST['complaint_id'];
    $newStatus = $_POST['new_status'];
    $validStatuses = ['Pending', 'In Progress', 'Resolved'];

    if (in_array($newStatus, $validStatuses)) {
        if ($newStatus === 'Resolved') {
            // Clear escalation flag and stamp resolved date
            $stmt = $conn->prepare("UPDATE complaints SET status = ?, is_escalated = 0, date_resolved = NOW() WHERE id = ?");
        } else {
            $stmt = $conn->prepare("UPDATE complaints SET status = ? WHERE id = ?");
        }
        $stmt->bind_param("si", $newStatus, $id);
        $stmt->execute();
        $stmt->close();
    }
    header('Location: dashboard.php');
    exit;
}

// ---------- FETCH STATS ----------
$stats = ['total' => 0, 'pending' => 0, 'resolved' => 0, 'escalated' => 0];

$res = $conn->query("SELECT COUNT(*) AS c FROM complaints");
$stats['total'] = $res->fetch_assoc()['c'];

$res = $conn->query("SELECT COUNT(*) AS c FROM complaints WHERE status = 'Pending'");
$stats['pending'] = $res->fetch_assoc()['c'];

$res = $conn->query("SELECT COUNT(*) AS c FROM complaints WHERE status = 'Resolved'");
$stats['resolved'] = $res->fetch_assoc()['c'];

$res = $conn->query("SELECT COUNT(*) AS c FROM complaints WHERE is_escalated = 1");
$stats['escalated'] = $res->fetch_assoc()['c'];

// ---------- FETCH COMPLAINTS (escalated first, then newest) — joined with users ----------
$complaints = $conn->query("
    SELECT complaints.*, users.name AS user_name, users.email AS user_email
    FROM complaints
    JOIN users ON complaints.user_id = users.id
    ORDER BY complaints.is_escalated DESC, complaints.date_submitted DESC
");

function statusBadge($status) {
    $map = [
        'Pending' => 'badge-pending',
        'In Progress' => 'badge-progress',
        'Resolved' => 'badge-resolved',
    ];
    $class = $map[$status] ?? 'badge-pending';
    return "<span class=\"badge $class\">$status</span>";
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Dashboard | Smart Complaint Management System</title>
<link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>

<div class="topbar">
  <div class="brand"><span class="dot"></span> Smart Complaint Desk</div>
  <div>
    <span style="color:#CBD5E1; font-size:0.88rem; margin-right:14px;">
      Signed in as <strong style="color:#fff;"><?php echo htmlspecialchars($_SESSION['admin_name']); ?></strong>
    </span>
    <a href="logout.php">Log Out</a>
  </div>
</div>

<div class="page-wide">

  <div class="card" style="margin-bottom:24px;">
    <h1 style="margin:0 0 4px;">Welcome, <?php echo htmlspecialchars($_SESSION['admin_name']); ?> 👋</h1>
    <p class="subtitle" style="margin:0;">Monitor, review, and manage all reported complaints.</p>
  </div>

  <div class="stats">
    <div class="stat-card">
      <div class="label">Total Complaints</div>
      <div class="value"><?php echo $stats['total']; ?></div>
    </div>
    <div class="stat-card pending">
      <div class="label">Pending</div>
      <div class="value"><?php echo $stats['pending']; ?></div>
    </div>
    <div class="stat-card resolved">
      <div class="label">Resolved</div>
      <div class="value"><?php echo $stats['resolved']; ?></div>
    </div>
    <div class="stat-card escalated">
      <div class="label">Escalated</div>
      <div class="value"><?php echo $stats['escalated']; ?></div>
    </div>
  </div>

  <div class="table-card">
    <table>
      <thead>
        <tr>
          <th>Complaint</th>
          <th>Category</th>
          <th>Submitted by</th>
          <th>Date</th>
          <th>Status</th>
          <th>Action</th>
        </tr>
      </thead>
      <tbody>
        <?php if ($complaints->num_rows === 0): ?>
          <tr><td colspan="6" style="text-align:center; color:#64748B; padding:48px 20px;">
            No complaints yet. Once someone submits one from the public form, it'll show up here.
          </td></tr>
        <?php endif; ?>

        <?php while ($row = $complaints->fetch_assoc()): ?>
          <tr class="<?php echo $row['is_escalated'] ? 'row-escalated' : ''; ?>">
            <td data-label="Complaint">
              <a href="view.php?id=<?php echo $row['id']; ?>" class="complaint-link">
                <?php echo htmlspecialchars($row['description']); ?>
              </a>
              <?php if ($row['is_escalated']): ?>
                <div style="margin-top:6px;">
                  <span class="badge badge-escalated"><span class="pulse"></span> Escalated</span>
                </div>
              <?php endif; ?>
            </td>
            <td data-label="Category"><span class="category-tag"><?php echo htmlspecialchars($row['category']); ?></span></td>
            <td data-label="Submitted by">
              <?php echo htmlspecialchars($row['user_name']); ?><br>
              <span style="color:#94A3B8; font-size:0.8rem;"><?php echo htmlspecialchars($row['user_email']); ?></span>
            </td>
            <td data-label="Date"><?php echo date('d M Y', strtotime($row['date_submitted'])); ?></td>
            <td data-label="Status"><?php echo statusBadge($row['status']); ?></td>
            <td data-label="Action">
              <form action="dashboard.php" method="POST" style="display:flex; gap:6px; flex-wrap:wrap;">
                <input type="hidden" name="complaint_id" value="<?php echo $row['id']; ?>">
                <select name="new_status" style="padding:6px 8px; border-radius:6px; border:1.5px solid #E2E8F0; font-size:0.8rem;">
                  <option value="Pending" <?php echo $row['status'] === 'Pending' ? 'selected' : ''; ?>>Pending</option>
                  <option value="In Progress" <?php echo $row['status'] === 'In Progress' ? 'selected' : ''; ?>>In Progress</option>
                  <option value="Resolved" <?php echo $row['status'] === 'Resolved' ? 'selected' : ''; ?>>Resolved</option>
                </select>
                <button type="submit" class="btn btn-primary btn-sm" style="width:auto;">Update</button>
              </form>
              <?php if (!$row['is_escalated'] && $row['status'] !== 'Resolved'): ?>
              <form action="dashboard.php" method="POST" style="margin-top:6px;">
                <input type="hidden" name="force_escalate_id" value="<?php echo $row['id']; ?>">
                <button type="submit" class="btn btn-sm" style="width:auto; background:#FEE2E2; color:#B91C1C; border:1px solid #FECACA;">
                  Force escalate (demo)
                </button>
              </form>
              <?php endif; ?>
            </td>
          </tr>
        <?php endwhile; ?>
      </tbody>
    </table>
  </div>

</div>

</body>
</html>
<?php $conn->close(); ?>
