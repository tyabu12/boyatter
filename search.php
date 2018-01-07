<?php

require "common.php";
require "mysqli_utils.php";

// セッション変数の取り出し
@session_start();
$my_user_id = (int)$_SESSION['user_id'];
$my_user_name = $_SESSION['user_name'];
$my_login_id = $_SESSION['login_id'];

// 検索語
$search_word = '';

try {
  $mysqli = new_mysqli();
  $search_word = $mysqli->real_escape_string(safe_get('q'));
  $boyaki_count = mysqli_count_boyaki($mysqli, $my_user_id);
  $follow_count = mysqli_count_following($mysqli, $my_user_id);
  $follower_count = mysqli_count_follower($mysqli, $my_user_id);
} catch (mysqli_sql_exception $e) {
  $GLOBALS['error'] = $e->getMessage();
}

// フォロー
if (isset($_POST['follow'])) {
  $user_id = (int)safe_post('user_id');
  $status = (int)safe_post('status');
  try {
    mysqli_follow($mysqli, $my_user_id, $user_id, $status);
  } catch (mysqli_sql_exception $e) {
    $GLOBALS['error'] = $e->getMessage();
  }
}

// フォロー解除
if (isset($_POST['unfollow'])) {
  $user_id = (int)safe_post('user_id');
  $status = (int)safe_post('status');
  try {
    mysqli_unfollow($mysqli, $my_user_id, $user_id, $status);
  } catch (mysqli_sql_exception $e) {
    $GLOBALS['error'] = $e->getMessage();
  }
}

?>
<!DOCTYPE html>
<html lang="ja">
  <?php print_head(); ?>
  <body>
    <!-- ヘッダー -->
    <?php print_header($search_word); ?>
     <div id="main-containar">
      <div id="main">
        <!-- ユーザー情報 -->
        <?php
          print_dashboard(
            $my_user_name,
            $my_login_id,
            $boyaki_count,
            $follow_count,
            $follower_count);
         ?>
        <div id="search_result" class="content-box left">
          <ol>
<?php
$query = "SELECT DISTINCT user_id, login_id, name, introduction
          FROM users
          WHERE name         LIKE '%${search_word}%' OR
                login_id     LIKE '%${search_word}%' OR
                introduction LIKE '%${search_word}%'";

try {
  $users = $mysqli->query($query);
  while ($user = $users->fetch_assoc()) {
    if ($user['user_id'] != $my_user_id) { // 今のところ自分は除く
      echo '
      <div class="list">
        <li>', "\n";

      echo atag_login_id($user['login_id'], $user['name']), "\n";
      echo '
      <p>
        ', h($user['introduction']), '
      </p>';

      $follow_status = FS_NO_FOLLOW;

$query = "SELECT status
          FROM friends
          WHERE user_id = $my_user_id AND friend_user_id = ${user['user_id']}";

      $result = $mysqli->query($query);
      if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $follow_status = (int)$row['status'];
       }

      if ($follow_status != FS_ONLY_FOLLOW && $follow_status != FS_FOLLOW_EACH_OTHER) {
        echo '
        <form action="" method="post">
          <input type="hidden" name="user_id" value="', $user['user_id'], '">
          <input type="hidden" name="status" value="', $follow_status, '">
          <input class="btn-02" type="submit" name="follow" value="フォロー">
        </form>';
      } else {
        echo '
        <form action="" method="post">
          <input type="hidden" name="user_id" value="', $user['user_id'], '">
          <input type="hidden" name="status" value="', $follow_status, '">
          <input class="btn-02" type="submit" name="unfollow" value="フォロー解除">
        </form>';
       }
      echo
      '  </li>
       </div>
       ';
    }
  }
} catch (mysqli_sql_exception $e) {
  $GLOBALS['error'] = $e->getMessage();
}
?>
          </ol>
        </div>
      </div>
    </div>
  </body>
</html>
