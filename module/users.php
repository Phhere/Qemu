<?php
$tmp = new RainTPL();
if(isset($_SESSION['user'])){
	if($_SESSION['user']->role['user_create'] == 1 ||
	$_SESSION['user']->role['user_edit'] == 1){

		if(isset($_GET['action'])){
			$action = $_GET['action'];
		}
		else {
			$action = null;
		}

		if(isset($_POST['save_new'])){
			if($_SESSION['user']->role['user_create'] == 1){

				/**
				 * @todo Mail versenden
				 */

				$query = $GLOBALS['pdo']->prepare("INSERT INTO users (role, email, password) VALUES (:role,:email,:pass)");
				$query->bindValue(':role',$_POST['role'],PDO::PARAM_INT);
				$query->bindValue(':email',$_POST['email'],PDO::PARAM_STR);
				$query->bindValue(':pass',md5($_POST['pass']),PDO::PARAM_STR);
				$query->execute();

				$tmp->assign('content',"<div class='notice success'>Benutzer wurde gespeichert</div>");
			}
		}
		elseif(isset($_POST['save_edit'])){
			if($_SESSION['user']->role['user_edit'] == 1){

				$query = $GLOBALS['pdo']->prepare("UPDATE users SET role = :role WHERE userID= :userID");
				$query->bindValue(':role',$_POST['role'],PDO::PARAM_INT);
				$query->bindValue(':userID',$_POST['user'],PDO::PARAM_INT);
				$query->execute();

				$tmp->assign('content',"<div class='notice success'>Änderungen gespeichert</div>");
			}
		}
		elseif($action =='delete'){
			if($_SESSION['user']->role['user_remove'] == 1){

				$query = $GLOBALS['pdo']->prepare("DELETE FROM users  WHERE userID= :userID");
				$query->bindValue(':userID',$_GET['user'],PDO::PARAM_INT);
				$query->execute();

				$tmp->assign('content',"<div class='notice success'>Benutzer wurde gelöscht</div>");
			}
		}

		if($action == 'new'){
			if($_SESSION['user']->role['user_create'] == 1){

				$roles = '';
				$query = $GLOBALS['pdo']->prepare("SELECT * FROM roles");
				$query->execute();

				while($ds = $query->fetch()){
					if($ds['roleID'] == $GLOBALS['config']['default_role']) $selected = "selected='selected'";
					else $selected = '';
					$roles .= '<option value="'.$ds['roleID'].'" '.$selected.'>'.$ds['name'].'</option>';
				}


				$tmp2 = new RainTPL();
				$tmp2->assign('roles',$roles);
				$tmp2->assign('pass',Helper::generatePassword(9));
				$tmp->assign('content',$tmp2->draw('user_new',true));
			}
		}
		elseif($action == 'edit'){
			if($_SESSION['user']->role['user_edit'] == 1){

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
					$tmp2->assign('role',$user_role);
					$tmp2->assign('userID',$data['userID']);
					$tmp->assign('content',$tmp2->draw('user_edit',true));
				}
				else{
					$tmp->assign('content','<div class="notice warning">Es existiert kein Nutzer mit dieser ID</div>');
				}

			}
		}
		else{
				
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
			$tmp->assign('content',$tmp2->draw('users_table',true));
		}
	}
	else{
		$tmp->assign('content',"<div class='notice warning'>Sie haben keinen Zugriff.</div>");
	}
}
else{
	$tmp->assign('content',"<div class='notice warning'>Sie müssen eingeloggt sein</div>");
}
$GLOBALS['template']->assign('content',$tmp->draw('users',true));