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
			$do = false;
			if($_SESSION['user']->role['image_create'] == 1){

				$query = $GLOBALS['pdo']->prepare("INSERT INTO images (name,path,type,deleteable) VALUES (:name, :path, :type, :deleteable)");
				$query->bindValue(":name",$_POST['name'],PDO::PARAM_STR);
				$query->bindParam(":type",$type,PDO::PARAM_STR);
				$query->bindParam(":path",$path,PDO::PARAM_STR);
				$query->bindValue(":deleteable",!isset($_POST['deleteable']),PDO::PARAM_INT);

				$type = $_POST['type'];

				if($_POST['tab'] == "tab2"){
					$path = $_POST['path_create'];
					exec($GLOBALS['config']['qemu_img_executable']." create -f ".$_POST['create_type']." ".$_POST['path_create']." ".$_POST['create_size']);
					$do = true;
					$tmp->assign('message','<div class="notice success">Neues Image angelegt</div>');
				}
				elseif($_POST['tab'] == "tab3"){
					$path = $_POST['usb_device'];
					if($path != "0"){
						$type = "usb";
						$do = true;
					}
					else{
						$tmp->assign('message','<div class="notice notice">Es muss ein USB-Device ausgewählt werden, wenn sie diesen Typ wählen</div>');
						$action = 'new';
					}
				}
				elseif(file_exists($_POST['path']) && is_file($_POST['path']) && $_POST['path'] != $GLOBALS['config']['qemu_image_folder']){
					$query2 = $GLOBALS['pdo']->prepare("SELECT path FROM images WHERE path= :path");
					$query2->bindValue(":path",$_POST['path'],PDO::PARAM_STR);
					$query2->execute();
					if($query2->rowCount() == 0){
						$do = true;
						$tmp->assign('message','<div class="notice success">Neues Image angelegt</div>');
					}
					else{
						$tmp->assign('message','<div class="notice notice">Das Image wurde bereits eingetragen.</div>');
						$action = 'new';
					}
					$path = $_POST['path'];
				}
				else{
					$tmp->assign('message','<div class="notice notice">Der Pfad existiert nicht.</div>');
					$action = 'new';
				}
				if($do){
					$query->execute();
				}
			}
		}
		elseif(isset($_POST['save_edit'])){
			if($_SESSION['user']->role['image_edit'] == 1){
				$do = false;
				$query = $GLOBALS['pdo']->prepare("UPDATE images SET name = :name, path = :path, type=:type, deleteable = :deleteable  WHERE imageID= :imageID");

				$query->bindValue(':name',$_POST['name'],PDO::PARAM_STR);
				$query->bindValue(':type',$_POST['type'],PDO::PARAM_STR);
				$query->bindValue(':deleteable',!isset($_POST['deleteable']),PDO::PARAM_INT);
				$query->bindValue(':imageID',$_POST['image'],PDO::PARAM_INT);
				$query->bindParam(':path',$path,PDO::PARAM_STR);

				if($_POST['type'] == "usb"){
					$path = $_POST['path_usb'];
					if($path != "0"){
						$do = true;
					}
				}
				else{
					$path = $_POST['path'];
					$do = true;
				}
				if($do){
					$query->execute();
				}
			}
		}
		elseif($action =='delete'){
			$id = $_GET['image'];
				
			$query = $GLOBALS['pdo']->prepare("SELECT v.vmID FROM vm_images i LEFT JOIN vm v ON v.vmID=i.vmID WHERE v.status= :status AND i.imageID= :imageID");
			$query->bindValue(":status",QemuMonitor::RUNNING,PDO::PARAM_INT);
			$query->bindValue(":imageID",$id,PDO::PARAM_INT);
			$query->execute();
				
			if($query->rowCount() > 0){
				$tmp->assign('message','<div class="notice notice">Das Image wird noch in einer VM genutzt</div>');
			}
			else{
				$query = $GLOBALS['pdo']->prepare("DELETE FROM images WHERE imageID= :imageID");
				$query->bindValue(":imageID",$id,PDO::PARAM_INT);
				$query->execute();

				$query = $GLOBALS['pdo']->prepare("DELETE FROM vm_images WHERE imageID= :imageID");
				$query->bindValue(":imageID",$id,PDO::PARAM_INT);
				$query->execute();

				$tmp->assign('message','<div class="notice success">Image erfolgreich gelöscht</div>');
			}
		}
		elseif($action == 'clone'){
			$id = $_GET['image'];
			$query = $GLOBALS['pdo']->prepare("SELECT * FROM images WHERE imageID= :imageID");
			$query->bindValue(":imageID",$id,PDO::PARAM_INT);
			$query->execute();
				
			$data = $query->fetch();
			if($data['type'] != "usb"){
				if(is_dir($data['path']) == false){
						
					$query = $GLOBALS['pdo']->prepare("INSERT INTO images (name,type,deleteable) VALUES (:name, :type, :deleteable)");
					$query->bindValue(":name",$data['name'],PDO::PARAM_STR);
					$query->bindValue(":type",$data['type'],PDO::PARAM_STR);
					$query->bindValue(":deleteable",$data['deleteable'],PDO::PARAM_INT);
					$do = $query->execute();

					if($do){
						$id = $GLOBALS['pdo']->lastInsertId();
						$name = explode(".",$data['path'],2);
						$newname = $name[0]."_".$id.".".$name[1];
						$copy = copy($data['path'],$newname);
						if($copy){
							$query = $GLOBALS['pdo']->prepare("UPDATE images SET path=:path WHERE imageID=:imageID");
							$query->bindValue(":imageID",$id,PDO::PARAM_INT);
							$query->bindValue(":path",$newname,PDO::PARAM_STR);
							$query->execute();
								
							$tmp->assign('message','<div class="notice successful">Datei erfolgreich geklont</div>');
						}
						else{
							$query = $GLOBALS['pdo']->prepare("DELETE FROM images WHERE imageID=:imageID");
							$query->bindValue(":imageID",$id,PDO::PARAM_INT);
							$query->execute();
							$tmp->assign('message','<div class="notice successful">Datei konnte nicht kopiert werden</div>');
						}
					}
					else{
						$tmp->assign('message','<div class="notice error">Fehler</div>');
					}
				}
				else{
					$tmp->assign('message','<div class="notice error">Ordner können nicht geklont werden.</div>');
				}
			}
			else{
				$tmp->assign('message','<div class="notice error">USB-Geräte können nicht geklont werden.</div>');
			}
		}

		if($action == 'new'){
			if($_SESSION['user']->role['image_create'] == 1){


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

				if(isset($_POST['path_create'])){
					$path_create = $_POST['path'];
				}
				else{
					$path_create = $GLOBALS['config']['qemu_image_folder'];
				}

				if(isset($_POST['type'])){
					$types = str_replace('value="'.$_POST['type'].'"', 'value="'.$_POST['type'].'" selected="selected"', $GLOBALS['device_types']);
				}
				else{
					$types = $GLOBALS['device_types'];
				}

				if(isset($_POST['create_type'])){
					$create_type = str_replace('value="'.$_POST['create_type'].'"', 'value="'.$_POST['create_type'].'" selected="selected"', $GLOBALS['hdd_formats']);
				}
				else{
					$create_type = str_replace('value="qcow2"', 'value="qcow2" selected="selected"', $GLOBALS['hdd_formats']);
				}

				if(isset($_POST['create_size'])){
					$create_size = $_POST['create_size'];
				}
				else{
					$create_size = "10G";
				}

				if(isset($_POST['deleteable'])){
					$delete = ' checked="checked"';
				}
				else{
					$delete = '';
				}

				$usb = '';
				$usb_list = Helper::getUSBDevices();
				if(count($usb_list)){
					foreach(Helper::getUSBDevices() as $dev){
						$usb .= '<option value="'.$dev[0].':'.$dev[1].'">'.$dev[2].'</option>';
					}
				}
				else{
					$usb = '<option value="0">kein USB Gerät vorhanden</option>';
				}

				if(isset($_POST['usb_device'])){
					$usb = str_replace('value="'.$_POST['usb_device'].'"', 'value="'.$_POST['usb_device'].'" selected="selected"', $usb);
				}

				$tmp2 = new RainTPL();
				$tmp2->assign('name',$name);
				$tmp2->assign('types',$types);
				$tmp2->assign('path',$path);
				$tmp2->assign('path_create',$path_create);
				$tmp2->assign('create_type',$create_type);
				$tmp2->assign('create_size',$create_size);
				$tmp2->assign('delete',$delete);
				$tmp2->assign('usb_device',$usb);
				$tmp->assign('content',$tmp2->draw('image_new',true));
			}
		}
		elseif($action == 'edit'){
			if($_SESSION['user']->role['image_edit'] == 1){

				$image = $_GET['image'];

				$query = $GLOBALS['pdo']->prepare("SELECT * FROM images WHERE imageID= :imageID");
				$query->bindValue(":imageID",$image,PDO::PARAM_INT);
				$query->execute();

				if($query->rowCount()>0){
					$data = $query->fetch();

					$types = str_replace('value="'.$data['type'].'"', 'value="'.$data['type'].'" selected="selected"', $GLOBALS['device_types']);
						
					if(!$data['deleteable']){
						$delete = ' checked="checked"';
					}
					else{
						$delete = '';
					}
						
					$usb = '';
					$usb_list = Helper::getUSBDevices();
					if(count($usb_list)){
						foreach(Helper::getUSBDevices() as $dev){
							$usb .= '<option value="'.$dev[0].':'.$dev[1].'">'.$dev[2].'</option>';
						}
					}
					else{
						$usb = '<option value="0">kein USB Gerät vorhanden</option>';
					}
						
					$usb = str_replace('value="'.$data['path'].'"', 'value="'.$data['path'].'" selected="selected"', $usb);
						
					if($data['type'] =="usb"){

						$style_usb = "";
						$style_path = 'hide';

						$path = '';
					}
					else{
						$path = $data['path'];
						$style_usb = "hide";
						$style_path = '';
					}
						
					$tmp2 = new RainTPL();
					$tmp2->assign('name',$data['name']);
					$tmp2->assign('types',$types);
					$tmp2->assign('path',$path);
					$tmp2->assign('usb',$usb);
					$tmp2->assign('class_usb',$style_usb);
					$tmp2->assign('class_path',$style_path);
					$tmp2->assign('imageID',$data['imageID']);
					$tmp2->assign('delete',$delete);
					$tmp->assign('content',$tmp2->draw('image_edit',true));
				}
				else{
					$tmp->assign('content','<div class="notice warning">Es existiert kein Image mit dieser ID</div>');
				}

			}
		}
		elseif($action == 'status'){
			$image = $_GET['image'];
				
			$query = $GLOBALS['pdo']->prepare("SELECT * FROM images WHERE imageID= :imageID");
			$query->bindValue(":imageID",$image,PDO::PARAM_INT);
			$query->execute();
				
			if($query->rowCount()>0){
				$data = $query->fetch();

				$tmp2 = new RainTPL();

				$tmp2->assign('path',$data['path']);

				$tmp2->assign('type','n/a');
				$tmp2->assign('virtual_size','n/a');
				$tmp2->assign('real_size','n/a');

				$status = Image::getStatus($data['imageID']);
				if($data['type']=="usb"){
					$tmp2->assign('type',"USB");
					$tmp2->assign('path',$data['path']." - ".Helper::getUsbDeviceName($data['path']));
				}
				elseif(isset($status['type'])){
					$tmp2->assign('type',$status['type']);
					$tmp2->assign('virtual_size',FileSystem::formatFileSize($status['virtual_size']));
					$tmp2->assign('real_size',FileSystem::formatFileSize(Helper::toBytes($status['real_size'])));
				}


				$tmp->assign('content',$tmp2->draw('images_status',true));
			}
			else{
				$tmp->assign('content','<div class="notice warning">Es existiert kein Image mit dieser ID</div>');
			}
		}
		else{
				
			$tmp2 = new RainTPL();
				
				
			$query = $GLOBALS['pdo']->prepare("SELECT i.*,v.status FROM images i LEFT JOIN vm_images t ON t.imageID = i.imageID LEFT JOIN vm v ON v.vmID = t.vmID");
			$query->execute();
				
			$vms = array();
			if($query->rowCount()){
				while($ds = $query->fetch()){
					$buttons = '';
					if($_SESSION['user']->role['image_edit'] == 1){
						$buttons .= '<a href="index.php?site=images&action=edit&image='.$ds['imageID'].'" class="button small center grey"><span class="icon" data-icon="G"></span>Edit</a>';
					}
					if($ds['deleteable'] && $ds['status'] == QemuMonitor::SHUTDOWN){
						$buttons .= '<a href="index.php?site=images&action=delete&image='.$ds['imageID'].'" class="button small center grey"><span class="icon" data-icon="T"></span>delete</a>';
					}
					if($ds['status'] == QemuMonitor::SHUTDOWN && $ds['type'] != 'usb'){
						$buttons .= '<a href="index.php?site=images&action=status&image='.$ds['imageID'].'" class="button small center grey"><span class="icon" data-icon="v"></span> Status</a>';
					}
						
					if($ds['type'] != 'usb' && is_dir($ds['path']) == false){
						$buttons .='<a href="index.php?site=images&action=clone&image='.$ds['imageID'].'" class="button small center grey"><span class="icon" data-icon="R"></span>Clone</a>';
					}
						
					$obj = array();
					$obj['buttons'] = $buttons;
					$obj['name'] = $ds['name'];
					if($ds['type'] == "usb"){
						$obj['path'] = Helper::getUsbDeviceName($ds['path']);
						$obj['type'] = $ds['type']." - ".$ds['path'];
					}
					else{
						$obj['path'] = $ds['path'];
						$obj['type'] = $ds['type'];
					}
						
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