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
<header class="reference-navbar">
  <a href="index.php" class="brand-section">
    <div class="brand-logo-text">
      <span class="brand-icon-circle">
        <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
          <circle cx="12" cy="12" r="10"></circle>
          <path d="m4.93 4.93 4.24 4.24"></path>
          <path d="m14.83 9.17 4.24-4.24"></path>
          <path d="m14.83 14.83 4.24 4.24"></path>
          <path d="m9.17 14.83-4.24 4.24"></path>
          <circle cx="12" cy="12" r="4"></circle>
        </svg>
      </span>
      <span class="brand-name">
        Only<span class="brand-accent">Sport</span>
        <span class="brand-tagline">Cleats Store Manager</span>
      </span>
    </div>
  </a>

  <nav class="nav-links-group">
    <a href="menu.php" class="nav-item-btn <?php echo ($current_page === 'menu' || $current_page === 'products') ? 'active' : ''; ?>">
      <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16"></path>
      </svg>
      <span>รายการสินค้าทั้งหมด</span>
    </a>

    <a href="add_new.php" class="nav-item-btn <?php echo ($current_page === 'add_new') ? 'active' : ''; ?>">
      <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"></path>
      </svg>
      <span>เพิ่มสินค้าใหม่</span>
    </a>

    <a href="manage.php" class="nav-item-btn <?php echo ($current_page === 'manage') ? 'active' : ''; ?>">
      <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
      </svg>
      <span>จัดการสินค้า</span>
    </a>

    <a href="orders.php" class="nav-item-btn <?php echo ($current_page === 'orders') ? 'active' : ''; ?>">
      <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"></path>
      </svg>
      <span>รายการคำสั่งซื้อ</span>
      <?php if ($nav_pending_count > 0): ?>
        <span class="nav-badge"><?php echo $nav_pending_count; ?></span>
      <?php endif; ?>
    </a>

    <a href="index.php" class="nav-item-btn <?php echo ($current_page === 'dashboard') ? 'active' : ''; ?>">
      <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"></path>
      </svg>
      <span>Dashboard</span>
    </a>
  </nav>

  <form action="menu.php" method="GET" class="navbar-search">
    <div class="search-input-wrap">
      <svg class="search-svg" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
      </svg>
      <input type="text" name="search" id="navbarSearchInput" placeholder="ค้นหารองเท้าสตั๊ด..." value="<?php echo htmlspecialchars($search_query); ?>">
      <?php if (!empty($search_query)): ?>
        <a href="<?php echo basename($_SERVER['PHP_SELF']); ?>" class="clear-btn" title="ล้างการค้นหา">&times;</a>
      <?php endif; ?>
    </div>
    <button type="submit" class="search-submit-btn">ค้นหา</button>
  </form>
</header>
