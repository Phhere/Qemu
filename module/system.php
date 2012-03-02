<?php
if(isset($_SESSION['user'])){
	if($_SESSION['user']->role['system'] == 1){
			
		$get = mysql_query("SELECT count(vmID) AS `vms`, SUM(IF(status=1,1,0)) AS `vms_on` FROM vm");
		$data = mysql_fetch_assoc($get);

		$images=mysql_num_rows(mysql_query("SELECT imageID FROM images"));

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
			foreach($_POST as $key => $value){
				if(isset($GLOBALS['config'][$key])){
					mysql_query("UPDATE config SET `value`='".mysql_real_escape_string($value)."' WHERE `key`='".$key."'");
					$GLOBALS['config'][$key] = $value;
				}
			}
			$tmp->assign('message',"<div class='notice success'>Einstellungen gespeichert</div>");
		}

		
		$roles = '';
		$get = mysql_query("SELECT * FROM roles");
		while($ds = mysql_fetch_assoc($get)){
			if($ds['roleID'] == $GLOBALS['config']['default_role']) $selected = "selected='selected'";
			else $selected = '';
			$roles .= '<option value="'.$ds['roleID'].'" '.$selected.'>'.$ds['name'].'</option>';
		}
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