<h3>VNC</h3>
<?php
if(isset($_SESSION['user'])){
	$vmID = $_GET['vmID'];
	if(isOwner($_GET['vmID'])){
		$vm = new QemuVm($_GET['vmID']);
		if($vm->status == QemuMonitor::RUNNING){
			/*?>
			<!--[if !IE]>-->
<object
        type="application/x-java-applet"
        width="640" height="480"
>
<!--<![endif]-->
<!--[if IE]>
<object
        classid="clsid:8AD9C840-044E-11D1-B3E9-00805F499D93"
        codebase="http://java.sun.com/update/1.6.0/jinstall-6u22-windows-i586.cab"
        type="application/x-java-applet"
        width="640" height="480"
>
<!--><!-- <![endif]-->
        <param name="archive" value="tightvnc-jviewer.jar" />
        <param name="code" value="com.glavsoft.viewer.Viewer" />

        <param name="port" value="5901" />
        <param name="OpenNewWindow" value="false" />
        <param name="ShowControls" value="yes" />
</object>

			<?php */
		}
		else{
			echo "<div class='notice warning'>Die VM scheint aus zu sein</div>";
		}
	}
	else{
		echo "<div class='notice error'>Du hast keinen Zugriff auf diese VM</div>";
	}
}
else {
	echo "<div class='notice warning'>Du musst eingeloggt sein</div>";
}