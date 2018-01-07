<?php

header("Content-Type: text/html; charset=utf-8");
// ↑文字コードを明示的に指定

// DEBUG
error_reporting(-1);
ini_set('display_errors', 'On');

// エラーメッセージの初期化
$error = '';

define("UPLOAD_IMG_WEIGHT_MAX", 640);
define("UPLOAD_IMG_HEIGHT_MAX", 640);

// デフォルトのタイムゾーンを東京に設定
date_default_timezone_set('Asia/Tokyo');

// HTMLの表現形式に変換
function h($str) {
  return htmlspecialchars($str, ENT_QUOTES, 'UTF-8');
}

// $_GETが設定されていない場合にnullを返す
function safe_get($key) {
  return isset($_GET[$key]) ? $_GET[$key] : null;
}

// $_SETが設定されていない場合にnullを返す
function safe_post($key) {
  return isset($_POST[$key]) ? $_POST[$key] : null;
}

// 5秒後にページジャンプ
function redirect($url, $msg) {
  $contents =
    "$msg<br>
     5秒後に <a href=\"$url\">$url</a> に移動します。
    <script type=\"text/javascript\">
    <!--
    setTimeout(function(){ location.replace('$url');}, 5000);
    -->
    </script>";

  echo $contents; //これを return して応用する
  exit;
}

// http系のURLを<a>タグに変換
function link_it($text){
  $pattern = '/((?:https?|ftp):\/\/[-_.!~*\'()a-zA-Z0-9;\/?:@&=+$,%#]+)/u';
  $replacement = '<a href="\1">\1</a>';
  $text = preg_replace($pattern, $replacement, $text);
  return $text;
}

// ログインIDからマイページのURLを返す
function mypage_url($login_id) {
  return "mypage.php?user_id=$login_id";
}

// ログインIDからマイページのURLにそれを<a>タグに変換
function atag_login_id($login_id, $name = '') {
  $title = $name . '@' . $login_id;
  return "<a title=\"$title\" href=\"" . mypage_url($login_id) . "\">$title</a>";
}

// ぼやきメッセージををいい感じにゴニョゴニョする
function replace_boyaki($text) {
  // 改行文字の前に<br>タグを挿入
  $text = nl2br($text/*, false*/);

  // アカウント名をaタグに
  $pattern = '/@([a-zA-Z0-9]+)/';
  $replacement = '<a href="mypage.php?user_id=$1">@$1</a>';
  $text = preg_replace($pattern, $replacement, $text);

  // リンクをaタグに
  $text = link_it($text);
  return $text;
}

// <head></head>を挿入
function print_head($title = 'Boyatter', $stylesheetArray = array('css/core.css')) {
  echo
   '<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title>Boyatter</title>';
  for ($i=0; $i < count($stylesheetArray); $i++) {
    echo '
    <link rel="stylesheet" href="', $stylesheetArray[$i] , '">', "\n";
  }
  echo
'  </head>', "\n\n";
}

// <header></header>を挿入
function print_header($search_word = '') {
  echo
   '<div id="header-wrapper">
      <div id="header-containar">
        <header>', h($GLOBALS['error']), '
          <a class="left btn-01" href="timeline.php">ホーム</a>
          <a class="right btn-01 logout" href="logout.php">ログアウト</a>
          <form class="right search" action="search.php">
            <input class="query"
             type="text"
             value="', $search_word, '"
             name="q"
             autocomplete="off"
             placeholder="Boyatterを検索">
            <input class="submit btn-02" type="submit" value="検索">
          </form>
          <div class="logo">
            <h1>Boyatter</h1>
          </div>
        </header>
      </div>
    </div>
';
}

// 左側のプロフィールを挿入
function print_dashboard($name, $login_id, $boyaki_cnt, $follow_cnt, $follower_cnt, $intro = '') {
  if (!empty($intro)) {
    $intro = '
    <p class="introduction">' . $intro . '</p>
    ';
  }
  echo
       '<div id="dashboard" class="content-box left">
          <div id="profile-stats">
            <a class="user-name"
             title="', $name, '@', $login_id, '"
             href="mypage.php?user_id=', $login_id, '">
             ', $name, '@', $login_id, '</a>' . $intro . '
            <ul>
              <li>
                <a class="boyaki left"
                 title="', $boyaki_cnt, 'ぼやき"
                 href="mypage.php?user_id=', $login_id, '">
                  <span class="name">ぼやき</span>
                  <span class="number">', $boyaki_cnt, '</span>
                </a>
                <a class="following left"
                 title="', $follow_cnt, '人をフォロー中"
                 href="following.php?user_id=', $login_id, '">
                  <span class="name">フォロー</span>
                  <span class="number">', $follow_cnt, '</span>
                </a>
              </li>
              <li>
                <a class="followers left"
                 title="', $follower_cnt, '人のフォロワー"
                 href="follower.php?user_id=', $login_id, '">
                  <span class="name">フォロワー</span>
                  <span class="number">', $follower_cnt, '</span>
                </a>
              </li>
            </ul>
          </div>
        </div>
';
}

?>
