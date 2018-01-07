<?php

require "common.php";
require "mysqli_utils.php";

// ログインボタンが押された
if (isset($_POST['login'])) {
  // 入力文字列の空チェック
  if (empty($_POST['login_id']) || !is_string($_POST['login_id'])) {
    $GLOBALS['error'] = 'IDが未入力です。';
  } else if (empty($_POST['password'])|| !is_string($_POST['password'])) {
    $GLOBALS['error'] = 'パスワードが未入力です。';
  } else { // ユーザIDとパスワードが入力されていたら認証
    try {
      $mysqli = new_mysqli();
      $login_id = $mysqli->real_escape_string($_POST['login_id']);
      $query = "SELECT user_id, name, pwd FROM users WHERE login_id = '$login_id'";
      $row = $mysqli->query($query)->fetch_assoc();
      if ($row['pwd'] == $_POST['password']) {
        // 認証成功なら、セッションIDを新規に発行
        @session_start();
        session_regenerate_id(true);
        $_SESSION['user_id'] = $row['user_id'];
        $_SESSION['user_name'] = $row['name'];
        $_SESSION['login_id'] = $login_id;
        header('Location: timeline.php'); // マイページに移動
        exit;
      } else {
        $GLOBALS['error'] = 'IDまたはパスワードに誤りがあります。';
      }
    } catch (mysqli_sql_exception $e) {
      $GLOBALS['error'] = $e->getMessage();
    }
  }
}
?>
<!DOCTYPE html>
<html lang="ja">
  <?php print_head(); ?>
  <body>
    <h1>Boyatterへようこそ</h1>
    <p>一通り完成しています！！！　気軽に登録してOK！！！</p>
    <form id="login-form" name="loginForm" action="" method="post">
      <fieldset>
        <legend>ログインフォーム</legend>
        <div><?php echo h($GLOBALS['error']) ?></div>
        <label for="login_id">ログインID</label>
        <input type="text"
               id="login_id"
               name="login_id"
               value="<?php echo h(safe_post('login_id')) ?>"
               placeholder="ログインID"
               required>
        <br>
        <label for="password">パスワード</label>
        <input type="password"
               id="password"
               name="password"
               value=""
               placeholder="パスワード"
               required>
        <br><br>
        <input type="submit" id="login" name="login" value="ログイン">
        <a href="signup.php">新規登録</a>
      </fieldset>
    </form>
  </body>
</html>
