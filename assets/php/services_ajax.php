<!DOCTYPE html>
<?php
	Ini_Set( 'display_errors', true );
	include("functions.php");
	include("service.class.php");
	include("serviceSAB.class.php");
?>
<html lang="en">
	<script>
	// Enable bootstrap tooltips
	$(function ()
	        { $("[rel=tooltip]").tooltip();
	        });
	</script>
<?php 
$sabnzbdXML = simplexml_load_file('http://'.$sab_server_ip.'/api?mode=qstatus&output=xml&apikey='.$sabnzbd_api);

if (($sabnzbdXML->state) == 'Downloading'):
	$timeleft = $sabnzbdXML->timeleft;
	$sabTitle = 'SABnzbd ('.$timeleft.')';
else:
	$sabTitle = 'SABnzbd';
endif;

$services = array(
	new service("Plex", 32400, 'http://'.$plex_server_ip.'/web/index.html#', $plex_server_ip),
	new service("pfSense", 80, 'https://'.$pf_server_ip, $pf_server_ip),
	new serviceSAB($sabTitle, 80, 'http://'.$sab_server_ip, $sab_server_ip),
	new service("NZBDrone", 80, 'http://'.$nzb_server_ip, $nzb_server_ip),
	new service("CouchPotato", 80, 'http://'.$cp_server_ip, $cp_server_ip),
	#new service("Transmission", 9091, "http://d4rk.co:9091"),
	new service("Madsonic",80, 'http://'.$mad_server_ip, $mad_server_ip)
	
);
?>
<table class ="center">
	<?php foreach($services as $service){ ?>
		<tr>
			<td style="text-align: right; padding-right:5px;" class="exoextralight"><?php echo $service->name; ?></td>
			<td style="text-align: left;"><?php echo $service->makeButton(); ?></td>
		</tr>
	<?php }?>
</table>
