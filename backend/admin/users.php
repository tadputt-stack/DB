<?php
include "db_conn.php";

$current_page = 'users';
$msg = "";
$msg_type = "success";

// -------------------------------------------------------------
// Auto-seed sample users if table is empty
// -------------------------------------------------------------
$check_empty = mysqli_query($conn, "SELECT COUNT(*) as c FROM users");
if ($check_empty) {
    $row_c = mysqli_fetch_assoc($check_empty);
    if ((int)($row_c['c'] ?? 0) === 0) {
        $seeds = [
            ['admin', 'ผู้ดูแลระบบ OnlySport', 'admin@onlysport.com', '081-234-5678'],
            ['chanathip18', 'ชนาธิป สรงกระสินธ์', 'jay.chanathip@gmail.com', '089-111-2233'],
            ['teerasil10', 'ธีรศิลป์ แดงดา', 'mew.teerasil@hotmail.com', '086-444-5566'],
            ['theerathon03', 'ธีราทร บุญมาทัน', 'aum.theerathon@gmail.com', '085-777-8899'],
            ['supachok19', 'สุภโชค สารชาติ', 'check.supachok@outlook.com', '082-333-4455'],
            ['kawin26', 'กวินทร์ ธรรมสัจจานันท์', 'flying.kawin@gmail.com', '084-666-7788'],
            ['sarach06', 'สารัช อยู่เย็น', 'tang.sarach@gmail.com', '087-999-0011'],
            ['worachit08', 'วรชิต กนิตศรีบำเพ็ญ', 'gita.worachit@yahoo.com', '083-222-3344'],
            ['kritsada17', 'กฤษดา กาแมน', 'andaman.kritsada@gmail.com', '088-555-6677'],
            ['supachai09', 'ศุภชัย ใจเด็ด', 'arm.supachai@gmail.com', '081-999-8877'],
            ['sasalak02', 'ศศลักษณ์ ไหประโคน', 'pee.sasalak@gmail.com', '086-123-4567'],
            ['elias04', 'เอเลียส ดอเลาะ', 'elias.dolah@outlook.com', '089-876-5432']
        ];
        $seed_stmt = mysqli_prepare($conn, "INSERT INTO users (username, name, email, phone) VALUES (?, ?, ?, ?)");
        if ($seed_stmt) {
            foreach ($seeds as $s) {
                mysqli_stmt_bind_param($seed_stmt, "ssss", $s[0], $s[1], $s[2], $s[3]);
                mysqli_stmt_execute($seed_stmt);
            }
            mysqli_stmt_close($seed_stmt);
        }
    }
}

