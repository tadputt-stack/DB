<?php
include "db_conn.php";

$from = isset($_GET['from']) && in_array($_GET['from'], ['menu', 'manage', 'index']) ? $_GET['from'] . '.php' : 'manage.php';

if (isset($_GET['id']) && !empty($_GET['id'])) {
    $id = intval($_GET['id']);

    $sql = "DELETE FROM products WHERE id = ?";
    $stmt = mysqli_prepare($conn, $sql);

    if ($stmt) {
        mysqli_stmt_bind_param($stmt, "i", $id);
        if (mysqli_stmt_execute($stmt)) {
            mysqli_stmt_close($stmt);
            mysqli_close($conn);
            header("Location: $from?msg=" . urlencode("ลบสินค้ารหัส #$id เรียบร้อยแล้ว"));
            exit();
        } else {
            $err = urlencode("ไม่สามารถลบสินค้าได้: " . mysqli_stmt_error($stmt));
            mysqli_stmt_close($stmt);
            mysqli_close($conn);
            header("Location: $from?msg=" . $err);
            exit();
        }
    } else {
        $err = urlencode("SQL Error: " . mysqli_error($conn));
        mysqli_close($conn);
        header("Location: $from?msg=" . $err);
        exit();
    }
} else {
    mysqli_close($conn);
    header("Location: $from");
    exit();
}
?>
