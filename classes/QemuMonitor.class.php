<?php
class QemuMonitor{
	const RUNNING = 1;
	const STOPPED = 2;
	const SHUTDOWN = 0;
	
	private $host;
	private $port;
	private $socket;
	private $sleeptime = 5000;
	public function __construct($host, $port){
		$this->host = $host;
		$this->port = $port;
		$this->socket = @fsockopen($host,$port);
		if(!$this->socket){
			throw new Exception("Can't connect to VM");
		}
		stream_set_blocking($this->socket,0);
	}
	/**
	 * Sends an command to VM without waiting for response
	 * @param string $command
	 */
	function execute($command){
		fwrite($this->socket,$command."\n");
		usleep($this->sleeptime);
	}

	/**
	 * Read the output
	 * @return string
	 */
	function getResponse(){
		$r='';
		do {
			$r.=fread($this->socket,1000);
			usleep($this->sleeptime);
			$s=socket_get_status($this->socket);
		} while ($s['unread_bytes']);
		usleep($this->sleeptime);
		return $r;
	}

	/**
	 * Destructor
	 * Close connection to vm
	 */
	function __destruct(){
		if(is_resource($this->socket))
		fclose($this->socket);
	}
}