<?php
class Images extends Modul{
	
	public function getHeader(){
		return "<h1>Images</h1>";
	}
	
	/*
	 * Handle $_POST['save_new']
	 */
	public function post_save_new(){

		if(Modul::hasAccess('image_create') == false){
			return '<div class="notice error">Sie haben keinen Zugriff</div>';
		}
		
		$content = '';

		$do = false;
		
		$query = $GLOBALS['pdo']->prepare("INSERT INTO images (name,path,type,shared,owner) VALUES (:name, :path, :type, :shared,:owner)");
		$query->bindValue(":name",$_POST['name'],PDO::PARAM_STR);
		$query->bindParam(":type",$type,PDO::PARAM_STR);
		$query->bindParam(":path",$path,PDO::PARAM_STR);
		$query->bindValue(":owner",$_SESSION['user']->id,PDO::PARAM_STR);
		$query->bindValue(":shared",isset($_POST['shared']),PDO::PARAM_INT);

		$type = $_POST['type'];

		if($_POST['tab'] == "tab2"){
			$path = $_POST['path_create'];
			exec($GLOBALS['config']['qemu_img_executable']." create -f ".$_POST['create_type']." ".$_POST['path_create']." ".$_POST['create_size']);
			$do = true;
			$content = '<div class="notice success">Neues Image angelegt</div>';
		}
		elseif($_POST['tab'] == "tab3"){
			$path = $_POST['usb_device'];
			if($path != "0"){
				$type = "usb";
				$do = true;
			}
			else{
				$content = '<div class="notice notice">Es muss ein USB-Device ausgewählt werden, wenn sie diesen Typ wählen</div>';
				Routing::getInstance()->appendRender($this,"action_new");
			}
		}
		elseif(file_exists($_POST['path']) && is_file($_POST['path']) && $_POST['path'] != $GLOBALS['config']['qemu_image_folder']){
			$query2 = $GLOBALS['pdo']->prepare("SELECT path FROM images WHERE path= :path");
			$query2->bindValue(":path",$_POST['path'],PDO::PARAM_STR);
			$query2->execute();
			if($query2->rowCount() == 0){
				$do = true;
				$content = '<div class="notice success">Neues Image angelegt</div>';
			}
			else{
				$content = '<div class="notice notice">Das Image wurde bereits eingetragen.</div>';
				Routing::getInstance()->appendRender($this,"action_new");
			}
			$path = $_POST['path'];
		}
		else{
			$content = '<div class="notice notice">Der Pfad existiert nicht.</div>';
			Routing::getInstance()->appendRender($this,"action_new");
		}
		if($do){
			$query->execute();
		}
		return $content;
	}

