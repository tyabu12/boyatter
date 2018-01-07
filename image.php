<?php

/* DEBUG */
error_reporting(1);
ini_set('display_errors', 'On');

function h($str) {
  return htmlspecialchars($str, ENT_QUOTES, 'UTF-8');
}
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
header("Content-Type: text/html; charset=utf-8");

// DBサーバーの情報
$db['host'] = "localhost"; // "isv.fecs.eng.u-fukui.ac.jp";
$db['user'] = "e33_15_18";
$db['pwd'] = "milkcoffee";
$db['dbname'] = "e33_15_18"; // "boyatter";


// セッション変数の取り出し
@session_start();
$my_user_id = $_SESSION['user_id'];
$my_user_name = $_SESSION['user_name'];
$my_login_id = $_SESSION['login_id'];

try {
  $mysqli = new mysqli($db['host'], $db['user'], $db['pwd'], $db['dbname']);

   // アップロードがあったとき
  if (isset($_FILES['upfile']['error']) && is_int($_FILES['upfile']['error'])) {

     

    try {
      // $_FILES['upfile']['error'] の値を確認
      switch ($_FILES['upfile']['error']) {
        case UPLOAD_ERR_OK: // OK
          break;
        case UPLOAD_ERR_NO_FILE:   // ファイル未選択
          throw new RuntimeException('ファイルが選択されていません', 400);
        case UPLOAD_ERR_INI_SIZE:  // php.ini定義の最大サイズ超過
        case UPLOAD_ERR_FORM_SIZE: // フォーム定義の最大サイズ超過
          throw new RuntimeException('ファイルサイズが大きすぎます', 400);
        default:
          throw new RuntimeException('その他のエラーが発生しました', 500);
       }

      // $_FILES['upfile']['mime']の値はブラウザ側で偽装可能なので
      // MIMEタイプを自前でチェックする
      if (!$info = @getimagesize($_FILES['upfile']['tmp_name'])) {
          $GLOBALS['error'] = '有効な画像ファイルを指定してください';
        throw new RuntimeException('有効な画像ファイルを指定してください', 400);
        }
      if (!in_array($info[2], array(IMAGETYPE_GIF, IMAGETYPE_JPEG, IMAGETYPE_PNG), true)) {
          $GLOBALS['error'] = '未対応の画像形式です';
        throw new RuntimeException('未対応の画像形式です', 400);
        }

        // 画像サイズの判定
      if ($info[0] > 640 || $info[1] > 480) {
          $GLOBALS['error'] = '画像サイズが大きすぎます.(横640, 縦480 のサイズまで対応)';
        throw new RuntimeException('画像サイズが大きすぎます.(横640, 縦480 のサイズまで対応)', 400);
        }
        
      $pic = file_get_contents($_FILES['upfile']['tmp_name']);
      $pic = $mysqli->real_escape_string($pic);

      $now_date = date('Y-m-d H:i:s');
      $query = "INSERT INTO images (user_id, type, post_id, raw_data, time)
            VALUES (1, 1, 10, '$pic', '$now_date')";
      $mysqli->query($query);
      

//file_get_contents($_FILES['upfile']['tmp_name'])
    } catch (mysqli_sql_exception $e) {
        $GLOBALS['error'] = $e->getMessage();

     //http_response_code($e instanceof mysqli_sql_exception ? 500 : $e->getCode());
     //$msgs[] = ['red', $e->getMessage()];

     }
  }
} catch (mysqli_sql_exception $e) {

    http_response_code(500);
    $GLOBALS['error'] = $e->getMessage();
    
}

    // サムネイル一覧取得
   //$rows = $pdo->query('SELECT id,name,type,thumb_data,date FROM image ORDER BY date DESC')->fetchAll();
?>
<!DOCTYPE html>
<html>
<head>
  <title>画像アップロード</title>
  <style><![CDATA[
    fieldset { margin: 10px; }
    legend { font-size: 12pt; }
    img {
        border: none;
        float: left;
    }
  ]]></style>
</head>
<body>
  <?php echo h($GLOBALS['error']); ?>
  <form enctype="multipart/form-data" method="post" action="">
    <fieldset>
      <legend>画像ファイルを選択(GIF, JPEG, PNGのみ対応)</legend>
      <input type="file" name="upfile" /><br />
      <input type="submit" value="送信" />
    </fieldset>
  </form>
<?php if (!empty($msgs)): ?>
  <fieldset>
    <legend>メッセージ</legend>
<?php foreach ($msgs as $msg): ?>
    <ul>
        <li style="color:<?=h($msg[0])?>;"><?=h($msg[1])?></li>
    </ul>
<?php endforeach; ?>
  </fieldset>
<?php endif; ?>
<?php if (!empty($rows)): ?>
   <fieldset>
     <legend>サムネイル一覧(クリックすると原寸大表示)</legend>
<?php foreach ($rows as $i => $row): ?>
<?php if ($i): ?>
     <hr />
<?php endif; ?>
     <p>
       <?=sprintf(
           '<a href="?id=%d"><img src="data:%s;base64,%s" alt="%s" /></a>',
           $row['id'],
           image_type_to_mime_type($row['type']),
           base64_encode($row['thumb_data']),
           h($row['name'])
       )?><br />
       ファイル名: <?=h($row['name'])?><br />
       日付: <?=h($row['date'])?><br clear="all" />
    </p>
<?php endforeach; ?>
   </fieldset>
<?php endif; ?>
</body>
</html>
