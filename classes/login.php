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
	
	if(isset($_POST['pass']) && is_string($_POST['pass']) && isset($_POST['username']) && is_string($_POST['username'])){
		if(!isset($_SESSION['wrongLoginCount']) || $_SESSION['wrongLoginCount'] < 5){
			$query = $GLOBALS['pdo']->prepare("SELECT * FROM users WHERE username= :username AND password= :password");
			$query->bindValue(':username', $_POST['username'], PDO::PARAM_STR);
			$query->bindValue(':password', md5($_POST['pass']), PDO::PARAM_STR);
			$query->execute();
			
			if($query->rowCount() > 0){
				$data = $query->fetch();
				$_SESSION['user'] = new User($data['userID']);
				$_SESSION['login_hash'] = md5($data['userID'].$data['username'].$data['password']);
			}
			else{
				if(isset($_SESSION['wrongLoginCount']) == false){
					$_SESSION['wrongLoginCount'] = 0;
				}
				$_SESSION['wrongLoginCount']++;
			}
		}
		if($GLOBALS['site'] == "logout"){
			$GLOBALS['site'] = "start";
		}
	}
}
?>