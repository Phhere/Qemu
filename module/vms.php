<?php
$tmp = new RainTPL();
if(isset($_SESSION['user'])){

	if(isset($_GET['action'])){
		$action = $_GET['action'];
	}
	else {
		$action = null;
	}

	if(isset($_POST['save'])){
		if($_SESSION['user']->role['vm_create'] == 1){
				
			$query = $GLOBALS['pdo']->prepare("INSERT INTO vm (owner,name,ram,password,params,persistent) VALUES (:owner,:name,:ram,:password,:params,:persostemt)");
			$query->bindValue(':owner',$_POST['owner'],PDO::PARAM_STR);
			$query->bindValue(':name',$_POST['name'],PDO::PARAM_STR);
			$query->bindValue(':ram',$_POST['ram'],PDO::PARAM_STR);
			$query->bindValue(':password',$_POST['password'],PDO::PARAM_STR);
			$query->bindValue(':params',$_POST['params'],PDO::PARAM_STR);
			$query->bindValue(':persistent',isset($_POST['persistent']),PDO::PARAM_INT);
				
			$do = $query->execute();
			if($do){
				$id = $GLOBALS['pdo']->lastInsertId();

				$query = $GLOBALS['pdo']->prepare("INSERT INTO vm_images (vmID,imageID) VALUES (:vmID,:imageID)");
				$query->bindValue(':vmID',$id,PDO::PARAM_INT);
				$query->bindParam(':vmID',$image,PDO::PARAM_INT);

				foreach($_POST['image'] as $image){
					if($image != "0"){
						$query->execute();
					}
				}

				$tmp->assign('message',"<div class='notice success'>Die Daten wurden gespeichert.</div>");
			}
			else{
				$tmp->assign('message',"<div class='notice error'>Fehler beim speichern der Daten.</div>");
			}
		}
	}
	elseif(isset($_POST['save_edit'])){
		$id = (int)$_POST['vm'];
		if($_SESSION['user']->role['vm_edit'] == 1){
			
			$query = $GLOBALS['pdo']->prepare("UPDATE vm SET owner= :owner, name= :name, ram= :ram, password= :password, params= :params, persistent = :persistent WHERE vmID= :vmID");
			$query->bindValue(':owner', $_POST['owner'], PDO::PARAM_INT);
			$query->bindValue(':name', $_POST['name'], PDO::PARAM_STR);
			$query->bindValue(':ram', $_POST['ram'], PDO::PARAM_INT);
			$query->bindValue(':password', $_POST['password'], PDO::PARAM_STR);
			$query->bindValue(':params', $_POST['params'], PDO::PARAM_STR);
			$query->bindValue(':persistent', isset($_POST['persistent']), PDO::PARAM_INT);
			$query->bindValue(':vmID', $id, PDO::PARAM_INT);
			$query->execute();			
			
			$query = $GLOBALS['pdo']->prepare("DELETE FROM vm_images WHERE vmID= :vmID");
			$query->bindValue(':vmID', $id, PDO::PARAM_INT);
			$query->execute();
			
			$query = $GLOBALS['pdo']->prepare("INSERT INTO vm_images (vmID,imageID) VALUES (:vmID,:imageID)");
			$query->bindValue(':vmID',$id,PDO::PARAM_INT);
			$query->bindParam(':vmID',$image,PDO::PARAM_INT);

			foreach($_POST['image'] as $image){
				if($image != "0"){
					$query->execute();
				}
			}
				
			$tmp->assign('message',"<div class='notice success'>Die Daten wurden gespeichert.</div>");
		}
	}
	elseif(isset($_POST['clone'])){

	}

	if($action == "start"){
		$vm = new QemuVm($_GET['vmID']);
		if(Helper::hasRessources($vm->ram)){
			if(Helper::isOwner($_GET['vmID'])){
				if($vm->status == QemuMonitor::RUNNING){
					$tmp->assign('content',"<div class='notice'>Die VM scheint bereits aus zu laufen.</div>");
				}
				else{
					$vm->startVM();
					try{
						$vm->connect();
					}
					catch(Exception $e){
						$tmp->assign('content',"<div class='notice warning'>Die VM scheint nicht zu starten.</div>");
						$vm->setStatus(QemuMonitor::SHUTDOWN);
					}
					if(!isset($e)){
						$tmp->assign('content',"<div class='notice success'>Die VM wurde gestartet.</div>");
					}
				}
			}
		}
		else{
			$tmp->assign('content',"<div class='notice error'>Es sind keine Ressourcen mehr verfügbar um die VM zu starten</div>");
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
					$tmp->assign('content',"<div class='notice warning'>Die VM scheint bereits aus zu sein.</div>");
					$vm->setStatus(QemuMonitor::SHUTDOWN);
				}
				if(!isset($e)){
					$vm->shutdown();
					$tmp->assign('content',"<div class='notice success'>Die VM wird ausgeschaltet.</div>");
				}
			}
			else{
				$tmp->assign('content',"<div class='notice warning'>Die VM scheint bereits aus zu sein.</div>");
			}
		}
		else{
			$tmp->assign('content',"<div class='notice error'>Sie besitzen nicht die Rechte die VM zu stoppen</div>");
		}
	}
	elseif($action == "new"){
		if($_SESSION['user']->role['vm_create'] == 1){

			$owner = '<option value="0">--</option>';
			$query = $GLOBALS['pdo']->query("SELECT userID, email FROM users");
			while($ds = $query->fetch()){
				$owner .= '<option value="'.$ds['userID'].'">'.$ds['email'].'</option>';
			}

			$image = '<option value="0">--</option>';
			$query = $GLOBALS['pdo']->query("SELECT imageID,type,name FROM images");
			while($ds = $query->fetch()){
				$image .= '<option value="'.$ds['imageID'].'">'.$ds['type'].' - '.$ds['name'].'</option>';
			}
				
			$tmp2 = new RainTPL();
			$tmp2->assign('owner',$owner);
			$tmp2->assign('image',$image);
			$tmp2->assign('ram',$GLOBALS['config']['default_ram']);
			$tmp->assign('content',$tmp2->draw('vms_new',true));
		}

	}
	elseif($action == "edit"){
		$id = $_GET['vmID'];
		
		$query = $GLOBALS['pdo']->prepare("SELECT * FROM vm WHERE vmID= :vmID");
		$query->bindValue(':vmID', $id, PDO::PARAM_INT);
		$query->execute();
		
		if($query->rowCount()){
			$data = $query->fetch();
			if($data['status'] == QemuMonitor::SHUTDOWN){
				$owner = '';
				
				$query2 = $GLOBALS['pdo']->query("SELECT userID, email FROM users");
				
				while($ds = $query2->fetch()){
					$owner .= '<option value="'.$ds['userID'].'">'.$ds['email'].'</option>';
				}

				$owner = str_replace('value="'.$data['owner'].'"','value="'.$data['owner'].'" selected="selected"',$owner);

				$image_list = '<option value="0">--</option>';
				$query2 = $GLOBALS['pdo']->query("SELECT imageID,type,name FROM images");
				while($ds = $query2->fetch()){
					$image_list .= '<option value="'.$ds['imageID'].'">'.$ds['type'].' - '.$ds['name'].'</option>';
				}

				$images = array();
				$i=1;
				
				$query3 = $GLOBALS['pdo']->prepare("SELECT * FROM vm_images WHERE vmID= :vmID");
				$query3->bindValue(':vmID', $id, PDO::PARAM_INT);
				$query3->execute();
				
				while($ds = $query3->fetch()){
					$image = str_replace('value="'.$ds['imageID'].'"','value="'.$ds['imageID'].'" selected="selected"',$image_list);
					$images[] = array('image'=>$image,'counter'=>$i);
					$i++;
				}
				if(count($images) == 0){
					$images[] = array('image'=>$image_list,'counter'=>1);
				}


				if($data['persistent']) $persistent = "checked='checked'";
				else $persistent = '';

				$tmp2 = new RainTPL();
				$tmp2->assign('name',$data['name']);
				$tmp2->assign('owner',$owner);
				$tmp2->assign('ram',$data['ram']);
				$tmp2->assign('password',$data['password']);
				$tmp2->assign('params',$data['params']);
				$tmp2->assign('vmID',$data['vmID']);
				$tmp2->assign('persistent',$persistent);
				$tmp2->assign('images',$images);
				$tmp->assign('content',$tmp2->draw('vms_edit',true));

			}
			else{
				$tmp->assign('content',"<div class='notice warning'>Die VM scheint zu laufen und kann so nicht bearbeitet werden.</div>");
			}
		}
		else{
			$tmp->assign('content',"<div class='notice error'>Es existiert keine VM mit dieser ID</div>");
		}
	}
	elseif($action == "clone"){

	}
	else{

		$tmp2 = new RainTPL();

		$query = $GLOBALS['pdo']->query("SELECT * FROM vm");
		
		$query2 = $GLOBALS['pdo']->prepare("SELECT *,i.path,i.type FROM vm_images v JOIN images i ON i.imageID=v.imageID WHERE v.vmID = :vmID");
		$query2->bindParam(':vmID', $vmID,PDO::PARAM_INT);
		
		if($query->rowCount()){
			$vms = array();
			while($ds = $query->fetch()){
				$vmID = $ds['vmID'];
				
				if($ds['lastrun'] != '0000-00-00 00:00:00'){
					$lastrun = date("d.m.Y H:i", strtotime($ds['lastrun']));
				}
				else{
					$lastrun = '---';
				}
				if($ds['status'] == QemuMonitor::RUNNING){
					$buttons = '<a href="index.php?site=vms&action=stop&vmID='.$ds['vmID'].'" class="button red small center "><span class="icon" data-icon="Q"></span>Stop</a>';
					$buttons .='<a href="vnc.php?vmID='.$ds['vmID'].'"class="button small center grey"><span class="icon" data-icon="0"></span>VNC</a>';
				}
				else{
					$buttons = '<a href="index.php?site=vms&action=start&vmID='.$ds['vmID'].'" class="button green small center"><span class="icon" data-icon="&nbsp;"></span>Start</a>';
					$buttons .='<a href="index.php?site=vms&action=edit&vmID='.$ds['vmID'].'" class="button small center grey"><span class="icon" data-icon="G"></span>Edit</a>';
					$buttons .='<a href="index.php?site=vms&action=clone&vmID='.$ds['vmID'].'" class="button small center grey"><span class="icon" data-icon="R"></span>Clone</a>';
				}
				$vm = array();
				$vm['name'] = $ds['name'];
				$vm['owner'] = Helper::getUserName($ds['owner']);
				$vm['ram'] = FileSystem::formatFileSize($ds['ram']*1024*1024,0);
				$vm['lastrun'] = $lastrun;
				$vm['buttons'] = $buttons;

				$images = array();
				$query2->execute();
				while($dq = $query2->fetch()){
					$images[] = $dq['name'];
				}
				$vm['images'] = implode(", ",$images);

				$vms[] = $vm;
			}
			$tmp2->assign('vms',$vms);
		}
		else{
			$tmp2->assign('vms',"Es gibt noch keine VMs.");
		}
		$tmp->assign('content',$tmp2->draw('vms_main',true));
	}
}
else{
	$tmp->assign('content',"<div class='notice warning'>Du musst dich einloggen um diese Funktion zu nutzen.</div>");
}
$GLOBALS['template']->assign('content',$tmp->draw('vms',true));