<?php
class User {
	public $loggedin = false;
	
	public function __construct($id){
		$query = $GLOBALS['pdo']->prepare("SELECT * FROM users WHERE userID= :userID");
		$query->bindValue(':userID', $id, PDO::PARAM_INT);
		$query->execute();
		if($query->rowCount()){
			$this->id = $id;
			$this->loggedin = true;
			$data = $query->fetch();
			$this->email = $data['email'];
			$query2 = $GLOBALS['pdo']->prepare("SELECT * FROM roles WHERE roleID= :roleID");
			$query2->bindValue(':roleID', $data['role'], PDO::PARAM_INT);
			$query2->execute();
			if($query2->rowCount()){
				$this->role = $query2->fetch();
			}
			else{
				$this->role = array();
			}
		}
		else{
			throw new Exception("Unkown user");
		}	
	}
	
	/**
	 * Gibt den Namen zu einer UserID zurück
	 * @param int $userID
	 */
	static function getUserName($userID){
		$query = $GLOBALS['pdo']->prepare("SELECT email FROM users WHERE userID= :userID");
		$query->bindValue(':userID', $userID, PDO::PARAM_INT);
		$query->execute();
	
		$data = $query->fetch();
		return $data['email'];
	}
}