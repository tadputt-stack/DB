<?php
include "db_conn.php";

$current_page = 'manage';

$search_query = isset($_GET['search']) ? trim($_GET['search']) : '';
$series_filter = isset($_GET['series']) ? intval($_GET['series']) : 0;

$where_clauses = [];
if (!empty($search_query)) {
    $safe_search = mysqli_real_escape_string($conn, $search_query);
    $where_clauses[] = "(p.name LIKE '%$safe_search%' OR p.ground_type LIKE '%$safe_search%' OR p.description LIKE '%$safe_search%')";
}
if ($series_filter > 0) {
    $where_clauses[] = "p.series_id = " . $series_filter;
}
$where_sql = !empty($where_clauses) ? "WHERE " . implode(" AND ", $where_clauses) : "";

$sql = "SELECT p.*, s.series_name 
        FROM products p 
        LEFT JOIN series s ON p.series_id = s.series_id 
        $where_sql 
        ORDER BY p.id DESC";
$result = mysqli_query($conn, $sql);
$total_products = $result ? mysqli_num_rows($result) : 0;

$series_list_res = mysqli_query($conn, "SELECT series_id, series_name FROM series ORDER BY series_name ASC");
?>
<!DOCTYPE html>
<html lang="th">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>OnlySport - จัดการสินค้า</title>
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

  <div class="card">
    <div class="card-header">
      <div>
        <h1 class="card-title">
          จัดการสินค้า (แก้ไข / ลบ)
          <span class="card-subtitle">(<span id="manageCount"><?php echo $total_products; ?></span> รายการ)</span>
        </h1>
      </div>

      <div style="display: flex; gap: 10px; align-items: center; flex-wrap: wrap;">
        <div style="display: flex; gap: 6px; align-items: center; flex-wrap: wrap;">
          <a href="manage.php<?php echo !empty($search_query) ? '?search=' . urlencode($search_query) : ''; ?>" 
             class="filter-btn <?php echo ($series_filter === 0) ? 'active' : ''; ?>">ทั้งหมด</a>
          <?php if ($series_list_res): ?>
            <?php while ($s = mysqli_fetch_assoc($series_list_res)): ?>
              <a href="manage.php?series=<?php echo $s['series_id']; ?><?php echo !empty($search_query) ? '&search=' . urlencode($search_query) : ''; ?>" 
                 class="filter-btn <?php echo ($series_filter == $s['series_id']) ? 'active' : ''; ?>">
                <?php echo htmlspecialchars($s['series_name']); ?>
              </a>
            <?php endwhile; ?>
          <?php endif; ?>
        </div>

        <a href="add_new.php" class="btn btn-primary btn-sm">
          <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"></path>
          </svg>
          เพิ่มสินค้าใหม่
        </a>
      </div>
    </div>

    <div class="table-responsive">
      <table>
        <thead>
          <tr>
            <th style="width: 50px;">#</th>
            <th style="width: 70px;">รูปสินค้า</th>
            <th>ชื่อรองเท้าสตั๊ด</th>
            <th>ซีรีส์</th>
            <th>ประเภทพื้น</th>
            <th>ราคา</th>
            <th>สเปก / รายละเอียด</th>
            <th style="width: 170px; text-align: center;">จัดการ</th>
          </tr>
        </thead>
        <tbody>
          <?php if ($total_products > 0): ?>
            <?php 
              $i = 1;
              while ($row = mysqli_fetch_assoc($result)): 
                $ground_class = 'badge-ground-fg';
                if ($row['ground_type'] === 'TF') $ground_class = 'badge-ground-tf';
                elseif ($row['ground_type'] === 'SG') $ground_class = 'badge-ground-sg';
                elseif ($row['ground_type'] === 'MG') $ground_class = 'badge-ground-mg';
            ?>
              <tr class="manage-item" 
                  data-name="<?php echo strtolower(htmlspecialchars($row['name'])); ?>"
                  data-desc="<?php echo strtolower(htmlspecialchars($row['description'] ?? '')); ?>"
                  data-series="<?php echo strtolower(htmlspecialchars($row['series_name'] ?? '')); ?>">
                <td><?php echo $i++; ?></td>
                <td>
                  <?php if (!empty($row['imageUrl'])): ?>
                    <img src="<?php echo htmlspecialchars($row['imageUrl']); ?>" 
                         alt="<?php echo htmlspecialchars($row['name']); ?>" 
                         class="product-img"
                         loading="lazy"
                         onerror="this.onerror=null; this.src='https://via.placeholder.com/48x48?text=Cleat';">
                  <?php else: ?>
                    <div class="product-img-empty">No Img</div>
                  <?php endif; ?>
                </td>
                <td>
                  <strong class="menu-name-text"><?php echo htmlspecialchars($row['name']); ?></strong>
                  <div style="font-size: 11px; color: var(--text-light); margin-top: 2px;">SKU ID: #<?php echo $row['id']; ?></div>
                </td>
                <td>
                  <span class="badge badge-series">
                    <?php echo htmlspecialchars($row['series_name'] ?? 'Football'); ?>
                  </span>
                </td>
                <td>
                  <span class="badge <?php echo $ground_class; ?>">
                    <?php echo htmlspecialchars($row['ground_type']); ?>
                  </span>
                </td>
                <td>
                  <span class="price-tag">฿<?php echo number_format($row['price'], 2); ?></span>
                </td>
                <td style="max-width: 250px; font-size: 12.5px; color: var(--text-muted); line-height: 1.4;">
                  <?php echo htmlspecialchars($row['description'] ?: '-'); ?>
                </td>
                <td style="text-align: center;">
                  <div style="display: flex; gap: 8px; justify-content: center;">
                    <a href="edit.php?id=<?php echo $row['id']; ?>" class="btn btn-sm btn-edit" title="แก้ไขข้อมูลสินค้า">
                      <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path>
                        <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path>
                      </svg>
                      แก้ไข
                    </a>
                    <a href="delete.php?id=<?php echo $row['id']; ?>&from=manage" 
                       class="btn btn-sm btn-delete" 
                       onclick="return confirm('ยืนยันการลบสินค้า \'<?php echo addslashes($row['name']); ?>\' (ID: #<?php echo $row['id']; ?>)?');"
                       title="ลบสินค้านี้">
                      <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <polyline points="3 6 5 6 21 6"></polyline>
                        <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path>
                      </svg>
                      ลบ
                    </a>
                  </div>
                </td>
              </tr>
            <?php endwhile; ?>
          <?php else: ?>
            <tr>
              <td colspan="8">
                <div class="empty-state">
                  <div class="empty-state-icon">
                    <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                      <path d="M21 8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16Z"></path>
                      <path d="m3.3 7 8.7 5 8.7-5"></path>
                      <path d="M12 22V12"></path>
                    </svg>
                  </div>
                  <p>ไม่พบรายการสินค้าที่ตรงกับเงื่อนไขการค้นหา</p>
                  <a href="add_new.php" class="btn btn-primary btn-sm" style="margin-top: 12px;">
                    <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"></path>
                    </svg>
                    เพิ่มสินค้าใหม่
                  </a>
                </div>
              </td>
            </tr>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
  const searchInput = document.getElementById('navbarSearchInput');
  if (searchInput) {
    searchInput.addEventListener('input', function(e) {
      const q = e.target.value.toLowerCase().trim();
      const rows = document.querySelectorAll('.manage-item');
      let visible = 0;

      rows.forEach(row => {
        const name = row.getAttribute('data-name') || '';
        const desc = row.getAttribute('data-desc') || '';
        const series = row.getAttribute('data-series') || '';

        if (!q || name.includes(q) || desc.includes(q) || series.includes(q)) {
          row.style.display = '';
          visible++;
        } else {
          row.style.display = 'none';
        }
      });

      const count = document.getElementById('manageCount');
      if (count) count.textContent = visible;
    });
  }
});
</script>

</body>
</html>
<?php 
mysqli_close($conn); 
?>
