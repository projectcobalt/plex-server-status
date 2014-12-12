//Plex functions

include("functions.php");
include '../../init.php';

function makeRecenlyPlayed()
{
	
	global $local_pfsense_ip;
	global $plex_server_ip;
	global $plexToken;
	global $trakt_username;
	global $weather_lat;
	global $weather_long;
	global $weather_name;
	global $network;
#	$clientIP = get_client_ip();
	$trakt_url = 'http://trakt.tv/user/mike-d/widgets/watched/all-tvthumb.jpg';
	$traktThumb = ROOT_DIR . '/assets/misc/all-tvthumb.jpg';
	$plexSessionXML = simplexml_load_file($plexSession);

	echo '<div class="col-md-12">';
	if (file_exists($traktThumb) && (filemtime($traktThumb) > (time() - 60 * 15))) {
		// Trakt image is less than 15 minutes old.
		// Don't refresh the image, just use the file as-is.
		echo '<img src="'.$network.'/assets/misc/all-tvthumb.jpg" alt="trakt.tv" class="img-responsive"></a>';
	} else {
		// Either file doesn't exist or our cache is out of date,
		// so check if the server has different data,
		// if it does, load the data from our remote server and also save it over our cache for next time.
		$thumbFromTrakt_md5 = md5_file($trakt_url);
		$traktThumb_md5 = md5_file($traktThumb);
		if ($thumbFromTrakt_md5 === $traktThumb_md5) {
			echo '<img src="'.$network.'/assets/misc/all-tvthumb.jpg" alt="trakt.tv" class="img-responsive"></a>';
		} else {
			$thumbFromTrakt = file_get_contents($trakt_url);
			file_put_contents($traktThumb, $thumbFromTrakt, LOCK_EX);
			echo '<img src="'.$network.'/assets/misc/all-tvthumb.jpg" alt="trakt.tv" class="img-responsive"></a>';

		}
	}
	if($clientIP == '127.0.0.1' && count($plexSessionXML->Video) == 0) {
		echo '<hr>';
		echo '<h1 class="exoextralight" style="margin-top:5px;">';
		echo 'Forecast</h1>';
		echo '<iframe id="forecast_embed" type="text/html" frameborder="0" height="245" width="100%" src="http://forecast.io/embed/#lat=40.7838&lon=-96.622773&name=Lincoln, NE"> </iframe>';
	}
	echo '</div>';
}

function makeRecenlyReleased()
    
