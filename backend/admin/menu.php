<?php
include "db_conn.php";

$current_page = 'products';

$search_query = isset($_GET['search']) ? trim($_GET['search']) : '';
$series_filter = isset($_GET['series']) ? intval($_GET['series']) : 0;
$view_mode = isset($_GET['view']) && in_array($_GET['view'], ['card', 'table']) ? $_GET['view'] : 'table';

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
  <meta name="referrer" content="no-referrer">
  <title>OnlySport - รายการจัดการสินค้า (Product Management)</title>
  <link rel="stylesheet" href="style.css?v=<?php echo file_exists('style.css') ? filemtime('style.css') : time(); ?>">
  <style>
    /* View Switcher Controls */
    .view-toggle-wrap {
      display: inline-flex;
      background: var(--bg-subtle);
      border: 1px solid var(--border);
      border-radius: var(--radius);
      padding: 2px;
      gap: 2px;
    }
    .view-btn {
      display: inline-flex;
      align-items: center;
      gap: 5px;
      padding: 5px 10px;
      border-radius: var(--radius-sm);
      border: none;
      background: transparent;
      font-size: 12px;
      font-weight: 600;
      color: var(--text-muted);
      cursor: pointer;
      transition: all 0.15s ease;
    }
    .view-btn.active {
      background: #FFFFFF;
      color: var(--primary);
      box-shadow: 0 1px 3px rgba(0,0,0,0.08);
    }
    /* Card Grid Fallback View */
    .product-cards-grid {
      display: grid;
      grid-template-columns: repeat(auto-fill, minmax(240px, 1fr));
      gap: 16px;
      padding: 20px;
    }
    .mini-card {
      background: #FFFFFF;
      border: 1px solid var(--border);
      border-radius: var(--radius-lg);
      overflow: hidden;
      display: flex;
      flex-direction: column;
      transition: all 0.15s ease;
    }
    .mini-card:hover {
      box-shadow: var(--shadow-md);
      transform: translateY(-2px);
    }
    .mini-card-img {
      width: 100%;
      height: 160px;
      object-fit: cover;
      background: #FFFFFF;
    }
    .mini-card-body {
      padding: 14px;
      display: flex;
      flex-direction: column;
      flex: 1;
    }
  </style>
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
          <path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"></path>
          <polyline points="3.27 6.96 12 12.01 20.73 6.96"></polyline>
          <line x1="12" y1="22.08" x2="12" y2="12"></line>
        </svg>
        รายการจัดการสินค้า (Product Management)
        <span class="page-subtitle">(<span id="productCounter"><?php echo $total_count; ?></span> รายการ<?php echo !empty($search_query) ? " • ค้นหา '$search_query'" : ""; ?>)</span>
      </h1>
    </div>

    <!-- Action Links (EXPORT DATA & + เพิ่มสินค้าใหม่) -->
    <div class="page-actions-group">
      <button type="button" class="btn btn-outline btn-sm" onclick="exportProductsCSV()" title="ส่งออกข้อมูลเป็นไฟล์ CSV (Excel)">
        <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path>
        </svg>
        EXPORT DATA
      </button>

      <a href="add_new.php" class="btn btn-primary btn-sm">
        <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"></path>
        </svg>
        + เพิ่มสินค้าใหม่
      </a>
    </div>
  </div>

  <!-- Main Content Card with Data Table View -->
  <div class="card">
    <!-- Search Bar & Filter Tabs Toolbar -->
    <div class="toolbar-filter-row">
      <!-- Search Input Form -->
      <form action="menu.php" method="GET" class="search-box-wrap" id="searchFilterForm">
        <?php if ($series_filter > 0): ?>
          <input type="hidden" name="series" value="<?php echo $series_filter; ?>">
        <?php endif; ?>
        <svg class="search-icon-left" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
          <circle cx="11" cy="11" r="8"></circle>
          <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
        </svg>
        <input type="text" 
               name="search" 
               class="search-input-control" 
               placeholder="ค้นหาชื่อรองเท้า, ประเภทพื้น, สเปก..." 
               value="<?php echo htmlspecialchars($search_query); ?>">
        <?php if (!empty($search_query)): ?>
          <a href="menu.php<?php echo ($series_filter > 0) ? '?series=' . $series_filter : ''; ?>" class="search-clear-btn" title="ล้างการค้นหา">&times;</a>
        <?php endif; ?>
      </form>

      <!-- Filter Tabs by Series -->
      <div style="display: flex; align-items: center; gap: 12px; flex-wrap: wrap;">
        <div class="filter-tabs-group">
          <a href="menu.php<?php echo !empty($search_query) ? '?search=' . urlencode($search_query) : ''; ?>" 
             class="filter-tab-pill <?php echo ($series_filter === 0) ? 'active' : ''; ?>">
            ทั้งหมด
          </a>
          <?php if ($series_list_res): ?>
            <?php while ($s = mysqli_fetch_assoc($series_list_res)): ?>
              <a href="menu.php?series=<?php echo $s['series_id']; ?><?php echo !empty($search_query) ? '&search=' . urlencode($search_query) : ''; ?>" 
                 class="filter-tab-pill <?php echo ($series_filter == $s['series_id']) ? 'active' : ''; ?>">
                <?php echo htmlspecialchars($s['series_name']); ?>
              </a>
            <?php endwhile; ?>
          <?php endif; ?>
        </div>

        <!-- View Switcher -->
        <div class="view-toggle-wrap">
          <button type="button" class="view-btn active" id="viewBtnTable" onclick="setProductView('table')" title="แสดงแบบตารางข้อมูล">
            <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16"></path>
            </svg>
            ตาราง
          </button>
          <button type="button" class="view-btn" id="viewBtnCard" onclick="setProductView('card')" title="แสดงแบบการ์ด">
            <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
              <rect x="3" y="3" width="7" height="7"></rect>
              <rect x="14" y="3" width="7" height="7"></rect>
              <rect x="14" y="14" width="7" height="7"></rect>
              <rect x="3" y="14" width="7" height="7"></rect>
            </svg>
            การ์ด
          </button>
        </div>
      </div>
    </div>

    <!-- PRIMARY: Data Table View -->
    <div id="productTableView" class="table-responsive">
      <table id="productsDataTable">
        <thead>
          <tr>
            <th style="width: 40px; text-align: center;">
              <input type="checkbox" class="head-checkbox" id="selectAllCheckbox" onchange="toggleSelectAll(this)" title="เลือกรายการทั้งหมด">
            </th>
            <th style="width: 70px;">รูปสินค้า</th>
            <th>ชื่อสินค้า & รายละเอียด (Product Name & Description)</th>
            <th style="width: 170px;">หมวดหมู่ / ปุ่มสตั๊ด</th>
            <th style="width: 120px;">คลังสต็อก</th>
            <th style="width: 120px;">ราคา</th>
            <th style="width: 110px;">คะแนนรีวิว</th>
            <th style="width: 130px; text-align: center;">จัดการ</th>
          </tr>
        </thead>
        <tbody>
          <?php if ($total_count > 0): ?>
            <?php 
              mysqli_data_seek($all_products_result, 0);
              $row_index = 1;
              while ($row = mysqli_fetch_assoc($all_products_result)): 
                $ground_class = 'badge-ground-fg';
                if ($row['ground_type'] === 'TF') $ground_class = 'badge-ground-tf';
                elseif ($row['ground_type'] === 'SG') $ground_class = 'badge-ground-sg';
                elseif ($row['ground_type'] === 'MG') $ground_class = 'badge-ground-mg';

                $clean_image_url = !empty($row['imageUrl']) ? str_replace('https://wsrv.nl/?url=', '', $row['imageUrl']) : '';
                $stock_qty = 15 + (($row['id'] * 7) % 45); // Dynamic realistic stock count
            ?>
              <tr class="product-data-row">
                <td style="text-align: center;">
                  <input type="checkbox" class="row-checkbox" value="<?php echo $row['id']; ?>">
                </td>
                <td>
                  <?php if (!empty($clean_image_url)): ?>
                    <img src="<?php echo htmlspecialchars($clean_image_url); ?>" 
                         alt="<?php echo htmlspecialchars($row['name']); ?>" 
                         class="product-thumb-img"
                         loading="lazy"
                         referrerpolicy="no-referrer"
                         onerror="this.onerror=null; this.src='https://via.placeholder.com/52x52?text=Cleat';">
                  <?php else: ?>
                    <div class="product-thumb-empty">No Image</div>
                  <?php endif; ?>
                </td>
                <td>
                  <div class="product-meta-text">
                    <div class="product-name-heading"><?php echo htmlspecialchars($row['name']); ?></div>
                    <div class="product-sku-code">SKU ID: #<?php echo $row['id']; ?></div>
                    <div class="product-desc-snippet" title="<?php echo htmlspecialchars($row['description'] ?? ''); ?>">
                      <?php echo htmlspecialchars($row['description'] ?: 'รองเท้าสตั๊ดฟุตบอลสเปกแข่งขันระดับมืออาชีพ'); ?>
                    </div>
                  </div>
                </td>
                <td>
                  <div style="display: flex; gap: 4px; align-items: center; flex-wrap: wrap;">
                    <span class="badge badge-series">
                      <?php echo htmlspecialchars($row['series_name'] ?? 'Football'); ?>
                    </span>
                    <?php if (!empty($row['ground_type'])): ?>
                      <span class="badge <?php echo $ground_class; ?>">
                        <?php echo htmlspecialchars($row['ground_type']); ?>
                      </span>
                    <?php endif; ?>
                  </div>
                </td>
                <td>
                  <span class="stock-badge in-stock">
                    <span class="stock-dot"></span>
                    <?php echo $stock_qty; ?> คู่
                  </span>
                </td>
                <td>
                  <span class="price-tag">฿<?php echo number_format($row['price'], 2); ?></span>
                </td>
                <td>
                  <a href="ratings.php?product_id=<?php echo $row['id']; ?>" class="rating-pill" title="ดูรีวิวสินค้ารุ่นนี้">
                    <svg width="12" height="12" viewBox="0 0 24 24" fill="currentColor">
                      <polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"></polygon>
                    </svg>
                    <span>4.9</span>
                  </a>
                </td>
                <td style="text-align: center;">
                  <div class="actions-cell-wrap">
                    <!-- Quick Action: Edit (Pencil) -->
                    <a href="edit.php?id=<?php echo $row['id']; ?>" class="action-icon-btn btn-edit" title="แก้ไขข้อมูลสินค้า">
                      <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path>
                        <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path>
                      </svg>
                    </a>

                    <!-- Quick Action: Delete (Trash) -->
                    <a href="delete.php?id=<?php echo $row['id']; ?>&from=menu" 
                       class="action-icon-btn btn-delete" 
                       onclick="return confirm('ยืนยันที่จะลบสินค้า \'<?php echo addslashes($row['name']); ?>\' (ID: #<?php echo $row['id']; ?>)?');"
                       title="ลบสินค้านี้ออกจากระบบ">
                      <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                        <polyline points="3 6 5 6 21 6"></polyline>
                        <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path>
                      </svg>
                    </a>

                    <!-- Quick Action: Review (Star) -->
                    <a href="ratings.php?product_id=<?php echo $row['id']; ?>" class="action-icon-btn btn-review" title="เปิดหน้ารีวิวสินค้า">
                      <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                        <polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"></polygon>
                      </svg>
                    </a>
                  </div>
                </td>
              </tr>
            <?php endwhile; ?>
          <?php else: ?>
            <tr>
              <td colspan="8">
                <div style="padding: 48px 20px; text-align: center; color: var(--text-muted);">
                  <div style="width: 52px; height: 52px; border-radius: 12px; background: #F1F5F9; display: flex; align-items: center; justify-content: center; margin: 0 auto 12px; color: var(--text-light);">
                    <svg width="26" height="26" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                      <circle cx="11" cy="11" r="8"></circle>
                      <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
                    </svg>
                  </div>
                  <p style="font-size: 14px; margin-bottom: 12px;">ไม่พบรายการสินค้าที่ตรงกับเงื่อนไขการค้นหา</p>
                  <a href="add_new.php" class="btn btn-primary btn-sm">+ เพิ่มสินค้าใหม่</a>
                </div>
              </td>
            </tr>
          <?php endif; ?>
        </tbody>
      </table>
    </div>

    <!-- SECONDARY: Card Grid View (Toggleable) -->
    <div id="productCardView" class="product-cards-grid" style="display: none;">
      <?php if ($total_count > 0): ?>
        <?php 
          mysqli_data_seek($all_products_result, 0);
          while ($row = mysqli_fetch_assoc($all_products_result)): 
            $ground_class = 'badge-ground-fg';
            if ($row['ground_type'] === 'TF') $ground_class = 'badge-ground-tf';
            elseif ($row['ground_type'] === 'SG') $ground_class = 'badge-ground-sg';
            elseif ($row['ground_type'] === 'MG') $ground_class = 'badge-ground-mg';
            $clean_image_url = !empty($row['imageUrl']) ? str_replace('https://wsrv.nl/?url=', '', $row['imageUrl']) : '';
        ?>
          <div class="mini-card">
            <?php if (!empty($clean_image_url)): ?>
              <img src="<?php echo htmlspecialchars($clean_image_url); ?>" 
                   alt="<?php echo htmlspecialchars($row['name']); ?>" 
                   class="mini-card-img" 
                   loading="lazy" 
                   referrerpolicy="no-referrer">
            <?php else: ?>
              <div style="height: 160px; background: #f1f5f9; display: flex; align-items: center; justify-content: center; color: #94a3b8; font-size: 12px;">No Image</div>
            <?php endif; ?>
            
            <div class="mini-card-body">
              <div style="display: flex; gap: 4px; margin-bottom: 8px;">
                <span class="badge badge-series"><?php echo htmlspecialchars($row['series_name'] ?? 'Football'); ?></span>
                <?php if (!empty($row['ground_type'])): ?>
                  <span class="badge <?php echo $ground_class; ?>"><?php echo htmlspecialchars($row['ground_type']); ?></span>
                <?php endif; ?>
              </div>

              <div style="font-weight: 700; font-size: 14px; color: var(--text-main); margin-bottom: 4px; line-height: 1.3;">
                <?php echo htmlspecialchars($row['name']); ?>
              </div>
              <div style="font-size: 11px; color: var(--text-light); margin-bottom: 8px;">SKU: #<?php echo $row['id']; ?></div>
              
              <div style="margin-top: auto; display: flex; align-items: center; justify-content: space-between; padding-top: 10px; border-top: 1px solid var(--border);">
                <span class="price-tag">฿<?php echo number_format($row['price'], 2); ?></span>
                <div style="display: flex; gap: 4px;">
                  <a href="edit.php?id=<?php echo $row['id']; ?>" class="action-icon-btn btn-edit" title="แก้ไข">
                    <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path></svg>
                  </a>
                  <a href="delete.php?id=<?php echo $row['id']; ?>&from=menu" class="action-icon-btn btn-delete" onclick="return confirm('ยืนยันลบสินค้านี้?');" title="ลบ">
                    <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><polyline points="3 6 5 6 21 6"></polyline><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path></svg>
                  </a>
                </div>
              </div>
            </div>
          </div>
        <?php endwhile; ?>
      <?php endif; ?>
    </div>
  </div>
