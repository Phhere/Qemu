<?php
class User {
	public $loggedin = false;
	
	public function __construct($id){
		$get = mysql_query("SELECT * FROM users WHERE userID='".$id."'");
		if(mysql_num_rows($get)){
			$this->id = $id;
			$this->loggedin = true;
			$data = mysql_fetch_assoc($get);
			$this->email = $data['email'];
			$get = mysql_query("SELECT * FROM roles WHERE roleID='".$data['role']."'");
			if(mysql_num_rows($get)){
				$this->role = mysql_fetch_assoc($get);
			}
		}
		else{
			throw new Exception("Unkown user");
		}	
	}
	
	
	
	static function createSSHUser($matrikel,$password){
		exec("bash scripts/addVncUser ".$matrikel." ".crypt($password));
	}
	
	static function pushSShKey($matrikel,$key){
		
		$permit = 'permitopen="127.0.0.1:5901",permitopen="127.0.0.1:5902"';		
		exec("bash scripts/addVncUserKey ".$matrikel." '".$key."' '".$permit."'");
	}
}