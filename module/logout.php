<?php
if(isset($_SESSION['user'])){
	unset($_SESSION['user']);
	echo "Sie sind nun ausgeloggt.";
}