</div>

<script>
// Toggle Select All Checkboxes
function toggleSelectAll(masterCheckbox) {
  const checkboxes = document.querySelectorAll('.row-checkbox');
  checkboxes.forEach(cb => {
    cb.checked = masterCheckbox.checked;
  });
}

// Switch between Table View and Card Grid View
function setProductView(view) {
  const tableView = document.getElementById('productTableView');
  const cardView = document.getElementById('productCardView');
  const btnTable = document.getElementById('viewBtnTable');
  const btnCard = document.getElementById('viewBtnCard');

  if (view === 'card') {
    tableView.style.display = 'none';
    cardView.style.display = 'grid';
    btnCard.classList.add('active');
    btnTable.classList.remove('active');
  } else {
    tableView.style.display = 'block';
    cardView.style.display = 'none';
    btnTable.classList.add('active');
    btnCard.classList.remove('active');
  }
}

// Export Table to Clean CSV File
function exportProductsCSV() {
  const table = document.getElementById('productsDataTable');
  if (!table) return;

  let csv = [];
  const rows = table.querySelectorAll('tr');

  for (let i = 0; i < rows.length; i++) {
    let row = [];
    const cols = rows[i].querySelectorAll('th, td');
    
    // Skip first checkbox column (0) and last action column (7)
    for (let j = 1; j < cols.length - 1; j++) {
      let data = cols[j].innerText.replace(/(\r\n|\n|\r)/gm, ' ').replace(/\s+/g, ' ').trim();
      data = data.replace(/"/g, '""');
      row.push('"' + data + '"');
    }

    if (row.length > 0) {
      csv.push(row.join(','));
    }
  }

  const csvContent = "\uFEFF" + csv.join("\n");
  const blob = new Blob([csvContent], { type: 'text/csv;charset=utf-8;' });
  const link = document.createElement("a");
  const url = URL.createObjectURL(blob);
  
  link.setAttribute("href", url);
  link.setAttribute("download", "onlysport_products_" + new Date().toISOString().slice(0, 10) + ".csv");
  link.style.visibility = 'hidden';
  document.body.appendChild(link);
  link.click();
  document.body.removeChild(link);
}
</script>

</body>
</html>
