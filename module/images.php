<?php
$tmp = new RainTPL();
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
				if(file_exists($_POST['path']) && is_file($_POST['path'])){
					if(mysql_num_rows(mysql_query("SELECT path FROM images WHERE path='".mysql_real_escape_string($_POST['path'])."'")) == 0){
						mysql_query("INSERT INTO images (name,path,type) VALUES ('".mysql_real_escape_string($_POST['name'])."', '".mysql_real_escape_string($_POST['path'])."', '".mysql_real_escape_string($_POST['type'])."' )");
						$tmp->assign('message','<div class="notice success">Neues Image angelegt</div>');
					}
					else{
						$tmp->assign('message','<div class="notice notice">Das Image wurde bereits eingetragen.</div>');
						$action = 'new';
					}
				}
				elseif(isset($_POST['create_type'])){
					exec($GLOBALS['config']['qemu_img_executable']." create -f ".$_POST['create_type']." ".$_POST['path']." ".$_POST['create_size']);
					mysql_query("INSERT INTO images (name,path,type) VALUES ('".mysql_real_escape_string($_POST['name'])."', '".mysql_real_escape_string($_POST['path'])."', '".mysql_real_escape_string($_POST['type'])."' )");
					$tmp->assign('message','<div class="notice success">Neues Image angelegt</div>');
				}
				else{
					$tmp->assign('message','<div class="notice notice">Der Pfad existiert nicht.</div>');
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
				$tmp->assign('message','<div class="notice notice">Das Image wird noch in einer VM genutzt</div>');
			}
			else{
				mysql_query("DELETE FROM images WHERE imageID='".$id."'");
				$tmp->assign('message','<div class="notice success">Image erfolgreich gelöscht</div>');
			}
		}
		
		if($action == 'new'){
			if($_SESSION['user']->role['image_create'] == 1){
				$types  = '<option value="cdrom">CD-ROM</option>';
				$types .= '<option value="hda">HDD A</option>';
				$types .= '<option value="hdb">HDD B</option>';
				$types .= '<option value="hdC">HDD C</option>';
				$types .= '<option value="floppy">Floppy</option>';
				
				$create_type  = '<option value="raw">Raw</option>';
				$create_type .= '<option value="qcow2">QCow2</option>';
				$create_type .= '<option value="vmdk">VMware (vmdk)</option>';
				$create_type .= '<option value="vdi">VirtualBox (vdi)</option>';
				
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
				
				if(isset($_POST['create_type'])){
					$create_type = str_replace('value="'.$_POST['create_type'].'"', 'value="'.$_POST['create_type'].'" selected="selected"', $create_type);
				}
				else{
					$create_type = str_replace('value="qcow2"', 'value="qcow2" selected="selected"', $create_type);
				}
				
				if(isset($_POST['create_size'])){
					$create_size = $_POST['create_size'];
				}
				else{
					$create_size = "10G";
				}
				
				$tmp2 = new RainTPL();
				$tmp2->assign('name',$name);
				$tmp2->assign('types',$types);
				$tmp2->assign('path',$path);
				$tmp2->assign('create_type',$create_type);
				$tmp2->assign('create_size',$create_size);
				$tmp->assign('content',$tmp2->draw('image_new',true));
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
					
					
					$tmp2 = new RainTPL();
					$tmp2->assign('name',$data['name']);
					$tmp2->assign('types',$types);
					$tmp2->assign('path',$data['path']);
					$tmp2->assign('imageID',$data['imageID']);
					$tmp->assign('content',$tmp2->draw('image_edit',true));
				}
				else{
					$tmp->assign('content','<div class="notice warning">Es existiert kein Image mit dieser ID</div>');
				}
				
			}
		}
		elseif($action == 'status'){
			$image = $_GET['image'];
			$get = mysql_query("SELECT * FROM images WHERE imageID='".$image."'");
			if(mysql_num_rows($get)){
				$data = mysql_fetch_assoc($get);
				
				$tmp2 = new RainTPL();
				
				$status = Image::getStatus($data['imageID']);
				
				$tmp2->assign('path',$data['path']);
				$tmp2->assign('type',$status['type']);
				$tmp2->assign('virtual_size',FileSystem::formatFileSize($status['virtual_size']));
				$tmp2->assign('real_size',$status['real_size']);
				
				$tmp->assign('content',$tmp2->draw('images_status',true));
			}
			else{
				$tmp->assign('content','<div class="notice warning">Es existiert kein Image mit dieser ID</div>');
			}
		}
		else{
			
			$tmp2 = new RainTPL();
			
			$get = mysql_query("SELECT i.*, v.status FROM images i LEFT JOIN vm v ON v.image = i.imageID");
			$vms = array();
			if(mysql_num_rows($get)){
				while($ds = mysql_fetch_assoc($get)){
					$buttons = '';
					if($_SESSION['user']->role['image_edit'] == 1){
						$buttons .= '<a href="index.php?site=images&action=edit&image='.$ds['imageID'].'" class="button small center grey"><span class="icon" data-icon="G"></span>Edit</a>';
					}
					$buttons .= '<a href="index.php?site=images&action=delete&image='.$ds['imageID'].'" class="button small center grey"><span class="icon" data-icon="T"></span>delete</a>';
				
					if($ds['status'] == QemuMonitor::SHUTDOWN){
						$buttons .= '<a href="index.php?site=images&action=status&image='.$ds['imageID'].'" class="button small center grey"><span class="icon" data-icon="v"></span> Status</a>';
					}
					
					$obj = array();
					$obj['buttons'] = $buttons;
					$obj['name'] = $ds['name'];
					$obj['path'] = $ds['path'];
					$obj['type'] = $ds['type'];
					
					$vms[] = $obj;
				}
				$tmp2->assign('vms',$vms);
			}
			else{
				$tmp2->assign('vms','<tr><td colspan="4">Kein Image vorhanden</td></tr>');
			}
			$tmp->assign('content',$tmp2->draw('images_main',true));
		}
	}
	else{
		$tmp->assign('content',"<div class='notice warning'>Sie haben keinen Zugriff.</div>");
	}
}
else{
	$tmp->assign('content',"<div class='notice warning'>Sie müssen eingeloggt sein</div>");
}
$GLOBALS['template']->assign('content',$tmp->draw('images',true));