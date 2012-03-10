<?php
class System extends Modul {
	public function getHeader(){
		return "<h1>Systemverwaltung</h1>";
	}
	public function post_save(){
		if(Modul::hasAccess('system') == false){
			return '<div class="notice error">Sie haben keinen Zugriff</div>';
		}
		Routing::getInstance()->appendRender($this,"action_default");

		$query = $GLOBALS['pdo']->prepare("UPDATE config SET `value`= :value WHERE `key`= :key");
		$query->bindParam(':value',$value,PDO::PARAM_STR);
		$query->bindParam(':key',$key,PDO::PARAM_STR);
			
		foreach($_POST as $key => $value){
			if(isset($GLOBALS['config'][$key])){
				$query->execute();
				$GLOBALS['config'][$key] = $value;
			}
		}
		return "<div class='notice success'>Einstellungen gespeichert</div>";
	}

	public function action_default(){
		if(Modul::hasAccess('system') == false){
			return '<div class="notice error">Sie haben keinen Zugriff</div>';
		}
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

		$ram = Server::getRamSettings();
		if($ram){
			$ram_usage = FileSystem::formatFileSize($ram['used']).' / '.FileSystem::formatFileSize($ram['all']);
		}
		else{
			$ram_usage = 'linux only';
		}

		$tmp->assign('ram_usage',$ram_usage);

		$cpu = Server::getCPUusage();
		if($cpu){
			$cpu_usage = implode(" ",array_values($cpu));
		}
		else{
			$cpu_usage = 'linux only';
		}

		$tmp->assign('cpu_usage',$cpu_usage);

		$free = disk_free_space ('/');
		$all = disk_total_space('/');

		$tmp->assign('free',FileSystem::formatFileSize($all-$free));
		$tmp->assign('all',FileSystem::formatFileSize($all));

		$qemu_ram = Server::getQemuRamTemp();
		if($qemu_ram !== false){
			$tmp->assign('ram_qemu',FileSystem::formatFileSize($qemu_ram));
		}
		else{
			$tmp->assign('ram_qemu','linux only');
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

		return $tmp->draw('system',true);
	}
}
$modul = new System();
$routing = Routing::getInstance();
$routing->addRouteByPostField($modul,'system','save','save');
$routing->addRouteByAction($modul,'system','default');
?>