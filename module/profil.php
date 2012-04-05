<?php
class Profil extends Modul {
	public function getHeader(){
		return '<h2>Profil</h2>';
	}
	public function post_save_pass(){
		if(isset($_SESSION['user']) == false){
			return '<div class="error notice">Sie haben keinen Zugriff</div>';
		}
		
		$query = $GLOBALS['pdo']->prepare("SELECT * FROM users WHERE userID=:user");
		$query->bindValue('user',$_SESSION['user']->id);
		$query->execute();
		
		$data = $query->fetch();
		
		if($data['password'] == md5($_POST['old_pass'])){
			if(md5($_POST['new_pass']) == md5($_POST['new_pass_2']) && !empty($_POST['new_pass_2'])){
				$query = $GLOBALS['pdo']->prepare("UPDATE users SET password=:password WHERE userID = :id");
				$query->bindValue(':password',md5($_POST['new_pass']),PDO::PARAM_STR);
				$query->bindValue(':id',$_SESSION['user']->id,PDO::PARAM_INT);
				$query->execute();
				Routing::getInstance()->appendRender($this,"action_default");
				return '<div class="success notice">Neues Password gespeichert.</div>';
			}
			else{
				return '<div class="error notice">Die beiden neuen Passwörter sind nicht gleich.</div>';
			}
		}
		else{
			return '<div class="error notice">Das eingegebene alte Password ist falsch.</div>';
		}
	}
	public function post_save(){
		if(isset($_SESSION['user']) == false){
			return '<div class="error notice">Sie haben keinen Zugriff</div>';
		}
		
		$query = $GLOBALS['pdo']->prepare("UPDATE users SET name=:name, email=:email WHERE userID = :id");
		$query->bindValue(':email',$_POST['email'],PDO::PARAM_STR);
		$query->bindValue(':name',$_POST['name'],PDO::PARAM_STR);
		$query->bindValue(':id',$_SESSION['user']->id,PDO::PARAM_INT);
		$query->execute();
		Routing::getInstance()->appendRender($this,"action_default");
		return '<div class="success notice">Neue Daten gespeichert.</div>';
	}
	public function action_edit_password(){
		if(isset($_SESSION['user']) == false){
			return '<div class="error notice">Sie haben keinen Zugriff</div>';
		}
		
		$tmp = new RainTPL();
				
		return $tmp->draw('profil_edit',true);
		
	}
	public function action_default(){
		if(isset($_SESSION['user']) == false){
			return '<div class="error notice">Sie haben keinen Zugriff</div>';
		}
		$query = $GLOBALS['pdo']->prepare("SELECT * FROM users WHERE userID=:user");
		$query->bindValue('user',$_SESSION['user']->id);
		$query->execute();
		
		$data = $query->fetch();
		$tmp = new RainTPL();
		
		$tmp->assign('username',$data['username']);
		$tmp->assign('email',$data['email']);
		$tmp->assign('name',$data['name']);
		
		return $tmp->draw('profil',true);
	}
}
$modul = new Profil();
$routing = Routing::getInstance();
$routing->addRouteByAction($modul,'profil','edit_password');
$routing->addRouteByPostField($modul,'profil','save','save');
$routing->addRouteByAction($modul,'profil','default');
