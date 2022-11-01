<?php
header("Content-Type: text/html; charset=utf-8");
if (!isset($page_title)) {
    $page_title = "Vehicle Log";
}
?>
<!DOCTYPE html>
<html lang="en">
 <head>
  <title><?php print($page_title); ?></title>
  <link rel="apple-touch-icon" sizes="180x180" href="/images/apple-touch-icon.png">
  <link rel="icon" type="image/png" sizes="32x32" href="/images/favicon-32x32.png">
  <link rel="icon" type="image/png" sizes="16x16" href="/images/favicon-16x16.png">
  <link rel="manifest" href="/site.webmanifest">
  <link rel="mask-icon" href="/images/safari-pinned-tab.svg" color="#5bbad5">
  <meta name="msapplication-TileColor" content="#da532c">
  <meta name="theme-color" content="#ffffff">
  <link rel="stylesheet" href="/includes/style.css?id=18">
 </head>
 <body>
