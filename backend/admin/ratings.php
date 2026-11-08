<?php
mysqli_report(MYSQLI_REPORT_OFF);

$host = getenv("DB_HOST") ?: "db";
$user = getenv("DB_USER") ?: "root";
$pass = (getenv("DB_PASSWORD") !== false && getenv("DB_PASSWORD") !== '') ? getenv("DB_PASSWORD") : "password";
$dbname = getenv("DB_NAME") ?: "onlysport";

$conn = @new mysqli($host, $user, $pass, $dbname);
if ($conn->connect_error) {
    $conn = @new mysqli("db", "user", "userpassword", "onlysport");
}
if ($conn->connect_error) {
    $conn = @new mysqli("127.0.0.1", "root", "password", "onlysport", 3307);
}

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$conn->set_charset("utf8mb4");

$current_page = 'ratings';
$selected_product_id = isset($_GET['product_id']) ? intval($_GET['product_id']) : 0;

$where_clause = "";
if ($selected_product_id > 0) {
    $where_clause = "WHERE r.product_id = $selected_product_id";
}

$sql = "SELECT r.*, 
               COALESCE(NULLIF(r.reviewer_name, ''), 'ผู้ใช้งานทั่วไป') AS display_reviewer,
               COALESCE(p.name, CONCAT('สินค้า ID: #', r.product_id)) AS product_name,
               p.imageUrl
        FROM product_ratings r
        LEFT JOIN products p ON r.product_id = p.id
        $where_clause
        ORDER BY r.rating_id DESC";

$result = $conn->query($sql);
$total_rows = ($result) ? $result->num_rows : 0;
?>
<!DOCTYPE html>
<html lang="th">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="referrer" content="no-referrer">
  <title>OnlySport - รายการรีวิวสินค้า (Ratings)</title>
  <link rel="stylesheet" href="style.css?v=<?php echo file_exists('style.css') ? filemtime('style.css') : time(); ?>">
  <style>
    .star-rating { color: #f59e0b; font-weight: 700; white-space: nowrap; }
    .star-score { font-size: 11px; color: #94a3b8; font-weight: 600; display: block; margin-top: 2px; }
    .badge-reviewer { background: #eff6ff; color: #1e40af; border: 1px solid #bfdbfe; padding: 4px 10px; border-radius: 9999px; font-size: 12px; font-weight: 600; display: inline-flex; align-items: center; gap: 4px; }
    .filter-banner-red { background: #fef2f2; border-left: 4px solid var(--primary, #d32f2f); padding: 12px 16px; border-radius: 8px; margin-bottom: 16px; color: #991b1b; font-size: 14px; display: flex; justify-content: space-between; align-items: center; }
  </style>
</head>
<body>

<?php include "navbar.php"; ?>

<div class="container">

  <div class="card" style="margin-top: 24px;">
    <div class="card-header">
      <div>
        <h1 class="card-title">
          ⭐ รายการให้คะแนนและรีวิวสินค้า (Product Ratings)
          <span class="card-subtitle">(<span id="ratingsCount"><?php echo $total_rows; ?></span> รายการ)</span>
        </h1>
      </div>

      <div style="display: flex; gap: 10px; align-items: center; flex-wrap: wrap;">
        <?php if ($selected_product_id > 0): ?>
          <a href="ratings.php" class="btn btn-secondary btn-sm">📋 ดูรีวิวทั้งหมด</a>
        <?php endif; ?>
        <a href="menu.php" class="btn btn-primary btn-sm">
          <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
          </svg>
          กลับหน้ารายการสินค้า
        </a>
      </div>
    </div>

    <?php if ($selected_product_id > 0): ?>
      <div class="filter-banner-red">
        <div>
          🔍 กำลังแสดงรีวิวเฉพาะสินค้า ID: <strong>#<?php echo $selected_product_id; ?></strong> (พบ <?php echo $total_rows; ?> รายการ)
        </div>
        <a href="ratings.php" style="color: #991b1b; text-decoration: underline; font-weight: 600; font-size: 13px;">ล้างตัวกรอง</a>
      </div>
    <?php endif; ?>

    <div class="table-responsive">
      <table>
        <thead>
          <tr>
            <th style="width: 60px;">ID</th>
            <th style="width: 70px;">รูปสินค้า</th>
            <th style="width: 250px;">สินค้าสตั๊ด</th>
            <th style="width: 150px;">ชื่อผู้ใช้งาน</th>
            <th style="width: 130px;">คะแนนดาว</th>
            <th>ความคิดเห็น (Comment)</th>
            <th style="width: 140px;">วันที่รีวิว</th>
          </tr>
        </thead>
        <tbody>
          <?php if ($result && $result->num_rows > 0): ?>
            <?php while($row = $result->fetch_assoc()): ?>
              <tr>
                <td><strong>#<?php echo $row['rating_id']; ?></strong></td>
                <td>
                  <?php if (!empty($row['imageUrl'])): ?>
                    <img src="<?= htmlspecialchars($row['imageUrl']) ?>" 
                         alt="<?= htmlspecialchars($row['product_name'] ?? '') ?>" 
                         referrerpolicy="no-referrer"
                         style="width: 50px; height: 50px; object-fit: cover; border-radius: 8px; border: 1px solid #e2e8f0; background: #fff;" />
                  <?php else: ?>
                    <div style="width: 50px; height: 50px; background: #f1f5f9; border-radius: 8px; display: flex; align-items: center; justify-content: center; font-size: 10px; color: #94a3b8; border: 1px dashed #cbd5e1;">
                      ไม่มีรูป
                    </div>
                  <?php endif; ?>
                </td>
                <td>
                  <strong style="color: var(--text-main, #0f172a); font-size: 14px;"><?php echo htmlspecialchars($row['product_name']); ?></strong>
                  <div style="font-size: 11px; color: var(--text-muted, #64748b); margin-top: 2px;">รหัสสินค้า: #<?php echo $row['product_id']; ?></div>
                </td>
                <td>
                  <span class="badge-reviewer">
                    <svg width="12" height="12" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                    </svg>
                    <?php echo htmlspecialchars($row['display_reviewer']); ?>
                  </span>
                </td>
                <td>
                  <div class="star-rating">
                    <?php echo str_repeat("⭐", $row['score']); ?>
                    <span class="star-score">(<?php echo $row['score']; ?>/5)</span>
                  </div>
                </td>
                <td style="color: var(--text-secondary, #334155); font-size: 13.5px; line-height: 1.5;">
                  <?php echo htmlspecialchars($row['comment']); ?>
                </td>
                <td style="color: var(--text-muted, #64748b); font-size: 12.5px; white-space: nowrap;">
                  <?php echo date('d/m/Y H:i', strtotime($row['created_at'])); ?>
                </td>
              </tr>
            <?php endwhile; ?>
          <?php else: ?>
            <tr><td colspan="7" style="text-align:center; padding: 36px; color: var(--text-muted, #64748b);">ยังไม่มีข้อมูลรีวิวในระบบ</td></tr>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>

</body>
</html>
<?php $conn->close(); ?>
<?php $conn->close(); ?>
