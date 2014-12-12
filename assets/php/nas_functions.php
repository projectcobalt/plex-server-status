//NAS functions

include("functions.php");
include '../../init.php';
include("lib/phpseclib0.3.5/Net/SSH2.php");

function zpoolHealth($name) //returns status of provided zpool
{
	global $nas_server_ip;
	global $nas_port;
	global $ssh_username;
	global $ssh_password;
	
	$NASssh = new Net_SSH2($nas_server_ip,$nas_port);
	if (!$NASssh->login($ssh_username,$ssh_password)) { // replace password and username with pfSense ssh username and password if you want to use this
		exit('Login Failed zpoolHealth');
	}
	$zpool = $NASssh->exec('/sbin/zpool status '.$name);
        $findme = 'state:';
        $stateStart = strpos($zpool, $findme);
        $health = (substr($zpool, $stateStart + 7, 8)); // GB
	return $health;
}	

function zfsFilesystems($zpool) //returns 2 dimensional array of all filesystems in provided zpool, with name, used space and available space
{
	global $nas_server_ip;
	global $nas_port;
	global $ssh_username;
	global $ssh_password;
	
	$NAS1ssh = new Net_SSH2($nas_server_ip,$nas_port);
	if (!$NAS1ssh->login($ssh_username,$ssh_password)) { // replace password and username with pfSense ssh username and password if you want to use this
		exit('Login Failed zfs Filesystems');
	}
	$output = $NAS1ssh->exec('/sbin/zfs get -r -o name,value -Hp used,avail '.$zpool);
        $zfs_fs_stats = preg_split('/[\n|\t]/',$output);
        $zfs_fs_stats_p = array_pop($zfs_fs_stats);
		$zfs_fs_array = array_chunk($zfs_fs_stats,4);
		return $zfs_fs_array;
}

function printZpools()
{
	global $zpools;
	foreach ($zpools as $index => $name) {
	$status = zpoolHealth($name);
	$fs = zfsFilesystems($name);
	$fs_avail = $fs[0][3];
	$fs_used = $fs[0][1];
	#foreach($fs as $fs_ind => $fss) {
	#	$fs_used += $fss[1];
	#	}
	$fs_total = $fs_used + $fs_avail;
	$fs_pct = number_format(($fs_used / $fs_total)*100);
	$online = $status == "ONLINE" ? 'True' : 'False';
	$zp = new zpool($name, $status, $online);
	echo '<table>';
		echo '<tr>';
			echo '<td style="text-align: right; padding-right:5px;" class="exoextralight">'.$zp->name.': '.number_format($fs_pct, 0) .'%</td>';
			echo '<td style="text-align: left;">'.$zp->makeButton().'</td>';
		echo '</tr>';
		echo '</table>';
			echo '<div id="zfs_'.$zp->name.'" class="collapse">';
				echo '<div rel="tooltip" data-toggle="tooltip" data-placement="bottom" title="' . byteFormat($fs_used, "GB", 0) . ' / ' . byteFormat($fs_total, "GB", 0) . '" class="progress">';
					echo '<div class="progress">';
  					echo '<div class="progress-bar" style="width: '.$fs_pct.'%"></div>';
  					echo '<span class="sr-only">'.$fs_pct.'% Complete</span>';
  					echo '</div>';
  				echo '</div>';
			foreach($fs as $fs_ind => $fss){
				$fss_n = $fss[0];
				$fss_u = $fss[1];
				$fss_p = number_format(($fss_u / $fs_total)*100);
				echo '<div rel="tooltip" data-toggle="tooltip" data-placement="bottom" title="'.$fss_n.': ' . byteFormat($fss_u, "GB", 0) . '" class="progress">';
					echo '<div class="progress">';
  					echo '<div class="progress-bar progress-bar-success" style="width: '.$fss_p.'%"></div>';
  					echo '<span class="sr-only">'.$fss_p.'% Complete</span>';
  					echo '</div>';
  				echo '</div>';
				}
			echo '</div>';
		
	}
}