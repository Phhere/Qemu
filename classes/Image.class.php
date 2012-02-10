<?php
class Image {
	static function getImagePath($imageID){
		$get = mysql_query("SELECT path FROM images WHERE imageID='".$imageID."'");
		$data = mysql_fetch_assoc($get);
		return $data['path'];
	}
	static function getImageType($imageID){
		$get = mysql_query("SELECT type FROM images WHERE imageID='".$imageID."'");
		$data = mysql_fetch_assoc($get);
		return $data['type'];
	}
}