<?php
include "db_conn.php";

$current_page = 'products';

$search_query = isset($_GET['search']) ? trim($_GET['search']) : '';
$series_filter = isset($_GET['series']) ? intval($_GET['series']) : 0;
$view_mode = isset($_GET['view']) && in_array($_GET['view'], ['card', 'table']) ? $_GET['view'] : 'card';

$where_clauses = [];
if (!empty($search_query)) {
    $safe_search = mysqli_real_escape_string($conn, $search_query);
    $where_clauses[] = "(p.name LIKE '%$safe_search%' OR p.ground_type LIKE '%$safe_search%' OR p.description LIKE '%$safe_search%')";
}
if ($series_filter > 0) {
    $where_clauses[] = "p.series_id = " . $series_filter;
}
$where_sql = !empty($where_clauses) ? "WHERE " . implode(" AND ", $where_clauses) : "";

$all_products_sql = "SELECT p.*, s.series_name 
                     FROM products p 
                     LEFT JOIN series s ON p.series_id = s.series_id 
                     $where_sql 
                     ORDER BY p.id DESC";
$all_products_result = mysqli_query($conn, $all_products_sql);
$total_count = $all_products_result ? mysqli_num_rows($all_products_result) : 0;

$series_list_res = mysqli_query($conn, "SELECT series_id, series_name FROM series ORDER BY series_name ASC");
?>
<!DOCTYPE html>
<html lang="th">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>OnlySport - รายการสินค้าทั้งหมด</title>
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
          รายการรองเท้าสตั๊ดทั้งหมด
          <span class="card-subtitle">(<span id="menuCount"><?php echo $total_count; ?></span> รายการ<?php echo !empty($search_query) ? " • ค้นหา: '$search_query'" : ""; ?>)</span>
        </h1>
      </div>

      <div style="display: flex; align-items: center; gap: 12px; flex-wrap: wrap;">
        <div style="display: flex; gap: 6px; align-items: center; flex-wrap: wrap;">
          <a href="menu.php<?php echo !empty($search_query) ? '?search=' . urlencode($search_query) : ''; ?>" 
             class="filter-btn <?php echo ($series_filter === 0) ? 'active' : ''; ?>">
            ทั้งหมด
          </a>
          <?php if ($series_list_res): ?>
            <?php while ($s = mysqli_fetch_assoc($series_list_res)): ?>
              <a href="menu.php?series=<?php echo $s['series_id']; ?><?php echo !empty($search_query) ? '&search=' . urlencode($search_query) : ''; ?>" 
                 class="filter-btn <?php echo ($series_filter == $s['series_id']) ? 'active' : ''; ?>">
                <?php echo htmlspecialchars($s['series_name']); ?>
              </a>
            <?php endwhile; ?>
          <?php endif; ?>
        </div>

        <div class="view-switcher-group">
          <button type="button" class="view-toggle-btn active" id="btnViewCard" onclick="switchView('card')">
            <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"></path>
            </svg>
            แบบการ์ด
          </button>
          <button type="button" class="view-toggle-btn" id="btnViewTable" onclick="switchView('table')">
            <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16"></path>
            </svg>
            แบบตาราง
          </button>
        </div>

        <a href="add_new.php" class="btn btn-primary btn-sm">
          <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"></path>
          </svg>
          เพิ่มสินค้าใหม่
        </a>
      </div>
    </div>

    <div id="cardViewContainer" class="product-card-grid">
      <?php if ($total_count > 0): ?>
        <?php 
          mysqli_data_seek($all_products_result, 0);
          while ($row = mysqli_fetch_assoc($all_products_result)): 
            $ground_class = 'badge-ground-fg';
            if ($row['ground_type'] === 'TF') $ground_class = 'badge-ground-tf';
            elseif ($row['ground_type'] === 'SG') $ground_class = 'badge-ground-sg';
            elseif ($row['ground_type'] === 'MG') $ground_class = 'badge-ground-mg';
        ?>
          <div class="menu-card product-item" 
               data-name="<?php echo strtolower(htmlspecialchars($row['name'])); ?>"
               data-desc="<?php echo strtolower(htmlspecialchars($row['description'] ?? '')); ?>"
               data-series="<?php echo strtolower(htmlspecialchars($row['series_name'] ?? '')); ?>">
            <div class="menu-card-img-wrap">
              <?php if (!empty($row['imageUrl'])): ?>
                <img src="<?php echo htmlspecialchars($row['imageUrl']); ?>" 
                     alt="<?php echo htmlspecialchars($row['name']); ?>" 
                     class="menu-card-img"
                     loading="lazy"
                     onerror="this.onerror=null; this.src='https://via.placeholder.com/300x200?text=Cleat+Photo';">
              <?php else: ?>
                <div class="menu-card-empty-img">ไม่มีรูปภาพสินค้า</div>
              <?php endif; ?>

              <div class="menu-card-badges">
                <span class="badge badge-series">
                  <?php echo htmlspecialchars($row['series_name'] ?? 'Football'); ?>
                </span>
                <?php if (!empty($row['ground_type'])): ?>
                  <span class="badge <?php echo $ground_class; ?>">
                    <?php echo htmlspecialchars($row['ground_type']); ?>
                  </span>
                <?php endif; ?>
              </div>
            </div>

            <div class="menu-card-body">
              <h3 class="menu-card-title"><?php echo htmlspecialchars($row['name']); ?></h3>
              <p class="menu-card-desc"><?php echo htmlspecialchars($row['description'] ?: 'รองเท้าสตั๊ดฟุตบอลสเปกคุณภาพสูง ยึดเกาะสนามดีเยี่ยม'); ?></p>

              <div class="menu-card-footer">
                <div class="menu-card-price">฿<?php echo number_format($row['price'], 2); ?></div>
                <div class="menu-card-rating">
                  <svg width="12" height="12" viewBox="0 0 24 24" fill="currentColor">
                    <polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"></polygon>
                  </svg>
                  <span>4.9</span>
                </div>
              </div>

              <div class="menu-card-actions">
                <a href="edit.php?id=<?php echo $row['id']; ?>" class="btn btn-sm btn-edit">
                  <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path>
                    <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path>
                  </svg>
                  <span>แก้ไข</span>
                </a>
                <a href="delete.php?id=<?php echo $row['id']; ?>&from=menu" 
                   class="btn btn-sm btn-delete" 
                   onclick="return confirm('ยืนยันที่จะลบสินค้า \'<?php echo addslashes($row['name']); ?>\' หรือไม่?');">
                  <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <polyline points="3 6 5 6 21 6"></polyline>
                    <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path>
                  </svg>
                  <span>ลบ</span>
                </a>
              </div>
            </div>
          </div>
        <?php endwhile; ?>
      <?php else: ?>
        <div class="empty-state" style="grid-column: 1 / -1;">
          <div class="empty-state-icon">
            <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
              <path d="M21 8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16Z"></path>
              <path d="m3.3 7 8.7 5 8.7-5"></path>
              <path d="M12 22V12"></path>
            </svg>
          </div>
          <p>ไม่พบรายการรองเท้าสตั๊ดที่ตรงกับคำค้นหา</p>
          <a href="add_new.php" class="btn btn-primary btn-sm" style="margin-top: 12px;">
            <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"></path>
            </svg>
            เพิ่มสินค้าใหม่
          </a>
        </div>
      <?php endif; ?>
    </div>

    <div id="tableViewContainer" class="table-responsive" style="display: none;">
      <table>
        <thead>
          <tr>
            <th style="width: 50px;">#</th>
            <th style="width: 70px;">รูปสินค้า</th>
            <th>ชื่อรองเท้าสตั๊ด</th>
            <th>ซีรีส์</th>
            <th>ประเภทพื้น</th>
            <th>ราคา</th>
            <th>คะแนน</th>
            <th>รายละเอียด</th>
            <th style="width: 150px; text-align: center;">จัดการสินค้า</th>
          </tr>
        </thead>
        <tbody>
          <?php if ($total_count > 0): ?>
            <?php 
              mysqli_data_seek($all_products_result, 0);
              $i = 1;
              while ($row = mysqli_fetch_assoc($all_products_result)): 
                $ground_class = 'badge-ground-fg';
                if ($row['ground_type'] === 'TF') $ground_class = 'badge-ground-tf';
                elseif ($row['ground_type'] === 'SG') $ground_class = 'badge-ground-sg';
                elseif ($row['ground_type'] === 'MG') $ground_class = 'badge-ground-mg';
            ?>
              <tr class="product-item"
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
                <td>
                  <span class="rating-pill">
                    <svg width="12" height="12" viewBox="0 0 24 24" fill="currentColor">
                      <polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"></polygon>
                    </svg>
                    4.9
                  </span>
                </td>
                <td style="max-width: 250px; font-size: 12.5px; color: var(--text-muted); line-height: 1.4;">
                  <?php echo htmlspecialchars($row['description'] ?: '-'); ?>
                </td>
                <td style="text-align: center;">
                  <div style="display: flex; gap: 6px; justify-content: center;">
                    <a href="edit.php?id=<?php echo $row['id']; ?>" class="btn btn-sm btn-edit">แก้ไข</a>
                    <a href="delete.php?id=<?php echo $row['id']; ?>&from=menu" 
                       class="btn btn-sm btn-delete" 
                       onclick="return confirm('ยืนยันที่จะลบสินค้า \'<?php echo addslashes($row['name']); ?>\' หรือไม่?');">
                      ลบ
                    </a>
                  </div>
                </td>
              </tr>
            <?php endwhile; ?>
          <?php else: ?>
            <tr>
              <td colspan="9">
                <div class="empty-state">
                  <div class="empty-state-icon">
                    <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                      <path d="M21 8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16Z"></path>
                      <path d="m3.3 7 8.7 5 8.7-5"></path>
                      <path d="M12 22V12"></path>
                    </svg>
                  </div>
                  <p>ไม่พบรายการรองเท้าสตั๊ดที่ตรงกับคำค้นหา</p>
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
function switchView(mode) {
  const cardView = document.getElementById('cardViewContainer');
  const tableView = document.getElementById('tableViewContainer');
  const btnCard = document.getElementById('btnViewCard');
  const btnTable = document.getElementById('btnViewTable');

  if (mode === 'table') {
    cardView.style.display = 'none';
    tableView.style.display = 'block';
    btnTable.classList.add('active');
    btnCard.classList.remove('active');
    localStorage.setItem('admin_products_view', 'table');
  } else {
    cardView.style.display = 'grid';
    tableView.style.display = 'none';
    btnCard.classList.add('active');
    btnTable.classList.remove('active');
    localStorage.setItem('admin_products_view', 'card');
  }
}

document.addEventListener('DOMContentLoaded', () => {
  const savedView = localStorage.getItem('admin_products_view');
  if (savedView === 'table') {
    switchView('table');
  }

  const searchInput = document.getElementById('navbarSearchInput');
  if (searchInput) {
    searchInput.addEventListener('input', function(e) {
      const q = e.target.value.toLowerCase().trim();
      const items = document.querySelectorAll('.product-item');
      let visibleCount = 0;

      items.forEach(item => {
        const name = item.getAttribute('data-name') || '';
        const desc = item.getAttribute('data-desc') || '';
        const series = item.getAttribute('data-series') || '';

        if (!q || name.includes(q) || desc.includes(q) || series.includes(q)) {
          item.style.display = '';
          visibleCount++;
        } else {
          item.style.display = 'none';
        }
      });

      const countDisplay = document.getElementById('menuCount');
      if (countDisplay) {
        countDisplay.textContent = visibleCount;
      }
    });
  }
});
</script>

</body>
</html>
<?php 
mysqli_close($conn); 
?>
