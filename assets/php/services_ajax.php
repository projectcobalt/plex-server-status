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
$sabnzbdXML = simplexml_load_file('http://sab.mike-d82.com/api?mode=f6102a3e1c4e40177d9e6b0e5b8d8f8d='.$sabnzbd_api);

if (($sabnzbdXML->state) == 'Downloading'):
	$timeleft = $sabnzbdXML->timeleft;
	$sabTitle = 'SABnzbd ('.$timeleft.')';
else:
	$sabTitle = 'SABnzbd';
endif;

$services = array(
	new service("Plex", 32400, "http://mike-d82.com:32400/web/index.html#!/dashboard"),
	new service("pfSense", 80, "https://mike-d82.com", "mike-d82.com"),
	new serviceSAB($sabTitle, 80, "http://sab.mike-d82.com"),
	new service("NZBDrone", 80, "http://NZB.mike-d82.com"),
	new service("CouchPotato", 80, "http://cp.mike-d82.com"),
	#new service("Transmission", 9091, "http://d4rk.co:9091"),
	new service("Subsonic",80, "http://mad.mike-d82.com")
	
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
