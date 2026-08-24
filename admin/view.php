<?php
session_start();
require_once '../includes/config.php';

if (!isset($_SESSION['admin_id'])) {
    header('Location: login.php');
    exit;
}

$id = isset($_GET['id']) ? (int) $_GET['id'] : 0;

$stmt = $conn->prepare("
    SELECT complaints.*, users.name AS user_name, users.email AS user_email
    FROM complaints
    JOIN users ON complaints.user_id = users.id
    WHERE complaints.id = ?
");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    header('Location: dashboard.php');
    exit;
}

$c = $result->fetch_assoc();
$stmt->close();

// ---------- Build the timeline data ----------
$submitted = strtotime($c['date_submitted']);
$escalateAt = strtotime('+3 days', $submitted);
$now = time();
$resolved = $c['date_resolved'] ? strtotime($c['date_resolved']) : null;

$daysElapsed = floor(($now - $submitted) / 86400);
$daysUntilEscalation = max(0, floor(($escalateAt - $now) / 86400));

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
<title>Complaint #<?php echo $c['id']; ?> | Smart Complaint Management System</title>
<link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>

<div class="topbar">
  <div class="brand"><span class="dot"></span> Smart Complaint Desk</div>
  <a href="logout.php">Log Out</a>
</div>

<div class="page-wide">

  <a href="dashboard.php" class="back-link">&larr; Back to Dashboard</a>

  <div class="detail-grid">

    <div class="card detail-main">
      <div class="detail-header">
        <div>
          <div class="category-tag" style="margin-bottom:6px;"><?php echo htmlspecialchars($c['category']); ?></div>
          <h1 style="margin:0;"><?php echo 'Complaint #' . $c['id']; ?></h1>
        </div>
        <?php echo statusBadge($c['status']); ?>
      </div>

      <?php if ($c['is_escalated']): ?>
        <div class="alert alert-error" style="display:flex; align-items:center; gap:8px;">
          <span class="pulse" style="background:#B91C1C;"></span>
          This complaint has been escalated — it was left pending for more than 3 days.
        </div>
      <?php endif; ?>

      <div class="detail-field">
        <div class="detail-label">Description</div>
        <p class="detail-text"><?php echo nl2br(htmlspecialchars($c['description'])); ?></p>
      </div>

      <div class="detail-field">
        <div class="detail-label">Submitted by</div>
        <p class="detail-text"><?php echo htmlspecialchars($c['user_name']); ?> &middot; <?php echo htmlspecialchars($c['user_email']); ?></p>
      </div>

      <form action="dashboard.php" method="POST" class="detail-status-form">
        <input type="hidden" name="complaint_id" value="<?php echo $c['id']; ?>">
        <label for="new_status">Update status</label>
        <div style="display:flex; gap:8px;">
          <select name="new_status" id="new_status">
            <option value="Pending" <?php echo $c['status'] === 'Pending' ? 'selected' : ''; ?>>Pending</option>
            <option value="In Progress" <?php echo $c['status'] === 'In Progress' ? 'selected' : ''; ?>>In Progress</option>
            <option value="Resolved" <?php echo $c['status'] === 'Resolved' ? 'selected' : ''; ?>>Resolved</option>
          </select>
          <button type="submit" class="btn btn-primary" style="width:auto;">Save</button>
        </div>
      </form>
    </div>

    <div class="card detail-timeline">
      <h2 class="timeline-title">Escalation Timeline</h2>

      <div class="timeline">
        <div class="timeline-step done">
          <div class="timeline-dot"></div>
          <div class="timeline-content">
            <div class="timeline-label">Submitted</div>
            <div class="timeline-date"><?php echo date('d M Y, g:i A', $submitted); ?></div>
          </div>
        </div>

        <div class="timeline-step <?php echo $c['status'] !== 'Pending' ? 'done' : 'active'; ?>">
          <div class="timeline-dot"></div>
          <div class="timeline-content">
            <div class="timeline-label">Under Review</div>
            <div class="timeline-date">
              <?php if ($c['status'] === 'Pending'): ?>
                <?php echo $daysElapsed; ?> day<?php echo $daysElapsed !== 1 ? 's' : ''; ?> since submission
              <?php else: ?>
                Moved to <?php echo strtolower($c['status']); ?>
              <?php endif; ?>
            </div>
          </div>
        </div>

        <div class="timeline-step <?php echo $c['is_escalated'] ? 'done escalated' : ($resolved ? 'skipped' : ''); ?>">
          <div class="timeline-dot"></div>
          <div class="timeline-content">
            <div class="timeline-label">Escalation Threshold (3 days)</div>
            <div class="timeline-date">
              <?php if ($c['is_escalated']): ?>
                Escalated automatically
              <?php elseif ($resolved): ?>
                Resolved before threshold — no escalation needed
              <?php else: ?>
                <?php echo $daysUntilEscalation; ?> day<?php echo $daysUntilEscalation !== 1 ? 's' : ''; ?> remaining before auto-escalation
              <?php endif; ?>
            </div>
          </div>
        </div>

        <div class="timeline-step <?php echo $c['status'] === 'Resolved' ? 'done resolved' : ''; ?>">
          <div class="timeline-dot"></div>
          <div class="timeline-content">
            <div class="timeline-label">Resolved</div>
            <div class="timeline-date">
              <?php echo $resolved ? date('d M Y, g:i A', $resolved) : 'Not yet resolved'; ?>
            </div>
          </div>
        </div>
      </div>
    </div>

  </div>
</div>

</body>
</html>
<?php $conn->close(); ?>
