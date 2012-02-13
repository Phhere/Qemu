<?php
if(isset($_SESSION['login_hash'])){
	$get = mysql_query('SELECT userID FROM users WHERE MD5(CONCAT(userID,email,password)) = "'.$_SESSION['login_hash'].'"');
	if(mysql_num_rows($get)){
		$data = mysql_fetch_assoc($get);
		$_SESSION['user'] = new User($data['userID']);
	}
}
?>