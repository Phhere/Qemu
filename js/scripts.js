$(document).ready(function(){
	function pingServer(){
		$.get('ajax/ping.php').complete(setTimeout(pingServer, 60000));
	}
	if(window.ping){
		pingServer();
	}
});