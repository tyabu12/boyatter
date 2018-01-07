<?php

define("FS_ONLY_FOLLOW",       0);
define("FS_ONLY_FOLLOWED",     1);
define("FS_FOLLOW_EACH_OTHER", 2);
define("FS_NO_FOLLOW",         3);

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

function new_mysqli() {
  $host = "localhost"; // "isv.fecs.eng.u-fukui.ac.jp";
  $user = "e33_15_18";
  $pwd = "milkcoffee";
  $dbname = "e33_15_18"; // "boyatter";
  $mysqli = new mysqli($host, $user, $pwd, $dbname);
  //$mysqli->set_charset('utf8');
  return $mysqli;
}

function mysqli_count_boyaki(&$mysqli, $user_id) {
  $query = "SELECT COUNT(*) FROM posts WHERE user_id = '$user_id'";
  $row = $mysqli->query($query)->fetch_row();
  $count = $row[0];
  return (int)$count;
}

function mysqli_count_following(&$mysqli, $user_id) {
  $query = "SELECT COUNT(*) FROM friends
            WHERE user_id = '$user_id' AND
                  (status = ".FS_ONLY_FOLLOW." OR
                   status = ".FS_FOLLOW_EACH_OTHER.")";
  $row = $mysqli->query($query)->fetch_row();
  $count = $row[0];
  return (int)$count;
}

function mysqli_count_follower(&$mysqli, $user_id) {
  $query = "SELECT COUNT(*) FROM friends
            WHERE user_id = '$user_id' AND
                  (status = ".FS_ONLY_FOLLOWED." OR
                   status = ".FS_FOLLOW_EACH_OTHER.")";
  $row = $mysqli->query($query)->fetch_row();
  $count = $row[0];
  return (int)$count;
}

function mysqli_follow(&$mysqli, $this_user_id, $other_user_id, $status) {
  if($status == FS_ONLY_FOLLOWED) {
    $query = "UPDATE friends
              SET status = ".FS_FOLLOW_EACH_OTHER."
              WHERE (user_id = $this_user_id AND friend_user_id = $other_user_id) OR
                    (user_id = $other_user_id AND friend_user_id = $this_user_id)";
    $mysqli->query($query);
  } else if ($status == FS_NO_FOLLOW) {
    $query = "INSERT INTO friends (user_id, friend_user_id, status) VALUES
              ($this_user_id, $other_user_id, ".FS_ONLY_FOLLOW."),
              ($other_user_id, $this_user_id, ".FS_ONLY_FOLLOWED.")";
    $mysqli->query($query);
  }
}

function mysqli_unfollow(&$mysqli, $this_user_id, $other_user_id, $status) {
  if($status == FS_FOLLOW_EACH_OTHER) {
    $query = "UPDATE friends
              SET status = ".FS_ONLY_FOLLOWED."
              WHERE user_id = $this_user_id AND friend_user_id = $other_user_id";
    $mysqli->query($query);
    $query ="UPDATE friends
              SET status = ".FS_ONLY_FOLLOW."
             WHERE user_id = $other_user_id AND friend_user_id = $this_user_id";
    $mysqli->query($query);
  } else if ($status == FS_ONLY_FOLLOW) {
    $query = "DELETE FROM friends
              WHERE (user_id = $this_user_id AND friend_user_id = $other_user_id) OR
                    (user_id = $other_user_id AND friend_user_id = $this_user_id)";
    $mysqli->query($query);
  }
}

function mysqli_post_boyaki(&$mysqli, $user_id, $message, $raw_img='') {
  $raw_img = $mysqli->real_escape_string($raw_img);
  $now_date = date('Y-m-d H:i:s');
  $query = "INSERT INTO posts (user_id, message, time, img_data)
            VALUES ($user_id, '$message', '$now_date', '$raw_img')";
  $mysqli->query($query);
}
