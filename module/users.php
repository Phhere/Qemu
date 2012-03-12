<?php
class Users extends Modul {
	public function getHeader(){
		return "<h1>Users</h1>";
	}
	public function post_save_new(){
		
		if(Modul::hasAccess('user_create') == false){
			return '<div class="notice error">Sie haben keinen Zugriff</div>';
		}
		
		/**
		* @todo Mail versenden
		*/
		
		$query = $GLOBALS['pdo']->prepare("INSERT INTO users (role, email, password, username) VALUES (:role,:email,:pass,:username)");
		$query->bindValue(':role',$_POST['role'],PDO::PARAM_INT);
		$query->bindValue(':email',$_POST['email'],PDO::PARAM_STR);
		$query->bindValue(':pass',md5($_POST['pass']),PDO::PARAM_STR);
		$query->bindValue(':username',$_POST['username'],PDO::PARAM_STR);
		$query->execute();
		
		return "<div class='notice success'>Benutzer wurde gespeichert</div>";
	}
	public function post_save_edit(){
		if(Modul::hasAccess('user_edit') == false){
			return '<div class="notice error">Sie haben keinen Zugriff</div>';
		}
		$query = $GLOBALS['pdo']->prepare("UPDATE users SET role = :role, username = :username, email=:email WHERE userID= :userID");
		$query->bindValue(':role',$_POST['role'],PDO::PARAM_INT);
		$query->bindValue(':username',$_POST['username'],PDO::PARAM_STR);
		$query->bindValue(':email',$_POST['email'],PDO::PARAM_STR);
		$query->bindValue(':userID',$_POST['user'],PDO::PARAM_INT);
		$query->execute();
		
		return "<div class='notice success'>Änderungen gespeichert</div>";
	}
	
	public function post_addvm(){
		if(Modul::hasAccess('vm_create') == false){
			return '<div class="notice error">Sie haben keinen Zugriff</div>';
		}
	
		$query = $GLOBALS['pdo']->prepare("UPDATE vm SET owner = :userID WHERE vmID=:vm");
		$query->bindValue(':userID',$_POST['user'],PDO::PARAM_INT);
		$query->bindValue(':vm',$_POST['vm'],PDO::PARAM_INT);
	
		$query->execute();
		return "<div class='notice success'>Änderungen gespeichert</div>";
	}
	
	public function action_delete(){
		if(Modul::hasAccess('user_delete') == false){
			return '<div class="notice error">Sie haben keinen Zugriff</div>';
		}
		Routing::getInstance()->appendRender($this,"action_default");
		$query = $GLOBALS['pdo']->prepare("DELETE FROM users WHERE userID= :userID");
		$query->bindValue(':userID',$_GET['user'],PDO::PARAM_INT);
		$query->execute();
		
		return "<div class='notice success'>Benutzer wurde gelöscht</div>";
	}
	
	public function action_new(){
		if(Modul::hasAccess('user_create') == false){
			return '<div class="notice error">Sie haben keinen Zugriff</div>';
		}
		$roles = '';
		$query = $GLOBALS['pdo']->prepare("SELECT * FROM roles");
		$query->execute();
		
		while($ds = $query->fetch()){
			$roles .= '<option value="'.$ds['roleID'].'">'.$ds['name'].'</option>';
		}
		
		$roles = str_replace('value="'.$GLOBALS['config']['default_role'].'"', 'value="'.$GLOBALS['config']['default_role'].'" selected="selected"', $roles);
		
		$tmp2 = new RainTPL();
		$tmp2->assign('roles',$roles);
		$tmp2->assign('pass',Helper::generatePassword(9));
		return $tmp2->draw('user_new',true);
	}
	
	public function action_edit(){
		if(Modul::hasAccess('user_edit') == false){
			return '<div class="notice error">Sie haben keinen Zugriff</div>';
		}
		$user = $_GET['user'];
		
		$query = $GLOBALS['pdo']->prepare("SELECT * FROM users WHERE userID = :user");
		$query->bindValue(":user",$user,PDO::PARAM_INT);
		$query->execute();
		
		if($query->rowCount()>0){
			$data = $query->fetch();
			$query = $GLOBALS['pdo']->prepare("SELECT * FROM roles");
			$query->execute();
		
			$roles = '';
			while($ds = $query->fetch()){
				$roles .= '<option value="'.$ds['roleID'].'">'.$ds['name'].'</option>';
			}
		
			$user_role = str_replace('value="'.$data['role'].'"', 'value="'.$data['role'].'" selected="selected"', $roles);
		
			$tmp2 = new RainTPL();
			$tmp2->assign('email',$data['email']);
			$tmp2->assign('username',$data['username']);
			$tmp2->assign('role',$user_role);
			$tmp2->assign('userID',$data['userID']);
			return $tmp2->draw('user_edit',true);
		}
		else{
			return '<div class="notice warning">Es existiert kein Nutzer mit dieser ID</div>';
		}
	}
	
