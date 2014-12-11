<?php
	Ini_Set( 'display_errors', true );
	include("functions.php");

	// This is separate from the now_playing div because the now_playing div
	// is a special scrollable div and we don't want the title scrolling with it.
	// You will only notice the scrolling feature when there are multiple
	// shows being watched at the same time.
	
	global $plex_server_ip;
	global $plex_port;
	global $plexToken;
	
	$plexSessionXML = simplexml_load_file('http://'.$plex_server_ip.':'.$plex_port.'/status/sessions/all?X-Plex-Token='.$plexToken);

	if (count($plexSessionXML->Video) == 0):
		$title = 'Recently Added';
		makeRecenlyReleased()
	else:
		$title = 'Now Playing';
	endif;

	echo '<h1 class="exoextralight">'.$title.'</h1>';
	echo '<hr>';
?>
