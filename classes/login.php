<?php
if(isset($_SESSION['login_hash'])){
	
	$query = $GLOBALS['pdo']->prepare("SELECT userID FROM users WHERE MD5(CONCAT(userID,username,password)) = :hash");
	$query->bindValue(':hash', $_SESSION['login_hash'], PDO::PARAM_STR);
	$query->execute();
		
	if($query->rowCount() > 0){
		$data = $query->fetch();
		$_SESSION['user'] = new User($data['userID']);
	}
}
elseif(isset($_POST['login'])){
	
	$query = $GLOBALS['pdo']->prepare("SELECT * FROM users WHERE username= :username AND password= :password");
	$query->bindValue(':username', $_POST['username'], PDO::PARAM_STR);
	$query->bindValue(':password', md5($_POST['pass']), PDO::PARAM_STR);
	$query->execute();
	
	if($query->rowCount() > 0){
		$data = $query->fetch();
		$_SESSION['user'] = new User($data['userID']);
		$_SESSION['login_hash'] = md5($data['userID'].$data['username'].$data['password']);
	}
	if($GLOBALS['site'] == "logout"){
		$GLOBALS['site'] = "start";
	}
}
?>