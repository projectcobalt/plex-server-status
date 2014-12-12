
//pfSense functions

include("functions.php");
include '../../init.php';
include("lib/phpseclib0.3.5/Net/SSH2.php");


function getBandwidth()
{
    global $local_pfsense_ip;
	global $ssh_username;
	global $ssh_password;
	global $pfsense_if_name;
	global $pfsense_port;
	$ssh = new Net_SSH2($local_pfsense_ip,$pfsense_port);
	if (!$ssh->login($ssh_username,$ssh_password)) { // replace password and username with pfSense ssh username and password if you want to use this
		exit('Login Failed');
	}

	$dump = $ssh->exec('/usr/local/bin/vnstat -i '.$pfsense_if_name.' -tr');
	$output = preg_split('/[\.|\s]/', $dump);
	for ($i=count($output)-1; $i>=0; $i--) {
		if ($output[$i] == '') unset ($output[$i]);
	}
	$output = array_values($output);
	$rxRate = $output[54];
	$rxFormat = $output[56];
	$txRate = $output[61];
	$txFormat = $output[62];
	if ($rxFormat == 'kbit/s') {
		$rxRateMB = $rxRate / 1024;
	} else {
		$rxRateMB = $rxRate;
	}
	if ($txFormat == 'kbit/s') {
		$txRateMB = $txRate / 1024;
	} else {
		$txRateMB = $txRate;
	}
	$rxRateMB = floatval($rxRateMB);
	$txRateMB = floatval($txRateMB);

	return  array($rxRateMB, $txRateMB);
}

function printBandwidthBar($percent, $name = "", $Mbps)
{
	if ($name != "") echo '<!-- ' . $name . ' -->';
	echo '<div class="exolight">';
		if ($name != "")
			echo $name . ": ";
			echo number_format($Mbps,2) . " Mbps";
		echo '<div class="progress">';
			echo '<div class="progress-bar" style="width: ' . $percent . '%"></div>';
		echo '</div>';
	echo '</div>';
}


function makeBandwidthBars()
{
	$array = getBandwidth();
	$dPercent = sprintf('%.0f',($array[0] / 55) * 100);
	$uPercent = sprintf('%.0f',($array[1] / 5) * 100);
	printBandwidthBar($dPercent, 'Download', $array[0]);
	printBandwidthBar($uPercent, 'Upload', $array[1]);
}
