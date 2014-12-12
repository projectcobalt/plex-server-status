<?php

	$config_path = "/var/www-credentials/config.ini"; //path to config file, recommend you place it outside of web root
	$plexTokenCache = "/var/www-credentials/plex_token.txt"; //path to config file, recommend you place it outside of web root
	
	Ini_Set( 'display_errors', false);
	include '../../init.php';
	include("lib/phpseclib0.3.5/Net/SSH2.php");
	$config = parse_ini_file($config_path, true);
	
	// Import variables from config file
    // Network Details
	$local_pfsense_ip = $config['network']['local_pfsense_ip'];
	$local_server_ip = $config['network']['local_server_ip'];
	$pfsense_if_name = $config['network']['pfsense_if_name'];
	$pfsense_port = $config['network']['pfsense_port'];
	$nas_port = $config['network']['nas_port'];
	$wan_domain = $config['network']['wan_domain'];
	$plex_server_ip = $config['network']['plex_server_ip'];
	$sab_server_ip = $config['network']['sab_server_ip'];
	$nzb_server_ip = $config['network']['nzb_server_ip'];
	$cp_server_ip = $config['network']['cp_server_ip'];
	$hp_server_ip = $config['network']['hp_server_ip'];
	$mad_server_ip = $config['network']['mad_server_ip'];
	$nas_server_ip = $config['network']['nas_server_ip'];
	$pf_server_ip = $config['network']['pf_server_ip'];
	$network = $config['network']['network'];
	// Credentials
	$ssh_username = $config['credentials']['ssh_username'];
	$ssh_password = $config['credentials']['ssh_password'];
	$plex_username = $config['credentials']['plex_username'];
	$plex_password = $config['credentials']['plex_password'];
	$trakt_username = $config['credentials']['trakt_username'];
	// API Keys
	$forecast_api = $config['api_keys']['forecast_api'];
	$sabnzbd_api = $config['api_keys']['sabnzbd_api'];
	$weather_lat = $config['misc']['weather_lat'];
	$weather_long = $config['misc']['weather_long'];
	$zpools = array($config['zpools']['zpool1'],$config['zpools']['zpool2']);
	$filesystems = $config['filesystems'];
	$plexSession = $config['misc']['plexSession'];
	$plexMovies = $config['misc']['plexMovies'];
	$plexNew = $config['misc']['plexNew'];

	// Set the path for the Plex Token
	//Check to see if the plex token exists and is younger than one week
	//if not grab it and write it to our caches folder
	if (file_exists($plexTokenCache) && (filemtime($plexTokenCache) > (time() - 60 * 60 * 24 * 7))) {
		$plexToken = file_get_contents("/var/www-credentials/plex_token.txt");
	} 
	else {
		$plexToken = file_get_contents("/var/www-credentials/plex_token.txt");
	}
	

if (strpos(strtolower(PHP_OS), "Darwin") === false)
	$loads = sys_getloadavg();
else
	$loads = Array(0.55,0.7,1);

function getCpuUsage()
{
	$top = shell_exec('top -n 0');
	$findme = 'idle';
	$cpuIdleStart = strpos($top, $findme);
	$cpuIdle = substr($top, ($cpuIdleStart - 7), 2);
	$cpuUsage = 100 - $cpuIdle;
	return $cpuUsage;
}

function makeCpuBars()
{
	printBar(getCpuUsage(), "Usage");
}	


function byteFormat($bytes, $unit = "", $decimals = 2) {
	$units = array('B' => 0, 'KB' => 1, 'MB' => 2, 'GB' => 3, 'TB' => 4, 
			'PB' => 5, 'EB' => 6, 'ZB' => 7, 'YB' => 8);
 
	$value = 0;
	if ($bytes > 0) {
		// Generate automatic prefix by bytes 
		// If wrong prefix given
		if (!array_key_exists($unit, $units)) {
			$pow = floor(log($bytes)/log(1024));
			$unit = array_search($pow, $units);
		}
 
		// Calculate byte value by prefix
		$value = ($bytes/pow(1024,floor($units[$unit])));
	}
 
	// If decimals is not numeric or decimals is less than 0 
	// then set default value
	if (!is_numeric($decimals) || $decimals < 0) {
		$decimals = 2;
	}
 
	// Format output
	return sprintf('%.' . $decimals . 'f '.$unit, $value);
  }

function makeDiskBars()
{
	global $filesystems;
	foreach ($filesystems as $fs_index => $fs_info){
		$fs = explode(",",$fs_info);
	
	printDiskBarGB(getDiskspace($fs[0]), $fs[1], getDiskspaceUsed($fs[0]), disk_total_space($fs[0]));
}
}

