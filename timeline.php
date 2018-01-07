<?php

require "common.php";
require "mysqli_utils.php";

// セッション変数の取り出し
@session_start();
$my_user_id = (int)$_SESSION['user_id'];
$my_user_name = $_SESSION['user_name'];
$my_login_id = $_SESSION['login_id'];

$raw_img = '';

try {
  $mysqli = new_mysqli();
  $boyaki_count = mysqli_count_boyaki($mysqli, $my_user_id);
  $follow_count = mysqli_count_following($mysqli, $my_user_id);
  $follower_count = mysqli_count_follower($mysqli, $my_user_id);
} catch (mysqli_sql_exception $e) {
  $GLOBALS['error'] = $e->getMessage();
}

// アップロードがあったとき
if (isset($_FILES['upfile']['error']) &&
    is_int($_FILES['upfile']['error'])) {
  $raw_img = '';
  try {
    switch ($_FILES['upfile']['error']) {
      case UPLOAD_ERR_OK: // OK
        break;
      case UPLOAD_ERR_NO_FILE:   // ファイル未選択
        throw new Exception('');
      case UPLOAD_ERR_INI_SIZE:  // php.ini定義の最大サイズ超過
      case UPLOAD_ERR_FORM_SIZE: // フォーム定義の最大サイズ超過
        throw new Exception('ファイルサイズが大きすぎます');
      default:
        throw new Exception('その他のエラーが発生しました');
    }
    if (!$info = @getimagesize($_FILES['upfile']['tmp_name'])) {
      throw new Exception('有効な画像ファイルを指定してください');
    }
     // 画像サイズの判定
    if ($info[0] > UPLOAD_IMG_WEIGHT_MAX
     || $info[1] > UPLOAD_IMG_HEIGHT_MAX) {
      throw new Exception('画像サイズが大きすぎます
        ('.UPLOAD_IMG_WEIGHT_MAX.'x'.UPLOAD_IMG_HEIGHT_MAX.'まで)');
    }
    $raw_img = file_get_contents($_FILES['upfile']['tmp_name']);
  } catch (Exception $e) {
    $GLOBALS['error'] = $e->getMessage();
  }
}

// ぼやき投稿
if (isset($_POST['post'])) {
  if (!empty($_POST['message']) && is_string($_POST['message'])) {
    try {
      $message = $mysqli->real_escape_string($_POST['message']);
      mysqli_post_boyaki($mysqli, $my_user_id, $message, $raw_img);
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
    <!-- ヘッダー -->
    <?php print_header(); ?>
    <div id="main-containar">
      <div id="main">
        <!-- ユーザー情報 -->
        <?php print_dashboard(
                $my_user_name,
                $my_login_id,
                $boyaki_count,
                $follow_count,
                $follower_count); ?>
        <div id="timeline-prompt" class="content-box left">
          <!-- ぼやき投稿 -->
          <div id="boyaki-post">
            <form class="boyaki"
                  enctype="multipart/form-data"
                  action="" method="post">
              <textarea name="message"
               maxlength="140"
               rows="3" cols="68"
               placeholder="どうした？"></textarea>
            <label for="upfile">画像を追加</label>
            <input type="file" name="upfile"
             accept="image/png, image/jpeg, image/gif">
            <input type="submit" class="right submit btn-02" name="post" value="投稿">
            </form>
          </div>
          <!-- タイムライン -->
          <ol id="timeline">
<?php
/*
$query = "SELECT DISTINCT users.name, posts.message, posts.time
          FROM friends, posts, users
          WHERE posts.user_id = users.user_id AND
                (posts.user_id = $my_user_id OR
                (friends.user_id = $my_user_id AND
                (friends.status = 0 OR friends.status = 2) AND
                posts.user_id = friends.friend_user_id))
          ORDER BY time DESC";
*/
/*
$query = "SELECT users.name, posts.message, posts.time
          FROM   posts, users
          WHERE  (posts.user_id = $my_user_id AND users.user_id = $my_user_id)
          ORDER BY time DESC";
*/
/*
$query = "SELECT users.name, posts.message, posts.time
          FROM   posts, users, friends
          WHERE  friends.user_id = $my_user_id AND
                 posts.user_id = friends.friend_user_id AND
                  (friends.status = ".FS_ONLY_FOLLOW." OR
                   friends.status = ".FS_FOLLOW_EACH_OTHER."))
          ORDER BY time DESC
*/

$query = "(SELECT users.name,
                  users.login_id,
                  posts.id,
                  posts.message,
                  (posts.img_data != '') as img_exists,
                  posts.time
           FROM   posts, users
           WHERE  (posts.user_id = $my_user_id AND users.user_id = $my_user_id))
          UNION
          (SELECT users.name,
                  users.login_id,
                  posts.id,
                  posts.message,
                  (posts.img_data != '') as img_exists,
                  posts.time
           FROM   posts, users, friends
           WHERE  friends.user_id = $my_user_id AND
                  users.user_id = friends.friend_user_id AND
                  posts.user_id = friends.friend_user_id AND
                  (friends.status = ".FS_ONLY_FOLLOW." OR
                   friends.status = ".FS_FOLLOW_EACH_OTHER."))
          ORDER BY time DESC";
/*          LIMIT 100"; */

try {
  $posts = $mysqli->query($query);
  while ($post = $posts->fetch_assoc()) {
    if ($post['img_exists']) {
      $image_url = 'image_open.php?id='. $post['id'];
      $image = '
      <a href="'. $image_url .'" target="_blank"><img src="'. $image_url .'"></a>
      ';
    } else {
      $image = '';
     }
    echo '
    <div class="boyaki">
      <li class="post">
         <a href="', mypage_url($post['login_id']), '">
           <strong class="user_name">', h($post['name']), '</strong>
           <span class="id">@', h($post['login_id']), '</span>
         </a>
         <small class="time">', h($post['time']), '</small>
         <br>
         <p class="message">
           ', replace_boyaki(h($post['message'])),'
         </p>', $image, '
      </li>
    </div>
';
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