{
	global $plex_server_ip;
	global $plexToken ;	// You can get your Plex token using the getPlexToken() function. This will be automated once I find out how often the token has to be updated.
	global $plexSession;
	global $plexNew;
	global $network;
	$plexNewestXML = simplexml_load_file($plexSession);
	
	//echo '<div class="col-md-12">';
	//echo '<div class="thumbnail">';
	//echo '<div id="carousel-example-generic" class=" carousel slide">';
	//echo '<!-- Indicators -->';
	//echo '<ol class="carousel-indicators">';
	//echo '<li data-target="#carousel-example-generic" data-slide-to="0" class="active"></li>';
	//echo '<li data-target="#carousel-example-generic" data-slide-to="1"></li>';
	//echo '<li data-target="#carousel-example-generic" data-slide-to="2"></li>';
	//echo '</ol>';
	echo '<!-- Wrapper for slides -->';
	echo '<div class="carousel-inner">';
	echo '<div class="item active">';
	$mediaKey = $plexNewestXML->Video[0]['key'];
	$mediaXML = simplexml_load_file($plexNew);
	$movieTitle = $mediaXML->Video['title'];
	$movieArt = $mediaXML->Video['thumb'];
	echo '<img src="plex.php?img='.urlencode('http://'.$plex_server_ip.$movieArt).'" alt="'.$movieTitle.'">';
	echo '</div>'; // Close item div
	$i=1;
	for ( ; ; ) {
		if($i==15) break;
		$mediaKey = $plexNewestXML->Video[$i]['key'];
		$mediaXML = simplexml_load_file('http://'.$plex_server_ip.$mediaKey.'/all?X-Plex-Token='.$plexToken);
		$movieTitle = $mediaXML->Video['title'];
		$movieArt = $mediaXML->Video['thumb'];
		$movieYear = $mediaXML->Video['year'];
		echo '<div class="item">';
		echo '<img src="plex.php?img='.urlencode('http://'.$plex_server_ip.$movieArt).'" alt="'.$movieTitle.'">';
		//echo '<img src="'.$network.$movieArt.'?X-Plex-Token='.$plexToken.'" alt="...">';
		//echo '<div class="carousel-caption">';
		//echo '<h3>'.$movieTitle.$movieYear.'</h3>';
		//echo '<p>Summary</p>';
		//echo '</div>';
		echo '</div>'; // Close item div
		$i++;
	}
	echo '</div>'; // Close carousel-inner div

	echo '<!-- Controls -->';
	echo '<a class="left carousel-control" href="#carousel-example-generic" data-slide="prev">';
	//echo '<span class="glyphicon glyphicon-chevron-left"></span>';
	echo '</a>';
	echo '<a class="right carousel-control" href="#carousel-example-generic" data-slide="next">';
	//echo '<span class="glyphicon glyphicon-chevron-right"></span>';
	echo '</a>';
	echo '</div>'; // Close carousel slide div
	echo '</div>'; // Close thumbnail div

	//if($clientIP == '10.0.1.1' && count($plexSessionXML->Video) == 0) {
	//	echo '<hr>';
	//	echo '<h1 class="exoextralight" style="margin-top:5px;">';
	//	echo 'Forecast</h1>';
	//	echo '<iframe id="forecast_embed" type="text/html" frameborder="0" height="245" width="100%" src="http://forecast.io/embed/#lat=40.7838&lon=-96.622773&name=Lincoln, NE"> </iframe>';
	//}
	echo '</div>'; // Close column div
}

function makeNowPlaying()
{
	global $plex_server_ip;
	global $plexToken;
	global $plexSession; 
	global $network;
	$plexSessionXML = simplexml_load_file($plexSession);
	if (!$plexSessionXML):
		makeRecenlyViewed();	
	elseif (count($plexSessionXML->Video) == 0):
		makeRecenlyReleased();
	else:
		$i = 0; // Initiate and assign a value to i & t
		$t = 0;
		echo '<div class="col-md-10 col-sm-offset-1">';
		foreach ($plexSessionXML->Video as $sessionInfo):
			$t++;
		endforeach;
		foreach ($plexSessionXML->Video as $sessionInfo):
			$mediaKey=$sessionInfo['key'];
			$playerTitle=$sessionInfo->Video['title'];
			$mediaXML = simplexml_load_file($plexSession);
			$type=$mediaXML->Video['type'];
			echo '<div class="thumbnail">';
			$i++; // Increment i every pass through the array
			if ($type == "movie"):
				// Build information for a movie

				$movieArt = $mediaXML->Video['thumb'];
				$movieTitle = $mediaXML->Video['title'];
				echo '<img src="plex.php?img='.urlencode('http://'.$plex_server_ip.$movieArt).'" alt="'.$movieTitle.'">';
				echo '<div class="caption">';
				$movieTitle = $mediaXML->Video['title'];
				//echo '<h2 class="exoextralight">'.$movieTitle.'</h2>';
				if (strlen($mediaXML->Video['summary']) < 800):
					$movieSummary = $mediaXML->Video['summary'];
				else:
					$movieSummary = substr_replace($mediaXML->Video['summary'], '...', 800);
				endif;

				echo '<p class="exolight" style="margin-top:5px;">'.$movieSummary.'</p>';
			else:
				// Build information for a tv show
				//-----------------------------------------
				file_put_contents('/tmp/tv.txt', 'tv');
				$tvArt = $mediaXML->Video['grandparentThumb'];
				echo '<img src="plex.php?img='.urlencode('http://'.$plex_server_ip.$tvArt).'" alt="'.$showTitle.'">';
				echo '<div class="caption">';
				$showTitle = $mediaXML->Video['grandparentTitle'];
				$episodeTitle = $mediaXML->Video['title'];
				$episodeSummary = $mediaXML->Video['summary'];
				$episodeSeason = $mediaXML->Video['parentIndex'];
				$episodeNumber = $mediaXML->Video['index'];
				//echo '<h2 class="exoextralight">'.$showTitle.'</h2>';
				echo '<h3 class="exoextralight" style="margin-top:5px;">Season '.$episodeSeason.'</h3>';
				echo '<h4 class="exoextralight" style="margin-top:5px;">E'.$episodeNumber.' - '.$episodeTitle.'</h4>';
				echo '<p class="exolight">'.$episodeSummary.'</p>';
			endif;
			// Action buttons if we ever want to do something
			//echo '<p><a href="#" class="btn btn-primary">Action</a> <a href="#" class="btn btn-default">Action</a></p>';
			echo "</div>";
			echo "</div>";
			// Should we make <hr>? Only if there is more than one video and it's not the last thumbnail created.
			if (($i > 0) && ($i < $t)):
				echo '<hr>';
			else:
				// Do nothing
			endif;
		endforeach;
		echo '</div>';
	endif;
}

