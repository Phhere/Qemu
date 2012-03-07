<?php
if(isset($_SESSION['user'])){
	if($_SESSION['user']->role['system'] == 1){
			
		$query = $GLOBALS['pdo']->prepare("SELECT count(vmID) AS `vms`, SUM(IF(status=1,1,0)) AS `vms_on` FROM vm");
		$query->execute();
		
		$data = $query->fetch();
		
		$query = $GLOBALS['pdo']->prepare("SELECT imageID FROM images");
		$query->execute();

		$images=$query->rowCount();

		$tmp = new RainTPL();
		$tmp->assign('vms',$data['vms']);
		$tmp->assign('vms_on',$data['vms_on']);
		$tmp->assign('images',$images);
		
		$ram = Helper::getRamSettings();
		if($ram){
			$ram_usage = FileSystem::formatFileSize($ram['used']).' / '.FileSystem::formatFileSize($ram['all']);
		}
		else{
			$ram_usage = 'linux only';
		}

		$tmp->assign('ram_usage',$ram_usage);
		
		$cpu = Helper::getCPUusage();
		if($cpu){
			$cpu_usage = implode(" ",array_values($cpu));
		}
		else{
			$cpu_usage = 'linux only';
		}
		
		$tmp->assign('cpu_usage',$cpu_usage);

		$free = disk_free_space ('/');
		$all = disk_total_space('/');
		
		$tmp->assign('free',FileSystem::formatFileSize($free));
		$tmp->assign('all',FileSystem::formatFileSize($all));
		
		$qemu_ram = Helper::getQemuRamTemp();
		if($qemu_ram !== false){
			$tmp->assign('ram_qemu',FileSystem::formatFileSize($qemu_ram));
		}
		else{
			$tmp->assign('ram_qemu','linux only');
		}
		
		
		if(isset($_POST['save'])){
			
			$query = $GLOBALS['pdo']->prepare("UPDATE config SET `value`= :value WHERE `key`= :key");
			$query->bindParam(':value',$value,PDO::PARAM_STR);
			$query->bindParam(':key',$key,PDO::PARAM_STR);
			
			foreach($_POST as $key => $value){
				if(isset($GLOBALS['config'][$key])){
					$query->execute();
					$GLOBALS['config'][$key] = $value;
				}
			}
			$tmp->assign('message',"<div class='notice success'>Einstellungen gespeichert</div>");
		}

		
		$roles = '';
		
		$query = $GLOBALS['pdo']->prepare("SELECT * FROM roles");
		$query->execute();

		while($ds = $query->fetch()){
			$roles .= '<option value="'.$ds['roleID'].'">'.$ds['name'].'</option>';
		}
		
		$roles = str_replace('value="'.$GLOBALS['config']['default_role'].'"', 'value="'.$GLOBALS['config']['default_role'].'" selected="selected"', $roles);
		
		$tmp->assign('roles',$roles);
		
		foreach($GLOBALS['config'] as $key => $value){
			$tmp->assign($key,$value);
		}
		
		$GLOBALS['template']->assign('content',$tmp->draw('system',true));
	}
	else{
		$GLOBALS['template']->assign('content',"<div class='notice warning'>Sie haben keinen Zugriff.</div>");
	}
}
else{
	$GLOBALS['template']->assign('content',"<div class='notice warning'>Sie müssen eingeloggt sein</div>");
}