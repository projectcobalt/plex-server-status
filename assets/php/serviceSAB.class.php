<?php
class serviceSAB
{
	public $name;
	public $port;
	public $url;
	public $host;
	public $status;
	
	function __construct($name, $port, $url = "", $host = "")
	{
		$this->name = $name;
		$this->port = $port;
		$this->url = $url;
		$this->host = $host;
		
		$this->status = $this->check_port();

	}
	
	function check_port()
	{
		$conn = @fsockopen($url, $this->port, $errno, $errstr, 10);
		//$conn = @fsockopen("sab.mike-d82.com", $this->port, $errno, $errstr, 10);
		if ($conn) 
		{
			fclose($conn);
			return true;
		}
		else
			return false;
	}
	
	function makeButton()
	{
		global $sabnzbd_api;
		global $sab_server_ip;
		$sabnzbdXML = simplexml_load_file('http://'.$sab_server_ip.'/api?mode=qstatus&output=xml&apikey='.$sabnzbd_api);

		if (($sabnzbdXML->state) == 'Downloading'):
			$icon = '<i class="icon-' . ($this->status ? 'download-alt' : 'remove') . ' icon-white"></i>';
		else:
			$icon = '<i class="icon-' . ($this->status ? 'ok' : 'remove') . ' icon-white"></i>';
		endif;
		$btn = $this->status ? 'success' : 'warning';
		$prefix = $this->url == "" ? '<button style="width:62px" class="btn btn-xs btn-' . $btn . ' disabled">' : '<a href="' . $this->url . '" style="width:62px" class="btn btn-xs btn-' . $btn . '">';
		$txt = $this->status ? 'Online' : 'Offline';
		$suffix = $this->url == "" ? '</button>' : '</a>';
		
		return $prefix . $icon . " " . $txt . $suffix;
	}
}
?>