// -------------------------------------------------------------
// Handle POST CRUD Actions
// -------------------------------------------------------------
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $action = $_POST['action'];

    // 1. ADD USER
    if ($action === 'add_user') {
        $username = trim($_POST['username'] ?? '');
        $name     = trim($_POST['name'] ?? '');
        $email    = trim($_POST['email'] ?? '');
        $phone    = trim($_POST['phone'] ?? '');

        if (empty($username)) {
            $msg = "กรุณากรอกชื่อผู้ใช้งาน (Username)";
            $msg_type = "error";
        } else {
            // Check if username already exists
            $stmt_check = mysqli_prepare($conn, "SELECT id FROM users WHERE username = ?");
            mysqli_stmt_bind_param($stmt_check, "s", $username);
            mysqli_stmt_execute($stmt_check);
            mysqli_stmt_store_result($stmt_check);
            if (mysqli_stmt_num_rows($stmt_check) > 0) {
                $msg = "ชื่อผู้ใช้งาน '$username' มีอยู่ในระบบแล้ว กรุณาใช้ชื่ออื่น";
                $msg_type = "error";
            } else {
                $stmt_insert = mysqli_prepare($conn, "INSERT INTO users (username, name, email, phone) VALUES (?, ?, ?, ?)");
                mysqli_stmt_bind_param($stmt_insert, "ssss", $username, $name, $email, $phone);
                if (mysqli_stmt_execute($stmt_insert)) {
                    $new_id = mysqli_insert_id($conn);
                    $msg = "เพิ่มผู้ใช้งานใหม่ '$username' (ID: #$new_id) เรียบร้อยแล้ว";
                    $msg_type = "success";
                } else {
                    $msg = "เกิดข้อผิดพลาดในการบันทึกข้อมูล: " . mysqli_error($conn);
                    $msg_type = "error";
                }
                mysqli_stmt_close($stmt_insert);
            }
            mysqli_stmt_close($stmt_check);
        }
    }

    // 2. EDIT USER
    if ($action === 'edit_user') {
        $id       = intval($_POST['id'] ?? 0);
        $username = trim($_POST['username'] ?? '');
        $name     = trim($_POST['name'] ?? '');
        $email    = trim($_POST['email'] ?? '');
        $phone    = trim($_POST['phone'] ?? '');

        if ($id <= 0 || empty($username)) {
            $msg = "ข้อมูลไม่ถูกต้อง กรุณากรอกชื่อผู้ใช้งาน (Username)";
            $msg_type = "error";
        } else {
            // Check if duplicate username for other users
            $stmt_check = mysqli_prepare($conn, "SELECT id FROM users WHERE username = ? AND id != ?");
            mysqli_stmt_bind_param($stmt_check, "si", $username, $id);
            mysqli_stmt_execute($stmt_check);
            mysqli_stmt_store_result($stmt_check);
            if (mysqli_stmt_num_rows($stmt_check) > 0) {
                $msg = "ชื่อผู้ใช้งาน '$username' ถูกใช้งานโดยบัญชีอื่นแล้ว";
                $msg_type = "error";
            } else {
                $stmt_update = mysqli_prepare($conn, "UPDATE users SET username = ?, name = ?, email = ?, phone = ? WHERE id = ?");
                mysqli_stmt_bind_param($stmt_update, "ssssi", $username, $name, $email, $phone, $id);
                if (mysqli_stmt_execute($stmt_update)) {
                    $msg = "อัปเดตข้อมูลผู้ใช้งาน ID #$id ($username) เรียบร้อยแล้ว";
                    $msg_type = "success";
                } else {
                    $msg = "เกิดข้อผิดพลาดในการแก้ไขข้อมูล: " . mysqli_error($conn);
                    $msg_type = "error";
                }
                mysqli_stmt_close($stmt_update);
            }
            mysqli_stmt_close($stmt_check);
        }
    }

    // 3. DELETE USER
    if ($action === 'delete_user') {
        $id = intval($_POST['id'] ?? 0);
        if ($id > 0) {
            $stmt_del = mysqli_prepare($conn, "DELETE FROM users WHERE id = ?");
            mysqli_stmt_bind_param($stmt_del, "i", $id);
            if (mysqli_stmt_execute($stmt_del)) {
                $msg = "ลบรายการผู้ใช้งาน ID #$id เรียบร้อยแล้ว";
                $msg_type = "success";
            } else {
                $msg = "เกิดข้อผิดพลาดในการลบผู้ใช้งาน: " . mysqli_error($conn);
                $msg_type = "error";
            }
            mysqli_stmt_close($stmt_del);
        }
    }
}

// -------------------------------------------------------------
// Calculate Summary Statistics (KPI Stat Cards)
// -------------------------------------------------------------
$stat_total_res = mysqli_query($conn, "SELECT COUNT(*) as c FROM users");
$total_users_count = $stat_total_res ? (int)mysqli_fetch_assoc($stat_total_res)['c'] : 0;

$stat_month_res = mysqli_query($conn, "SELECT COUNT(*) as c FROM users WHERE MONTH(created_at) = MONTH(CURRENT_DATE()) AND YEAR(created_at) = YEAR(CURRENT_DATE())");
$new_month_count = $stat_month_res ? (int)mysqli_fetch_assoc($stat_month_res)['c'] : 0;

$stat_contact_res = mysqli_query($conn, "SELECT COUNT(*) as c FROM users WHERE (email IS NOT NULL AND email != '') OR (phone IS NOT NULL AND phone != '')");
$with_contact_count = $stat_contact_res ? (int)mysqli_fetch_assoc($stat_contact_res)['c'] : 0;

// -------------------------------------------------------------
// Search & Filter
// -------------------------------------------------------------
$search_query = isset($_GET['search']) ? trim($_GET['search']) : '';
$where_clauses = [];
if (!empty($search_query)) {
    $safe_search = mysqli_real_escape_string($conn, $search_query);
    $where_clauses[] = "(username LIKE '%$safe_search%' OR name LIKE '%$safe_search%' OR email LIKE '%$safe_search%' OR phone LIKE '%$safe_search%')";
}
$where_sql = !empty($where_clauses) ? "WHERE " . implode(" AND ", $where_clauses) : "";

// Count filtered rows
$count_query = "SELECT COUNT(*) as total FROM users $where_sql";
$count_res = mysqli_query($conn, $count_query);
$filtered_count = $count_res ? (int)mysqli_fetch_assoc($count_res)['total'] : 0;

// -------------------------------------------------------------
// Pagination
// -------------------------------------------------------------
$limit_param = isset($_GET['limit']) ? trim($_GET['limit']) : '10';
$is_all = ($limit_param === 'all');
$limit = $is_all ? 999999 : max(5, intval($limit_param));
$total_pages = $is_all ? 1 : max(1, ceil($filtered_count / $limit));

