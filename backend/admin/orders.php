<?php
include "db_conn.php";

$current_page = 'orders';
$msg = "";

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

$filter_status = isset($_GET['status']) ? trim($_GET['status']) : '';
$where_clauses = [];
if (!empty($filter_status)) {
    $safe_status = mysqli_real_escape_string($conn, $filter_status);
    $where_clauses[] = "o.status = '$safe_status'";
}
$where_sql = !empty($where_clauses) ? "WHERE " . implode(" AND ", $where_clauses) : "";

$pending_res = mysqli_query($conn, "SELECT COUNT(*) as c FROM orders WHERE status IN ('Pending', 'รอดำเนินการ', 'pending', 'Processing', 'กำลังดำเนินการ')");
$pending_count = $pending_res ? (int)mysqli_fetch_assoc($pending_res)['c'] : 0;

$sql = "SELECT o.*, 
        COUNT(oi.id) as item_count,
        GROUP_CONCAT(CONCAT(oi.product_name, ' (', oi.size, ') x', oi.qty) SEPARATOR ' • ') as items_summary
        FROM orders o
        LEFT JOIN order_items oi ON o.order_id = oi.order_id
        $where_sql
        GROUP BY o.order_id
        ORDER BY o.order_id DESC";
$result = mysqli_query($conn, $sql);
$orders_count = $result ? mysqli_num_rows($result) : 0;
?>
<!DOCTYPE html>
<html lang="th">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>OnlySport - จัดการคำสั่งซื้อ</title>
  <link rel="stylesheet" href="style.css?v=<?php echo file_exists('style.css') ? filemtime('style.css') : time(); ?>">
</head>
<body>

<div class="container">
  <?php include "navbar.php"; ?>

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

  <div class="card">
    <div class="card-header">
      <div>
        <h1 class="card-title">
          รายการคำสั่งซื้อ
          <span class="card-subtitle">(<?php echo $orders_count; ?> รายการทั้งหมด)</span>
        </h1>
      </div>

      <div style="display: flex; gap: 6px; align-items: center; flex-wrap: wrap;">
        <a href="orders.php" class="filter-btn <?php echo empty($filter_status) ? 'active' : ''; ?>">ทั้งหมด</a>
        <a href="orders.php?status=Pending" class="filter-btn <?php echo ($filter_status === 'Pending') ? 'active' : ''; ?>">Pending</a>
        <a href="orders.php?status=Processing" class="filter-btn <?php echo ($filter_status === 'Processing') ? 'active' : ''; ?>">Processing</a>
        <a href="orders.php?status=Shipped" class="filter-btn <?php echo ($filter_status === 'Shipped') ? 'active' : ''; ?>">Shipped</a>
        <a href="orders.php?status=Completed" class="filter-btn <?php echo ($filter_status === 'Completed') ? 'active' : ''; ?>">Completed</a>
        <a href="orders.php?status=Cancelled" class="filter-btn <?php echo ($filter_status === 'Cancelled') ? 'active' : ''; ?>">Cancelled</a>
      </div>
    </div>

    <div class="table-responsive">
      <table>
        <thead>
          <tr>
            <th style="width: 80px;">Order ID</th>
            <th>ชื่อลูกค้า</th>
            <th>วันที่ / เวลา</th>
            <th>ยอดรวม (฿)</th>
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

              $order_json = [
                  'order_id' => $order_id,
                  'customer_name' => $row['customer_name'],
                  'user_id' => $row['user_id'],
                  'created_at' => $row['created_at'],
                  'total_price' => (float)$row['total_price'],
                  'status' => $selected_status,
                  'items' => $items_arr
              ];
            ?>
              <tr>
                <td><strong>#<?php echo $order_id; ?></strong></td>
                <td>
                  <strong style="color: var(--text-main);"><?php echo htmlspecialchars($row['customer_name']); ?></strong>
                  <?php if (!empty($row['user_id'])): ?>
                    <span style="font-size: 11px; color: var(--text-muted); display: block;">User ID: #<?php echo $row['user_id']; ?></span>
                  <?php endif; ?>
                </td>
                <td style="font-size: 13px; color: var(--text-muted); white-space: nowrap;">
                  <?php echo htmlspecialchars($row['created_at']); ?>
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
                  <div style="display: flex; gap: 6px; justify-content: center;">
                    <button type="button" 
                            class="btn btn-sm btn-view" 
                            onclick='openDetailModal(<?php echo htmlspecialchars(json_encode($order_json), ENT_QUOTES, "UTF-8"); ?>)'>
                      <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
                        <circle cx="12" cy="12" r="3"></circle>
                      </svg>
                      <span>รายละเอียด</span>
                    </button>

                    <form method="POST" action="orders.php<?php echo !empty($filter_status) ? '?status=' . urlencode($filter_status) : ''; ?>" style="margin: 0;" onsubmit="return confirm('ยืนยันที่จะลบออเดอร์ #<?php echo $order_id; ?> ของคุณ <?php echo addslashes($row['customer_name']); ?> หรือไม่?');">
                      <input type="hidden" name="action" value="delete_order">
                      <input type="hidden" name="order_id" value="<?php echo $order_id; ?>">
                      <button type="submit" class="btn btn-sm btn-delete" title="ลบออเดอร์นี้">
                        <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                          <polyline points="3 6 5 6 21 6"></polyline>
                          <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path>
                        </svg>
                        <span>ลบ</span>
                      </button>
                    </form>
                  </div>
                </td>
              </tr>
            <?php endwhile; ?>
          <?php else: ?>
            <tr>
              <td colspan="6">
                <div class="empty-state">
                  <div class="empty-state-icon">
                    <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                      <path d="M16 11V7a4 4 0 0 0-8 0v4M5 9h14l1 12H4L5 9z"></path>
                    </svg>
                  </div>
                  <p>ยังไม่มีคำสั่งซื้อในระบบ<?php echo !empty($filter_status) ? " สำหรับสถานะ '$filter_status'" : ""; ?></p>
                  <p style="font-size: 13px; margin-top: 6px; color: var(--text-light);">เมื่อสั่งซื้อสินค้าผ่านแอป Flutter รายการจะแสดงขึ้นที่นี่แบบเรียลไทม์</p>
                </div>
              </td>
            </tr>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>

