<?php
include "db_conn.php";

$current_page = 'dashboard';

function get_bestseller_rating_status($rank, $product) {
    $rating = !empty($product['rating']) ? floatval($product['rating']) : null;
    $status = !empty($product['status']) ? trim($product['status']) : null;

    if ($rating === null) {
        $preset_ratings = [1 => 5.0, 2 => 4.9, 3 => 4.9, 4 => 4.8, 5 => 4.7];
        $rating = $preset_ratings[$rank] ?? 4.8;
    }

    if ($status === null) {
        $preset_statuses = [
            1 => ['label' => 'ยอดนิยม', 'class' => 'status-pill-hot'],
            2 => ['label' => 'ขายดี', 'class' => 'status-pill-ready'],
            3 => ['label' => 'แนะนำ', 'class' => 'status-pill-recommended'],
            4 => ['label' => 'พร้อมส่ง', 'class' => 'rank-other'],
            5 => ['label' => 'พร้อมส่ง', 'class' => 'rank-other'],
        ];
        $status_item = $preset_statuses[$rank] ?? ['label' => 'พร้อมส่ง', 'class' => 'rank-other'];
    } else {
        $status_item = ['label' => $status, 'class' => 'rank-other'];
    }

    return [
        'rating' => number_format($rating, 1),
        'status' => $status_item['label'],
        'status_class' => $status_item['class'],
    ];
}

