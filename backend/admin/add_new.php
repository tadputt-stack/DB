<?php
include "db_conn.php";

$current_page = 'add_new';
$error = "";

if (isset($_POST["submit"])) {
    $name = trim($_POST['name'] ?? '');
    $series_id = intval($_POST['series_id'] ?? 0);
    $ground_type = trim($_POST['ground_type'] ?? 'FG');
    $price = floatval($_POST['price'] ?? 0);
    $imageUrl = trim($_POST['imageUrl'] ?? '');
    $description = trim($_POST['description'] ?? '');

    if (empty($name) || empty($series_id) || empty($ground_type) || $price < 0) {
        $error = "กรุณากรอกข้อมูลที่จำเป็นให้ครบถ้วน (ชื่อสินค้า, ซีรีส์, ประเภทพื้น, ราคา)";
    } else {
        $sql = "INSERT INTO products (name, imageUrl, price, series_id, ground_type, description) 
                VALUES (?, ?, ?, ?, ?, ?)";
        $stmt = mysqli_prepare($conn, $sql);

        if ($stmt) {
            mysqli_stmt_bind_param($stmt, "ssdiss", $name, $imageUrl, $price, $series_id, $ground_type, $description);
            if (mysqli_stmt_execute($stmt)) {
                mysqli_stmt_close($stmt);
                mysqli_close($conn);
                header("Location: menu.php?msg=" . urlencode("เพิ่มสินค้า '$name' เรียบร้อยแล้ว"));
                exit();
            } else {
                $error = "ไม่สามารถเพิ่มสินค้าได้: " . mysqli_stmt_error($stmt);
            }
            mysqli_stmt_close($stmt);
        } else {
            $error = "เกิดข้อผิดพลาดในการเตรียมคำสั่ง SQL: " . mysqli_error($conn);
        }
    }
}

$series_query = "SELECT series_id, series_name FROM series ORDER BY series_name ASC";
$series_result = mysqli_query($conn, $series_query);
?>
<!DOCTYPE html>
<html lang="th">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>OnlySport - เพิ่มสินค้าใหม่</title>
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
          เพิ่มรองเท้าสตั๊ดใหม่
        </h1>
        <span class="card-subtitle">กรอกรายละเอียดเพื่อลงทะเบียนสินค้าเข้าสู่ระบบคลังสินค้า</span>
      </div>
    </div>

    <div class="card-body">
      <form action="add_new.php" method="POST">
        <div class="form-grid">
          <div class="form-group full-width">
            <label for="name">ชื่อรองเท้าสตั๊ด / สินค้า <span style="color: var(--primary);">*</span></label>
            <input type="text" id="name" name="name" class="form-control" placeholder="เช่น Predator Elite FT FG" required value="<?php echo htmlspecialchars($_POST['name'] ?? ''); ?>">
          </div>

          <div class="form-group">
            <label for="series_id">ซีรีส์ / คอลเลกชัน <span style="color: var(--primary);">*</span></label>
            <select id="series_id" name="series_id" class="form-control" required>
              <option value="">-- เลือกซีรีส์ --</option>
              <?php if ($series_result): ?>
                <?php while ($s = mysqli_fetch_assoc($series_result)): ?>
                  <option value="<?php echo $s['series_id']; ?>" <?php echo (isset($_POST['series_id']) && $_POST['series_id'] == $s['series_id']) ? 'selected' : ''; ?>>
                    <?php echo htmlspecialchars($s['series_name']); ?>
                  </option>
                <?php endwhile; ?>
              <?php endif; ?>
            </select>
          </div>

          <div class="form-group">
            <label for="ground_type">ประเภทปุ่มและพื้นสนาม (Ground Type) <span style="color: var(--primary);">*</span></label>
            <select id="ground_type" name="ground_type" class="form-control" required>
              <option value="FG" <?php echo (isset($_POST['ground_type']) && $_POST['ground_type'] === 'FG') ? 'selected' : ''; ?>>FG (Firm Ground - สนามหญ้าจริง)</option>
              <option value="TF" <?php echo (isset($_POST['ground_type']) && $_POST['ground_type'] === 'TF') ? 'selected' : ''; ?>>TF (Turf - สนามหญ้าเทียม/ร้อยปุ่ม)</option>
              <option value="SG" <?php echo (isset($_POST['ground_type']) && $_POST['ground_type'] === 'SG') ? 'selected' : ''; ?>>SG (Soft Ground - สนามหญ้าเปียกนุ่ม)</option>
              <option value="MG" <?php echo (isset($_POST['ground_type']) && $_POST['ground_type'] === 'MG') ? 'selected' : ''; ?>>MG (Multi Ground - ใช้งานได้หลากหลายพื้นผิว)</option>
            </select>
          </div>

          <div class="form-group">
            <label for="price">ราคาขาย (บาท ฿) <span style="color: var(--primary);">*</span></label>
            <input type="number" step="0.01" min="0" id="price" name="price" class="form-control" placeholder="เช่น 8500.00" required value="<?php echo htmlspecialchars($_POST['price'] ?? ''); ?>">
          </div>

          <div class="form-group full-width">
            <label for="imageUrl">ลิงก์รูปภาพสินค้า (Image URL)</label>
            <input type="url" id="imageUrl" name="imageUrl" class="form-control" placeholder="https://images.unsplash.com/..." value="<?php echo htmlspecialchars($_POST['imageUrl'] ?? ''); ?>" oninput="updateImagePreview(this.value)">
            <div class="image-preview-box" id="previewContainer">
              <span id="previewPlaceholder" style="color: var(--text-light); font-size: 13px;">ตัวอย่างรูปภาพสินค้าจะปรากฏที่นี่</span>
              <img id="previewImage" src="" alt="Preview" style="display: none;" onerror="onImgError()">
            </div>
          </div>

          <div class="form-group full-width">
            <label for="description">รายละเอียดและสเปกรองเท้า</label>
            <textarea id="description" name="description" class="form-control" placeholder="ระบุเทคโนโลยี อัปเปอร์ น้ำหนัก ความกระชับ หรือจุดเด่นของรุ่นนี้..."><?php echo htmlspecialchars($_POST['description'] ?? ''); ?></textarea>
          </div>
        </div>

        <div style="display: flex; gap: 12px; justify-content: flex-end; margin-top: 24px; padding-top: 18px; border-top: 1px solid var(--border);">
          <a href="menu.php" class="btn btn-secondary">ยกเลิก</a>
          <button type="submit" name="submit" class="btn btn-primary">
            <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"></path>
            </svg>
            บันทึกเพิ่มสินค้า
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

document.addEventListener('DOMContentLoaded', () => {
  const input = document.getElementById('imageUrl');
  if (input && input.value) {
    updateImagePreview(input.value);
  }
});
</script>

</body>
</html>
<?php 
mysqli_close($conn); 
?>
