<?php
$current_page = $current_page ?? 'dashboard';
$search_query = $search_query ?? '';

$nav_pending_count = 0;
if (isset($conn) && $conn) {
    $q_badge = @mysqli_query($conn, "SELECT COUNT(*) as c FROM orders WHERE status IN ('Pending', 'รอดำเนินการ', 'pending', 'Processing', 'กำลังดำเนินการ')");
    if ($q_badge) {
        $nav_pending_count = (int)(@mysqli_fetch_assoc($q_badge)['c'] ?? 0);
    }
}
?>
<!-- Top Header Bar (Original Brand RED Palette #D90429 / #E5383B) -->
<header class="top-header-bar">
  <div class="header-left">
    <button type="button" class="sidebar-toggle-btn" onclick="toggleSidebar()" aria-label="Toggle Navigation">
      <svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16"></path>
      </svg>
    </button>
    <a href="index.php" class="header-brand">
      <span class="header-brand-icon">
        <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
          <circle cx="12" cy="12" r="10"></circle>
          <path d="m4.93 4.93 4.24 4.24"></path>
          <path d="m14.83 9.17 4.24-4.24"></path>
          <path d="m14.83 14.83 4.24 4.24"></path>
          <path d="m9.17 14.83-4.24 4.24"></path>
          <circle cx="12" cy="12" r="4"></circle>
        </svg>
      </span>
      <div class="header-brand-text">
        <span class="brand-title">Only<span class="brand-accent-red">Sport</span></span>
        <span class="brand-sub">Back Office Suite</span>
      </div>
    </a>
  </div>

  <div class="header-right">
    <div class="header-user-badge">
      <div class="header-avatar-circle">OS</div>
      <div class="header-user-meta">
        <span class="header-admin-name">Admin OnlySport</span>
        <span class="header-admin-role">Super Administrator</span>
      </div>
    </div>
  </div>
</header>

<!-- Left Sidebar (~240px, Light Grey / Ice-Blue #F4F7F9) -->
<aside class="left-sidebar" id="leftSidebar">
  <div class="sidebar-label-head">MAIN NAVIGATION</div>
  <nav class="sidebar-menu-list">
    <!-- 1. Dashboard -->
    <a href="index.php" class="sidebar-link <?php echo ($current_page === 'dashboard') ? 'active' : ''; ?>">
      <svg class="sidebar-svg" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
        <rect x="3" y="3" width="7" height="7"></rect>
        <rect x="14" y="3" width="7" height="7"></rect>
        <rect x="14" y="14" width="7" height="7"></rect>
        <rect x="3" y="14" width="7" height="7"></rect>
      </svg>
      <span class="sidebar-text">Dashboard</span>
    </a>

    <!-- 2. Products / สินค้า -->
    <a href="menu.php" class="sidebar-link <?php echo ($current_page === 'products' || $current_page === 'menu' || $current_page === 'manage') ? 'active' : ''; ?>">
      <svg class="sidebar-svg" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
        <path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"></path>
        <polyline points="3.27 6.96 12 12.01 20.73 6.96"></polyline>
        <line x1="12" y1="22.08" x2="12" y2="12"></line>
      </svg>
      <span class="sidebar-text">Products / สินค้า</span>
    </a>

    <!-- 3. Orders / คำสั่งซื้อ -->
    <a href="orders.php" class="sidebar-link <?php echo ($current_page === 'orders') ? 'active' : ''; ?>">
      <svg class="sidebar-svg" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
        <path d="M6 2L3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4z"></path>
        <line x1="3" y1="6" x2="21" y2="6"></line>
        <path d="M16 10a4 4 0 0 1-8 0"></path>
      </svg>
      <span class="sidebar-text">Orders / คำสั่งซื้อ</span>
      <?php if ($nav_pending_count > 0): ?>
        <span class="sidebar-counter-badge"><?php echo $nav_pending_count; ?></span>
      <?php endif; ?>
    </a>

    <!-- 4. Reviews & Ratings / รีวิวสินค้า -->
    <a href="ratings.php" class="sidebar-link <?php echo ($current_page === 'ratings') ? 'active' : ''; ?>">
      <svg class="sidebar-svg" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
        <polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"></polygon>
      </svg>
      <span class="sidebar-text">Reviews & Ratings / รีวิวสินค้า</span>
    </a>

    <!-- 5. Users / ผู้ใช้งาน -->
    <a href="users.php" class="sidebar-link <?php echo ($current_page === 'users') ? 'active' : ''; ?>">
      <svg class="sidebar-svg" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
        <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path>
        <circle cx="9" cy="7" r="4"></circle>
        <path d="M23 21v-2a4 4 0 0 0-3-3.87"></path>
        <path d="M16 3.13a4 4 0 0 1 0 7.75"></path>
      </svg>
      <span class="sidebar-text">Users / ผู้ใช้งาน</span>
    </a>
  </nav>

  <div class="sidebar-bottom-panel">
    <div class="sidebar-system-card">
      <div class="system-title">OnlySport Back Office</div>
      <div class="system-meta">v2.5 &bull; PHP 8.2 &bull; MySQL 8.0</div>
    </div>
  </div>
</aside>

<script>
function toggleSidebar() {
  const sb = document.getElementById('leftSidebar');
  if (sb) sb.classList.toggle('show');
}
</script>
