<?php
class VNC extends Modul{

	public function getHeader(){
		return "<h1>VNC</h1>";
	}
	
	public function action_default(){
		$content = '<h3>VNC</h3>';
		if(isset($_SESSION['user'])){
			$vmID = $_GET['vmID'];
			try {
				$vm = new QemuVm($vmID);
			}
			catch(Exception $e){
				return $content."<div class='notice error'>Unbekannte VM</div>";
			}
			if($vm->isOwner()){
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
					$content .= "<div class='notice warning'>Die VM scheint aus zu sein</div>";
				}
			}
			else{
				$content .= "<div class='notice error'>Du hast keinen Zugriff auf diese VM</div>";
			}
		}
		else {
			$content .= "<div class='notice warning'>Du musst eingeloggt sein</div>";
		}
		return $content;
	}
}
Routing::getInstance()->addRouteByAction(new VNC(),'vnc','default');
?>