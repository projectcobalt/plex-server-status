<!DOCTYPE html>
<?php
	Ini_Set( 'display_errors', true );
	include("functions.php");
	include("zfs.class.php");
	//include("nas_functions.php");
?>
<html lang="en">
	<script>
	// Enable bootstrap tooltips
	$(function ()
	        { $("[rel=tooltip]").tooltip();
	        });
	</script>
<?php printZpools(); ?>


		

		


