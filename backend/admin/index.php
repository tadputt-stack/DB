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

<?php include "navbar.php"; ?>

<div class="container">
  <!-- Flash Alert Message -->
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

  <!-- Page Header Row -->
  <div class="page-header-row">
    <div>
      <h1 class="page-title">
        <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" style="color: var(--primary);">
          <rect x="3" y="3" width="7" height="7"></rect>
          <rect x="14" y="3" width="7" height="7"></rect>
          <rect x="14" y="14" width="7" height="7"></rect>
          <rect x="3" y="14" width="7" height="7"></rect>
        </svg>
        Dashboard & ภาพรวมระบบ (Store Overview)
      </h1>
      <span class="page-subtitle" style="margin-left: 32px; display: block; margin-top: 2px;">
        สรุปผลประกอบการ คลังสินค้า และ 5 อันดับสตั๊ดยอดนิยมประจำระบบ
      </span>
    </div>

    <div class="page-actions-group">
      <a href="menu.php" class="btn btn-outline btn-sm">
        <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16"></path>
        </svg>
        จัดการสินค้า
      </a>
      <a href="add_new.php" class="btn btn-primary btn-sm">
        <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"></path>
        </svg>
        + เพิ่มสินค้าใหม่
      </a>
    </div>
  </div>

  <!-- Summary Stat Cards -->
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

  <!-- Best Sellers Table Card -->
  <div class="card">
    <div class="card-header">
      <div>
        <h2 class="card-title">
          <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" style="color: #F59E0B;">
            <polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"></polygon>
          </svg>
          สินค้าขายดีอันดับ 1 - 5 (Top 5 Best Sellers)
        </h2>
        <span class="card-subtitle">จัดอันดับตามจำนวนคู่ที่ลูกค้าทำรายการสั่งซื้อสำเร็จ</span>
      </div>

      <a href="menu.php" class="btn btn-outline btn-sm">
        <span>ดูแคตตาล็อกทั้งหมด</span>
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
            <th>ชื่อรองเท้าสตั๊ด</th>
            <th style="width: 140px;">ราคา</th>
            <th style="width: 180px; text-align: center;">คะแนน & สถานะ</th>
            <th style="width: 150px; text-align: center;">จำนวนสั่งซื้อ</th>
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
                $clean_image_url = !empty($top['imageUrl']) ? str_replace('https://wsrv.nl/?url=', '', $top['imageUrl']) : '';
            ?>
              <tr>
                <td style="text-align: center;">
                  <span class="badge" style="width: 28px; height: 28px; border-radius: 6px; display: inline-flex; align-items: center; justify-content: center; font-weight: 800; <?php echo ($rank === 1) ? 'background:#FEF3C7; color:#92400E; border:1px solid #FDE68A;' : (($rank === 2) ? 'background:#F1F5F9; color:#334155; border:1px solid #CBD5E1;' : (($rank === 3) ? 'background:#FFEDD5; color:#9A3412; border:1px solid #FED7AA;' : 'background:#F8FAFC; color:#64748B; border:1px solid #E2E8F0;')); ?>">
                    #<?php echo $rank; ?>
                  </span>
                </td>
                <td style="text-align: center;">
                  <?php if (!empty($clean_image_url)): ?>
                    <img src="<?php echo htmlspecialchars($clean_image_url); ?>" 
                         alt="<?php echo htmlspecialchars($top['name']); ?>" 
                         class="product-thumb-img" 
                         style="margin: 0 auto; width: 48px; height: 48px;"
                         loading="lazy"
                         referrerpolicy="no-referrer"
                         onerror="this.onerror=null; this.src='https://via.placeholder.com/48x48?text=Cleat';">
                  <?php else: ?>
                    <div class="product-thumb-empty" style="margin: 0 auto; width: 48px; height: 48px;">No Img</div>
                  <?php endif; ?>
                </td>
                <td>
                  <strong style="color: var(--text-main); font-size: 13.5px;"><?php echo htmlspecialchars($top['name']); ?></strong>
                  <?php if (!empty($top['ground_type'])): ?>
                    <span class="badge badge-ground-fg" style="margin-left: 6px;">
                      <?php echo htmlspecialchars($top['ground_type']); ?>
                    </span>
                  <?php endif; ?>
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
                    <span class="stock-badge in-stock">
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
              <td colspan="6" style="text-align: center; padding: 32px; color: var(--text-muted);">
                ยังไม่มีข้อมูลการสั่งซื้อสินค้า
              </td>
            </tr>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>

  <!-- Quick System Shortcuts Card -->
  <div class="card">
    <div class="card-header">
      <h2 class="card-title">
        <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
          <polygon points="13 2 3 14 12 14 11 22 21 10 12 10 13 2"></polygon>
        </svg>
        ทางลัดการจัดการระบบ (Quick Actions)
      </h2>
    </div>
    <div class="card-body" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 14px;">
      <a href="menu.php" class="btn btn-outline" style="padding: 12px; justify-content: flex-start;">
        <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16"></path>
        </svg>
        จัดการรายการสินค้าทั้งหมด
      </a>
      <a href="add_new.php" class="btn btn-primary" style="padding: 12px; justify-content: flex-start;">
        <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"></path>
        </svg>
        เพิ่มสินค้าสตั๊ดใหม่
      </a>
      <a href="orders.php" class="btn btn-outline" style="padding: 12px; justify-content: flex-start;">
        <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" d="M16 11V7a4 4 0 0 0-8 0v4M5 9h14l1 12H4L5 9z"></path>
        </svg>
        จัดการคำสั่งซื้อลูกค้า
      </a>
      <a href="ratings.php" class="btn btn-outline" style="padding: 12px; justify-content: flex-start;">
        <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
          <polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"></polygon>
        </svg>
        ดูรีวิวและความพึงพอใจ
      </a>
    </div>
  </div>
</div>

</body>
</html>
<?php 
mysqli_close($conn); 
?>
