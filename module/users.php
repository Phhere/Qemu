<h3>Benutzerverwaltung</h3>
<?php
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
				
		}
		elseif(isset($_POST['save_edit'])){
			if($_SESSION['user']->role['user_edit'] == 1){
				mysql_query("UPDATE users SET role = '".$_POST['role']."' WHERE userID='".$_POST['user']."'");
			}
		}
		elseif($action =='delete'){
			
		}
		
		if($action == 'new'){
			
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
					
					echo '<form action="index.php?site=users" method="post">';
						echo '<table cellspacing="0" cellpadding="0">
						<thead><tr>
				<th colspan="2">'.$data['email'].'</th>
			</tr></thead>';
							echo '<tr><td>Role:</td><td><select class="no-margin" name="role">'.$user_role.'</select></td></tr>';
						echo '</table>';
						echo '<input type="hidden" name="user" value="'.$data['userID'].'"/><input type="submit" class="no-margin center" name="save_edit" value="Speichern" />';
					echo '</form>';
				}
				else{
					echo '<div class="notice warning">Es existiert kein Nutzer mit dieser ID</div>';
				}
				
			}
		}
		else{
			
			echo '<a href="index.php?site=users&action=new" class="button no-margin small center grey "><span class="icon">+</span>neuer User</a>';
			
			$get = mysql_query("SELECT * FROM users");
			echo '<table cellspacing="0" cellpadding="0">
			<thead><tr>
				<th width="100"> </th>
				<th width="100">Role</th>
				<th>VMs</th>
				<th width="120">Options</th>
			</tr></thead>';
			if(mysql_num_rows($get)){
				while($ds = mysql_fetch_assoc($get)){
					$buttons = '';
					if($_SESSION['user']->role['user_edit'] == 1){
						$buttons .= '<a href="index.php?site=users&action=edit&user='.$ds['userID'].'" class="button small center grey "><span class="icon">G</span>Edit</a>';
					}
					if($_SESSION['user']->role['vm_create'] == 1){
						$buttons .= '<a href="index.php?site=users&action=addVM&user='.$ds['userID'].'" class="button no-margin small center grey "><span class="icon">+</span>add VM</a>';
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
					
					echo '<tr>
						<th>'.$ds['email'].'</th>
						<td>'.getRoleName($ds['role']).'</td>
						<td>'.$user_vms.'</td>
						<td>'.$buttons.'</td>
					</tr>';
				}
			}
			else{
				echo '<tr><td colspan="4">Keine Nutzer vorhanden</td></tr>';
			}
			echo '</table>';
		}
	}
	else{
		echo "<div class='notice warning'>Sie haben keinen Zugriff.</div>";
	}
}
else{
	echo "<div class='notice warning'>Sie müssen eingeloggt sein</div>";
}