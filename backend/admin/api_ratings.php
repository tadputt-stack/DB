<?php
error_reporting(0);
ini_set('display_errors', '0');

header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Origin, X-Requested-With, Content-Type, Accept, Authorization");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

include "db_conn.php";

// Ensure table exists
@mysqli_query($conn, "CREATE TABLE IF NOT EXISTS `product_ratings` (
  `rating_id` INT AUTO_INCREMENT PRIMARY KEY,
  `product_id` INT NOT NULL,
  `reviewer_name` VARCHAR(150) DEFAULT NULL,
  `user_id` INT DEFAULT 1,
  `score` INT NOT NULL DEFAULT 5,
  `comment` TEXT,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  INDEX `idx_rating_product` (`product_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;");

// Auto-seed if empty
$chk = @mysqli_query($conn, "SELECT COUNT(*) as c FROM product_ratings");
$count = $chk ? (int)mysqli_fetch_assoc($chk)['c'] : 0;
if ($count === 0) {
    $seed_data = [
        [1, 'Mandy25', 5, 'Great all rounder football boots. Great colours to stand out on the field and very comfortable at a great price.'],
        [2, 'Alex', 5, 'Super comfy, son loves them. Great value for money.'],
        [3, 'idkidc', 5, 'best shoes ever. lovely! great football shoes.These shoes filled my heart with love and joy.Best shoes ever.'],
        [4, 'Geminem', 5, 'Great purchase. Bought for my nephew, he absolutely loves the colour.'],
        [5, 'Sumy', 4, 'Good purchase. A bit tight so need to size up by about 1/2 a size but good boots for weekly practice!'],
        [6, 'Hazza', 5, 'Good boots for the price. A Softer tongue would improve the comfort overall.'],
        [7, 'M39M', 5, 'V happy customer. My son was v happy with these. Comfortable and stylish. As always delivery from adidas was speedy.'],
        [8, 'Kt85', 5, 'Nice. Lovely colours, clean up nice. No problems with comfort or fit! Successful buy.'],
        [9, 'Tim', 5, 'Great value. Daughter needed them for school, they look good and fit well. Great value for money.'],
        [10, 'Jacob17', 5, 'My son loves them. Lovely football boots for my son. Great colours too, very comfortable and durable.'],
        [11, 'JEM11', 5, 'GREAT quality materials for the price point! Didn\'t expect much in terms of good feeling materials, but was pleasantly impressed.'],
        [12, 'Footymum', 5, 'Much better fit than the laceless F50\'s. The boot feels a little wider across the toes and laces stay tied the full match.'],
        [13, 'Kea', 4, 'Boots look great and feel nice on pitch. Great traction and good ball feel.'],
        [14, 'CopaKing', 5, 'Best youth cleats. Supple so don\'t take forever to break in. These Copas are perfect.'],
        [15, 'edboy', 5, 'the quality of the boots is good. nice leather. One of the best football boots I buy.'],
        [16, 'Dave', 5, 'Good fitting boot and cleans well. Black laces don\'t show the mud and boots look great.'],
        [17, 'Chanathip', 5, 'คล่องตัวสูง เลี้ยวเปลี่ยนทิศทางได้เร็วมาก น้ำหนักเบาสบายเท้า คอนโทรลง่าย'],
        [18, 'Teerasil', 5, 'ยิงประตูล็อคข้อเท้าดีมาก หนังแท้คลาสสิก นุ่มสบายที่สุด สไตล์ Copa แท้'],
        [19, 'Theerathon', 5, 'เปิดบอลโค้งเข้าเป้าทุกลูก หนังนุ่ม ไม่กัดส้นเท้าเลยครับ สัมผัสคลาสสิก'],
        [20, 'Supachok', 5, 'สปีดต้นดีมาก เบาเท้า เตะบอลพุ่งแรง คุ้มค่าสมราคา วิ่งเต็มสนามสบายๆ'],
        [21, 'Sarach', 5, 'คอนโทรลบอลนิ่งมาก วางบอลยาวได้แม่นยำ พื้น FG เกาะหญ้าธรรมชาติได้ดี'],
        [22, 'Kawin', 5, 'เกาะสนามแน่นมาก เซฟจังหวะพุ่งตัวได้มั่นใจ 100% สตั๊ดคุณภาพระดับโลก'],
        [23, 'Kitsada', 5, 'ดีไซน์โฉบเฉี่ยว กระชับข้อเท้า ปุ่มสตั๊ดจิกหญ้าดีเยี่ยม คุ้มราคาสุดๆ'],
        [24, 'Worachit', 4, 'ใส่สบาย เล่นได้ทั้งหญ้าจริงและหญ้าเทียม สีสันสะดุดตา สวมใส่ง่าย'],
        [25, 'Sasalak', 5, 'วิ่งไม่มีสะดุด ปุ่มสตั๊ดกระจายน้ำหนักได้ดี ไม่ปวดฝ่าเท้าเวลาวิ่งสปีด'],
        [26, 'Bas55', 4, 'มาแล้วครับระยะเวลาส่งประมาณ5วัน สินค้าของแท้ครับราคาก็ดีครับ คุ้มค่ามาก'],
        [27, 'Phoo78', 5, 'ร้านจัดส่งไวมากครับ สินค้าสวยงามตามที่สั่ง ดีมากครับ'],
        [28, 'sangwong', 5, 'ความยาวรองเท้าพอดี ความกว้างเล็กกว่าปกติ แต่ว่าสามารถสั่งไซต์ปกติที่เคยสั่งได้ หนังนุ่มกำลังดี']
    ];

    $stmt_seed = mysqli_prepare($conn, "INSERT INTO product_ratings (product_id, reviewer_name, score, comment) VALUES (?, ?, ?, ?)");
    if ($stmt_seed) {
        foreach ($seed_data as $s) {
            mysqli_stmt_bind_param($stmt_seed, "isis", $s[0], $s[1], $s[2], $s[3]);
            mysqli_stmt_execute($stmt_seed);
        }
        mysqli_stmt_close($stmt_seed);
    }
}

$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'POST') {
    $raw = file_get_contents("php://input");
    $data = json_decode($raw, true) ?: $_POST;

    $product_id = intval($data['product_id'] ?? 0);
    $reviewer_name = trim($data['reviewer_name'] ?? 'ผู้ใช้งาน OnlySport');
    $score = intval($data['score'] ?? 5);
    $comment = trim($data['comment'] ?? '');

    if ($score < 1) $score = 1;
    if ($score > 5) $score = 5;

    if ($product_id <= 0) {
        http_response_code(400);
        echo json_encode(["status" => "error", "message" => "กรุณาระบุรหัสสินค้า (product_id)"]);
        exit();
    }

    if (empty($comment)) {
        http_response_code(400);
        echo json_encode(["status" => "error", "message" => "กรุณากรอกความคิดเห็นรีวิว"]);
        exit();
    }

    $insert_sql = "INSERT INTO product_ratings (product_id, reviewer_name, score, comment, created_at) VALUES (?, ?, ?, ?, NOW())";
    $stmt = mysqli_prepare($conn, $insert_sql);
    if ($stmt) {
        mysqli_stmt_bind_param($stmt, "isis", $product_id, $reviewer_name, $score, $comment);
        if (mysqli_stmt_execute($stmt)) {
            $new_id = mysqli_insert_id($conn);
            http_response_code(201);
            echo json_encode([
                "status" => "success",
                "message" => "ส่งรีวิวสินค้าเรียบร้อยแล้ว ขอบคุณสำหรับความคิดเห็นครับ",
                "rating_id" => $new_id
            ], JSON_UNESCAPED_UNICODE);
        } else {
            http_response_code(500);
            echo json_encode(["status" => "error", "message" => "บันทึกรีวิวไม่สำเร็จ: " . mysqli_stmt_error($stmt)]);
        }
        mysqli_stmt_close($stmt);
    } else {
        http_response_code(500);
        echo json_encode(["status" => "error", "message" => "Database error: " . mysqli_error($conn)]);
    }
    exit();
}

// GET method: Return ratings
$product_id = isset($_GET['product_id']) ? intval($_GET['product_id']) : 0;
$series_id  = isset($_GET['series_id']) ? intval($_GET['series_id']) : 0;

$where_clauses = [];
if ($product_id > 0) {
    $where_clauses[] = "r.product_id = $product_id";
}
if ($series_id > 0) {
    $where_clauses[] = "p.series_id = $series_id";
}

$where_sql = !empty($where_clauses) ? "WHERE " . implode(" AND ", $where_clauses) : "";

$sql = "SELECT r.*, 
               COALESCE(NULLIF(r.reviewer_name, ''), 'ผู้ใช้งานทั่วไป') AS display_reviewer,
               COALESCE(p.name, CONCAT('รองเท้าสตั๊ด ID: #', r.product_id)) AS product_name,
               p.imageUrl,
               p.series_id,
               p.price
        FROM product_ratings r
        LEFT JOIN products p ON r.product_id = p.id
        $where_sql
        ORDER BY r.rating_id DESC";

$result = mysqli_query($conn, $sql);
$ratings = [];

if ($result) {
    while ($row = mysqli_fetch_assoc($result)) {
        $ratings[] = [
            "rating_id" => (int)$row['rating_id'],
            "product_id" => (int)$row['product_id'],
            "product_name" => $row['product_name'],
            "imageUrl" => $row['imageUrl'] ?? '',
            "price" => (float)($row['price'] ?? 0.0),
            "reviewer_name" => $row['display_reviewer'],
            "score" => (int)$row['score'],
            "comment" => $row['comment'] ?? '',
            "created_at" => $row['created_at']
        ];
    }
}

echo json_encode($ratings, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
if ($conn) {
    @mysqli_close($conn);
}

