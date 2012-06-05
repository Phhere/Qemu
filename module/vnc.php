<?php
class VNC extends Modul{

	public function getHeader(){
		return "<h1>VNC</h1>";
	}
	
	public function action_default(){
		$GLOBALS['just_content'] = true;
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
					$content = ' <!--[if !IE]>-->
					<applet archive="tightvnc-jviewer.jar"
			code="com.glavsoft.viewer.Viewer"
			width="820" height="640">
					<param name="archive" value="tightvnc-jviewer.jar" />
					<param code="com.glavsoft.viewer.Viewer" />

					<param name="Host" value="10.11.12.1" />
					<param name="Port" value="'.$vm->vnc_port.'" />
					<param name="OpenNewWindow" value="no" />
					<param name="Password" value="'.$vm->password.'"> 
					<param name="ShowControls" value="yes" />
					<param name="AllowClipboardTransfer" value="yes" />
					<param name="JpegImageQuality" value="Lossless" />
					</applet>';
				}
				else{
					$content = "<div class='notice warning'>Die VM scheint aus zu sein</div>";
				}
			}
			else{
				$content = "<div class='notice error'>Du hast keinen Zugriff auf diese VM</div>";
			}
		}
		else {
			$content = "<div class='notice warning'>Du musst eingeloggt sein</div>";
		}
		return $content;
	}
}
Routing::getInstance()->addRouteByAction(new VNC(),'vnc','default');
?>