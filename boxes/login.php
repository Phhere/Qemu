<?php
if(isset($_POST['login'])){
	
	$get = mysql_query("SELECT * FROM users WHERE email='".$_POST['email']."' AND password='".md5($_POST['pass'])."'");
	if(mysql_num_rows($get)){
		$data = mysql_fetch_assoc($get);
		$_SESSION['user'] = new User($data['userID']);
		$_SESSION['login_hash'] = md5($data['userID'].$data['email'].$data['password']);
	}
}
if(isset($_SESSION['user'])){
	?>
	<h6>Profil</h6>
	<ul>
<li><a href="index.php">Startseite</a></li>
<li><a href="index.php?site=profil">Mein Profil</a></li>
<li style="visibility: hidden;"></li>
<li><a href="index.php?site=logout">Logout</a></li>
</ul>
	<?php 
}
else{
	?>
	<h6>Login</h6>
<form action="" method="post">
	<label for="email">E-Mail Addresse</label>
	<input type="text" id="email" name="email" />
	<label for="pass">Password</label>
	<input type="password" id="pass" name="pass" /> <input type="submit" name="login" value="einloggen" />
</form>
<?php
}