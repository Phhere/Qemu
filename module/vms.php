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
			mysql_query("INSERT INTO vm (owner,name,image,ram,password,params,persistent) VALUES
			('".mysql_real_escape_string($_POST['owner'])."',
			 '".mysql_real_escape_string($_POST['name'])."',
			 '".mysql_real_escape_string($_POST['image'])."',
			 '".mysql_real_escape_string($_POST['ram'])."',
			 '".mysql_real_escape_string($_POST['password'])."',
			 '".mysql_real_escape_string($_POST['params'])."',
			 '".isset($_POST['persistent'])."')");
			$tmp->assign('message',"<div class='notice success'>Die Daten wurden gespeichert.</div>");
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
			params='".mysql_real_escape_string($_POST['params'])."',
			persistent='".isset($_POST['persistent'])."'
				 WHERE vmID='".$id."'");
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
					$tmp->assign('message',"<div class='notice'>Die VM scheint bereits aus zu laufen.</div>");
				}
				else{
					$vm->startVM();
					try{
						$vm->connect();
					}
					catch(Exception $e){
						$tmp->assign('message',"<div class='notice warning'>Die VM scheint nicht zu starten.</div>");
						$vm->setStatus(QemuMonitor::SHUTDOWN);
					}
					if(!isset($e)){
						$tmp->assign('message',"<div class='notice success'>Die VM wurde gestartet.</div>");
					}
				}
			}
		}
		else{
			$tmp->assign('message',"<div class='notice error'>Es sind keine Ressourcen mehr verfügbar um die VM zu starten</div>");
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
					$tmp->assign('message',"<div class='notice warning'>Die VM scheint bereits aus zu sein.</div>");
					$vm->setStatus(QemuMonitor::SHUTDOWN);
				}
				if(!isset($e)){
					$vm->shutdown();
					$tmp->assign('message',"<div class='notice success'>Die VM wird ausgeschaltet.</div>");
				}
			}
			else{
				$tmp->assign('message',"<div class='notice warning'>Die VM scheint bereits aus zu sein.</div>");
			}
		}
		else{
			$tmp->assign('message',"<div class='notice error'>Sie besitzen nicht die Rechte die VM zu stoppen</div>");
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
			
			$tmp2 = new RainTPL();
			$tmp2->assign('owner',$owner);
			$tmp2->assign('image',$image);
			$tmp2->assign('ram',$GLOBALS['config']['default_ram']);
			$tmp->assign('content',$tmp2->draw('vms_new',true));
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

				if($data['persistent']) $persistent = "checked='checked'";
				else $persistent = '';
				
				$tmp2 = new RainTPL();
				$tmp2->assign('name',$data['name']);
				$tmp2->assign('owner',$owner);
				$tmp2->assign('image',$image);
				$tmp2->assign('ram',$data['ram']);
				$tmp2->assign('password',$data['password']);
				$tmp2->assign('params',$data['params']);
				$tmp2->assign('vmID',$data['vmID']);
				$tmp2->assign('persistent',$persistent);
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

		$get = mysql_query("SELECT * FROM vm");
		if(mysql_num_rows($get)){
			$vms = array();
			while($ds = mysql_fetch_assoc($get)){
				if($ds['lastrun'] != '0000-00-00'){
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
				$vm['image'] = Image::getImagePath($ds['image']);
				$vm['ram'] = FileSystem::formatFileSize($ds['ram']*1024*1024,0);
				$vm['lastrun'] = $lastrun;
				$vm['buttons'] = $buttons;
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