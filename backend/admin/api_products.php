<?php
error_reporting(0);
ini_set('display_errors', '0');

header("Content-Type: application/json; charset=UTF-8");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

include "db_conn.php";

$series_filter = isset($_GET['series_id']) ? intval($_GET['series_id']) : 0;

if ($series_filter > 0) {
    $sql = "SELECT * FROM products WHERE series_id = " . intval($series_filter) . " ORDER BY id DESC";
} else {
    $sql = "SELECT * FROM products ORDER BY id DESC";
}

$result = @mysqli_query($conn, $sql);
$products = [];

if ($result) {
    while ($row = mysqli_fetch_assoc($result)) {
        $seriesId = isset($row['series_id']) ? (int)$row['series_id'] : (isset($row['seriesId']) ? (int)$row['seriesId'] : 1);
        $sName = $row['series_name'] ?? $row['seriesName'] ?? '';
        $name = $row['name'] ?? 'Soccer Cleat';

        if (empty($sName)) {
            if ($seriesId === 1) {
                $sName = 'Predator';
            } elseif ($seriesId === 2) {
                $sName = 'F50';
            } elseif ($seriesId === 3) {
                $sName = 'Copa';
            } elseif (stripos($name, 'f50') !== false) {
                $sName = 'F50';
            } elseif (stripos($name, 'copa') !== false) {
                $sName = 'Copa';
            } else {
                $sName = 'Predator';
            }
        }

        $imageUrl = $row['imageUrl'] ?? $row['image_url'] ?? $row['image'] ?? "";

        $products[] = [
            "id" => (int)($row['id'] ?? $row['product_id'] ?? 0),
            "name" => $name,
            "imageUrl" => $imageUrl,
            "image_url" => $imageUrl,
            "price" => (float)($row['price'] ?? 0.0),
            "series_id" => $seriesId,
            "series_name" => $sName,
            "ground_type" => $row['ground_type'] ?? $row['groundType'] ?? "FG",
            "description" => $row['description'] ?? ""
        ];
    }
}

echo json_encode($products, JSON_UNESCAPED_UNICODE);

if ($conn) {
    @mysqli_close($conn);
}
?>
