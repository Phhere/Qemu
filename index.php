<?php 
include 'config.php';

include 'classes/Helper.class.php';
Helper::loadClasses();

$GLOBALS['site'] = "start";
if(isset($_GET['site'])){
	if(file_exists('./module/'.basename($_GET['site']).'.php')){
		$GLOBALS['site'] = basename($_GET['site']);
	}
}
?>
<!DOCTYPE html>
<html><head>
<title>HHU-FSCS Virtuelle Maschienen</title>
<meta charset="UTF-8">
<meta name="description" content="" />
<script type="text/javascript" src="https://ajax.googleapis.com/ajax/libs/jquery/1.6.4/jquery.min.js"></script>
<!--[if lt IE 9]><script src="http://html5shiv.googlecode.com/svn/trunk/html5.js"></script><![endif]-->
<script type="text/javascript" src="js/prettify.js"></script>                                   <!-- PRETTIFY -->
<script type="text/javascript" src="js/kickstart.js"></script>                                  <!-- KICKSTART -->
<link rel="stylesheet" type="text/css" href="css/kickstart.css" media="all" />                  <!-- KICKSTART -->
<link rel="stylesheet" type="text/css" href="style.css" media="all" />                          <!-- CUSTOM STYLES -->
</head>
<body><a id="top-of-page"></a>
<div id="wrap" class="clearfix">
<!-- ===================================== END HEADER ===================================== -->
	 <!-- Menu Horizontal -->
	<ul class="menu">
	<li class="<?php echo (in_array($GLOBALS['site'],array("","start"))) ? 'current' : '';?>"><a href="index.php">Startseite</a></li>
	<li class="<?php echo (in_array($GLOBALS['site'],array("myvm"))) ? 'current' : '';?>"><a href="index.php?site=myvm">Meine VMs</a></li>
	<li class="<?php echo (in_array($GLOBALS['site'],array("images","vms","users","system"))) ? 'current' : '';?>"><a href=""><span class="icon">R</span>Verwaltung</a>
		<ul>
		<li class="<?php echo (in_array($GLOBALS['site'],array("vms"))) ? 'current' : '';?>"><a href="index.php?site=vms"><span class="icon">4</span>Vms</a></li>
		<li class="<?php echo (in_array($GLOBALS['site'],array("images"))) ? 'current' : '';?>"><a href="index.php?site=images"><span class="icon">D</span>Images</a></li>
		<li class="divider <?php echo (in_array($GLOBALS['site'],array("users"))) ? 'current' : '';?>"><a href="index.php?site=users"><span class="icon">u</span>Benutzer</a></li>
		<li class="divider <?php echo (in_array($GLOBALS['site'],array("system"))) ? 'current' : '';?>"><a href="index.php?site=system"><span class="icon">S</span>Einstellungen</a></li>
		</ul>
	</li>
	<li class="<?php echo (in_array($GLOBALS['site'],array("downloads"))) ? 'current' : '';?>"><a href="index.php?site=downloads">Downloads</a></li>
	</ul>
	 
<div class="col_12">
	<div class="col_9">
	<?php 
	include './module/'.$GLOBALS['site'].'.php';
	?>
	</div>
	
	<div class="col_3">
	<?php include('boxes/login.php');?>
	</div>
	
	<hr />
	
	<div class="col_4">
	<h4>Status</h4>
	<p><?php include('boxes/status.php');	?></p>
	</div>
	
	<div class="col_4">
	<h4>Neuigkeiten</h4>
	<p><?php include('boxes/news.php');	?></p>
	</div>
	
	<div class="col_4">
	<h4>Links</h4>
	<ul>
		<li><a title="Homepage der Fachschaft Mathematik" href="http://www.fsmathe.de">FS Mathe</a></li>
		<li><a href="http://www.fsphy.uni-duesseldorf.de/">FS Physik</a></li>
		<li><a href="http://www.cs.uni-duesseldorf.de/">Institut für Informatik</a></li>
		<li><a href="http://www.studentenwerk-duesseldorf.de/Essen/Speiseplaene.html">Speisepläne</a></li>
		<li><a href="https://lsf.uni-duesseldorf.de/">Vorlesungsverzeichnis (LSF)</a></li>
	</ul>
	</div>
</div>

<!-- ===================================== START FOOTER ===================================== -->
<div class="clear"></div>
<div id="footer">
&copy; Copyright 2011–2012 All Rights Reserved. This website was built with <a href="http://www.99lime.com">HTML KickStart</a>
<a id="link-top" href="#top-of-page">Top</a>
</div>

</div><!-- END WRAP -->
</body></html>