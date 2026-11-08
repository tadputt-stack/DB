<?php
include "db_conn.php";

$current_page = 'orders';

$order_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
if ($order_id <= 0) {
    header("Location: orders.php");
    exit();
}

// Fetch Order Info
$order = null;
$sql = "SELECT o.*, u.name as user_name, u.email as user_email, u.phone as user_phone 
        FROM orders o 
        LEFT JOIN users u ON o.user_id = u.id 
        WHERE o.order_id = ? 
        LIMIT 1";
$stmt = mysqli_prepare($conn, $sql);
if ($stmt) {
    mysqli_stmt_bind_param($stmt, "i", $order_id);
    mysqli_stmt_execute($stmt);
    $res = mysqli_stmt_get_result($stmt);
    if ($res) {
        $order = mysqli_fetch_assoc($res);
    }
    mysqli_stmt_close($stmt);
}

if (!$order) {
    $fallback_res = mysqli_query($conn, "SELECT * FROM orders WHERE order_id = " . intval($order_id));
    if ($fallback_res && mysqli_num_rows($fallback_res) > 0) {
        $order = mysqli_fetch_assoc($fallback_res);
    }
}

if (!$order) {
    header("Location: orders.php?msg=" . urlencode("ไม่พบคำสั่งซื้อรหัส #$order_id"));
    exit();
}

// Fetch Order Items
$items_sql = "SELECT oi.*, p.imageUrl 
              FROM order_items oi 
              LEFT JOIN products p ON oi.product_id = p.id 
              WHERE oi.order_id = ?";
$stmt_items = mysqli_prepare($conn, $items_sql);
mysqli_stmt_bind_param($stmt_items, "i", $order_id);
mysqli_stmt_execute($stmt_items);
$items_res = mysqli_stmt_get_result($stmt_items);

$raw_status = $order['status'];
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
?>
<!DOCTYPE html>
<html lang="th">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>OnlySport - ใบเสร็จและรายละเอียดคำสั่งซื้อ #<?php echo $order_id; ?></title>
  <link rel="stylesheet" href="style.css?v=<?php echo file_exists('style.css') ? filemtime('style.css') : time(); ?>">
  <style>
    @media print {
      .top-header-bar, .left-sidebar, .page-actions-group, .btn-no-print {
        display: none !important;
      }
      .container {
        margin: 0 !important;
        padding: 0 !important;
        max-width: 100% !important;
        width: 100% !important;
      }
      .card {
        border: none !important;
        box-shadow: none !important;
      }
    }
  </style>
</head>
<body>

<?php include "navbar.php"; ?>

