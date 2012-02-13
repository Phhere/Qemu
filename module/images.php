<h3>Images</h3>
<?php
if(isset($_SESSION['user'])){
	if($_SESSION['user']->role['image_create'] == 1 ||
	   $_SESSION['user']->role['image_edit'] == 1){
		
		if(isset($_GET['action'])){
			$action = $_GET['action'];
		}
		else {
			$action = null;
		}
		
		if(isset($_POST['save_new'])){
			if($_SESSION['user']->role['image_create'] == 1){
				if(file_exists($_POST['path'])){
					mysql_query("INSERT INTO images (name,path,type) VALUES ('".mysql_real_escape_string($_POST['name'])."', '".mysql_real_escape_string($_POST['path'])."', '".mysql_real_escape_string($_POST['type'])."' )");
					echo '<div class="notice success">Neues Image angelegt</div>';
				}
				else{
					echo '<div class="notice notice">Der Pfad existiert nicht.</div>';
					$action = 'new';
				}
			}
		}
		elseif(isset($_POST['save_edit'])){
			if($_SESSION['user']->role['image_edit'] == 1){
				mysql_query("UPDATE images SET name = '".mysql_real_escape_string($_POST['name'])."',path = '".mysql_real_escape_string($_POST['path'])."',type = '".mysql_real_escape_string($_POST['type'])."' WHERE imageID='".$_POST['image']."'");
			}
		}
		elseif($action =='delete'){
			$id = $_GET['image'];
			if(mysql_num_rows(mysql_query("SELECT vmID FROM vm WHERE status='".QemuMonitor::RUNNING."' AND image='".$id."'"))){
				echo '<div class="notice notice">Das Image wird noch in einer VM genutzt</div>';
			}
			else{
				mysql_query("DELETE FROM images WHERE imageID='".$id."'");
				echo '<div class="notice success">Image erfolgreich gelöscht</div>';
			}
		}
		
		if($action == 'new'){
			if($_SESSION['user']->role['image_create'] == 1){
				$types = '<option value="cdrom">CD-ROM</option>';
				$types .= '<option value="hda">HDD A</option>';
				$types .= '<option value="hdb">HDD B</option>';
				$types .= '<option value="hdC">HDD C</option>';
				$types .= '<option value="floppy">Floppy</option>';
				
				if(isset($_POST['name'])){
					$name = $_POST['name'];
				}
				else{
					$name = '';
				}
				
				if(isset($_POST['path'])){
					$path = $_POST['path'];
				}
				else{
					$path = $GLOBALS['config']['qemu_image_folder'];
				}
				
				if(isset($_POST['type'])){
					$types = str_replace('value="'.$_POST['type'].'"', 'value="'.$_POST['type'].'" selected="selected"', $types);
				}
				
				echo '<form action="index.php?site=images" method="post">';
				echo '<table cellspacing="0" cellpadding="0">
										<thead><tr>
								<th colspan="2">new Image</th>
							</tr></thead>';
				echo '<tr><td>Name:</td><td><input type="text" class="no-margin" name="name" value="'.$name.'" /></td></tr>';
				echo '<tr><td>Type:</td><td><select class="no-margin" name="type">'.$types.'</select></td></tr>';
				echo '<tr><td>Path:</td><td><input type="text" class="no-margin" name="path" value="'.$path.'" /></td></tr>';
				
				echo '</table>';
				echo '<input type="submit" class="no-margin center" name="save_new" value="Speichern" />';
				echo '</form>';
			}
		}
		elseif($action == 'edit'){
			if($_SESSION['user']->role['image_edit'] == 1){
				
				$image = $_GET['image'];
				$get = mysql_query("SELECT * FROM images WHERE imageID='".$image."'");
				if(mysql_num_rows($get)){
					$data = mysql_fetch_assoc($get);
					$types = '<option value="cdrom">CD-ROM</option>';
					$types .= '<option value="hda">HDD A</option>';
					$types .= '<option value="hdb">HDD B</option>';
					$types .= '<option value="hdC">HDD C</option>';
					$types .= '<option value="floppy">Floppy</option>';
					$types = str_replace('value="'.$data['type'].'"', 'value="'.$data['type'].'" selected="selected"', $types);
					
					echo '<form action="index.php?site=images" method="post">';
						echo '<table cellspacing="0" cellpadding="0">
						<thead><tr>
				<th colspan="2">edit Image</th>
			</tr></thead>';
						echo '<tr><td>Name:</td><td><input type="text" class="no-margin" name="name" value="'.$data['name'].'" /></td></tr>';
							echo '<tr><td>Type:</td><td><select class="no-margin" name="type">'.$types.'</select></td></tr>';
							echo '<tr><td>Path:</td><td><input type="text" class="no-margin" name="path" value="'.$data['path'].'" /></td></tr>';
						echo '</table>';
						echo '<input type="hidden" name="image" value="'.$data['imageID'].'"/><input type="submit" class="no-margin center" name="save_edit" value="Speichern" />';
					echo '</form>';
				}
				else{
					echo '<div class="notice warning">Es existiert kein Image mit dieser ID</div>';
				}
				
			}
		}
		else{
			
			echo '<a href="index.php?site=images&action=new" class="button no-margin small center grey "><span class="icon">+</span>neues Image</a>';
			
			$get = mysql_query("SELECT * FROM images");
			echo '<table cellspacing="0" cellpadding="0">
			<thead><tr>
				<th width="100"> </th>
				<th width="200">Type</th>
				<th width="120">Options</th>
			</tr></thead>';
			if(mysql_num_rows($get)){
				while($ds = mysql_fetch_assoc($get)){
					$buttons = '';
					if($_SESSION['user']->role['image_edit'] == 1){
						$buttons .= '<a href="index.php?site=images&action=edit&image='.$ds['imageID'].'" class="button small center grey no-margin"><span class="icon">G</span>Edit</a>';
					}
					$buttons .= '<a href="index.php?site=images&action=delete&image='.$ds['imageID'].'" class="button small center grey no-margin"><span class="icon">T</span>delete</a>';
										echo '<tr>
						<th>'.$ds['name'].'</th>
						<td>'.$ds['path'].'<br/><small>'.$ds['type'].'</small></td>
						<td>'.$buttons.'</td>
					</tr>';
				}
			}
			else{
				echo '<tr><td colspan="4">Kein Image vorhanden</td></tr>';
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