<?php
mysqli_report(MYSQLI_REPORT_OFF);

$dbname = getenv("DB_NAME") ?: "onlysport";
$username = getenv("DB_USER") ?: "root";
$password = (getenv("DB_PASSWORD") !== false && getenv("DB_PASSWORD") !== '') ? getenv("DB_PASSWORD") : "password";

$conn = false;
$servername = "db";
$port = 3306;

if (getenv("DB_HOST")) {
    $envHost = getenv("DB_HOST");
    $envPort = (int)(getenv("DB_PORT") ?: 3306);
    try {
        $testConn = @mysqli_connect($envHost, $username, $password, $dbname, $envPort);
        if ($testConn) {
            $conn = $testConn;
            $servername = $envHost;
            $port = $envPort;
        }
    } catch (Throwable $e) {
    }
}

if (!$conn) {
    $candidates = [
        ["host" => "db", "port" => 3306],
        ["host" => "127.0.0.1", "port" => 3306],
        ["host" => "localhost", "port" => 3306],
    ];

    foreach ($candidates as $cand) {
        try {
            $testConn = @mysqli_connect($cand["host"], $username, $password, $dbname, $cand["port"]);
            if ($testConn) {
                mysqli_set_charset($testConn, "utf8mb4");
                mysqli_query($testConn, "SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci");

                $cnt = 0;
                $res = @mysqli_query($testConn, "SELECT COUNT(*) as c FROM products");
                if ($res) {
                    $row = mysqli_fetch_assoc($res);
                    $cnt = (int)($row['c'] ?? 0);
                }

                $conn = $testConn;
                $servername = $cand["host"];
                $port = $cand["port"];

                if ($cnt > 0) {
                    break;
                }
            }
        } catch (Throwable $e) {
            continue;
        }
    }
}

if (!$conn) {
    http_response_code(500);
    die(json_encode([
        "status" => "error",
        "message" => "Database connection failed: ต่อฐานข้อมูลไม่ได้จ้า ลองเช็ค MySQL Container ดูนะ " . mysqli_connect_error()
    ], JSON_UNESCAPED_UNICODE));
}

mysqli_set_charset($conn, "utf8mb4");
mysqli_query($conn, "SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci");
?>
