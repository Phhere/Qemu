<?php
if(isset($_SESSION['login_hash'])){
	$get = mysql_query('SELECT userID FROM users WHERE MD5(CONCAT(userID,username,password)) = "'.$_SESSION['login_hash'].'"');
	if(mysql_num_rows($get)){
		$data = mysql_fetch_assoc($get);
		$_SESSION['user'] = new User($data['userID']);
	}
}
elseif(isset($_POST['login'])){
	
	$get = mysql_query("SELECT * FROM users WHERE username='".$_POST['username']."' AND password='".md5($_POST['pass'])."'");
	if(mysql_num_rows($get)){
		$data = mysql_fetch_assoc($get);
		$_SESSION['user'] = new User($data['userID']);
		$_SESSION['login_hash'] = md5($data['userID'].$data['username'].$data['password']);
	}
	if($GLOBALS['site'] == "logout"){
		$GLOBALS['site'] = "start";
	}
}
?>