<?php
class MyVM extends Modul{
	public function getHeader(){
		return "<h1>Meine Vms</h1>";
	}
	
	public function action_start(){
		Routing::getInstance()->appendRender($this,"action_default");
		$vm = new QemuVm($_GET['vmID']);
		if(Server::hasRessources($vm->ram)){
			if($vm->isOwner()){
				if($vm->status == QemuMonitor::RUNNING){
					return "<div class='notice'>Die VM scheint bereits aus zu laufen.</div>";
				}
				else{
					$vm->startVM();
					try{
						$vm->connect();
					}
					catch(Exception $e){
						$vm->setStatus(QemuMonitor::SHUTDOWN);
						return "<div class='notice warning'>Die VM scheint nicht zu starten.</div>";
					}
					if(!isset($e)){
						return "<div class='notice success'>Die VM wurde gestartet.</div>";
					}
				}
			}
		}
		else{
			return "<div class='notice error'>Es sind keine Ressourcen mehr verfügbar um die VM zu starten</div>";
		}
	}
	
	public function action_stop(){
		Routing::getInstance()->appendRender($this,"action_default");
		$vm = new QemuVm($_GET['vmID']);
		if($vm->isOwner()){
			if($vm->status == QemuMonitor::RUNNING){
				try{
					$vm->connect();
				}
				catch(Exception $e){
					$vm->setStatus(QemuMonitor::SHUTDOWN);
					return "<div class='notice warning'>Die VM scheint bereits aus zu sein.</div>";
				}
				if(!isset($e)){
					$vm->shutdown();
					return "<div class='notice success'>Die VM wird ausgeschaltet.</div>";
				}
			}
			else{
				return "<div class='notice warning'>Die VM scheint bereits aus zu sein.</div>";
			}
		}
		else{
			return "<div class='notice error'>Sie besitzen nicht die Rechte die VM zu stoppen</div>";
		}
	}
	
	public function action_default(){
		$tmp2 = new RainTPL();
		
		$query = $GLOBALS['pdo']->prepare("SELECT * FROM vm WHERE owner = :owner");
		$query->bindValue(":owner",$_SESSION['user']->id,PDO::PARAM_INT);
		$query->execute();
		
		if($query->rowCount() > 0){
			$vms = array();
			while($ds = $query->fetch()){
				if($ds['lastrun'] != '0000-00-00'){
					$lastrun = date("d.m.Y H:i", strtotime($ds['lastrun']));
				}
				else{
					$lastrun = '---';
				}
				if($ds['status'] == QemuMonitor::RUNNING){
					$buttons  = '<a href="index.php?site=myvm&action=stop&vmID='.$ds['vmID'].'" class="button red small center"><span class="icon" data-icon="Q"></span>Stop</a>';
					$buttons  .= '<a href="screenshot.php?vmID='.$ds['vmID'].'" class="button small center"><span class="icon" data-icon="0"></span>Screenshot</a>';
					if($ds['password']){
						$buttons .= '<a href="vnc.php?vmID='.$ds['vmID'].'" class="button small center grey"><span class="icon" data-icon="0"></span>VNC</a>';
					}
					else{
						$buttons .= '<a href="#'.$ds['vmID'].'" disabled="disabled" class="button small center grey vm_disabled"><span class="icon" data-icon="0"></span>VNC</a>';
					}
				}
				else{
					$buttons  = '<a href="index.php?site=myvm&action=start&vmID='.$ds['vmID'].'" class="button green small center"><span class="icon" data-icon="&nbsp;"></span>Start</a>';
					$buttons .= '<a class="button small center grey"><span class="icon" data-icon="G"></span>Edit</a>';
				}
				$vm = array();
				$vm['lastrun'] = $lastrun;
				$vm['buttons'] = $buttons;
				$vm['name'] = $ds['name'];
				$vm['ram'] = FileSystem::formatFileSize($ds['ram']*1024*1024,0);
				$vms[] = $vm;
			}
			$tmp2->assign('vms',$vms);
			return $tmp2->draw('myvm_table',true);
		}
		else{
			return "Du hast noch keine VM. Du musst warten bis dir eine zugeteilt wird von den Admins.";
		}
	}
}
$modul = new MyVM();
Routing::getInstance()->addRouteByAction($modul,'myvm','start');
Routing::getInstance()->addRouteByAction($modul,'myvm','stop');
Routing::getInstance()->addRouteByAction($modul,'myvm','default');