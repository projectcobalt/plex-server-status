<?php
include 'init.php';
include ROOT_DIR . '/assets/php/functions.php';
global $plexToken;

$image_url = $_GET['img'];
$image_src = $image_url . '?X-Plex-Token=' . $plexToken;
header('Content-type: image/jpeg');
//header("Content-Length: " . filesize($image_src));
readfile($image_src);

?>