	/*
	* Handle $_POST['save_edit']
	*/
	public function post_save_edit(){
		
		if(Modul::hasAccess('image_edit') == false){
			return '<div class="notice error">Sie haben keinen Zugriff</div>';
		}
		
		$do = false;
		$query = $GLOBALS['pdo']->prepare("UPDATE images SET name = :name, path = :path, type=:type, shared = :shared  WHERE imageID= :imageID");

		$query->bindValue(':name',$_POST['name'],PDO::PARAM_STR);
		$query->bindValue(':type',$_POST['type'],PDO::PARAM_STR);
		$query->bindValue(':shared',isset($_POST['shared']),PDO::PARAM_INT);
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
			$content = '<div class="notice success">Änderung gespeichert</div>';
		}
		else{
			$content = '<div class="notice error">Fehler beim Speichern</div>';
		}
		return $content;
	}

	/*
	* Handle $_GET['action'] = status
	*/
	public function action_status(){
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
				$tmp2->assign('path',$data['path']." - ".Server::getUsbDeviceName($data['path']));
			}
			elseif(isset($status['type'])){
				$tmp2->assign('type',$status['type']);
				$tmp2->assign('virtual_size',FileSystem::formatFileSize($status['virtual_size']));
				$tmp2->assign('real_size',FileSystem::formatFileSize(Helper::toBytes($status['real_size'])));
			}


			return $tmp2->draw('images_status',true);
		}
		else{
			return '<div class="notice warning">Es existiert kein Image mit dieser ID</div>';
		}
	}

	/*
	* Handle $_GET['action'] = delete
	*/
	public function action_delete(){
		
		if(Modul::hasAccess('image_remove') == false){
			return '<div class="notice error">Sie haben keinen Zugriff</div>';
		}
		
		Routing::getInstance()->appendRender($this,"action_default");

		$id = $_GET['image'];

		$query = $GLOBALS['pdo']->prepare("SELECT v.vmID FROM vm_images i LEFT JOIN vm v ON v.vmID=i.vmID WHERE v.status= :status AND i.imageID= :imageID");
		$query->bindValue(":status",QemuMonitor::RUNNING,PDO::PARAM_INT);
		$query->bindValue(":imageID",$id,PDO::PARAM_INT);
		$query->execute();

		if($query->rowCount() > 0){
			$content = '<div class="notice notice">Das Image wird noch in einer VM genutzt</div>';
		}
		else{
			$query = $GLOBALS['pdo']->prepare("DELETE FROM images WHERE imageID= :imageID");
			$query->bindValue(":imageID",$id,PDO::PARAM_INT);
			$query->execute();

			$query = $GLOBALS['pdo']->prepare("DELETE FROM vm_images WHERE imageID= :imageID");
			$query->bindValue(":imageID",$id,PDO::PARAM_INT);
			$query->execute();

			$content = '<div class="notice success">Image erfolgreich gelöscht</div>';
		}
		return $content;
	}

	/*
	* Handle $_GET['action'] = clone
	*/
	public function action_clone(){
		
		if(Modul::hasAccess('image_clone') == false){
			return '<div class="notice error">Sie haben keinen Zugriff</div>';
		}
		
		Routing::getInstance()->appendRender($this,"action_default");
		$id = $_GET['image'];
		$query = $GLOBALS['pdo']->prepare("SELECT * FROM images WHERE imageID= :imageID");
		$query->bindValue(":imageID",$id,PDO::PARAM_INT);
		$query->execute();

		$data = $query->fetch();
		if($data['type'] != "usb"){
			if(is_dir($data['path']) == false){

				$query = $GLOBALS['pdo']->prepare("INSERT INTO images (name,type,shared) VALUES (:name, :type, :shared)");
				$query->bindValue(":name",$data['name'],PDO::PARAM_STR);
				$query->bindValue(":type",$data['type'],PDO::PARAM_STR);
				$query->bindValue(":shared",$data['shared'],PDO::PARAM_INT);
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

						$content = '<div class="notice success">Datei erfolgreich geklont</div>';
					}
					else{
						$query = $GLOBALS['pdo']->prepare("DELETE FROM images WHERE imageID=:imageID");
						$query->bindValue(":imageID",$id,PDO::PARAM_INT);
						$query->execute();
						$content = '<div class="notice error">Datei konnte nicht kopiert werden</div>';
					}
				}
				else{
					$content = '<div class="notice error">Fehler</div>';
				}
			}
			else{
				$content = '<div class="notice error">Ordner können nicht geklont werden.</div>';
			}
		}
		else{
			$content = '<div class="notice error">USB-Geräte können nicht geklont werden.</div>';
		}
		return $content;
	}

	/*
	* Handle $_GET['action'] = new
	*/
	public function action_new(){
		
		if(Modul::hasAccess('image_create') == false){
			return '<div class="notice error">Sie haben keinen Zugriff</div>';
		}
		
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

		if(isset($_POST['shared'])){
			$shared = ' checked="checked"';
		}
		else{
			$shared = '';
		}

		$usb = '';
		$usb_list = Server::getUSBDevices();
		if(count($usb_list)){
			foreach($usb_list as $dev){
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
		$tmp2->assign('shared',$shared);
		$tmp2->assign('usb_device',$usb);
		return $tmp2->draw('image_new',true);
	}

	/*
	* Handle $_GET['action'] = edit
	*/
	public function action_edit(){
		
		if(Modul::hasAccess('image_edit') == false){
			return '<div class="notice error">Sie haben keinen Zugriff</div>';
		}
		
		$image = $_GET['image'];

		$query = $GLOBALS['pdo']->prepare("SELECT * FROM images WHERE imageID= :imageID");
		$query->bindValue(":imageID",$image,PDO::PARAM_INT);
		$query->execute();

		if($query->rowCount()>0){
			$data = $query->fetch();

			$types = str_replace('value="'.$data['type'].'"', 'value="'.$data['type'].'" selected="selected"', $GLOBALS['device_types']);

			if(!$data['shared']){
				$shared = ' checked="checked"';
			}
			else{
				$shared = '';
			}

			$usb = '';
			$usb_list = Server::getUSBDevices();
			if(count($usb_list)){
				foreach($usb_list as $dev){
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
			$tmp2->assign('shared',$shared);
			return $tmp2->draw('image_edit',true);
		}
		else{
			return '<div class="notice warning">Es existiert kein Image mit dieser ID</div>';
		}
	}

	/*
	* Handle default requests on this modul
	*/
	public function action_default(){
		
		if(Modul::hasAccess('image_edit','image_create','image_clone','image_remove') == false){
			return '<div class="notice error">Sie haben keinen Zugriff</div>';
		}
		
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
				if(Image::isUsed($ds['imageID']) == false && ($ds['owner'] == $_SESSION['user']->id || Modul::hasAccess('image_remove'))){
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
					$obj['path'] = Server::getUsbDeviceName($ds['path']);
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
		return $tmp2->draw('images_main',true);
	}
}
$modul = new Images();
$routing = Routing::getInstance();
$routing->addRouteByAction($modul,'images','clone');
$routing->addRouteByAction($modul,'images','new');
$routing->addRouteByAction($modul,'images','edit');
$routing->addRouteByAction($modul,'images','status');
$routing->addRouteByPostField($modul,'images','save_new','save_new');
$routing->addRouteByPostField($modul,'images','save_edit','save_edit');
$routing->addRouteByAction($modul,'images','default');
?>