<?php
header("Content-Type: application/json; charset=UTF-8");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

include "db_conn.php";

$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'POST') {
    $json = file_get_contents("php://input");
    $data = json_decode($json, true);

    if (!$data) {
        http_response_code(400);
        echo json_encode(["status" => "error", "message" => "Invalid JSON payload: ส่งอะไรมาเนี่ย แปลงไม่ได้"]);
        exit();
    }

    $customer_name = !empty($data['customer_name']) ? trim($data['customer_name']) : 'User Customer';
    $total_price = !empty($data['total_price']) ? floatval($data['total_price']) : 0.0;
    $status = !empty($data['status']) ? trim($data['status']) : 'สำเร็จ';
    $user_id = !empty($data['user_id']) ? intval($data['user_id']) : null;
    $items = isset($data['items']) && is_array($data['items']) ? $data['items'] : [];

    mysqli_begin_transaction($conn);

    try {
        $order_sql = "INSERT INTO orders (user_id, customer_name, total_price, created_at, status) 
                      VALUES (?, ?, ?, NOW(), ?)";
        $stmt = mysqli_prepare($conn, $order_sql);
        mysqli_stmt_bind_param($stmt, "isds", $user_id, $customer_name, $total_price, $status);
        
        if (!mysqli_stmt_execute($stmt)) {
            throw new Exception("บันทึกหัวออเดอร์ไม่ลง: " . mysqli_stmt_error($stmt));
        }
        $order_id = mysqli_insert_id($conn);
        mysqli_stmt_close($stmt);

        $item_sql = "INSERT INTO order_items (order_id, product_id, product_name, size, qty, price, subtotal) 
                     VALUES (?, ?, ?, ?, ?, ?, ?)";
        $item_stmt = mysqli_prepare($conn, $item_sql);

        foreach ($items as $item) {
            $product_id = !empty($item['product_id']) ? intval($item['product_id']) : 1;
            $product_name = !empty($item['product_name']) ? trim($item['product_name']) : 'Soccer Cleat';
            $size = !empty($item['size']) ? trim($item['size']) : 'US 9';
            $qty = !empty($item['quantity']) ? intval($item['quantity']) : (!empty($item['qty']) ? intval($item['qty']) : 1);
            $price = !empty($item['price']) ? floatval($item['price']) : 0.0;
            $subtotal = !empty($item['subtotal']) ? floatval($item['subtotal']) : ($price * $qty);

            mysqli_stmt_bind_param($item_stmt, "iissidd", $order_id, $product_id, $product_name, $size, $qty, $price, $subtotal);
            if (!mysqli_stmt_execute($item_stmt)) {
                throw new Exception("บันทึกรายการสินค้าไม่ได้: " . mysqli_stmt_error($item_stmt));
            }
        }
        mysqli_stmt_close($item_stmt);

        mysqli_commit($conn);

        http_response_code(201);
        echo json_encode([
            "status" => "success",
            "message" => "Order placed and recorded to MySQL successfully",
            "order_id" => $order_id,
            "total_price" => $total_price
        ], JSON_UNESCAPED_UNICODE);
    } catch (Exception $e) {
        mysqli_rollback($conn);
        http_response_code(500);
        echo json_encode(["status" => "error", "message" => $e->getMessage()], JSON_UNESCAPED_UNICODE);
    }
} else {
    $sql = "SELECT o.* FROM orders o ORDER BY o.order_id DESC";
    $result = mysqli_query($conn, $sql);
    $orders = [];

    if ($result) {
        while ($row = mysqli_fetch_assoc($result)) {
            $order_id = (int)$row['order_id'];
            $items_sql = "SELECT oi.*, p.imageUrl 
                          FROM order_items oi 
                          LEFT JOIN products p ON oi.product_id = p.id 
                          WHERE oi.order_id = " . $order_id;
            $items_res = mysqli_query($conn, $items_sql);
            $items = [];
            if ($items_res) {
                while ($irow = mysqli_fetch_assoc($items_res)) {
                    $items[] = [
                        "id" => (int)$irow['id'],
                        "product_id" => (int)$irow['product_id'],
                        "product_name" => $irow['product_name'],
                        "size" => $irow['size'],
                        "quantity" => (int)$irow['qty'],
                        "price" => (float)$irow['price'],
                        "subtotal" => (float)$irow['subtotal'],
                        "imageUrl" => $irow['imageUrl'] ?? ""
                    ];
                }
            }
            $orders[] = [
                "order_id" => $order_id,
                "user_id" => $row['user_id'] ? (int)$row['user_id'] : null,
                "customer_name" => $row['customer_name'],
                "total_price" => (float)$row['total_price'],
                "created_at" => $row['created_at'],
                "status" => $row['status'],
                "items" => $items
            ];
        }
    }
    echo json_encode($orders, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
}

mysqli_close($conn);
?>