function plexMovieStats()
{

	global $plex_server_ip;
	global $plexMovies;
	global $plexToken;	// You can get your Plex token using the getPlexToken() function. This will be automated once I find out how often the token has to be updated.
	global $network;
	
	$plexNewestXML = simplexml_load_file($plexMovies);
	
#	$clientIP = get_client_ip();
	$total_movies = count($plexNewestXML -> Video);
	$hd1080 = count($plexNewestXML->xpath("Video/Media[@videoResolution='1080']/parent::*"));
	$hd720 = count($plexNewestXML->xpath("Video/Media[@videoResolution='720']/parent::*"));
	$sd = ($total_movies - $hd1080 - $hd720);
	//$sd = count($plexNewestXML->xpath("Video/Media[@videoResolution='sd']/parent::*"));
	$hd1080_pc = number_format(($hd1080 / $total_movies)*100);
	$hd720_pc = number_format(($hd720 / $total_movies)*100);
	$sd_pc = number_format(($sd / $total_movies)*100);
	$bitrate_1080 = 0;
	foreach ($plexNewestXML->Video as $video) { //we assume that there is only one audio stream. Video bitrate alone does not seem to appear in the plex xml
		foreach ($video->Media as $media){
			if ($media['videoResolution'] == '1080'){
				$duration = ((string)$media['duration']/1000); //convert from milliseconds to seconds
				$size = ((string)$media->Part['size']/131072); //we need to convert from bytes into Megabits
				$audio_size = ((((string)$media['bitrate']*$duration))/131072);
				$bitrate_1080 += (($size - $audio_size) / ($duration));
			}
		}
	}
	$bitrate_720 = 0;
	foreach ($plexNewestXML->Video as $video) {
		foreach ($video->Media as $media){
			if ($media['videoResolution'] == '720'){
				$duration = ((string)$media['duration']/1000);
				$size = ((string)$media->Part['size']/131072);
				$audio_size = ((((string)$media['bitrate']*$duration))/131072);
				$bitrate_720 += (($size - $audio_size) / ($duration));
			}
		}
	}
	$bitrate_sd = 0;
	foreach ($plexNewestXML->Video as $video) {
		foreach ($video->Media as $media){
			if ($media['videoResolution'] != '720' and $media['videoResolution'] != '1080'){
				$duration = ((string)$media['duration']/1000);
				$size = ((string)$media->Part['size']/131072);
				$audio_size = ((((string)$media['bitrate']*$duration))/131072);
				$bitrate_sd += (($size - $audio_size) / ($duration));
			}
		}
	}
	$bitrate_1080_av = ($bitrate_1080 / $hd1080);
	$bitrate_720_av = ($bitrate_720 / $hd720);
	$bitrate_sd_av = ($bitrate_sd / $sd);
	

	echo '<div class="exolight">';
	echo $total_movies.' Movies';
		echo '<div class="progress">';
			echo '<div rel="tooltip" data-toggle="tooltip" data-placement="bottom" title="'.$hd1080_pc.'% 1080p / '.$hd720_pc.'% 720p / '.$sd_pc.'% SD" class="progress">';
  				echo '<div class="progress-bar progress-bar-success" style="width: '.$hd1080_pc.'%">';
    			echo '<span class="sr-only">'.$hd1080_pc.'% Complete (success)</span>';
  				echo '</div>';
  				echo '<div class="progress-bar progress-bar-warning" style="width: '.$hd720_pc.'%">';
    			echo '<span class="sr-only">'.$hd720_pc.'% Complete (warning)</span>';
  				echo '</div>';
  				echo '<div class="progress-bar progress-bar-danger" style="width: '.$sd_pc.'%">';
    			echo '<span class="sr-only">'.$sd_pc.'% Complete (danger)</span>';
  				echo '</div>';
  			echo '</div>';	
		echo '</div>';
	echo '<table>';
	echo '<tr>';
		echo '<th style="text-align: left; padding-right:5px;" class="exoextralight"></th>';
		echo '<th style="text-align: centre;">Average Bitrate</th>';
		echo '</tr>';
	echo '<tr>';
		echo '<td style="text-align: right; padding-right:5px; class="exoextralight">1080p</td>';
		echo '<td style="text-align: centre; class="exoextralight">'.number_format($bitrate_1080_av,2).' Mbps</td>';
	echo '</tr>';
	echo '<tr>';
		echo '<td style="text-align: right; padding-right:5px; class="exoextralight">720p</td>';
		echo '<td style="text-align: centre; class="exoextralight">'.number_format($bitrate_720_av,2).' Mbps</td>';
	echo '</tr>';
	echo '<tr>';
		echo '<td style="text-align: right; padding-right:5px; class="exoextralight">SD</td>';
		echo '<td style="text-align: centre; class="exoextralight">'.number_format($bitrate_sd_av,2).' Mbps</td>';
	echo '</tr>';
	echo '</table>';
	echo '</div>';
}

