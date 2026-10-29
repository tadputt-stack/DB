<?php
include "db_conn.php";

$current_page = 'manage';
$error = "";

if (!isset($_GET['id']) || empty($_GET['id'])) {
    header("Location: manage.php");
    exit();
}

$id = intval($_GET['id']);

if (isset($_POST["submit"])) {
    $name = trim($_POST['name'] ?? '');
    $series_id = intval($_POST['series_id'] ?? 0);
    $ground_type = trim($_POST['ground_type'] ?? 'FG');
    $price = floatval($_POST['price'] ?? 0);
    $imageUrl = trim($_POST['imageUrl'] ?? '');
    $description = trim($_POST['description'] ?? '');

    if (empty($name) || empty($series_id) || empty($ground_type) || $price < 0) {
        $error = "กรุณากรอกข้อมูลที่จำเป็นให้ครบถ้วน";
    } else {
        $sql = "UPDATE products 
                SET name = ?, imageUrl = ?, price = ?, series_id = ?, ground_type = ?, description = ? 
                WHERE id = ?";
        $stmt = mysqli_prepare($conn, $sql);

        if ($stmt) {
            mysqli_stmt_bind_param($stmt, "ssdissi", $name, $imageUrl, $price, $series_id, $ground_type, $description, $id);
            if (mysqli_stmt_execute($stmt)) {
                mysqli_stmt_close($stmt);
                mysqli_close($conn);
                header("Location: manage.php?msg=" . urlencode("แก้ไขสินค้า '$name' เรียบร้อยแล้ว"));
                exit();
            } else {
                $error = "เกิดข้อผิดพลาดในการอัปเดต: " . mysqli_stmt_error($stmt);
            }
            mysqli_stmt_close($stmt);
        } else {
            $error = "เกิดข้อผิดพลาดในการเตรียม SQL: " . mysqli_error($conn);
        }
    }
}

$fetch_sql = "SELECT * FROM products WHERE id = ? LIMIT 1";
$fetch_stmt = mysqli_prepare($conn, $fetch_sql);
mysqli_stmt_bind_param($fetch_stmt, "i", $id);
mysqli_stmt_execute($fetch_stmt);
$result = mysqli_stmt_get_result($fetch_stmt);
$product = mysqli_fetch_assoc($result);
mysqli_stmt_close($fetch_stmt);

if (!$product) {
    mysqli_close($conn);
    header("Location: manage.php?msg=" . urlencode("ไม่พบสินค้ารหัส #$id"));
    exit();
}

$series_query = "SELECT series_id, series_name FROM series ORDER BY series_name ASC";
$series_result = mysqli_query($conn, $series_query);
?>
<!DOCTYPE html>
<html lang="th">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>OnlySport - แก้ไขสินค้า #<?php echo $product['id']; ?></title>
  <link rel="stylesheet" href="style.css?v=<?php echo file_exists('style.css') ? filemtime('style.css') : time(); ?>">
</head>
<body>

