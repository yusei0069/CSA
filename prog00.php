<html>
	<head>
		<title>科学科実験Iサンプルプログラム</title>
	<style>
.item{
	overflow:hidden;
	float:left;
	margin:10px;
	-webkit-box-shadow: 0 5px 3px -3px #777;
	   -moz-box-shadow: 0 5px 3px -3px #777;
	        box-shadow: 0 5px 3px -3px #777;
}
.title{
	height:32px;
	line-height:32px;
	font-size:16px;
	color:#fff;
}
body{
	background: -webkit-gradient(linear, left top, left bottom, color-stop(0.74, #1d6723), color-stop(0.00, #a2d93f));
	background: -webkit-linear-gradient(top, #a2d93f 0%, #1d6723 74%);
	background: -moz-linear-gradient(top, #a2d93f 0%, #1d6723 74%);
	background: -o-linear-gradient(top, #a2d93f 0%, #1d6723 74%);
	background: -ms-linear-gradient(top, #a2d93f 0%, #1d6723 74%);
	background: linear-gradient(to bottom, #a2d93f 0%, #1d6723 74%);
}
	</style>
	</head>
	<body>
<h1>Flickrに最近アップロードされた500枚</h1>
<?php
$Flickr_apikey = "fd1e42cffcde1d0f62dce642c95a8948";
$Flickr_getRecent = "https://api.flickr.com/services/rest/?method=flickr.photos.getRecent&api_key=".$Flickr_apikey."&extras=url_s&per_page=500&format=php_serial";
$result = unserialize(file_get_contents($Flickr_getRecent));
$colors = array(
	"#f39700",	"#e60012",	"#9caeb7",	"#00a7db",
	"#009944",	"#d7c447",	"#9b7cb6",	"#00ada9",
	"#bb641d",	"#e85298",	"#0079c2",	"#6cbb5a",
	"#b6007a",	"#e5171f",	"#522886",	"#0078ba",
	"#019a66",	"#e44d93",	"#814721",	"#a9cc51",
	"#ee7b1a",	"#00a0de");
$c=0;
foreach($result["photos"]["photo"] as $k => $photo){
	if(isset($photo["url_s"])){
		$title = $photo["title"];
		$url   = $photo["url_s"];
		$width = $photo["width_s"];
		$height= $photo["height_s"];
		$size  = max($width,$height);
		$margin_top = ($size-$height)/2;
		$margin_left= ($size-$width) /2;
		echo '<div class="item" style="width:'.$size.'px;height:'.($size+32).'px;background-color:'.($colors[$c%count($colors)]).';border:1px solid '.($colors[$c++%count($colors)]).';">';
		echo '<div class="image">';
		echo '<img src="'.$url.'" width="'.$width.'" height="'.$height.'" style="margin-bottom:'.$margin_top.'px;margin-top:'.$margin_top.'px;margin-left:'.$margin_left.'px;">';
		echo '</div>';
		echo '<div class="title">';
		echo $title;
		echo '</div>';
		echo '</div>';
	}
}
?>
	</body>
</html>