function makeRecenlyViewed()
{
	global $local_pfsense_ip;
	global $trakt_username;
	global $weather_lat;
	global $weather_long;
	global $weather_name;
	global $plexSession;
	global $network;
#	$clientIP = get_client_ip();
	$plexSessionXML = simplexml_load_file($plexSession);
	$trakt_url = 'http://trakt.tv/user/'.$trakt_username.'/widgets/watched/all-tvthumb.jpg';
	$traktThumb = ROOT_DIR . '/assets/misc/all-tvthumb.jpg';
	echo '<div class="col-md-12">';
	echo '<a href="http://trakt.tv/user/'.$trakt_username.'" class="thumbnail">';
	if (file_exists($traktThumb) && (filemtime($traktThumb) > (time() - 60 * 15))) {
		// Trakt image is less than 15 minutes old.
		// Don't refresh the image, just use the file as-is.
		echo '<img src="'.$network.'/assets/misc/all-tvthumb.jpg" alt="trakt.tv" class="img-responsive"></a>';
	} else {
		// Either file doesn't exist or our cache is out of date,
		// so check if the server has different data,
		// if it does, load the data from our remote server and also save it over our cache for next time.
		$thumbFromTrakt_md5 = md5_file($trakt_url);
		$traktThumb_md5 = md5_file($traktThumb);
		if ($thumbFromTrakt_md5 === $traktThumb_md5) {
			echo '<img src="'.$network.'/assets/misc/all-tvthumb.jpg" alt="trakt.tv" class="img-responsive"></a>';
		} else {
			$thumbFromTrakt = file_get_contents($trakt_url);
			file_put_contents($traktThumb, $thumbFromTrakt, LOCK_EX);
			echo '<img src="'.$network.'/assets/misc/all-tvthumb.jpg" alt="trakt.tv" class="img-responsive"></a>';
		}
	}
	// This checks to see if you are inside your local network. If you are it gives you the forecast as well.
	if($clientIP == $local_pfsense_ip && count($plexSessionXML->Video) == 0) {
		echo '<hr>';
		echo '<h1 class="exoextralight" style="margin-top:5px;">';
		echo 'Forecast</h1>';
		echo '<iframe id="forecast_embed" type="text/html" frameborder="0" height="245" width="100%" src="http://forecast.io/embed/#lat='.$weather_lat.'&lon='.$weather_long.'&name='.$weather_name.'"> </iframe>';
	}
	echo '</div>';
}