<?php
$tmp = new RainTPL();
if(isset($_SESSION['user'])){
	
	if(isset($_GET['action'])){
		$action = $_GET['action'];
	}
	else {
		$action = null;
	}
	
	if($action == "start"){
		if(Helper::hasRessources()){
			if(Helper::isOwner($_GET['vmID'])){
				$vm = new QemuVm($_GET['vmID']);
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
	
	$tmp2 = new RainTPL();
	
	$get = mysql_query("SELECT * FROM vm WHERE owner = '".$_SESSION['user']->id."'");
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
				$buttons  = '<a href="index.php?site=myvm&action=stop&vmID='.$ds['vmID'].'" class="button red small center"><span class="icon" data-icon="Q"></span>Stop</a>';
				$buttons .= '<a href="vnc.php?vmID='.$ds['vmID'].'"class="button small center grey"><span class="icon" data-icon="0"></span>VNC</a>';
			}
			else{
				$buttons  = '<a href="index.php?site=myvm&action=start&vmID='.$ds['vmID'].'" class="button green small center"><span class="icon" data-icon="&nbsp;"></span>Start</a>';
				$buttons .= '<a class="button small center grey"><span class="icon" data-icon="G"></span>Edit</a>';
			}
			$vm = array();
			$vm['lastrun'] = $lastrun;
			$vm['buttons'] = $buttons;
			$vm['name'] = $ds['name'];
			$vm['image'] = Image::getImagePath($ds['image']);
			$vm['ram'] = FileSystem::formatFileSize($ds['ram']*1024*1024,0);
			$vms[] = $vm;
		}
		$tmp2->assign('vms',$vms);
		$tmp->assign('content',$tmp2->draw('myvm_table',true));
	}
	else{
		$tmp->assign('content',"Du hast noch keine VM. Du musst warten bis dir eine zugeteilt wird von den Admins.");
	}
}
else{
	$tmp->assign('content',"Du musst dich einloggen um diese Funktion zu nutzen.");
}
$GLOBALS['template']->assign('content',$tmp->draw('myvm',true));