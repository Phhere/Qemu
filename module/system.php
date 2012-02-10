<h3>Systemverwaltung</h3>


<?php
if(isset($_SESSION['user'])){
	if($_SESSION['user']->role['system'] == 1){
			
		echo '<div class="col_5"><h4>VM Status</h4>
		VMs:<br/>
		VMs online:<br/>
		Images:
		</div>';
		echo '<div class="col_2"></div>';
		echo '<div class="col_5"><h4>Server Status</h4>
		CPU: <br/>
		Ram:<br/>
		HDD: '.formatFileSize(disk_free_space ('/')).' / '.formatFileSize(disk_total_space ('/')).'<br/></div>';

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