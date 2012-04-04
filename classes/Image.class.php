<?php
class Image {
	static function getImagePath($imageID){
		
		$query = $GLOBALS['pdo']->prepare("SELECT path FROM images WHERE imageID= :imageID");
		$query->bindValue(':imageID', $imageID, PDO::PARAM_INT);
		$query->execute();
		
		$data = $query->fetch();
		
		return $data['path'];
	}
	static function getImageType($imageID){
		
		$query = $GLOBALS['pdo']->prepare("SELECT type FROM images WHERE imageID= :imageID");
		$query->bindValue(':imageID', $imageID, PDO::PARAM_INT);
		$query->execute();
		
		$data = $query->fetch();
		
		return $data['type'];
	}
	static function getStatus($imageID){
		$path = self::getImagePath($imageID);
		$cmd = $GLOBALS['config']['qemu_img_executable']." info ".$path;
		exec($cmd,$output);
		$return = array();
		foreach($output as $line){
			list($key,$value) = explode(": ",$line,2);
			switch($key){
				case 'file format':
					$return['type'] = $value;
					break;
				case 'virtual size':
					preg_match("/([0-9]*?) bytes/s",$value,$match);
					$return['virtual_size'] = $match[1];
					break;
				case 'disk size':
					$return['real_size'] = $value;
					break;
			}
		}
		return $return;
	}
	
	static function isUsed($imageID){
		$query = $GLOBALS['pdo']->prepare("SELECT v.status FROM vm_images i JOIN vm v ON v.vmID = i.vmID WHERE i.imageID= :imageID AND v.status = 1");
		$query->bindValue(':imageID', $imageID, PDO::PARAM_INT);
		$query->execute();
		
		return !($query->rowCount() == 0);
	}
}