<div class="container">
  <!-- Page Header Row -->
  <div class="page-header-row">
    <div>
      <h1 class="page-title">
        <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" style="color: var(--primary);">
          <path d="M16 11V7a4 4 0 0 0-8 0v4M5 9h14l1 12H4L5 9z"></path>
        </svg>
        รายละเอียดคำสั่งซื้อ #<?php echo $order_id; ?>
      </h1>
      <span class="page-subtitle" style="margin-left: 32px; display: block; margin-top: 2px;">
        ลูกค้า: <?php echo htmlspecialchars($order['customer_name']); ?> &bull; วันที่: <?php echo htmlspecialchars($order['created_at']); ?>
      </span>
    </div>

    <div class="page-actions-group btn-no-print">
      <button type="button" class="btn btn-outline btn-sm" onclick="window.print()">
        <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
          <polyline points="6 9 6 2 18 2 18 9"></polyline>
          <path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"></path>
          <rect x="6" y="14" width="12" height="8"></rect>
        </svg>
        พิมพ์ใบคำสั่งซื้อ
      </button>

      <a href="orders.php" class="btn btn-primary btn-sm">
        <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
        </svg>
        กลับหน้ารายการคำสั่งซื้อ
      </a>
    </div>
  </div>

  <div class="card">
    <div class="card-header">
      <div style="display: flex; align-items: center; gap: 10px;">
        <h2 class="card-title">สรุปข้อมูลการสั่งซื้อ</h2>
        <span class="status-pill <?php echo $status_class; ?>"><?php echo $selected_status; ?></span>
      </div>
      <div style="font-size: 13px; color: var(--text-muted);">
        Order SKU: <strong>#OS-<?php echo str_pad($order_id, 5, '0', STR_PAD_LEFT); ?></strong>
      </div>
    </div>

    <div class="card-body">
      <!-- Order Metadata & Customer Box -->
      <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 16px; background: var(--bg-subtle); padding: 18px; border-radius: var(--radius); border: 1px solid var(--border); margin-bottom: 24px;">
        <div>
          <div style="font-size: 11.5px; color: var(--text-muted); font-weight: 700; text-transform: uppercase;">ข้อมูลผู้สั่งซื้อ</div>
          <strong style="font-size: 14px; color: var(--text-main); display: block; margin-top: 3px;">
            <?php echo htmlspecialchars($order['customer_name']); ?>
          </strong>
          <?php if (!empty($order['user_id'])): ?>
            <span style="font-size: 12px; color: var(--text-light);">รหัสสมาชิก: #<?php echo $order['user_id']; ?></span>
          <?php endif; ?>
        </div>

        <div>
          <div style="font-size: 11.5px; color: var(--text-muted); font-weight: 700; text-transform: uppercase;">การติดต่อ</div>
          <div style="font-size: 13px; color: var(--text-main); margin-top: 3px;">
            📞 <?php echo htmlspecialchars($order['user_phone'] ?: '081-234-5678'); ?>
          </div>
          <div style="font-size: 12px; color: var(--text-muted);">
            ✉️ <?php echo htmlspecialchars($order['user_email'] ?: 'customer@onlysport.com'); ?>
          </div>
        </div>

        <div>
          <div style="font-size: 11.5px; color: var(--text-muted); font-weight: 700; text-transform: uppercase;">วันที่สั่งซื้อ</div>
          <div style="font-size: 13.5px; color: var(--text-main); font-weight: 600; margin-top: 3px;">
            <?php echo htmlspecialchars($order['created_at']); ?>
          </div>
        </div>

        <div>
          <div style="font-size: 11.5px; color: var(--text-muted); font-weight: 700; text-transform: uppercase;">ยอดชำระสุทธิ</div>
          <div style="font-size: 18px; color: var(--primary); font-weight: 800; margin-top: 2px;">
            ฿<?php echo number_format($order['total_price'], 2); ?>
          </div>
        </div>
      </div>

      <!-- Shipping Address Box -->
      <div style="background: #FFFFFF; border: 1px solid var(--border); border-radius: var(--radius); padding: 14px 18px; margin-bottom: 24px;">
        <div style="font-size: 12px; font-weight: 700; color: var(--text-muted); text-transform: uppercase; margin-bottom: 4px;">
          📍 ที่อยู่สำหรับจัดส่งสินค้า
        </div>
        <div style="font-size: 13.5px; color: var(--text-main);">
          123/45 ถนนพัฒนาการ แขวงสวนหลวง เขตสวนหลวง กรุงเทพมหานคร 10250 (จัดส่งด่วน OnlySport Express)
        </div>
      </div>

      <!-- Order Items Table -->
      <h3 style="font-size: 15px; font-weight: 700; color: var(--text-main); margin-bottom: 12px;">
        รายการสินค้าในคำสั่งซื้อ (Order Items)
      </h3>
      <div class="table-responsive" style="border: 1px solid var(--border); border-radius: var(--radius); overflow: hidden;">
        <table>
          <thead>
            <tr>
              <th style="width: 70px; text-align: center;">รูป</th>
              <th>ชื่อสินค้า</th>
              <th style="text-align: center; width: 100px;">ไซส์</th>
              <th style="text-align: center; width: 80px;">จำนวน</th>
              <th style="text-align: right; width: 130px;">ราคาต่อชิ้น</th>
              <th style="text-align: right; width: 140px;">ยอดรวม</th>
            </tr>
          </thead>
          <tbody>
            <?php 
              $subtotal_sum = 0;
              if ($items_res && mysqli_num_rows($items_res) > 0): 
                while ($item = mysqli_fetch_assoc($items_res)):
                  $clean_img = !empty($item['imageUrl']) ? str_replace('https://wsrv.nl/?url=', '', $item['imageUrl']) : '';
                  $subtotal_sum += (float)$item['subtotal'];
            ?>
              <tr>
                <td style="text-align: center;">
                  <?php if (!empty($clean_img)): ?>
                    <img src="<?php echo htmlspecialchars($clean_img); ?>" 
                         class="product-thumb-img" 
                         style="width: 44px; height: 44px; margin: 0 auto;" 
                         loading="lazy" 
                         referrerpolicy="no-referrer"
                         onerror="this.src='https://via.placeholder.com/44x44?text=Cleat';">
                  <?php else: ?>
                    <div class="product-thumb-empty" style="width: 44px; height: 44px; margin: 0 auto;">No Img</div>
                  <?php endif; ?>
                </td>
                <td>
                  <strong style="color: var(--text-main); font-size: 14px;"><?php echo htmlspecialchars($item['product_name']); ?></strong>
                </td>
                <td style="text-align: center;">
                  <span class="badge" style="background:#F1F5F9; color:#334155; border:1px solid #CBD5E1;">
                    <?php echo htmlspecialchars($item['size'] ?: 'US 9'); ?>
                  </span>
                </td>
                <td style="text-align: center;">
                  <strong><?php echo (int)$item['qty']; ?></strong>
                </td>
                <td style="text-align: right;">
                  ฿<?php echo number_format($item['price'], 2); ?>
                </td>
                <td style="text-align: right;">
                  <strong style="color: var(--primary); font-weight: 800;">
                    ฿<?php echo number_format($item['subtotal'], 2); ?>
                  </strong>
                </td>
              </tr>
            <?php 
                endwhile;
              else: 
            ?>
              <tr>
                <td colspan="6" style="text-align: center; padding: 24px; color: var(--text-muted);">
                  ไม่มีรายการสินค้าในคำสั่งซื้อนี้
                </td>
              </tr>
            <?php endif; ?>
          </tbody>
          <tfoot>
            <tr style="background: #F8FAFC;">
              <td colspan="5" style="text-align: right; font-weight: 700; color: var(--text-secondary);">ยอดรวมทั้งสิ้น (Grand Total):</td>
              <td style="text-align: right;">
                <strong style="font-size: 16px; color: var(--primary); font-weight: 800;">
                  ฿<?php echo number_format($order['total_price'], 2); ?>
                </strong>
              </td>
            </tr>
          </tfoot>
        </table>
      </div>
    </div>
  </div>
</div>

</body>
</html>
<?php 
mysqli_stmt_close($stmt_items);
mysqli_close($conn); 
?>

