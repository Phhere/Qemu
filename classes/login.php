<?php
if(isset($_SESSION['login_hash'])){
	$get = mysql_query('SELECT userID FROM users WHERE MD5(CONCAT(userID,email,password)) = "'.$_SESSION['login_hash'].'"');
	if(mysql_num_rows($get)){
		$data = mysql_fetch_assoc($get);
		$_SESSION['user'] = new User($data['userID']);
	}
}

function isOwner($vmID){
	if(isset($_SESSION['user'])){
		$get = mysql_query("SELECT owner FROM vm WHERE vmID = '".$vmID."' AND owner = '".$_SESSION['user']->id."'");
		if(mysql_num_rows($get)){
			return true;
		}
		elseif($_SESSION['user']->role['vm_create'] == 1){
			return true;
		}
		else{
			return false;
		}
	}
	return false;
}

function hasRessources() {
	/*
	 * @todo
	*/
	return true;
}

function getRoleName($roleID){
	$get = mysql_query("SELECT name FROM roles WHERE roleID='".$roleID."'");
	$data = mysql_fetch_assoc($get);
	return $data['name'];
}

/**
 * Format File size
 * @return string
 * @param integer $bytes
 * @param integer $round[optional]
 */
function formatFileSize($bytes, $round = 2){
	$units = array('Byte', 'kB', 'MB', 'GB', 'TB');
	$pow = floor(log($bytes)/log(1024));
	$pow = min($pow, count($units)-1);
	$bytes /= pow(1024,$pow);
	return round($bytes,$round)." ".$units[$pow];
}