function makeRamBars()
{
	printRamBar(getFreeRam()[0],getFreeRam()[1],getFreeRam()[2],getFreeRam()[3]);
}

function makeLoadBars()
{
	printBar(getLoad(0), "1 min");
	printBar(getLoad(1), "5 min");
	printBar(getLoad(2), "15 min");
}

function getFreeRam()
{
	$top = shell_exec('free -m');
	$output = preg_split('/[\s]/', $top);
		for ($i=count($output)-1; $i>=0; $i--) {
		if ($output[$i] == '') unset ($output[$i]);
		}
	$output = array_values($output);
	$totalRam = $output[7]/1000; // GB
	$freeRam = $output[16]/1000; // GB
	$usedRam = $totalRam - $freeRam;
	return array (sprintf('%.0f',($usedRam / $totalRam) * 100), 'Used Ram', $usedRam, $totalRam);
}

function getDiskspace($dir)
{
	$df = disk_free_space($dir);
	$dt = disk_total_space($dir);
	$du = $dt - $df;
	return sprintf('%.0f',($du / $dt) * 100);
}

function getDiskspaceUsed($dir)
{
	$df = disk_free_space($dir);
	$dt = disk_total_space($dir);
	$du = $dt - $df;
	return $du;
}




function getLoad($id)
{
	return 100 * ($GLOBALS['loads'][$id] / 8);
}

function printBar($value, $name = "")
{
	if ($name != "") echo '<!-- ' . $name . ' -->';
	echo '<div class="exolight">';
		if ($name != "")
			echo $name . ": ";
			echo number_format($value, 0) . "%";
		echo '<div class="progress">';
			echo '<div class="progress-bar" style="width: ' . $value . '%"></div>';
		echo '</div>';
	echo '</div>';
}

function printRamBar($percent, $name = "", $used, $total)
{
	if ($percent < 90)
	{
		$progress = "progress-bar";
	}
	else if (($percent >= 90) && ($percent < 95))
	{
		$progress = "progress-bar progress-bar-warning";
	}
	else
	{
		$progress = "progress-bar progress-bar-danger";
	}

	if ($name != "") echo '<!-- ' . $name . ' -->';
	echo '<div class="exolight">';
		if ($name != "")
			echo $name . ": ";
			echo number_format($percent, 0) . "%";
		echo '<div rel="tooltip" data-toggle="tooltip" data-placement="bottom" title="' . number_format($used, 2) . ' GB / ' . number_format($total, 0) . ' GB" class="progress">';
			echo '<div class="'. $progress .'" style="width: ' . $percent . '%"></div>';
		echo '</div>';
	echo '</div>';
}


function printDiskBarGB($dup, $name = "", $dsu, $dts)
{
	if ($dup < 90)
	{
		$progress = "progress-bar";
	}
	else if (($dup >= 90) && ($dup < 95))
	{
		$progress = "progress-bar progress-bar-warning";
	}
	else
	{
		$progress = "progress-bar progress-bar-danger";
	}

	if ($name != "") echo '<!-- ' . $name . ' -->';
	echo '<div class="exolight">';
		if ($name != "")
			echo $name . ": ";
			echo number_format($dup, 0) . "%";
		echo '<div rel="tooltip" data-toggle="tooltip" data-placement="bottom" title="' . byteFormat($dsu, "GB", 0) . ' / ' . byteFormat($dts, "GB", 0) . '" class="progress">';
			echo '<div class="'. $progress .'" style="width: ' . $dup . '%"></div>';
		echo '</div>';
	echo '</div>';
}

function ping()
{
	$pingIP = '8.8.8.8';
	$terminal = shell_exec('ping -c 5 '.$pingIP);
	$findme = 'dev =';
	$start = strpos($terminal, $findme);
	$avgPing = substr($terminal, ($start +13), 2);
	return $avgPing;
}

#function getNetwork() //returns wan_domain if you are outside your network, and local_server_ip if you are within the network
#{
#	global $local_server_ip;
#	global $local_pfsense_ip;
#	global $wan_domain;
#	$clientIP = get_client_ip();
#	if(preg_match("/192.168.1.*/",$clientIP))
#		$network='http://'.$local_server_ip;
#	else
#		$network=$wan_domain;
#	return $network;
#}

