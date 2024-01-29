<?php
$host = "localhost";    // MySQL 服务器主机名
$username = "root";   // MySQL 用户名
$password = "root"; // MySQL 密码
$database = "websocket"; // 要连接的数据库名称

// 建立数据库连接
$conn = mysqli_connect($host, $username, $password, $database);
mysqli_set_charset($conn, "utf8mb4");
// 检查连接是否成功
if (!$conn) {
    die("连接失败: " . mysqli_connect_error());
}


?>