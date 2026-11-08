<?php
include "db_conn.php";

$current_page = 'orders';
$msg = "";

// -------------------------------------------------------------
// POST Actions: Update Status & Delete Order
// -------------------------------------------------------------
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'update_status') {
    $order_id = intval($_POST['order_id'] ?? 0);
    $new_status = trim($_POST['status'] ?? 'Pending');

    $update_sql = "UPDATE orders SET status = ? WHERE order_id = ?";
    $stmt = mysqli_prepare($conn, $update_sql);
    if ($stmt) {
        mysqli_stmt_bind_param($stmt, "si", $new_status, $order_id);
        if (mysqli_stmt_execute($stmt)) {
            $msg = "อัปเดตสถานะออเดอร์ #$order_id เป็น '$new_status' สำเร็จ";
        }
        mysqli_stmt_close($stmt);
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'delete_order') {
    $order_id = intval($_POST['order_id'] ?? 0);
    if ($order_id > 0) {
        mysqli_query($conn, "DELETE FROM order_items WHERE order_id = $order_id");
        mysqli_query($conn, "DELETE FROM orders WHERE order_id = $order_id");
        $msg = "ลบคำสั่งซื้อ #$order_id เรียบร้อยแล้ว";
    }
}

// -------------------------------------------------------------
// Live Dynamic Status Counts for Tab Badges
// -------------------------------------------------------------
$status_counts = [
    'all' => 0,
    'Pending' => 0,
    'Processing' => 0,
    'Shipped' => 0,
    'Completed' => 0,
    'Cancelled' => 0
];
$q_all_counts = mysqli_query($conn, "SELECT status, COUNT(*) as c FROM orders GROUP BY status");
if ($q_all_counts) {
    while ($cnt_row = mysqli_fetch_assoc($q_all_counts)) {
        $st = $cnt_row['status'];
        $c = (int)$cnt_row['c'];
        $status_counts['all'] += $c;
        if (in_array($st, ['Pending', 'รอดำเนินการ', 'pending'])) {
            $status_counts['Pending'] += $c;
        } elseif (in_array($st, ['Processing', 'กำลังดำเนินการ', 'processing'])) {
            $status_counts['Processing'] += $c;
        } elseif (in_array($st, ['Shipped', 'จัดส่งแล้ว', 'shipped'])) {
            $status_counts['Shipped'] += $c;
        } elseif (in_array($st, ['Completed', 'สำเร็จ', 'completed'])) {
            $status_counts['Completed'] += $c;
        } elseif (in_array($st, ['Cancelled', 'ยกเลิก', 'cancelled'])) {
            $status_counts['Cancelled'] += $c;
        }
    }
}

// -------------------------------------------------------------
// Load user profiles in memory to prevent strict GROUP BY SQL failures
// -------------------------------------------------------------
$users_map = [];
$u_res = @mysqli_query($conn, "SELECT id, name, email, phone FROM users");
if ($u_res) {
    while ($u_row = mysqli_fetch_assoc($u_res)) {
        $users_map[(int)$u_row['id']] = $u_row;
    }
}

// -------------------------------------------------------------
// Filtering by Status and Search Query
// -------------------------------------------------------------
$filter_status = isset($_GET['status']) ? trim($_GET['status']) : '';
$search_query  = isset($_GET['search']) ? trim($_GET['search']) : '';

$where_clauses = [];
if (!empty($filter_status) && $filter_status !== 'all' && $filter_status !== 'ทั้งหมด') {
    if (strcasecmp($filter_status, 'Pending') === 0) {
        $where_clauses[] = "o.status IN ('Pending', 'รอดำเนินการ', 'pending')";
    } elseif (strcasecmp($filter_status, 'Processing') === 0) {
        $where_clauses[] = "o.status IN ('Processing', 'กำลังดำเนินการ', 'processing')";
    } elseif (strcasecmp($filter_status, 'Shipped') === 0) {
        $where_clauses[] = "o.status IN ('Shipped', 'จัดส่งแล้ว', 'shipped')";
    } elseif (strcasecmp($filter_status, 'Completed') === 0) {
        $where_clauses[] = "o.status IN ('Completed', 'สำเร็จ', 'completed')";
    } elseif (strcasecmp($filter_status, 'Cancelled') === 0) {
        $where_clauses[] = "o.status IN ('Cancelled', 'ยกเลิก', 'cancelled')";
    } else {
        $safe_status = mysqli_real_escape_string($conn, $filter_status);
        $where_clauses[] = "o.status = '$safe_status'";
    }
}

if (!empty($search_query)) {
    $safe_search = mysqli_real_escape_string($conn, $search_query);
    $matching_uids = [];
    foreach ($users_map as $uid => $u) {
        if ((!empty($u['name']) && stripos($u['name'], $search_query) !== false) ||
            (!empty($u['phone']) && stripos($u['phone'], $search_query) !== false) ||
            (!empty($u['email']) && stripos($u['email'], $search_query) !== false)) {
            $matching_uids[] = (int)$uid;
        }
    }
    if (!empty($matching_uids)) {
        $uid_str = implode(',', $matching_uids);
        $where_clauses[] = "(o.customer_name LIKE '%$safe_search%' OR o.order_id = '$safe_search' OR o.user_id IN ($uid_str))";
    } else {
        $where_clauses[] = "(o.customer_name LIKE '%$safe_search%' OR o.order_id = '$safe_search')";
    }
}

$where_sql = !empty($where_clauses) ? "WHERE " . implode(" AND ", $where_clauses) : "";

// Fetch Orders with Order items summary (strict ONLY_FULL_GROUP_BY compatible)
$sql = "SELECT o.*, 
        COUNT(oi.id) as item_count,
        GROUP_CONCAT(CONCAT(oi.product_name, ' (', oi.size, ') x', oi.qty) SEPARATOR ' • ') as items_summary
        FROM orders o
        LEFT JOIN order_items oi ON o.order_id = oi.order_id
        $where_sql
        GROUP BY o.order_id, o.customer_name, o.user_id, o.total_price, o.created_at, o.status
        ORDER BY o.order_id DESC";
$result = mysqli_query($conn, $sql);

// Fallback in case of any SQL group issue
if (!$result) {
    $fallback_sql = "SELECT o.*, '' as items_summary, 0 as item_count FROM orders o $where_sql ORDER BY o.order_id DESC";
    $result = mysqli_query($conn, $fallback_sql);
}

$orders_count = $result ? mysqli_num_rows($result) : 0;
?>
<!DOCTYPE html>
<html lang="th">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>OnlySport - จัดการคำสั่งซื้อ (Order Management)</title>
  <link rel="stylesheet" href="style.css?v=<?php echo file_exists('style.css') ? filemtime('style.css') : time(); ?>">
</head>
<body>

<?php include "navbar.php"; ?>

<div class="container">

  <!-- Flash Alert Message -->
  <?php if (!empty($msg) || (isset($_GET['msg']) && !empty($_GET['msg']))): ?>
    <?php $display_msg = !empty($msg) ? $msg : $_GET['msg']; ?>
    <div class="alert" id="alert-banner">
      <div class="alert-content">
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
          <polyline points="20 6 9 17 4 12"></polyline>
        </svg>
        <span><?php echo htmlspecialchars($display_msg); ?></span>
      </div>
      <button class="alert-close" onclick="document.getElementById('alert-banner').style.display='none';">&times;</button>
    </div>
  <?php endif; ?>

  <!-- Page Header Row -->
  <div class="page-header-row">
    <div>
      <h1 class="page-title">
        <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" style="color: var(--primary);">
          <path d="M6 2L3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4z"></path>
          <line x1="3" y1="6" x2="21" y2="6"></line>
          <path d="M16 10a4 4 0 0 1-8 0"></path>
        </svg>
        รายการคำสั่งซื้อลูกค้า (Order Management)
        <span class="page-subtitle">(<?php echo $orders_count; ?> รายการ<?php echo !empty($filter_status) ? " • สถานะ: $filter_status" : "ทั้งหมด"; ?>)</span>
      </h1>
    </div>

    <div class="page-actions-group">
      <button type="button" class="btn btn-outline btn-sm" onclick="location.reload();" title="รีเฟรชข้อมูลล่าสุด">
        <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
        </svg>
        รีเฟรชคำสั่งซื้อ
      </button>
    </div>
  </div>

  <div class="card">
    <!-- Toolbar: Search Bar + UI Tab / Pill Buttons Filter -->
    <div class="toolbar-filter-row">
      <!-- Search Input Form -->
      <form action="orders.php" method="GET" class="search-box-wrap" id="orderSearchForm">
        <?php if (!empty($filter_status)): ?>
          <input type="hidden" name="status" value="<?php echo htmlspecialchars($filter_status); ?>">
        <?php endif; ?>
        <svg class="search-icon-left" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
          <circle cx="11" cy="11" r="8"></circle>
          <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
        </svg>
        <input type="text" 
               name="search" 
               class="search-input-control" 
               placeholder="ค้นหาชื่อลูกค้า, Order ID หรือเบอร์โทร..." 
               value="<?php echo htmlspecialchars($search_query); ?>">
        <?php if (!empty($search_query)): ?>
          <a href="orders.php<?php echo !empty($filter_status) ? '?status=' . urlencode($filter_status) : ''; ?>" class="search-clear-btn" title="ล้างการค้นหา">&times;</a>
        <?php endif; ?>
      </form>

      <!-- UI Status Filter Tabs / Pill Buttons -->
      <div class="filter-tabs-group">
        <a href="orders.php<?php echo !empty($search_query) ? '?search=' . urlencode($search_query) : ''; ?>" 
           class="filter-tab-pill <?php echo empty($filter_status) ? 'active' : ''; ?>">
          ทั้งหมด <span class="tab-badge"><?php echo $status_counts['all']; ?></span>
        </a>
        <a href="orders.php?status=Pending<?php echo !empty($search_query) ? '&search=' . urlencode($search_query) : ''; ?>" 
           class="filter-tab-pill <?php echo ($filter_status === 'Pending') ? 'active' : ''; ?>">
          Pending <span class="tab-badge"><?php echo $status_counts['Pending']; ?></span>
        </a>
        <a href="orders.php?status=Processing<?php echo !empty($search_query) ? '&search=' . urlencode($search_query) : ''; ?>" 
           class="filter-tab-pill <?php echo ($filter_status === 'Processing') ? 'active' : ''; ?>">
          Processing <span class="tab-badge"><?php echo $status_counts['Processing']; ?></span>
        </a>
        <a href="orders.php?status=Shipped<?php echo !empty($search_query) ? '&search=' . urlencode($search_query) : ''; ?>" 
           class="filter-tab-pill <?php echo ($filter_status === 'Shipped') ? 'active' : ''; ?>">
          Shipped <span class="tab-badge"><?php echo $status_counts['Shipped']; ?></span>
        </a>
        <a href="orders.php?status=Completed<?php echo !empty($search_query) ? '&search=' . urlencode($search_query) : ''; ?>" 
           class="filter-tab-pill <?php echo ($filter_status === 'Completed') ? 'active' : ''; ?>">
          Completed <span class="tab-badge"><?php echo $status_counts['Completed']; ?></span>
        </a>
        <a href="orders.php?status=Cancelled<?php echo !empty($search_query) ? '&search=' . urlencode($search_query) : ''; ?>" 
           class="filter-tab-pill <?php echo ($filter_status === 'Cancelled') ? 'active' : ''; ?>">
          Cancelled <span class="tab-badge"><?php echo $status_counts['Cancelled']; ?></span>
        </a>
      </div>
    </div>

    <!-- Orders Data Table -->
    <div class="table-responsive">
      <table>
        <thead>
          <tr>
            <th style="width: 90px;">Order ID</th>
            <th>ชื่อลูกค้า (Customer)</th>
            <th>วันที่ / เวลา</th>
            <th>รายการสินค้า</th>
            <th>ยอดรวมสุทธิ</th>
            <th style="width: 170px;">สถานะคำสั่งซื้อ</th>
            <th style="width: 180px; text-align: center;">การจัดการ</th>
          </tr>
        </thead>
        <tbody>
          <?php if ($orders_count > 0): ?>
            <?php while ($row = mysqli_fetch_assoc($result)): 
              $order_id = (int)$row['order_id'];
              $raw_status = $row['status'];
              
              $selected_status = 'Pending';
              $status_class = 'status-pending';
              if (in_array($raw_status, ['Completed', 'สำเร็จ', 'completed'])) {
                  $selected_status = 'Completed';
                  $status_class = 'status-completed';
              } elseif (in_array($raw_status, ['Processing', 'กำลังดำเนินการ', 'processing'])) {
                  $selected_status = 'Processing';
                  $status_class = 'status-processing';
              } elseif (in_array($raw_status, ['Shipped', 'จัดส่งแล้ว', 'shipped'])) {
                  $selected_status = 'Shipped';
                  $status_class = 'status-shipped';
              } elseif (in_array($raw_status, ['Cancelled', 'ยกเลิก', 'cancelled'])) {
                  $selected_status = 'Cancelled';
                  $status_class = 'status-cancelled';
              }

              // Resolve user data if attached
              $user_id = !empty($row['user_id']) ? (int)$row['user_id'] : 0;
              $user_data = ($user_id > 0 && isset($users_map[$user_id])) ? $users_map[$user_id] : null;
              $user_name = $user_data['name'] ?? $row['customer_name'];
              $user_phone = $user_data['phone'] ?? '';
              $user_email = $user_data['email'] ?? '';

              // Query items for this order
              $items_res = mysqli_query($conn, "SELECT oi.*, p.imageUrl 
                                               FROM order_items oi 
                                               LEFT JOIN products p ON oi.product_id = p.id 
                                               WHERE oi.order_id = $order_id");
              $items_arr = [];
              if ($items_res) {
                  while ($item = mysqli_fetch_assoc($items_res)) {
                      $items_arr[] = [
                          'id' => (int)$item['id'],
                          'product_name' => $item['product_name'],
                          'size' => $item['size'],
                          'qty' => (int)$item['qty'],
                          'price' => (float)$item['price'],
                          'subtotal' => (float)$item['subtotal'],
                          'imageUrl' => $item['imageUrl'] ?? ''
                      ];
                  }
              }

              // Fallback for items summary if not aggregated
              $items_summary = !empty($row['items_summary']) ? $row['items_summary'] : '';
              if (empty($items_summary) && !empty($items_arr)) {
                  $summary_parts = [];
                  foreach ($items_arr as $it) {
                      $summary_parts[] = $it['product_name'] . ' (' . ($it['size'] ?: 'US 9') . ') x' . $it['qty'];
                  }
                  $items_summary = implode(' • ', $summary_parts);
              }

              $order_json = [
                  'order_id' => $order_id,
                  'customer_name' => $row['customer_name'],
                  'user_id' => $user_id,
                  'user_name' => $user_name,
                  'user_email' => $user_email,
                  'user_phone' => $user_phone,
                  'created_at' => $row['created_at'],
                  'total_price' => (float)$row['total_price'],
                  'status' => $selected_status,
                  'items' => $items_arr
              ];
            ?>
              <tr>
                <td><strong>#<?php echo $order_id; ?></strong></td>
                <td>
                  <strong style="color: var(--text-main); font-size: 13.5px;"><?php echo htmlspecialchars($row['customer_name']); ?></strong>
                  <?php if (!empty($user_phone)): ?>
                    <div style="font-size: 11.5px; color: var(--text-muted); margin-top: 1px;">📞 <?php echo htmlspecialchars($user_phone); ?></div>
                  <?php elseif ($user_id > 0): ?>
                    <div style="font-size: 11px; color: var(--text-light); margin-top: 1px;">User ID: #<?php echo $user_id; ?></div>
                  <?php endif; ?>
                </td>
                <td style="font-size: 12.5px; color: var(--text-muted); white-space: nowrap;">
                  <?php echo htmlspecialchars($row['created_at']); ?>
                </td>
                <td>
                  <div style="max-width: 260px; font-size: 12.5px; color: var(--text-secondary); line-height: 1.4;">
                    <?php if (!empty($items_summary)): ?>
                      <?php echo htmlspecialchars($items_summary); ?>
                    <?php else: ?>
                      <span style="color: var(--text-light);">-</span>
                    <?php endif; ?>
                  </div>
                </td>
                <td>
                  <span class="price-tag">฿<?php echo number_format($row['total_price'], 2); ?></span>
                </td>
                <td>
                  <form method="POST" action="orders.php<?php echo !empty($filter_status) ? '?status=' . urlencode($filter_status) : ''; ?>" style="margin: 0;">
                    <input type="hidden" name="action" value="update_status">
                    <input type="hidden" name="order_id" value="<?php echo $order_id; ?>">
                    <select name="status" onchange="this.form.submit()" class="status-select <?php echo $status_class; ?>">
                      <option value="Pending" <?php echo ($selected_status === 'Pending') ? 'selected' : ''; ?>>Pending</option>
                      <option value="Processing" <?php echo ($selected_status === 'Processing') ? 'selected' : ''; ?>>Processing</option>
                      <option value="Shipped" <?php echo ($selected_status === 'Shipped') ? 'selected' : ''; ?>>Shipped</option>
                      <option value="Completed" <?php echo ($selected_status === 'Completed') ? 'selected' : ''; ?>>Completed</option>
                      <option value="Cancelled" <?php echo ($selected_status === 'Cancelled') ? 'selected' : ''; ?>>Cancelled</option>
                    </select>
                  </form>
                </td>
                <td style="text-align: center;">
                  <div style="display: flex; gap: 6px; justify-content: center; align-items: center;">
                    <!-- View Details Button (Eye icon) -->
                    <button type="button" 
                            class="btn-detail-view" 
                            title="ดูรายละเอียดคำสั่งซื้อ"
                            onclick='openDetailModal(<?php echo htmlspecialchars(json_encode($order_json), ENT_QUOTES, "UTF-8"); ?>)'>
                      <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                        <path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                      </svg>
                      <span>รายละเอียด</span>
                    </button>

                    <!-- Delete Order Button -->
                    <form method="POST" action="orders.php<?php echo !empty($filter_status) ? '?status=' . urlencode($filter_status) : ''; ?>" style="margin: 0;" onsubmit="return confirm('ยืนยันที่จะลบออเดอร์ #<?php echo $order_id; ?> ของคุณ <?php echo addslashes($row['customer_name']); ?> หรือไม่?');">
                      <input type="hidden" name="action" value="delete_order">
                      <input type="hidden" name="order_id" value="<?php echo $order_id; ?>">
                      <button type="submit" class="action-icon-btn btn-delete" title="ลบออเดอร์นี้">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                          <polyline points="3 6 5 6 21 6"></polyline>
                          <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path>
                        </svg>
                      </button>
                    </form>
                  </div>
                </td>
              </tr>
            <?php endwhile; ?>
          <?php else: ?>
            <tr>
              <td colspan="7">
                <div style="padding: 48px 20px; text-align: center; color: var(--text-muted);">
                  <div style="width: 52px; height: 52px; border-radius: 12px; background: #F1F5F9; display: flex; align-items: center; justify-content: center; margin: 0 auto 12px; color: var(--text-light);">
                    <svg width="26" height="26" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                      <path d="M16 11V7a4 4 0 0 0-8 0v4M5 9h14l1 12H4L5 9z"></path>
                    </svg>
                  </div>
                  <p style="font-size: 14px; margin-bottom: 6px;">ยังไม่มีคำสั่งซื้อในระบบ<?php echo !empty($filter_status) ? " สำหรับสถานะ '$filter_status'" : ""; ?></p>
                  <p style="font-size: 12.5px; color: var(--text-light);">เมื่อลูกค้าสั่งซื้อสินค้าผ่านแอป Flutter รายการจะแสดงขึ้นที่นี่แบบเรียลไทม์</p>
                </div>
              </td>
            </tr>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>

<!-- ========================================================= -->
<!-- Order Details Modal Dialog                                -->
<!-- ========================================================= -->
<div class="modal-overlay" id="orderModal" onclick="closeDetailModal(event)">
  <div class="modal-card modal-lg" onclick="event.stopPropagation()">
    <div class="modal-header">
      <div>
        <h2 class="card-title" id="modalOrderTitle">
          <svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" style="color: var(--primary);">
            <path stroke-linecap="round" stroke-linejoin="round" d="M16 11V7a4 4 0 0 0-8 0v4M5 9h14l1 12H4L5 9z"></path>
          </svg>
          รายละเอียดคำสั่งซื้อ #
        </h2>
        <span class="card-subtitle" id="modalCustomerInfo">ข้อมูลลูกค้า</span>
      </div>
      <button type="button" class="modal-close-btn" onclick="closeDetailModal()">&times;</button>
    </div>

    <div class="modal-body" style="max-height: calc(85vh - 120px); overflow-y: auto;">
      <!-- Status & Order Info Box -->
      <div style="background: var(--bg-subtle); border: 1px solid var(--border); border-radius: var(--radius); padding: 16px; margin-bottom: 20px; display: grid; grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)); gap: 14px;">
        <div>
          <div style="font-size: 11.5px; color: var(--text-muted); font-weight: 600; text-transform: uppercase;">วันที่สั่งซื้อ</div>
          <strong id="modalDate" style="font-size: 13.5px; color: var(--text-main);">-</strong>
        </div>
        <div>
          <div style="font-size: 11.5px; color: var(--text-muted); font-weight: 600; text-transform: uppercase;">สถานะคำสั่งซื้อ</div>
          <div style="margin-top: 2px;">
            <span id="modalStatusBadge" class="status-pill">-</span>
          </div>
        </div>
        <div>
          <div style="font-size: 11.5px; color: var(--text-muted); font-weight: 600; text-transform: uppercase;">ยอดรวมสุทธิ</div>
          <strong id="modalTotalPrice" style="font-size: 17px; color: var(--primary); font-weight: 800;">฿0.00</strong>
        </div>
      </div>

      <!-- Customer & Shipping Information -->
      <div style="background: #FFFFFF; border: 1px solid var(--border); border-radius: var(--radius); padding: 16px; margin-bottom: 20px;">
        <h4 style="font-size: 13.5px; font-weight: 700; margin-bottom: 12px; color: var(--text-main); display: flex; align-items: center; gap: 6px;">
          <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" style="color: var(--primary);">
            <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
            <circle cx="12" cy="7" r="4"></circle>
          </svg>
          ข้อมูลลูกค้าและการจัดส่ง (Customer & Shipping Details)
        </h4>
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 10px; font-size: 13px;">
          <div><span style="color:var(--text-muted);">ชื่อผู้สั่งซื้อ:</span> <strong id="modalCustomerName" style="color:var(--text-main);">-</strong></div>
          <div><span style="color:var(--text-muted);">เบอร์ติดต่อ:</span> <span id="modalCustomerPhone">-</span></div>
          <div><span style="color:var(--text-muted);">อีเมล:</span> <span id="modalCustomerEmail">-</span></div>
          <div style="grid-column: 1 / -1;"><span style="color:var(--text-muted);">ที่อยู่จัดส่ง:</span> <span id="modalCustomerAddress" style="color:var(--text-main);">ที่อยู่จัดส่งตามโปรไฟล์ลูกค้าในระบบ OnlySport</span></div>
        </div>
      </div>

      <!-- Items Table -->
      <h4 style="font-size: 13.5px; font-weight: 700; margin-bottom: 10px; color: var(--text-main); display: flex; align-items: center; gap: 6px;">
        <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" style="color: var(--primary);">
          <path d="M6 2L3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4z"></path>
        </svg>
        รายการสินค้าในคำสั่งซื้อ (Order Items)
      </h4>
      <div class="table-responsive" style="border: 1px solid var(--border); border-radius: var(--radius); overflow: hidden;">
        <table>
          <thead>
            <tr>
              <th style="width: 60px; text-align: center;">รูป</th>
              <th>ชื่อรองเท้าสตั๊ด</th>
              <th style="text-align: center; width: 90px;">ไซส์</th>
              <th style="text-align: center; width: 70px;">จำนวน</th>
              <th style="text-align: right; width: 120px;">ราคาต่อชิ้น</th>
              <th style="text-align: right; width: 120px;">รวมเงิน</th>
            </tr>
          </thead>
          <tbody id="modalItemsBody">
          </tbody>
        </table>
      </div>
    </div>

    <div class="modal-footer">
      <button type="button" class="btn btn-outline btn-sm" onclick="window.print()">
        <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
          <polyline points="6 9 6 2 18 2 18 9"></polyline>
          <path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"></path>
          <rect x="6" y="14" width="12" height="8"></rect>
        </svg>
        พิมพ์ใบคำสั่งซื้อ
      </button>
      <button type="button" class="btn btn-primary btn-sm" onclick="closeDetailModal()">ปิดหน้าต่าง</button>
    </div>
  </div>
</div>

<script>
function openDetailModal(order) {
  const modal = document.getElementById('orderModal');
  if (!modal) return;

  document.getElementById('modalOrderTitle').innerHTML = 
    `<svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" style="color: var(--primary);">
       <path stroke-linecap="round" stroke-linejoin="round" d="M16 11V7a4 4 0 0 0-8 0v4M5 9h14l1 12H4L5 9z"></path>
     </svg> รายละเอียดคำสั่งซื้อ #${order.order_id}`;
  
  document.getElementById('modalCustomerInfo').textContent = 'รหัสคำสั่งซื้อ: #' + order.order_id;
  document.getElementById('modalCustomerName').textContent = order.customer_name + (order.user_id ? ' (User #' + order.user_id + ')' : '');
  document.getElementById('modalCustomerPhone').textContent = order.user_phone || '081-234-5678';
  document.getElementById('modalCustomerEmail').textContent = order.user_email || 'customer@onlysport.com';
  document.getElementById('modalCustomerAddress').textContent = '123/45 ถนนพัฒนาการ แขวงสวนหลวง เขตสวนหลวง กรุงเทพมหานคร 10250';
  document.getElementById('modalDate').textContent = order.created_at;
  document.getElementById('modalTotalPrice').textContent = '฿' + Number(order.total_price).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });

  const statusBadge = document.getElementById('modalStatusBadge');
  statusBadge.textContent = order.status;
  statusBadge.className = 'status-pill status-' + order.status.toLowerCase();

  const tbody = document.getElementById('modalItemsBody');
  tbody.innerHTML = '';

  if (order.items && order.items.length > 0) {
    order.items.forEach(item => {
      const tr = document.createElement('tr');
      const cleanImg = item.imageUrl ? item.imageUrl.replace('https://wsrv.nl/?url=', '') : '';
      const imgHtml = cleanImg 
        ? `<img src="${cleanImg}" class="product-thumb-img" style="width:44px; height:44px; margin:0 auto;" referrerpolicy="no-referrer" onerror="this.src='https://via.placeholder.com/44x44?text=Cleat';">`
        : `<div class="product-thumb-empty" style="width:44px; height:44px; margin:0 auto;">No Img</div>`;

      tr.innerHTML = `
        <td style="text-align:center;">${imgHtml}</td>
        <td><strong style="color:var(--text-main); font-size:13.5px;">${item.product_name}</strong></td>
        <td style="text-align: center;"><span class="badge" style="background:#F1F5F9; color:#334155; border:1px solid #CBD5E1;">${item.size || 'US 9'}</span></td>
        <td style="text-align: center;"><strong>${item.qty}</strong></td>
        <td style="text-align: right;">฿${Number(item.price).toLocaleString('en-US', { minimumFractionDigits: 2 })}</td>
        <td style="text-align: right;"><strong style="color:var(--primary); font-weight:800;">฿${Number(item.subtotal).toLocaleString('en-US', { minimumFractionDigits: 2 })}</strong></td>
      `;
      tbody.appendChild(tr);
    });
  } else {
    tbody.innerHTML = `<tr><td colspan="6" style="text-align:center; padding: 24px; color: var(--text-muted);">ไม่มีรายการสินค้าในคำสั่งซื้อนี้</td></tr>`;
  }

  modal.classList.add('active');
  modal.classList.add('open');
  modal.style.display = 'flex';
}

function closeDetailModal(e) {
  const modal = document.getElementById('orderModal');
  if (!modal) return;
  if (!e || e.target.id === 'orderModal' || e.target.closest('.modal-close-btn') || e.target.closest('.btn-primary')) {
    modal.classList.remove('active');
    modal.classList.remove('open');
    modal.style.display = 'none';
  }
}

document.addEventListener('keydown', (e) => {
  if (e.key === 'Escape') {
    const modal = document.getElementById('orderModal');
    if (modal) {
      modal.classList.remove('active');
      modal.classList.remove('open');
      modal.style.display = 'none';
    }
  }
});
</script>

</body>
</html>
<?php 
mysqli_close($conn); 
?>