$order_stats_res = mysqli_query($conn, "SELECT 
    COUNT(*) as total_orders,
    COALESCE(SUM(total_price), 0) as total_revenue
FROM orders");
$order_stats = ($order_stats_res) ? mysqli_fetch_assoc($order_stats_res) : ['total_orders' => 0, 'total_revenue' => 0];

$total_revenue = (float)($order_stats['total_revenue'] ?? 0);
$total_orders = (int)($order_stats['total_orders'] ?? 0);

$prod_count_res = mysqli_query($conn, "SELECT COUNT(*) as cnt FROM products");
$total_products = ($prod_count_res) ? (int)mysqli_fetch_assoc($prod_count_res)['cnt'] : 0;

$top5_sql = "SELECT 
    p.id,
    p.name,
    p.imageUrl,
    p.price,
    p.ground_type,
    COALESCE(SUM(oi.qty), 0) AS total_ordered
FROM products p
LEFT JOIN order_items oi ON p.id = oi.product_id
GROUP BY p.id, p.name, p.imageUrl, p.price, p.ground_type
ORDER BY total_ordered DESC, p.id ASC
LIMIT 5";
$top5_result = mysqli_query($conn, $top5_sql);
?>
<!DOCTYPE html>
<html lang="th">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>OnlySport - Dashboard & ภาพรวมระบบ</title>
  <link rel="stylesheet" href="style.css?v=<?php echo file_exists('style.css') ? filemtime('style.css') : time(); ?>">
</head>
<body>

<div class="container">
  <?php include "navbar.php"; ?>

  <?php if (isset($_GET['msg']) && !empty($_GET['msg'])): ?>
    <div class="alert" id="alert-banner">
      <div class="alert-content">
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
          <polyline points="20 6 9 17 4 12"></polyline>
        </svg>
        <span><?php echo htmlspecialchars($_GET['msg']); ?></span>
      </div>
      <button class="alert-close" onclick="document.getElementById('alert-banner').style.display='none';">&times;</button>
    </div>
  <?php endif; ?>

  <div class="three-stat-grid">
    <div class="summary-stat-box">
      <div class="stat-icon-circle revenue-bg">
        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
          <line x1="12" y1="1" x2="12" y2="23"></line>
          <path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"></path>
        </svg>
      </div>
      <div class="stat-meta">
        <div class="stat-meta-title">ยอดขายรวม</div>
        <div class="stat-meta-value">฿<?php echo number_format($total_revenue, 2); ?></div>
      </div>
    </div>

    <a href="orders.php" class="summary-stat-box">
      <div class="stat-icon-circle orders-bg">
        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
          <circle cx="8" cy="21" r="1"></circle>
          <circle cx="19" cy="21" r="1"></circle>
          <path d="M2.05 2.05h2l2.66 12.42a2 2 0 0 0 2 1.58h9.78a2 2 0 0 0 1.95-1.57l1.65-7.43H5.12"></path>
        </svg>
      </div>
      <div class="stat-meta">
        <div class="stat-meta-title">จำนวนออเดอร์</div>
        <div class="stat-meta-value">
          <?php echo number_format($total_orders); ?> 
          <span class="stat-meta-unit">ออเดอร์</span>
        </div>
      </div>
    </a>

    <a href="menu.php" class="summary-stat-box">
      <div class="stat-icon-circle products-bg">
        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
          <path d="M21 8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16Z"></path>
          <path d="m3.3 7 8.7 5 8.7-5"></path>
          <path d="M12 22V12"></path>
        </svg>
      </div>
      <div class="stat-meta">
        <div class="stat-meta-title">สินค้าทั้งหมด</div>
        <div class="stat-meta-value">
          <?php echo number_format($total_products); ?> 
          <span class="stat-meta-unit">รายการ</span>
        </div>
      </div>
    </a>
  </div>

  <div class="card">
    <div class="card-header">
      <div>
        <h2 class="card-title">
          สินค้าขายดีอันดับ 1 - 5
        </h2>
        <span class="card-subtitle">จัดอันดับตามจำนวนคู่ที่ลูกค้าสั่งซื้อ</span>
      </div>

      <a href="menu.php" class="btn btn-secondary btn-sm">
        <span>ดูสินค้าทั้งหมด</span>
        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
          <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/>
        </svg>
      </a>
    </div>

    <div class="table-responsive">
      <table>
        <thead>
          <tr>
            <th style="width: 70px; text-align: center;">อันดับ</th>
            <th style="width: 70px; text-align: center;">รูปสินค้า</th>
            <th>ชื่อสินค้า</th>
            <th style="width: 140px;">ราคา</th>
            <th style="width: 180px; text-align: center;">คะแนน & สถานะ</th>
            <th style="width: 150px; text-align: center;">จำนวนที่สั่งซื้อ</th>
          </tr>
        </thead>
        <tbody>
          <?php if ($top5_result && mysqli_num_rows($top5_result) > 0): ?>
            <?php 
              $rank = 1;
              while ($top = mysqli_fetch_assoc($top5_result)): 
                $rank_class = 'rank-other';
                if ($rank === 1) {
                    $rank_class = 'rank-1';
                } elseif ($rank === 2) {
                    $rank_class = 'rank-2';
                } elseif ($rank === 3) {
                    $rank_class = 'rank-3';
                }
                $rating_info = get_bestseller_rating_status($rank, $top);
            ?>
              <tr>
                <td style="text-align: center;">
                  <span class="rank-pill <?php echo $rank_class; ?>">
                    <?php echo $rank; ?>
                  </span>
                </td>
                <td style="text-align: center;">
                  <?php if (!empty($top['imageUrl'])): ?>
                    <img src="<?php echo htmlspecialchars($top['imageUrl']); ?>" 
                         alt="<?php echo htmlspecialchars($top['name']); ?>" 
                         class="product-img" 
                         style="margin: 0 auto;"
                         loading="lazy"
                         onerror="this.onerror=null; this.src='https://via.placeholder.com/48x48?text=Cleat';">
                  <?php else: ?>
                    <div class="product-img-empty" style="margin: 0 auto;">No Img</div>
                  <?php endif; ?>
                </td>
                <td>
                  <div class="menu-name-cell">
                    <strong class="menu-name-text"><?php echo htmlspecialchars($top['name']); ?></strong>
                    <?php if (!empty($top['ground_type'])): ?>
                      <span class="badge badge-ground-fg">
                        <?php echo htmlspecialchars($top['ground_type']); ?>
                      </span>
                    <?php endif; ?>
                  </div>
                </td>
                <td>
                  <span class="price-tag">฿<?php echo number_format($top['price'], 2); ?></span>
                </td>
                <td style="text-align: center;">
                  <div style="display: flex; gap: 6px; justify-content: center; align-items: center;">
                    <span class="rating-pill">
                      <svg width="12" height="12" viewBox="0 0 24 24" fill="currentColor">
                        <polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"></polygon>
                      </svg>
                      <?php echo $rating_info['rating']; ?>
                    </span>
                    <span class="status-pill <?php echo $rating_info['status_class']; ?>">
                      <?php echo $rating_info['status']; ?>
                    </span>
                  </div>
                </td>
                <td style="text-align: center;">
                  <strong style="color: var(--primary); font-size: 15px; font-weight: 800;">
                    <?php echo number_format($top['total_ordered']); ?>
                  </strong> 
                  <span style="font-size: 12px; color: var(--text-muted);">คู่</span>
                </td>
              </tr>
            <?php 
              $rank++;
              endwhile; 
            ?>
          <?php else: ?>
            <tr>
              <td colspan="6">
                <div class="empty-state">
                  <div class="empty-state-icon">
                    <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                      <line x1="18" y1="20" x2="18" y2="10"></line>
                      <line x1="12" y1="20" x2="12" y2="4"></line>
                      <line x1="6" y1="20" x2="6" y2="14"></line>
                    </svg>
                  </div>
                  <p>ยังไม่มีข้อมูลการสั่งซื้อสินค้า</p>
                </div>
              </td>
            </tr>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>

  <div class="card">
    <div class="card-header">
      <h2 class="card-title">
        ทางลัดการจัดการระบบ
      </h2>
    </div>
    <div class="card-body" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 14px;">
      <a href="menu.php" class="btn btn-secondary" style="padding: 12px;">
        <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16"></path>
        </svg>
        ดูรายการสินค้าทั้งหมด
      </a>
      <a href="add_new.php" class="btn btn-primary" style="padding: 12px;">
        <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"></path>
        </svg>
        เพิ่มสินค้าใหม่
      </a>
      <a href="manage.php" class="btn btn-secondary" style="padding: 12px;">
        <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
        </svg>
        จัดการสินค้า (แก้ไข / ลบ)
      </a>
      <a href="orders.php" class="btn btn-secondary" style="padding: 12px;">
        <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"></path>
        </svg>
        จัดการคำสั่งซื้อลูกค้า
      </a>
    </div>
  </div>
</div>

</body>
</html>
<?php 
mysqli_close($conn); 
?>
