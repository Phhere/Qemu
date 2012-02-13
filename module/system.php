<h3>Systemverwaltung</h3>

<?php
if(isset($_SESSION['user'])){
	if($_SESSION['user']->role['system'] == 1){
			
		$get = mysql_query("SELECT count(vmID) AS `vms`, SUM(IF(status=1,1,0)) AS `vms_on` FROM vm");
		$data = mysql_fetch_assoc($get);

		$images=mysql_num_rows(mysql_query("SELECT imageID FROM images"));

		echo '<div class="col_5"><h4>VM Status</h4>
		VMs: '.$data['vms'].'<br/>
		VMs online: '.$data['vms_on'].'<br/>
		Images: '.$images.'
		</div>';
		echo '<div class="col_2"></div>';

		$ram = Helper::getRamSettings();
		if($ram){
			$ram_usage = FileSystem::formatFileSize($ram['used']).' / '.FileSystem::formatFileSize($ram['all']);
		}
		else{
			$ram_usage = 'linux only';
		}

		$cpu = Helper::getCPUusage();
		if($cpu){
			$cpu_usage = implode(" ",array_values($cpu));
		}
		else{
			$cpu_usage = 'linux only';
		}

		echo '<div class="col_5"><h4>Server Status</h4>
		CPU: '.$cpu_usage.'<br/>
		Ram: '.$ram_usage.'<br/>
		HDD: '.FileSystem::formatFileSize(disk_free_space ('/')).' / '.FileSystem::formatFileSize(disk_total_space('/')).'<br/>
		Qemu Ram: '.FileSystem::getDirectorySize('/dev/shm',true).'</div>';

		echo '<h4>Einstellungen</h4>';

		if(isset($_POST['save'])){
			foreach($_POST as $key => $value){
				if(isset($GLOBALS['config'][$key])){
					mysql_query("UPDATE config SET `value`='".mysql_real_escape_string($value)."' WHERE `key`='".$key."'");
					$GLOBALS['config'][$key] = $value;
				}
			}
			echo "<div class='notice success'>Einstellungen gespeichert</div>";
		}

		echo "<form method='post'>";

		echo '<table cellspacing="0" cellpadding="0">
<thead><tr>
<th>Einstellung</th>
<th>Wert</th>
</tr></thead>';

		echo '<tr>
<td>Qemu Executable</td>
<td><input type="text" class="no-margin" name="qemu_executable" value="'.$GLOBALS['config']['qemu_executable'].'" /></td>
</tr>';
		echo '<tr>
<td>Qemu Bios Folder</td>
<td><input type="text" class="no-margin" name="qemu_bios_folder" value="'.$GLOBALS['config']['qemu_bios_folder'].'" /></td>
</tr>';

		echo '<tr>
<td>Qemu Image Folder</td>
<td><input type="text" class="no-margin" name="qemu_image_folder" value="'.$GLOBALS['config']['qemu_image_folder'].'" /></td>
</tr>';

		echo '<tr>
<td>Qemu Monitor Startport</td>
<td><input type="text" class="no-margin" size="6" name="monitorport_min" value="'.$GLOBALS['config']['monitorport_min'].'" /></td>
</tr>';

		echo '<tr>
<td>VNC Startport</td>
<td><input type="text" class="no-margin" size="6" name="vncport_min" value="'.$GLOBALS['config']['vncport_min'].'" /></td>
</tr>';

		echo '<tr>
<td>Log Path</td>
<td><input type="text" class="no-margin" name="log_path" value="'.$GLOBALS['config']['log_path'].'" /></td>
</tr>';
		echo '<tr>
<td>Max Ram Usage</td>
<td><input type="text" class="no-margin inline" name="max_ram" size="4" value="'.$GLOBALS['config']['max_ram'].'" /> Gb</td>
</tr>';

		echo '</table>';
		echo '<input type="submit" class="no-margin center" name="save" value="Speichern" />';
		echo '</form>';
	}
	else{
		echo "<div class='notice warning'>Sie haben keinen Zugriff.</div>";
	}
}
else{
	echo "<div class='notice warning'>Sie müssen eingeloggt sein</div>";
}