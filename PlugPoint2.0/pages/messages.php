<?php
include '../includes/header.php';
require_once '../includes/db.php';
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}
$user_id = $_SESSION['user_id'];
$selected_user = isset($_GET['user']) ? intval($_GET['user']) : null;
$selected_product = isset($_GET['product']) ? intval($_GET['product']) : null;

// Fetch all conversations (distinct user-product pairs)
$convos = $conn->query("SELECT 
    MAX(m.id) as last_msg_id,
    u.name as user_name, 
    p.name as product_name, 
    p.id as prod_id, 
    u.id as other_id,
    MAX(m.created_at) as last_msg_time
  FROM messages m
  JOIN users u ON (u.id = IF(m.sender_id = $user_id, m.receiver_id, m.sender_id))
  LEFT JOIN products p ON m.product_id = p.id
  WHERE m.sender_id = $user_id OR m.receiver_id = $user_id
  GROUP BY other_id, prod_id
  ORDER BY last_msg_time DESC");

// If a conversation is selected, fetch its messages
$messages = [];
if ($selected_user) {
    $where = "((sender_id = $user_id AND receiver_id = $selected_user) OR (sender_id = $selected_user AND receiver_id = $user_id))";
    if ($selected_product) {
        $where .= " AND product_id = $selected_product";
    }
    $msg_res = $conn->query("SELECT m.*, u.name as sender_name FROM messages m JOIN users u ON m.sender_id = u.id WHERE $where ORDER BY m.created_at ASC");
    while ($row = $msg_res && $msg_res->num_rows > 0 ? $msg_res->fetch_assoc() : false) {
        $messages[] = $row;
    }
    // Mark as read
    $conn->query("UPDATE messages SET is_read = 1 WHERE receiver_id = $user_id AND sender_id = $selected_user" . ($selected_product ? " AND product_id = $selected_product" : ""));
    // Handle sending a new message
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['message']) && trim($_POST['message'])) {
        $msg = $conn->real_escape_string($_POST['message']);
        $conn->query("INSERT INTO messages (sender_id, receiver_id, product_id, message) VALUES ($user_id, $selected_user, " . ($selected_product ?: 'NULL') . ", '$msg')");
        // Insert notification for receiver
        $sender_name = $conn->query("SELECT name FROM users WHERE id = $user_id")->fetch_assoc()['name'];
        $notif_content = $conn->real_escape_string("New message from $sender_name");
        $notif_link = $conn->real_escape_string("/PlugPoint2.0/PlugPoint2.0/pages/messages.php?user=$user_id" . ($selected_product ? "&product=$selected_product" : ""));
        $conn->query("INSERT INTO notifications (user_id, type, content, link) VALUES ($selected_user, 'message', '$notif_content', '$notif_link')");
        header("Location: messages.php?user=$selected_user" . ($selected_product ? "&product=$selected_product" : ""));
        exit();
    }
}
?>
<div class="container py-5">
  <div class="row">
    <div class="col-md-4">
      <div class="card h-100">
        <div class="card-header fw-bold">Conversations</div>
        <ul class="list-group list-group-flush">
          <?php if ($convos && $convos->num_rows > 0):
            $shown = [];
            while ($c = $convos->fetch_assoc()):
              $key = $c['other_id'] . '-' . ($c['prod_id'] ?? '0');
              if (in_array($key, $shown)) continue;
              $shown[] = $key;
              $active = ($selected_user == $c['other_id'] && $selected_product == $c['prod_id']);
          ?>
            <li class="list-group-item<?= $active ? ' active' : '' ?>">
              <a href="messages.php?user=<?= $c['other_id'] ?>&product=<?= $c['prod_id'] ?>" class="text-decoration-none<?= $active ? ' text-light' : '' ?>">
                <i class="bi bi-person"></i> <?= htmlspecialchars($c['user_name']) ?>
                <?php if ($c['product_name']): ?>
                  <br><span class="small text-muted"><i class="bi bi-box"></i> <?= htmlspecialchars($c['product_name']) ?></span>
                <?php endif; ?>
              </a>
            </li>
          <?php endwhile; else: ?>
            <li class="list-group-item text-muted">No conversations yet.</li>
          <?php endif; ?>
        </ul>
      </div>
    </div>
    <div class="col-md-8">
      <div class="card h-100">
        <div class="card-header fw-bold">Messages</div>
        <div class="card-body" style="height:400px; overflow-y:auto;">
          <?php if ($selected_user): ?>
            <?php if ($messages): ?>
              <?php foreach ($messages as $m): ?>
                <div class="mb-2 <?= $m['sender_id'] == $user_id ? 'text-end' : 'text-start' ?>">
                  <span class="badge bg-<?= $m['sender_id'] == $user_id ? 'primary' : 'secondary' ?>">
                    <?= htmlspecialchars($m['sender_name']) ?>:
                  </span>
                  <span><?= nl2br(htmlspecialchars($m['message'])) ?></span>
                  <div class="small text-muted"><?= date('Y-m-d H:i', strtotime($m['created_at'])) ?></div>
                </div>
              <?php endforeach; ?>
            <?php else: ?>
              <div class="text-muted">No messages yet. Say hello!</div>
            <?php endif; ?>
          <?php else: ?>
            <div class="text-muted">Select a conversation to view messages.</div>
          <?php endif; ?>
        </div>
        <?php if ($selected_user): ?>
        <form method="POST" class="card-footer d-flex gap-2">
          <input type="text" name="message" class="form-control" placeholder="Type a message..." required>
          <button type="submit" class="btn btn-primary"><i class="bi bi-send"></i></button>
        </form>
        <?php endif; ?>
      </div>
    </div>
  </div>
</div>
<?php include '../includes/footer.php'; ?> 