<div class="modal-overlay" id="orderModal" onclick="closeDetailModal(event)">
  <div class="modal-card" onclick="event.stopPropagation()">
    <div class="modal-header">
      <div>
        <h2 class="card-title" id="modalOrderTitle">
          รายละเอียดคำสั่งซื้อ #
        </h2>
        <span class="card-subtitle" id="modalCustomerInfo">ลูกค้า: </span>
      </div>
      <button class="alert-close" onclick="closeDetailModal()">&times;</button>
    </div>

    <div class="modal-body">
      <div style="background: var(--bg); border: 1px solid var(--border); border-radius: var(--radius); padding: 14px 16px; margin-bottom: 18px; display: flex; justify-content: space-between; flex-wrap: wrap; gap: 10px;">
        <div>
          <div style="font-size: 12px; color: var(--text-muted); font-weight: 600;">วันที่สั่งซื้อ:</div>
          <strong id="modalDate" style="font-size: 13.5px; color: var(--text-main);">-</strong>
        </div>
        <div>
          <div style="font-size: 12px; color: var(--text-muted); font-weight: 600;">สถานะปัจจุบัน:</div>
          <span id="modalStatusBadge" class="badge">-</span>
        </div>
        <div>
          <div style="font-size: 12px; color: var(--text-muted); font-weight: 600;">ยอดรวมทั้งสิ้น:</div>
          <strong id="modalTotalPrice" style="font-size: 16px; color: var(--primary); font-weight: 800;">฿0.00</strong>
        </div>
      </div>

      <h3 style="font-size: 14px; font-weight: 700; margin-bottom: 10px; color: var(--text-main);">รายการสินค้าที่สั่งซื้อ:</h3>
      <div class="table-responsive" style="border: 1px solid var(--border); border-radius: var(--radius); overflow: hidden;">
        <table>
          <thead>
            <tr>
              <th style="width: 50px;">รูป</th>
              <th>ชื่อสินค้า</th>
              <th style="text-align: center;">ไซส์</th>
              <th style="text-align: center;">จำนวน</th>
              <th style="text-align: right;">ราคา/ชิ้น</th>
              <th style="text-align: right;">รวม</th>
            </tr>
          </thead>
          <tbody id="modalItemsBody">
          </tbody>
        </table>
      </div>
    </div>

    <div class="modal-footer">
      <button type="button" class="btn btn-secondary" onclick="closeDetailModal()">ปิดหน้าต่าง</button>
    </div>
  </div>
</div>

<script>
function openDetailModal(order) {
  document.getElementById('modalOrderTitle').textContent = 'รายละเอียดคำสั่งซื้อ #' + order.order_id;
  document.getElementById('modalCustomerInfo').textContent = 'ลูกค้า: ' + order.customer_name + (order.user_id ? ' (User #' + order.user_id + ')' : '');
  document.getElementById('modalDate').textContent = order.created_at;
  document.getElementById('modalTotalPrice').textContent = '฿' + Number(order.total_price).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });

  const statusBadge = document.getElementById('modalStatusBadge');
  statusBadge.textContent = order.status;
  statusBadge.className = 'badge status-' + order.status.toLowerCase();

  const tbody = document.getElementById('modalItemsBody');
  tbody.innerHTML = '';

  if (order.items && order.items.length > 0) {
    order.items.forEach(item => {
      const tr = document.createElement('tr');
      const imgHtml = item.imageUrl 
        ? `<img src="${item.imageUrl}" class="product-img" style="width:40px; height:40px;" onerror="this.src='https://via.placeholder.com/40';">`
        : `<div class="product-img-empty" style="width:40px; height:40px;">No Img</div>`;

      tr.innerHTML = `
        <td>${imgHtml}</td>
        <td><strong style="color:var(--text-main);">${item.product_name}</strong></td>
        <td style="text-align: center;"><span class="badge" style="background:#f1f5f9; color:#334155; border:1px solid #cbd5e1;">${item.size}</span></td>
        <td style="text-align: center;"><strong>${item.qty}</strong></td>
        <td style="text-align: right;">฿${Number(item.price).toLocaleString('en-US', { minimumFractionDigits: 2 })}</td>
        <td style="text-align: right;"><strong style="color:var(--primary); font-weight:800;">฿${Number(item.subtotal).toLocaleString('en-US', { minimumFractionDigits: 2 })}</strong></td>
      `;
      tbody.appendChild(tr);
    });
  } else {
    tbody.innerHTML = `<tr><td colspan="6" style="text-align:center; padding: 20px; color: var(--text-muted);">ไม่มีรายการสินค้า</td></tr>`;
  }

  document.getElementById('orderModal').classList.add('open');
}

function closeDetailModal(e) {
  if (!e || e.target.id === 'orderModal' || e.target.closest('.alert-close') || e.target.closest('.btn-secondary')) {
    document.getElementById('orderModal').classList.remove('open');
  }
}

document.addEventListener('keydown', (e) => {
  if (e.key === 'Escape') {
    document.getElementById('orderModal').classList.remove('open');
  }
});
</script>

</body>
</html>
<?php 
mysqli_close($conn); 
?>
