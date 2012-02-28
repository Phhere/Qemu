<?php
	class FileSystem {
		/**
		 * Holds an array with all units
		 * @var array
		 */
		private static $units = array('Byte', 'kB', 'MB', 'GB', 'TB');
		
		/**
		 * Format File size
		 * @return string
		 * @param integer $bytes
		 * @param integer $round[optional]
		 */
		public static function formatFileSize($bytes, $round = 2){
			if($bytes > 0){
				$pow = floor(log($bytes)/log(1024));
				$pow = min($pow, count(self::$units)-1);
				$bytes /= pow(1024,$pow);
			}
			else{
				$pow = 0;
			}
			return round($bytes,$round)." ".self::$units[$pow];
		}
		
		/**
		 * Remove a folder with files recursive
		 * @return boolean
		 * @param string $path Path to the folder
		 */
		public static function removeDirRecursive($path){
			if(trim($path) == "") return false;
			if(is_dir($path) && !is_link($path)){
				$path = self::Folder($path);
				$dir = dir($path);
				while(false !== ($entry = $dir->read())){
					if($entry == "." 
					|| $entry == ".." ){
						continue;
					}
					if(!self::removeDirRecursive($path.$entry)){
						return false;
					}
				}
				$dir->close();
				return @rmdir($path);
			}
			return @unlink($path);
		}
		
		/**
		 * Fix trailing slashes in paths
		 * @return string
		 * @param string $path
		 */
		public static function Folder($path){
			$end = substr($path,-1,1);
			if($end != "/" && $end != "\\"){
				$path .= "/";
			}
			return $path;
		}
		
		/**
		 * Get the size of a folder recursive
		 * @return integer / boolean / String
		 * @param string $path Path to the folder
		 * @param boolean $format Get the result formated
		 */
		public static function getDirectorySize($path, $format = false){
			if(trim($path) == "") return false;
			$size = 0;
			$path = self::Folder($path);
			if(is_dir($path)){
				$dir = dir($path);
				while(false !== ($entry = $dir->read())){
						if($entry == "." 
						|| $entry == ".." ){
							continue;
						}
						if(is_dir($path.$entry)){
							$size += self::getDirectorySize($path.$entry, false);
						}
						else{
							$size += filesize($path.$entry);
						}
				}
			}
			else{
				$size = -1;
			}
			if($format == true){
				return self::formatFileSize($size);
			}
			else{
				return $size;
			}
		}
		
		/**
		 * Get all subfolders and files inside a folder
		 * @param string $path Path to the folder
		 * @param array $exclude [optional] Folder/files to be excluded
		 * @return array
		 */
		public static function getDirectory($path,$exclude=array()){
		  if(trim($path) == "")
		    return false;
		  $path = self::Folder($path);
		  $data = array();
		  if(is_dir($path)){
		    $dir = dir($path);
		    while(false !== ($entry=$dir->read())){
		      if($entry == "."||$entry == ".."){
		        continue;
		      }
		      if(!in_array($entry,$exclude)){
		        if(is_dir($path.$entry)){
		          $data[] = $path.$entry;
		          $data = array_merge($data,self::getDirectory($path.$entry,$exclude));
		        }
		        else{
		          $data[] = $path.$entry;
		        }
		      }
		    }
		  }
		  return $data;
		}
		
		/**
		* Get the file extension
		* @return string
		* @param string $string
		*/
		static function getExtension($string){
			return substr($string, strrpos($string,".") + 1);
		}
	}
?>