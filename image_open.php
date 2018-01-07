<?php

require "common.php";
require "mysqli_utils.php";

$id = $_GET['id'];

try {
  $mysqli = new_mysqli();
  
  $query = "SELECT img_data FROM posts WHERE id = $id";
  $row = $mysqli->query($query)->fetch_row();

  // 画像ヘッダとしてjpegを指定（取得データがjpegの場合）
  header("Content-Type: image/jpeg");

  // バイナリデータを直接表示
  echo $row[0];
} catch (mysqli_sql_exception $e) {
  $GLOBALS['error'] = $e->getMessage();
}

?> 