	public function action_addvm(){
		if(Modul::hasAccess('user_edit','user_create') == false){
			return '<div class="notice error">Sie haben keinen Zugriff</div>';
		}
		
		$user = $_GET['user'];
		
		$query = $GLOBALS['pdo']->prepare("SELECT * FROM users WHERE userID = :user");
		$query->bindValue(":user",$user,PDO::PARAM_INT);
		$query->execute();
		
		if($query->rowCount()>0){
			$data = $query->fetch();
			
			$get = $GLOBALS['pdo']->prepare("SELECT * FROM vm WHERE owner = 0");
			$get->execute();
			$vms = '';
			while($ds = $get->fetch()){
				$vms .= '<option value="'.$ds['vmID'].'">'.$ds['name'].' - '.$ds['vmID'].'</option>';
			}
			if(empty($vms)){
				$vms = '<option value="-1" disabled="disabled" selected="selected">keine freie VM vorhanden</option>';
			}
			
			$tmp2 = new RainTPL();
			$tmp2->assign('username',$data['username']);
			$tmp2->assign('userID',$data['userID']);
			$tmp2->assign('vms',$vms);
			
			return $tmp2->draw('user_addvm',true);
		}
		else{
			return '<div class="notice warning">Es existiert kein Nutzer mit dieser ID</div>';
		}
	}
	
	public function action_default(){
		if(Modul::hasAccess('user_edit','user_create','user_delete') == false){
			return '<div class="notice error">Sie haben keinen Zugriff</div>';
		}
		$tmp2 = new RainTPL();
		$query = $GLOBALS['pdo']->prepare("SELECT * FROM users");
		$query->execute();
			
		$query2 = $GLOBALS['pdo']->prepare("SELECT * FROM vm WHERE owner= :user");
		$query2->bindParam(":user",$user,PDO::PARAM_INT);
		
		if($query->rowCount()>0){
			$users = array();
			while($ds = $query->fetch()){
				$buttons = '';
				if($_SESSION['user']->role['user_edit'] == 1){
					$buttons .= '<a href="index.php?site=users&action=edit&user='.$ds['userID'].'" class="button grey small center"><span class="icon" data-icon="G"></span>Edit</a>';
				}
				if($_SESSION['user']->role['vm_create'] == 1){
					$buttons .= '<a href="index.php?site=users&action=addVM&user='.$ds['userID'].'" class="button green small center"><span class="icon" data-icon="+"></span>add VM</a>';
				}
				if($_SESSION['user']->role['user_remove'] == 1){
					$buttons .= '<a href="index.php?site=users&action=delete&user='.$ds['userID'].'" class="button red small center"><span class="icon" data-icon="x"></span>delete</a>';
				}
		
				$user = $ds['userID'];
					
				$query2->execute();
					
				$user_vms = array();
				if($query2->rowCount()){
					while($vm = $query2->fetch()){
						$user_vms[] = '<a href="index.php?site=vms&action=edit&vmID='.$vm['vmID'].'">'.$vm['name'].'</a>';
					}
					$user_vms = implode(", ",$user_vms);
				}
				else{
					$user_vms = 'Keine zugewiesen';
				}
		
				$user = array();
				$user['mail'] = $ds['email'];
				$user['role'] = Helper::getRoleName($ds['role']);
				$user['vms'] = $user_vms;
				$user['buttons'] = $buttons;
		
				$users[] = $user;
			}
			$tmp2->assign('users',$users);
		}
		else{
			$tmp2->assign('users','<tr><td colspan="4">Keine Nutzer vorhanden</td></tr>');
		}
		return $tmp2->draw('users_table',true);
	}
}
$modul = new Users();
$routing = Routing::getInstance();
$routing->addRouteByAction($modul,'users','new');
$routing->addRouteByAction($modul,'users','edit');
$routing->addRouteByAction($modul,'users','addVM');
$routing->addRouteByPostField($modul,'users','save_new','save_new');
$routing->addRouteByPostField($modul,'users','save_edit','save_edit');
$routing->addRouteByPostField($modul,'users','addvm','addvm');
$routing->addRouteByAction($modul,'users','default');
?>