#function get_client_ip() 
#{
#	if ( isset($_SERVER["REMOTE_ADDR"])) { 
#		$ipaddress = $_SERVER["REMOTE_ADDR"];
#	}else if (isset($_SERVER["HTTP_X_FORWARDED_FOR"])) {
#		$ipaddress = $_SERVER["HTTP_X_FORWARDED_FOR"];
#	}else if (isset($_SERVER["HTTP_CLIENT_IP"])) {
#		$ipaddress = $_SERVER["HTTP_CLIENT_IP"];
#	} 
#	return $ipaddress;
#}




function getPlexToken()
{
    global $plex_username;
	global $plex_password;
	$myPlex = shell_exec('curl -H "Content-Length: 0" -H "X-Plex-Client-Identifier: my-app" -u "'.$plex_username.'"":""'.$plex_password.'" -X POST https://my.plexapp.com/users/sign_in.xml 2> /dev/null');
        $myPlex_xml = simplexml_load_string($myPlex);
        $token = $myPlex_xml['authenticationToken'];
	return $token;
}

function getDir($b)
{
   $dirs = array('N', 'NE', 'E', 'SE', 'S', 'SW', 'W', 'NW', 'N');
   return $dirs[round($b/45)];
}

function makeWeatherSidebar()
{
    global $weather_lat;
	global $weather_long;
	global $forecast_api;
	$forecastExcludes = '?exclude=daily,flags&units=si';
	// Kennington, London
	$forecastLat = $weather_lat;
	$forecastLong = $weather_long;
	$currentForecast = json_decode(file_get_contents('https://api.forecast.io/forecast/'.$forecast_api.'/'.$forecastLat.','.$forecastLong.$forecastExcludes));

	$currentSummary = $currentForecast->currently->summary;
	$currentSummaryIcon = $currentForecast->currently->icon;
	$currentTemp = round($currentForecast->currently->temperature);
	$currentWindSpeed = round($currentForecast->currently->windSpeed);
	if ($currentWindSpeed > 0) {
		$currentWindBearing = $currentForecast->currently->windBearing;
	}
	$minutelySummary = $currentForecast->minutely->summary;
	$hourlySummary = $currentForecast->hourly->summary;
	// If there are alerts, make the alerts variables
	if (isset($currentForecast->alerts)) {
		$alertTitle = $currentForecast->alerts[0]->title;
		$alertExpires = $currentForecast->alerts[0]->expires;
		$alertDescription = $currentForecast->alerts[0]->description;
		$alertUri = $currentForecast->alerts[0]->uri;
	}
	// Make the array for weather icons
	$weatherIcons = [
		'clear-day' => 'B',
		'clear-night' => 'C',
		'rain' => 'R',
		'snow' => 'W',
		'sleet' => 'X',
		'wind' => 'F',
		'fog' => 'L',
		'cloudy' => 'N',
		'partly-cloudy-day' => 'H',
		'partly-cloudy-night' => 'I',
	];
	$weatherIcon = $weatherIcons[$currentSummaryIcon];
	// If there is a severe weather warning, display it
	//if (isset($currentForecast->alerts)) {
	//	echo '<div class="alert alert-warning alert-dismissable">';
	//	echo '<button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>';
	//	echo '<strong><a href="'.$alertUri.'" class="alert-link">'.$alertTitle.'</a></strong>';
	//	echo '</div>';
	//}
	echo '<ul class="list-inline" style="margin-bottom:-20px">';
	echo '<li><h1 data-icon="'.$weatherIcon.'" style="font-size:500%;margin:0px -10px 20px -5px"></h1></li>';
	echo '<li><ul class="list-unstyled">';
	echo '<li><h1 class="exoregular" style="margin:0px">'.$currentTemp.'°</h1></li>';
	echo '<li><h4 class="exoregular" style="margin:0px;padding-right:10px;width:80px">'.$currentSummary.'</h4></li>';
	echo '</ul></li>';
	echo '</ul>';
	//if ($currentWindSpeed > 0) {
	//	$direction = getDir($currentWindBearing);
	//	echo '<h4 class="exoextralight" style="margin-top:0px">Wind: '.$currentWindSpeed.' mph ('.$direction.')</h4>';
	//}
	echo '<h4 class="exoregular">Next Hour</h4>';
	echo '<h5 class="exoextralight" style="margin-top:10px">'.$minutelySummary.'</h5>';
	echo '<h4 class="exoregular">Next 24 Hours</h4>';
	echo '<h5 class="exoextralight" style="margin-top:10px">'.$hourlySummary.'</h5>';
	echo '<p class="text-right no-link-color"><small><a href="http://forecast.io/#/f/',$forecastLat,',',$forecastLong,'">Forecast.io</a></small></p>';
}

?>
