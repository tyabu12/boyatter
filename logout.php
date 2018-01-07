<?php

require "common.php";

?>
<!DOCTYPE html>
<html lang="ja">
  <?php print_head(); ?>
<?php

@session_start();

// 全てのセッション変数を削除
$_SESSION = array();

// クライアント側に保存されているクッキーを削除
if (isset($_COOKIE['PHPSESSID'])) {
  setcookie('PHPSESSID', '', time() - 1800, '/');
}

// 最後にセッションに登録されたデータを全て破棄
session_destroy();

redirect('index.php', 'ログアウトしました');
exit;

?>
</html>
