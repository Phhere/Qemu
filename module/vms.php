<h3>Virtuelle Maschienen</h3>

<?php
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
	elseif($action == "edit"){

	}
	elseif($action == "clone"){

	}
	else{
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
					$buttons  = '<a href="index.php?site=vms&action=start&vmID='.$ds['vmID'].'" class="button green small center no-margin"><span class="icon">&nbsp;</span>Start</a>';
					$buttons .='<a href="index.php?site=vms&action=edit&vmID='.$ds['vmID'].'" class="button small center grey no-margin"><span class="icon">G</span>Edit</a>';
					$buttons .='<a href="index.php?site=vms&action=clone&vmID='.$ds['vmID'].'" class="button small center grey no-margin"><span class="icon">R</span>Clone</a>';
				}
				echo '<tr>
	<th>'.$ds['name'].'</th>
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