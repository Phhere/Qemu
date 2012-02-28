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
				
				mysql_query("INSERT INTO users (role, email, password) VALUES ('".$_POST['role']."','".$_POST['email']."','".md5($_POST['pass'])."')");
				$tmp->assign('content',"<div class='notice success'>Benutzer wurde gespeichert</div>");
			}
		}
		elseif(isset($_POST['save_edit'])){
			if($_SESSION['user']->role['user_edit'] == 1){
				mysql_query("UPDATE users SET role = '".$_POST['role']."' WHERE userID='".$_POST['user']."'");
				$tmp->assign('content',"<div class='notice success'>Änderungen gespeichert</div>");
			}
		}
		elseif($action =='delete'){
			if($_SESSION['user']->role['user_remove'] == 1){
				mysql_query("DELETE FROM users WHERE userID='".(int)$_GET['user']."'");
				$tmp->assign('content',"<div class='notice success'>Benutzer wurde gelöscht</div>");
			}
		}

		if($action == 'new'){
			if($_SESSION['user']->role['user_create'] == 1){

				$roles = '';
				$get = mysql_query("SELECT * FROM roles");
				while($ds = mysql_fetch_assoc($get)){
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
				$get = mysql_query("SELECT * FROM users WHERE userID='".$user."'");
				if(mysql_num_rows($get)){
					$data = mysql_fetch_assoc($get);
					$roles = '';
					$get = mysql_query("SELECT * FROM roles");
					while($ds = mysql_fetch_assoc($get)){
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
			$get = mysql_query("SELECT * FROM users");
			if(mysql_num_rows($get)){
				$users = array();
				while($ds = mysql_fetch_assoc($get)){
					$buttons = '';
					if($_SESSION['user']->role['user_edit'] == 1){
						$buttons .= '<a href="index.php?site=users&action=edit&user='.$ds['userID'].'" class="button grey small center no-margin"><span class="icon" data-icon="G"></span>Edit</a>';
					}
					if($_SESSION['user']->role['vm_create'] == 1){
						$buttons .= '<a href="index.php?site=users&action=addVM&user='.$ds['userID'].'" class="button green small center no-margin"><span class="icon" data-icon="+"></span>add VM</a>';
					}
					if($_SESSION['user']->role['user_remove'] == 1){
						$buttons .= '<a href="index.php?site=users&action=delete&user='.$ds['userID'].'" class="button red small center no-margin"><span class="icon" data-icon="x"></span>delete</a>';
					}
						
					$vms = mysql_query("SELECT * FROM vm WHERE owner='".$ds['userID']."'");
					$user_vms = array();
					if(mysql_num_rows($vms)){
						while($vm = mysql_fetch_assoc($vms)){
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