<h3>Virtuelle Maschienen</h3>



<?php
if(isset($_SESSION['user'])){

	if(isset($_GET['action'])){
		$action = $_GET['action'];
	}
	else {
		$action = null;
	}

	if(isset($_POST['save'])){
		if($_SESSION['user']->role['vm_create'] == 1){
			mysql_query("INSERT INTO vm (owner,name,image,ram,password,params) VALUES
			('".mysql_real_escape_string($_POST['owner'])."',
			 '".mysql_real_escape_string($_POST['name'])."',
			 '".mysql_real_escape_string($_POST['image'])."',
			 '".mysql_real_escape_string($_POST['ram'])."',
			 '".mysql_real_escape_string($_POST['password'])."',
			 '".mysql_real_escape_string($_POST['params'])."')");
			echo "<div class='notice success'>Die Daten wurden gespeichert.</div>";
		}
	}
	elseif(isset($_POST['save_edit'])){
		$id = $_POST['vm'];
		if($_SESSION['user']->role['vm_edit'] == 1){
			mysql_query("UPDATE vm SET owner='".mysql_real_escape_string($_POST['owner'])."',
			name='".mysql_real_escape_string($_POST['name'])."', 
			image='".mysql_real_escape_string($_POST['image'])."',
			ram='".mysql_real_escape_string($_POST['ram'])."',
			password='".mysql_real_escape_string($_POST['password'])."',
			params='".mysql_real_escape_string($_POST['params'])."' WHERE vmID='".$id."'");
			echo "<div class='notice success'>Die Daten wurden gespeichert.</div>";
		}
	}
	elseif(isset($_POST['clone'])){

	}

	if($action == "start"){
		if(Helper::hasRessources()){
			if(Helper::isOwner($_GET['vmID'])){
				$vm = new QemuVm($_GET['vmID']);
				if($vm->status == QemuMonitor::RUNNING){
					echo "<div class='notice'>Die VM scheint bereits aus zu laufen.</div>";
				}
				else{
					$vm->startVM();
					try{
						$vm->connect();
					}
					catch(Exception $e){
						echo "<div class='notice warning'>Die VM scheint nicht zu starten.</div>";
						$vm->setStatus(QemuMonitor::SHUTDOWN);
					}
					if(!isset($e)){
						echo "<div class='notice success'>Die VM wurde gestartet.</div>";
					}
				}
			}
		}
		else{
			echo "<div class='notice error'>Es sind keine Ressourcen mehr verfügbar um die VM zu starten</div>";
		}
	}
	elseif($action == "stop"){
		if(Helper::isOwner($_GET['vmID'])){
			$vm = new QemuVm($_GET['vmID']);
			if($vm->status == QemuMonitor::RUNNING){
				try{
					$vm->connect();
				}
				catch(Exception $e){
					echo "<div class='notice warning'>Die VM scheint bereits aus zu sein.</div>";
					$vm->setStatus(QemuMonitor::SHUTDOWN);
				}
				if(!isset($e)){
					$vm->shutdown();
					echo "<div class='notice success'>Die VM wird ausgeschaltet.</div>";
				}
			}
			else{
				echo "<div class='notice warning'>Die VM scheint bereits aus zu sein.</div>";
			}
		}
		else{
			echo "<div class='notice error'>Sie besitzen nicht die Rechte die VM zu stoppen</div>";
		}
	}
	elseif($action == "new"){
		if($_SESSION['user']->role['vm_create'] == 1){

			$owner = '<option value="0">--</option>';
			$get = mysql_query("SELECT userID, email FROM users");
			while($ds = mysql_fetch_assoc($get)){
				$owner .= '<option value="'.$ds['userID'].'">'.$ds['email'].'</option>';
			}

			$image = '<option value="0">--</option>';
			$get = mysql_query("SELECT imageID,type,name FROM images");
			while($ds = mysql_fetch_assoc($get)){
				$image .= '<option value="'.$ds['imageID'].'">'.$ds['type'].' - '.$ds['name'].'</option>';
			}


			echo '<form action="index.php?site=vms" method="post">';
			echo '<table cellspacing="0" cellpadding="0">
												<thead><tr>
										<th colspan="2">new VM</th>
									</tr></thead>';
			echo '<tr><td>Name:</td><td><input type="text" class="no-margin" value="" name="name"/></td></tr>';
			echo '<tr><td>Owner:</td><td><select name="owner" class="no-margin">'.$owner.'</select></td></tr>';
			echo '<tr><td>Image:</td><td><select name="image" class="no-margin">'.$image.'</select></td></tr>';
			echo '<tr><td>Ram:</td><td><input type="text" class="no-margin inline" value="'.$GLOBALS['config']['default_ram'].'" name="ram" size="6"/> Mb</td></tr>';
			echo '<tr><td>VNC Password:</td><td><input type="text" class="no-margin inline" value="" name="password"/></td></tr>';
			echo '<tr><td>Optionale Parameter:</td><td><input type="text" class="no-margin inline" value="" name="params"/></td></tr>';
			echo '</table>';
			echo '<input type="submit" class="no-margin center" name="save" value="Speichern" />';
			echo '</form>';
		}

	}
	elseif($action == "edit"){
		$id = $_GET['vmID'];
		$get = mysql_query("SELECT * FROM vm WHERE vmID='".$id."'");
		if(mysql_num_rows($get)){
			$data = mysql_fetch_array($get);
			if($data['status'] == QemuMonitor::SHUTDOWN){
				$owner = '';
				$get = mysql_query("SELECT userID, email FROM users");
				while($ds = mysql_fetch_assoc($get)){
					$owner .= '<option value="'.$ds['userID'].'">'.$ds['email'].'</option>';
				}

				$owner = str_replace('value="'.$data['owner'].'"','value="'.$data['owner'].'" selected="selected"',$owner);

				$image = '';
				$get = mysql_query("SELECT imageID,type,name FROM images");
				while($ds = mysql_fetch_assoc($get)){
					$image .= '<option value="'.$ds['imageID'].'">'.$ds['type'].' - '.$ds['name'].'</option>';
				}

				$image = str_replace('value="'.$data['image'].'"','value="'.$data['image'].'" selected="selected"',$image);

				echo '<form action="index.php?site=vms" method="post">';
				echo '<table cellspacing="0" cellpadding="0">
										<thead><tr>
								<th colspan="2">edit VM</th>
							</tr></thead>';
				echo '<tr><td>Name:</td><td><input type="text" class="no-margin" value="'.$data['name'].'" name="name"/></td></tr>';
				echo '<tr><td>Owner:</td><td><select name="owner" class="no-margin">'.$owner.'</select></td></tr>';
				echo '<tr><td>Image:</td><td><select name="image" class="no-margin">'.$image.'</select></td></tr>';
				echo '<tr><td>Ram:</td><td><input type="text" class="no-margin inline" value="'.$data['ram'].'" name="ram" size="6"/> Mb</td></tr>';
				echo '<tr><td>VNC Password:</td><td><input type="text" class="no-margin inline" value="'.$data['password'].'" name="password"/></td></tr>';
				echo '<tr><td>Optionale Parameter:</td><td><input type="text" class="no-margin inline" value="'.$data['params'].'" name="params"/></td></tr>';
				echo '</table>';
				echo '<input type="hidden" name="vm" value="'.$data['vmID'].'"/><input type="submit" class="no-margin center" name="save_edit" value="Speichern" />';
				echo '</form>';
			}
			else{
				echo "<div class='notice warning'>Die VM scheint zu laufen und kann so nicht bearbeitet werden.</div>";
			}
		}
		else{
			echo "<div class='notice error'>Es existiert keine VM mit dieser ID</div>";
		}
	}
	elseif($action == "clone"){

	}
	else{
		
		echo '<a href="index.php?site=vms&action=new" class="button no-margin small center grey "><span class="icon">+</span>neue VM</a>';
		
		$get = mysql_query("SELECT * FROM vm");
		if(mysql_num_rows($get)){
			echo '<table cellspacing="0" cellpadding="0">
<thead><tr>
	<th width="80"> </th>
	<th>Image</th>
	<th>Ram</th>
	<th width="120">Last Run</th>
	<th width="140">Options</th>
</tr></thead>';
			while($ds = mysql_fetch_assoc($get)){
				if($ds['lastrun'] != '0000-00-00'){
					$lastrun = date("d.m.Y H:i", strtotime($ds['lastrun']));
				}
				else{
					$lastrun = '---';
				}
				if($ds['status'] == QemuMonitor::RUNNING){
					$buttons = '<a href="index.php?site=vms&action=stop&vmID='.$ds['vmID'].'" class="button red small center  no-margin"><span class="icon">Q</span>Stop</a>';
					$buttons .='<a href="vnc.php?vmID='.$ds['vmID'].'"class="button small center grey  no-margin"><span class="icon">0</span>VNC</a>';
				}
				else{
					$buttons = '<a href="index.php?site=vms&action=start&vmID='.$ds['vmID'].'" class="button green small center no-margin"><span class="icon">&nbsp;</span>Start</a>';
					$buttons .='<a href="index.php?site=vms&action=edit&vmID='.$ds['vmID'].'" class="button small center grey no-margin"><span class="icon">G</span>Edit</a>';
					$buttons .='<a href="index.php?site=vms&action=clone&vmID='.$ds['vmID'].'" class="button small center grey no-margin"><span class="icon">R</span>Clone</a>';
				}
				echo '<tr>
	<th>'.$ds['name'].'<br/><small>'.Helper::getUserName($ds['owner']).'</small></th>
	<td>'.Image::getImagePath($ds['image']).'</td>
	<td>'.$ds['ram'].' MB</td>
	<td>'.$lastrun.'</td>
	<td>'.$buttons.'</td>
</tr>';
			}
			echo '</table>';
		}
		else{
			echo "Es gibt noch keine VMs.";
		}
	}
}
else{
	echo "Du musst dich einloggen um diese Funktion zu nutzen.";
}