<div class="container">
  <?php include "navbar.php"; ?>

  <?php if (!empty($error)): ?>
    <div class="alert alert-error">
      <div class="alert-content">
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
          <circle cx="12" cy="12" r="10"></circle>
          <line x1="12" y1="8" x2="12" y2="12"></line>
          <line x1="12" y1="16" x2="12.01" y2="16"></line>
        </svg>
        <span><?php echo htmlspecialchars($error); ?></span>
      </div>
      <button class="alert-close" onclick="this.parentElement.style.display='none';">&times;</button>
    </div>
  <?php endif; ?>

  <div class="card" style="max-width: 800px; margin: 0 auto 30px;">
    <div class="card-header">
      <div>
        <h1 class="card-title">
          แก้ไขข้อมูลสินค้า #<?php echo $product['id']; ?>
        </h1>
        <span class="card-subtitle"><?php echo htmlspecialchars($product['name']); ?></span>
      </div>
    </div>

    <div class="card-body">
      <form action="edit.php?id=<?php echo $id; ?>" method="POST">
        <div class="form-grid">
          <div class="form-group full-width">
            <label for="name">ชื่อสินค้า <span style="color: var(--primary);">*</span></label>
            <input type="text" id="name" name="name" class="form-control" required value="<?php echo htmlspecialchars($product['name']); ?>">
          </div>

          <div class="form-group">
            <label for="series_id">ซีรีส์ / หมวดหมู่ <span style="color: var(--primary);">*</span></label>
            <select id="series_id" name="series_id" class="form-control" required>
              <option value="">-- เลือกซีรีส์ --</option>
              <?php if ($series_result): ?>
                <?php while ($s = mysqli_fetch_assoc($series_result)): ?>
                  <option value="<?php echo $s['series_id']; ?>" <?php echo ($product['series_id'] == $s['series_id']) ? 'selected' : ''; ?>>
                    <?php echo htmlspecialchars($s['series_name']); ?>
                  </option>
                <?php endwhile; ?>
              <?php endif; ?>
            </select>
          </div>

          <div class="form-group">
            <label for="ground_type">ประเภทพื้น (Ground Type) <span style="color: var(--primary);">*</span></label>
            <select id="ground_type" name="ground_type" class="form-control" required>
              <option value="FG" <?php echo ($product['ground_type'] === 'FG') ? 'selected' : ''; ?>>FG (สนามหญ้าจริง / Firm Ground)</option>
              <option value="TF" <?php echo ($product['ground_type'] === 'TF') ? 'selected' : ''; ?>>TF (สนามหญ้าเทียม / Turf)</option>
              <option value="SG" <?php echo ($product['ground_type'] === 'SG') ? 'selected' : ''; ?>>SG (สนามหญ้านุ่ม / Soft Ground)</option>
              <option value="MG" <?php echo ($product['ground_type'] === 'MG') ? 'selected' : ''; ?>>MG (Multi Ground)</option>
            </select>
          </div>

          <div class="form-group">
            <label for="price">ราคาขาย (บาท ฿) <span style="color: var(--primary);">*</span></label>
            <input type="number" step="0.01" min="0" id="price" name="price" class="form-control" required value="<?php echo htmlspecialchars($product['price']); ?>">
          </div>

          <div class="form-group full-width">
            <label for="imageUrl">ลิงก์รูปภาพ (Image URL)</label>
            <input type="url" id="imageUrl" name="imageUrl" class="form-control" value="<?php echo htmlspecialchars($product['imageUrl']); ?>" oninput="updateImagePreview(this.value)">
            <div class="image-preview-box" id="previewContainer">
              <span id="previewPlaceholder" style="color: var(--text-light); font-size: 13px; <?php echo !empty($product['imageUrl']) ? 'display:none;' : ''; ?>">ตัวอย่างรูปภาพสินค้า</span>
              <img id="previewImage" src="<?php echo htmlspecialchars($product['imageUrl']); ?>" alt="Preview" style="<?php echo !empty($product['imageUrl']) ? 'display:block;' : 'display:none;'; ?>" onerror="onImgError()">
            </div>
          </div>

          <div class="form-group full-width">
            <label for="description">รายละเอียดและสเปกรองเท้า</label>
            <textarea id="description" name="description" class="form-control"><?php echo htmlspecialchars($product['description'] ?? ''); ?></textarea>
          </div>
        </div>

        <div style="display: flex; gap: 12px; justify-content: flex-end; margin-top: 24px; padding-top: 18px; border-top: 1px solid var(--border);">
          <a href="manage.php" class="btn btn-secondary">ยกเลิก</a>
          <button type="submit" name="submit" class="btn btn-primary">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
              <polyline points="20 6 9 17 4 12"></polyline>
            </svg>
            บันทึกการแก้ไข
          </button>
        </div>
      </form>
    </div>
  </div>
</div>

<script>
function updateImagePreview(url) {
  const previewImg = document.getElementById('previewImage');
  const placeholder = document.getElementById('previewPlaceholder');

  if (url && url.trim() !== '') {
    previewImg.src = url.trim();
    previewImg.style.display = 'block';
    placeholder.style.display = 'none';
  } else {
    previewImg.style.display = 'none';
    placeholder.style.display = 'block';
  }
}

function onImgError() {
  const previewImg = document.getElementById('previewImage');
  const placeholder = document.getElementById('previewPlaceholder');
  previewImg.style.display = 'none';
  placeholder.textContent = 'ลิงก์รูปภาพไม่ถูกต้องหรือไม่สามารถโหลดได้';
  placeholder.style.display = 'block';
}
</script>

</body>
</html>
<?php 
mysqli_close($conn); 
?>