$page = max(1, min($total_pages, intval($_GET['page'] ?? 1)));
$offset = ($page - 1) * $limit;

// Query Users Data
$sql = "SELECT * FROM users $where_sql ORDER BY id DESC LIMIT $limit OFFSET $offset";
$result = mysqli_query($conn, $sql);
$shown_count = $result ? mysqli_num_rows($result) : 0;

// Start & End index for display
$start_record = ($filtered_count > 0) ? ($offset + 1) : 0;
$end_record = min($offset + $shown_count, $filtered_count);
?>
<!DOCTYPE html>
<html lang="th">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>OnlySport - จัดการรายชื่อผู้ใช้งาน (User Management)</title>
  <link rel="stylesheet" href="style.css?v=<?php echo file_exists('style.css') ? filemtime('style.css') : time(); ?>">
  <style>
    /* Custom User Page Enhancements */
    .user-avatar {
      width: 40px;
      height: 40px;
      border-radius: 50%;
      background: linear-gradient(135deg, #fee2e2 0%, #fecaca 100%);
      color: #b91c1c;
      display: inline-flex;
      align-items: center;
      justify-content: center;
      font-weight: 700;
      font-size: 14px;
      border: 2px solid #ffffff;
      box-shadow: 0 1px 3px rgba(0,0,0,0.08);
      flex-shrink: 0;
      text-transform: uppercase;
    }
    .user-cell {
      display: flex;
      align-items: center;
      gap: 12px;
    }
    .username-badge {
      display: inline-flex;
      align-items: center;
      gap: 4px;
      padding: 3px 10px;
      background: #eff6ff;
      color: #1d4ed8;
      border: 1px solid #bfdbfe;
      border-radius: 9999px;
      font-size: 12px;
      font-weight: 600;
    }
    .contact-link {
      display: inline-flex;
      align-items: center;
      gap: 6px;
      color: var(--text-secondary);
      text-decoration: none;
      font-size: 13px;
      transition: color 0.15s ease;
    }
    .contact-link:hover {
      color: var(--primary);
      text-decoration: underline;
    }
    .contact-icon {
      color: var(--text-light);
      flex-shrink: 0;
    }
    .toolbar-wrapper {
      display: flex;
      justify-content: space-between;
      align-items: center;
      gap: 16px;
      padding: 16px 22px;
      background: #fbfcfe;
      border-bottom: 1px solid var(--border);
      flex-wrap: wrap;
    }
    .search-filter-box {
      display: flex;
      align-items: center;
      gap: 8px;
      flex: 1;
      max-width: 460px;
      min-width: 260px;
    }
    .search-input-field {
      width: 100%;
      padding: 8px 14px;
      border: 1px solid var(--border);
      border-radius: var(--radius);
      font-size: 13.5px;
      outline: none;
      transition: all 0.15s ease;
      background: #ffffff;
      font-family: inherit;
    }
    .search-input-field:focus {
      border-color: var(--primary);
      box-shadow: 0 0 0 3px rgba(211, 47, 47, 0.12);
    }
    .limit-select {
      padding: 7px 12px;
      border: 1px solid var(--border);
      border-radius: var(--radius);
      font-size: 13px;
      color: var(--text-secondary);
      background: #ffffff;
      outline: none;
      cursor: pointer;
      font-family: inherit;
    }

    /* Pagination Styles */
    .pagination-bar {
      display: flex;
      align-items: center;
      justify-content: space-between;
      padding: 16px 22px;
      border-top: 1px solid var(--border);
      flex-wrap: wrap;
      gap: 12px;
      font-size: 13.5px;
      color: var(--text-muted);
    }
    .pagination-nav {
      display: flex;
      align-items: center;
      gap: 4px;
    }
    .page-link-btn {
      min-width: 34px;
      height: 34px;
      padding: 0 10px;
      display: inline-flex;
      align-items: center;
      justify-content: center;
      border-radius: var(--radius-sm);
      border: 1px solid var(--border);
      background: #ffffff;
      color: var(--text-secondary);
      font-size: 13px;
      font-weight: 600;
      text-decoration: none;
      transition: all 0.15s ease;
    }
    .page-link-btn:hover:not(.disabled) {
      background: var(--bg-subtle);
      border-color: #cbd5e1;
      color: var(--text-main);
    }
    .page-link-btn.active {
      background: var(--primary-gradient);
      border-color: transparent;
      color: #ffffff;
      box-shadow: 0 2px 6px rgba(211, 47, 47, 0.28);
    }
    .page-link-btn.disabled {
      opacity: 0.45;
      cursor: not-allowed;
      pointer-events: none;
    }

    /* Modal Dialog Window */
    .modal-overlay {
      position: fixed;
      inset: 0;
      background: rgba(15, 23, 42, 0.55);
      backdrop-filter: blur(4px);
      display: none;
      align-items: center;
      justify-content: center;
      z-index: 9999;
      padding: 16px;
      animation: fadeIn 0.18s ease-out;
    }
    .modal-overlay.active {
      display: flex;
    }
    .modal-card {
      background: #ffffff;
      border-radius: var(--radius-lg);
      box-shadow: var(--shadow-lg);
      width: 100%;
      max-width: 520px;
      overflow: hidden;
      animation: slideDown 0.2s cubic-bezier(0.16, 1, 0.3, 1);
      border: 1px solid var(--border);
    }
    .modal-header {
      padding: 18px 24px;
      border-bottom: 1px solid var(--border);
      display: flex;
      align-items: center;
      justify-content: space-between;
      background: #ffffff;
    }
    .modal-title {
      font-size: 16.5px;
      font-weight: 700;
      color: var(--text-main);
      display: flex;
      align-items: center;
      gap: 8px;
    }
    .modal-close-btn {
      width: 32px;
      height: 32px;
      border-radius: 8px;
      border: none;
      background: #f1f5f9;
      color: #64748b;
      cursor: pointer;
      display: flex;
      align-items: center;
      justify-content: center;
      font-size: 18px;
      line-height: 1;
      transition: all 0.15s ease;
    }
    .modal-close-btn:hover {
      background: #fee2e2;
      color: #dc2626;
    }
    .modal-body {
      padding: 24px;
    }
    .modal-footer {
      padding: 16px 24px;
      background: #f8fafc;
      border-top: 1px solid var(--border);
      display: flex;
      justify-content: flex-end;
      gap: 10px;
    }
    .form-group {
      margin-bottom: 16px;
    }
    .form-label {
      display: block;
      font-size: 13px;
      font-weight: 600;
      color: var(--text-main);
      margin-bottom: 6px;
    }
    .form-label span.req {
      color: #dc2626;
      margin-left: 2px;
    }
    .form-control {
      width: 100%;
      padding: 9px 14px;
      border: 1px solid var(--border);
      border-radius: var(--radius);
      font-size: 13.5px;
      color: var(--text-main);
      background: #ffffff;
      outline: none;
      font-family: inherit;
      transition: all 0.15s ease;
    }
    .form-control:focus {
      border-color: var(--primary);
      box-shadow: 0 0 0 3px rgba(211, 47, 47, 0.12);
    }
    .form-hint {
      font-size: 11.5px;
      color: var(--text-light);
      margin-top: 4px;
    }
    @keyframes fadeIn {
      from { opacity: 0; }
      to { opacity: 1; }
    }
    @keyframes slideDown {
      from { transform: translateY(-16px); opacity: 0; }
      to { transform: translateY(0); opacity: 1; }
    }
  </style>
</head>
<body>

<?php include "navbar.php"; ?>

<div class="container">

  <!-- Alert Banner -->
  <?php if (!empty($msg)): ?>
    <div class="alert" id="alert-banner" style="<?php echo ($msg_type === 'error') ? 'background:#fef2f2; border-color:#fecaca; color:#991b1b;' : ''; ?>">
      <div class="alert-content">
        <?php if ($msg_type === 'error'): ?>
          <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
            <circle cx="12" cy="12" r="10"></circle>
            <line x1="12" y1="8" x2="12" y2="12"></line>
            <line x1="12" y1="16" x2="12.01" y2="16"></line>
          </svg>
        <?php else: ?>
          <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
            <polyline points="20 6 9 17 4 12"></polyline>
          </svg>
        <?php endif; ?>
        <span><?php echo htmlspecialchars($msg); ?></span>
      </div>
      <button class="alert-close" onclick="document.getElementById('alert-banner').style.display='none';">&times;</button>
    </div>
  <?php endif; ?>

  <!-- Top 3 Stat Cards Summary -->
  <div class="three-stat-grid" style="margin-bottom: 24px;">
    <!-- Stat 1: Total Users -->
    <div class="summary-stat-box">
      <div class="stat-icon-circle orders-bg">
        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
          <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path>
          <circle cx="9" cy="7" r="4"></circle>
          <path d="M23 21v-2a4 4 0 0 0-3-3.87"></path>
          <path d="M16 3.13a4 4 0 0 1 0 7.75"></path>
        </svg>
      </div>
      <div class="stat-meta">
        <div class="stat-meta-title">ผู้ใช้งานทั้งหมด</div>
        <div class="stat-meta-value">
          <?php echo number_format($total_users_count); ?> 
          <span class="stat-meta-unit">คน</span>
        </div>
      </div>
    </div>

    <!-- Stat 2: New This Month -->
    <div class="summary-stat-box">
      <div class="stat-icon-circle revenue-bg">
        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
          <path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"></path>
          <circle cx="9" cy="7" r="4"></circle>
          <line x1="19" y1="8" x2="19" y2="14"></line>
          <line x1="22" y1="11" x2="16" y2="11"></line>
        </svg>
      </div>
      <div class="stat-meta">
        <div class="stat-meta-title">ผู้ใช้ใหม่ในเดือนนี้</div>
        <div class="stat-meta-value">
          <?php echo number_format($new_month_count); ?> 
          <span class="stat-meta-unit">คน</span>
        </div>
      </div>
    </div>

    <!-- Stat 3: Verified Contacts -->
    <div class="summary-stat-box">
      <div class="stat-icon-circle products-bg">
        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
          <path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"></path>
        </svg>
      </div>
      <div class="stat-meta">
        <div class="stat-meta-title">มีข้อมูลติดต่อครบถ้วน</div>
        <div class="stat-meta-value">
          <?php echo number_format($with_contact_count); ?> 
          <span class="stat-meta-unit">คน</span>
        </div>
      </div>
    </div>
  </div>

  <!-- Main User Management Table Card -->
  <div class="card">
    <div class="card-header">
      <div>
        <h1 class="card-title">
          <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" style="color: var(--primary);">
            <path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"></path>
            <circle cx="9" cy="7" r="4"></circle>
            <path d="M22 21v-2a4 4 0 0 0-3-3.87"></path>
            <path d="M16 3.13a4 4 0 0 1 0 7.75"></path>
          </svg>
          รายชื่อผู้ใช้งานระบบ (User Management)
          <span class="card-subtitle">(<?php echo number_format($filtered_count); ?> รายการ<?php echo !empty($search_query) ? 'ที่พบ' : 'ทั้งหมด'; ?>)</span>
        </h1>
      </div>

      <div style="display: flex; gap: 10px; align-items: center; flex-wrap: wrap;">
        <button type="button" class="btn btn-primary btn-sm" onclick="openAddModal()">
          <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"></path>
          </svg>
          เพิ่มผู้ใช้งานใหม่
        </button>
      </div>
    </div>

    <!-- Search Box and Per Page Filter Toolbar -->
    <div class="toolbar-wrapper">
      <form method="GET" action="users.php" class="search-filter-box">
        <input type="text" 
               name="search" 
               class="search-input-field" 
               placeholder="ค้นหาด้วย Username, ชื่อ หรืออีเมล..." 
               value="<?php echo htmlspecialchars($search_query); ?>">
        
        <?php if (!empty($limit_param) && $limit_param !== '10'): ?>
          <input type="hidden" name="limit" value="<?php echo htmlspecialchars($limit_param); ?>">
        <?php endif; ?>

        <button type="submit" class="btn btn-primary btn-sm" style="flex-shrink: 0;">
          <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
            <circle cx="11" cy="11" r="8"></circle>
            <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
          </svg>
          ค้นหา
        </button>

        <?php if (!empty($search_query)): ?>
          <a href="users.php<?php echo ($limit_param !== '10') ? '?limit=' . htmlspecialchars($limit_param) : ''; ?>" 
             class="btn btn-secondary btn-sm" 
             style="flex-shrink: 0;" 
             title="ล้างการค้นหา">
            ล้าง
          </a>
        <?php endif; ?>
      </form>

      <!-- Per Page Limit Selector -->
      <div style="display: flex; align-items: center; gap: 8px; font-size: 13px; color: var(--text-muted);">
        <span>แสดงต่อหน้า:</span>
        <select class="limit-select" onchange="changeLimit(this.value)">
          <option value="10" <?php echo ($limit_param === '10') ? 'selected' : ''; ?>>10 คน</option>
          <option value="25" <?php echo ($limit_param === '25') ? 'selected' : ''; ?>>25 คน</option>
          <option value="50" <?php echo ($limit_param === '50') ? 'selected' : ''; ?>>50 คน</option>
          <option value="all" <?php echo ($limit_param === 'all') ? 'selected' : ''; ?>>แสดงทั้งหมด</option>
        </select>
      </div>
    </div>

    <!-- Users Table -->
    <div class="table-responsive">
      <table>
        <thead>
          <tr>
            <th style="width: 60px;">ID</th>
            <th style="width: 220px;">ชื่อผู้ใช้งาน (Username)</th>
            <th style="width: 200px;">ชื่อ-นามสกุล (Name)</th>
            <th style="width: 220px;">อีเมล (Email)</th>
            <th style="width: 150px;">เบอร์โทรศัพท์ (Phone)</th>
            <th style="width: 150px;">วันที่สร้าง (Date Created)</th>
            <th style="width: 140px; text-align: center;">จัดการ</th>
          </tr>
        </thead>
        <tbody>
          <?php if ($shown_count > 0): ?>
            <?php 
              while ($user = mysqli_fetch_assoc($result)): 
                // Generate initials for avatar
                $initial = mb_substr(trim($user['name'] ?: $user['username']), 0, 1, 'UTF-8');
            ?>
              <tr>
                <td><strong>#<?php echo $user['id']; ?></strong></td>
                <td>
                  <div class="user-cell">
                    <div class="user-avatar"><?php echo htmlspecialchars($initial); ?></div>
                    <div>
                      <div class="username-badge">
                        <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                          <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
                          <circle cx="12" cy="7" r="4"></circle>
                        </svg>
                        <?php echo htmlspecialchars($user['username']); ?>
                      </div>
                    </div>
                  </div>
                </td>
                <td>
                  <strong style="color: var(--text-main); font-size: 13.5px;">
                    <?php echo htmlspecialchars($user['name'] ?: '-'); ?>
                  </strong>
                </td>
                <td>
                  <?php if (!empty($user['email'])): ?>
                    <a href="mailto:<?php echo htmlspecialchars($user['email']); ?>" class="contact-link" title="ส่งอีเมล">
                      <svg class="contact-icon" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"></path>
                        <polyline points="22,6 12,13 2,6"></polyline>
                      </svg>
                      <?php echo htmlspecialchars($user['email']); ?>
                    </a>
                  <?php else: ?>
                    <span style="color: var(--text-light); font-size: 13px;">-</span>
                  <?php endif; ?>
                </td>
                <td>
                  <?php if (!empty($user['phone'])): ?>
                    <a href="tel:<?php echo htmlspecialchars($user['phone']); ?>" class="contact-link" title="โทรออก">
                      <svg class="contact-icon" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"></path>
                      </svg>
                      <?php echo htmlspecialchars($user['phone']); ?>
                    </a>
                  <?php else: ?>
                    <span style="color: var(--text-light); font-size: 13px;">-</span>
                  <?php endif; ?>
                </td>
                <td style="font-size: 12.5px; color: var(--text-muted); white-space: nowrap;">
                  <?php 
                    $created_time = !empty($user['created_at']) ? strtotime($user['created_at']) : 0;
                    echo ($created_time > 0) ? date('d/m/Y H:i', $created_time) : '-';
                  ?>
                </td>
                <td style="text-align: center;">
                  <div style="display: flex; gap: 6px; justify-content: center; align-items: center;">
                    <button type="button" 
                            class="btn btn-sm btn-edit" 
                            title="แก้ไขข้อมูลผู้ใช้"
                            onclick='openEditModal(<?php echo json_encode($user, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP | JSON_UNESCAPED_UNICODE); ?>)'>
                      <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path>
                        <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path>
                      </svg>
                      แก้ไข
                    </button>

                    <button type="button" 
                            class="btn btn-sm btn-delete" 
                            title="ลบผู้ใช้งาน"
                            onclick="confirmDelete(<?php echo $user['id']; ?>, '<?php echo addslashes($user['username']); ?>')">
                      <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <polyline points="3 6 5 6 21 6"></polyline>
                        <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path>
                      </svg>
                      ลบ
                    </button>
                  </div>
                </td>
              </tr>
            <?php endwhile; ?>
          <?php else: ?>
            <tr>
              <td colspan="7">
                <div class="empty-state" style="padding: 48px 20px; text-align: center;">
                  <div class="empty-state-icon" style="margin: 0 auto 12px; width: 56px; height: 56px; border-radius: 14px; background: #f1f5f9; display: flex; align-items: center; justify-content: center; color: var(--text-light);">
                    <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                      <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path>
                      <circle cx="9" cy="7" r="4"></circle>
                      <line x1="23" y1="11" x2="17" y2="11"></line>
                    </svg>
                  </div>
                  <p style="color: var(--text-muted); font-size: 14px; margin-bottom: 12px;">
                    <?php echo !empty($search_query) ? "ไม่พบรายชื่อผู้ใช้งานที่ตรงกับคำค้นหา '$search_query'" : "ยังไม่มีข้อมูลผู้ใช้งานในระบบ"; ?>
                  </p>
                  <button type="button" class="btn btn-primary btn-sm" onclick="openAddModal()">
                    <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"></path>
                    </svg>
                    เพิ่มผู้ใช้งานใหม่คนแรก
                  </button>
                </div>
              </td>
            </tr>
          <?php endif; ?>
        </tbody>
      </table>
    </div>

    <!-- Pagination Footer -->
    <?php if ($filtered_count > 0): ?>
      <div class="pagination-bar">
        <div>
          กำลังแสดง <strong><?php echo $start_record; ?> - <?php echo $end_record; ?></strong> จากทั้งหมด <strong><?php echo number_format($filtered_count); ?></strong> คน
        </div>

        <?php if (!$is_all && $total_pages > 1): ?>
          <nav class="pagination-nav">
            <!-- First & Prev -->
            <a href="users.php?page=1<?php echo !empty($search_query) ? '&search=' . urlencode($search_query) : ''; ?><?php echo ($limit_param !== '10') ? '&limit=' . urlencode($limit_param) : ''; ?>" 
               class="page-link-btn <?php echo ($page <= 1) ? 'disabled' : ''; ?>" 
               title="หน้าแรก">&laquo;</a>
            
            <a href="users.php?page=<?php echo max(1, $page - 1); ?><?php echo !empty($search_query) ? '&search=' . urlencode($search_query) : ''; ?><?php echo ($limit_param !== '10') ? '&limit=' . urlencode($limit_param) : ''; ?>" 
               class="page-link-btn <?php echo ($page <= 1) ? 'disabled' : ''; ?>" 
               title="หน้าก่อนหน้า">&lsaquo;</a>

            <!-- Numeric Pages (Sliding Window) -->
            <?php
              $start_p = max(1, $page - 2);
              $end_p = min($total_pages, $page + 2);
              if ($start_p > 1) {
                  echo '<span style="padding: 0 4px; color: var(--text-light);">&hellip;</span>';
              }
              for ($p = $start_p; $p <= $end_p; $p++):
            ?>
              <a href="users.php?page=<?php echo $p; ?><?php echo !empty($search_query) ? '&search=' . urlencode($search_query) : ''; ?><?php echo ($limit_param !== '10') ? '&limit=' . urlencode($limit_param) : ''; ?>" 
                 class="page-link-btn <?php echo ($p === $page) ? 'active' : ''; ?>">
                <?php echo $p; ?>
              </a>
            <?php endfor; ?>
            <?php
              if ($end_p < $total_pages) {
                  echo '<span style="padding: 0 4px; color: var(--text-light);">&hellip;</span>';
              }
            ?>

            <!-- Next & Last -->
            <a href="users.php?page=<?php echo min($total_pages, $page + 1); ?><?php echo !empty($search_query) ? '&search=' . urlencode($search_query) : ''; ?><?php echo ($limit_param !== '10') ? '&limit=' . urlencode($limit_param) : ''; ?>" 
               class="page-link-btn <?php echo ($page >= $total_pages) ? 'disabled' : ''; ?>" 
               title="หน้าถัดไป">&rsaquo;</a>
            
            <a href="users.php?page=<?php echo $total_pages; ?><?php echo !empty($search_query) ? '&search=' . urlencode($search_query) : ''; ?><?php echo ($limit_param !== '10') ? '&limit=' . urlencode($limit_param) : ''; ?>" 
               class="page-link-btn <?php echo ($page >= $total_pages) ? 'disabled' : ''; ?>" 
               title="หน้าสุดท้าย">&raquo;</a>
          </nav>
        <?php endif; ?>
      </div>
    <?php endif; ?>
  </div>
</div>

<!-- ========================================== -->
<!-- 1. MODAL: ADD USER                         -->
<!-- ========================================== -->
<div class="modal-overlay" id="addUserModal" onclick="handleBackdropClick(event, 'addUserModal')">
  <div class="modal-card">
    <div class="modal-header">
      <div class="modal-title">
        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" style="color: var(--primary);">
          <path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"></path>
          <circle cx="9" cy="7" r="4"></circle>
          <line x1="19" y1="8" x2="19" y2="14"></line>
          <line x1="22" y1="11" x2="16" y2="11"></line>
        </svg>
        เพิ่มผู้ใช้งานใหม่
      </div>
      <button type="button" class="modal-close-btn" onclick="closeModal('addUserModal')">&times;</button>
    </div>
    <form method="POST" action="users.php">
      <input type="hidden" name="action" value="add_user">
      <div class="modal-body">
        <div class="form-group">
          <label class="form-label">ชื่อผู้ใช้งาน (Username) <span class="req">*</span></label>
          <input type="text" name="username" class="form-control" placeholder="เช่น player99, alex20" required autofocus>
          <div class="form-hint">ระบุตัวอักษรภาษาอังกฤษหรือตัวเลขสำหรับใช้ระบุตัวตนในระบบ</div>
        </div>

        <div class="form-group">
          <label class="form-label">ชื่อ-นามสกุล (Name)</label>
          <input type="text" name="name" class="form-control" placeholder="เช่น ชนาธิป สรงกระสินธ์">
        </div>

        <div class="form-group">
          <label class="form-label">อีเมล (Email)</label>
          <input type="email" name="email" class="form-control" placeholder="เช่น user@example.com">
        </div>

        <div class="form-group" style="margin-bottom: 0;">
          <label class="form-label">เบอร์โทรศัพท์ (Phone)</label>
          <input type="tel" name="phone" class="form-control" placeholder="เช่น 081-234-5678">
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" onclick="closeModal('addUserModal')">ยกเลิก</button>
        <button type="submit" class="btn btn-primary">
          <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
            <polyline points="20 6 9 17 4 12"></polyline>
          </svg>
          บันทึกผู้ใช้ใหม่
        </button>
      </div>
    </form>
  </div>
</div>

<!-- ========================================== -->
<!-- 2. MODAL: EDIT USER                        -->
<!-- ========================================== -->
<div class="modal-overlay" id="editUserModal" onclick="handleBackdropClick(event, 'editUserModal')">
  <div class="modal-card">
    <div class="modal-header">
      <div class="modal-title">
        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" style="color: var(--info);">
          <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path>
          <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path>
        </svg>
        แก้ไขข้อมูลผู้ใช้งาน (<span id="editUserTitleId">#0</span>)
      </div>
      <button type="button" class="modal-close-btn" onclick="closeModal('editUserModal')">&times;</button>
    </div>
    <form method="POST" action="users.php">
      <input type="hidden" name="action" value="edit_user">
      <input type="hidden" name="id" id="editUserId" value="0">
      
      <div class="modal-body">
        <div class="form-group">
          <label class="form-label">ชื่อผู้ใช้งาน (Username) <span class="req">*</span></label>
          <input type="text" name="username" id="editUsername" class="form-control" required>
        </div>

        <div class="form-group">
          <label class="form-label">ชื่อ-นามสกุล (Name)</label>
          <input type="text" name="name" id="editName" class="form-control">
        </div>

        <div class="form-group">
          <label class="form-label">อีเมล (Email)</label>
          <input type="email" name="email" id="editEmail" class="form-control">
        </div>

        <div class="form-group" style="margin-bottom: 0;">
          <label class="form-label">เบอร์โทรศัพท์ (Phone)</label>
          <input type="tel" name="phone" id="editPhone" class="form-control">
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" onclick="closeModal('editUserModal')">ยกเลิก</button>
        <button type="submit" class="btn btn-primary">
          <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
            <polyline points="20 6 9 17 4 12"></polyline>
          </svg>
          บันทึกการแก้ไข
        </button>
      </div>
    </form>
  </div>
</div>

<!-- ========================================== -->
<!-- 3. HIDDEN FORM FOR SAFE DELETE             -->
<!-- ========================================== -->
<form id="deleteUserForm" method="POST" action="users.php" style="display:none;">
  <input type="hidden" name="action" value="delete_user">
  <input type="hidden" name="id" id="deleteUserId" value="0">
</form>

<script>
function openAddModal() {
  document.getElementById('addUserModal').classList.add('active');
}

function openEditModal(user) {
  document.getElementById('editUserId').value = user.id;
  document.getElementById('editUserTitleId').textContent = '#' + user.id;
  document.getElementById('editUsername').value = user.username || '';
  document.getElementById('editName').value = user.name || '';
  document.getElementById('editEmail').value = user.email || '';
  document.getElementById('editPhone').value = user.phone || '';
  document.getElementById('editUserModal').classList.add('active');
}

function closeModal(modalId) {
  document.getElementById(modalId).classList.remove('active');
}

function handleBackdropClick(e, modalId) {
  if (e.target.id === modalId) {
    closeModal(modalId);
  }
}

document.addEventListener('keydown', function(e) {
  if (e.key === 'Escape') {
    closeModal('addUserModal');
    closeModal('editUserModal');
  }
});

function confirmDelete(id, username) {
  if (confirm(`คุณต้องการลบผู้ใช้งาน "${username}" (ID: #${id}) ออกจากระบบหรือไม่?\n\nการกระทำนี้ไม่สามารถยกเลิกได้`)) {
    document.getElementById('deleteUserId').value = id;
    document.getElementById('deleteUserForm').submit();
  }
}

function changeLimit(limitVal) {
  const url = new URL(window.location.href);
  url.searchParams.set('limit', limitVal);
  url.searchParams.set('page', 1);
  window.location.href = url.toString();
}
</script>

</body>
</html>

