<?php
session_start();
$i = imageCreateFromJpeg("noise.jpg");

imageAntiAlias($i, true);

$txt = substr(md5(uniqid()), 0, 5);
$_SESSION['cap'] = $txt;


$x = 3; $y = 35; 
//$z = 45;
for($j=0; $j<5; $j++)
{
  $z = rand(35, 45);
  $color = imageColorAllocate($i, rand(0, 255), rand(0, 125), rand(0, 55));
  $size = rand(25, 35);
  $angle = -30 + rand(0, 60);
  imageTtfText($i, $size, $angle, $x, $y, $color,'arial.ttf', $txt[$j]);
  $x += $z;
}

header("Content-type: image/jpg");
imageJpeg($i);
?>