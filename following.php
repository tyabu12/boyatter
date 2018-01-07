<?php

require "common.php";
require "mysqli_utils.php";

// セッション変数の取り出し
@session_start();
$my_user_id = (int)$_SESSION['user_id'];
$my_user_name = $_SESSION['user_name'];
$my_login_id = $_SESSION['login_id'];

$this_login_id = safe_get('user_id');
if (!$this_login_id) {
  $this_login_id = $my_login_id;
}

try {
  $mysqli = new_mysqli();

  $query = "SELECT user_id, name FROM users WHERE login_id = '$this_login_id'";
  $row = $mysqli->query($query)->fetch_assoc();
  $this_user_id = (int)$row['user_id'];
  $this_user_name = $row['name'];

  $boyaki_count = mysqli_count_boyaki($mysqli, $this_user_id);
  $follow_count = mysqli_count_following($mysqli, $this_user_id);
  $follower_count = mysqli_count_follower($mysqli, $this_user_id);
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
    <?php print_header(); ?>
     <div id="main-containar">
      <div id="main">
        <!-- ユーザー情報 -->
        <?php
          print_dashboard(
            $this_user_name,
            $this_login_id,
            $boyaki_count,
            $follow_count,
            $follower_count);
         ?>
        <ol id="following" class="user-list content-box left">
<?php
$query = "SELECT users.user_id, login_id, name, status
          FROM   users, friends
          WHERE  friends.user_id = $this_user_id AND
                 users.user_id = friends.friend_user_id AND
                 (status = ".FS_ONLY_FOLLOW." OR status = ".FS_FOLLOW_EACH_OTHER.")";
try {
  $result = $mysqli->query($query);
  while ($row = $result->fetch_assoc()) {
    echo '<li>', "\n";
    echo atag_login_id($row['login_id'], $row['name']), "\n";

    echo '<form action="" method="post">', "\n";
    echo '  <input type="hidden" name="user_id" value="',  $row['user_id'], "\">\n";
    echo '  <input type="hidden" name="status" value="', $row['status'], "\">\n";
    if ($row['status'] != FS_ONLY_FOLLOW && $row['status'] != FS_FOLLOW_EACH_OTHER) {
      echo '  <input class="btn-02" type="submit" name="follow" value="フォロー">', "\n";
    } else {
      echo '  <input class="btn-02" type="submit" name="unfollow" value="フォロー解除">', "\n";
     }
    echo '</form>', "\n";
    echo '</li>', "\n";
  }
} catch (mysqli_sql_exception $e) {
  $GLOBALS['error'] = $e->getMessage();
}
?>
        </ol>
      </div>
    </div>
  </body>
</html>
