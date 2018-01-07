<?php

require "common.php";
require "mysqli_utils.php";

// 新規登録ボタンが押された
if (isset($_POST['signup'])) {

  // 入力文字列の空チェック
  if (empty($_POST['login_id']) || !is_string($_POST['login_id'])) {
    $GLOBALS['error'] = 'IDが未入力です。';
  } else if (empty($_POST['password'])|| !is_string($_POST['password'])) {
    $GLOBALS['error'] = 'パスワードが未入力です。';
  } else if (empty($_POST['name'])|| !is_string($_POST['name'])) {
    $GLOBALS['error'] = '名前が未入力です。';
  } else { // ユーザIDとパスワードが入力されていたら登録
    try {
      $mysqli = new_mysqli();
      $login_id = $mysqli->real_escape_string($_POST['login_id']);
      $pwd = $mysqli->real_escape_string($_POST['password']);
      $name = $mysqli->real_escape_string($_POST['name']);
      $introduction = $mysqli->real_escape_string($_POST['introduction']);
      $query = "SELECT COUNT(*) FROM users WHERE login_id = '${login_id}'";
      $row = $mysqli->query($query)->fetch_row();
      if ($row[0] == 0) {
        $query = "INSERT INTO users (login_id, pwd, name, introduction)
                  VALUES ('$login_id', '$pwd', '$name', '$introduction')";
        $mysqli->query($query);
        redirect('index.php', '登録に成功しました');
        exit;
      } else {
        $GLOBALS['error'] = 'そのユーザーIDはすでに使われています。';
      }
    } catch (mysqli_sql_exception $e) {
      $GLOBALS['error'] = $e->getMessage();
    }
  }
}

?>
<!DOCTYPE html>
<html lang="ja">
  <?php print_head('Boyatterに登録する'); ?>
  <body>
    <h1>Boyatterをはじめましょう</h1>
    <form id="signup-form" name="signup-form" action="" method="post">
      <fieldset>
        <legend>登録フォーム</legend>
        <div><?php echo h($GLOBALS['error']) ?></div>
        <br>
        <label for="login_id">ログインID（半角英数）</label><br>
        <input type="text"
               id="login_id"
               name="login_id"
               value=<?php echo '"', h(safe_post('login_id')), "\"\n"; ?>
               maxlength="31"
               placeholder="ログインID"
               pattern="^[0-9A-Za-z]+$"
               required>
        <br>
        <label for="password">パスワード（半角英数）</label><br>
        <input type="password"
               id="password"
               name="password"
               value=""
               maxlength="127"
               placeholder="パスワード"
               pattern="^[0-9A-Za-z]+$"
               required>
        <br><br>
        <label for="name">名前</label><br>
        <input type="text"
               id="name"
               name="name"
               value=<?php echo '"', h(safe_post('name')), "\"\n"; ?>
               maxlength="50"
               placeholder="名前"
               required>
        <br><br>
        <label for="name">自己紹介</label><br>
        <textarea name="introduction"
                  maxlength="100"
                  rows="3" cols="34"
                  placeholder="自己紹介"><?php echo h(safe_post('introduction')); ?></textarea>
         <br><br>
        <input type="submit" id="signup" name="signup" value="登録">
      </fieldset>
    </form>
  </body>
</html>
