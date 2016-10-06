<?php
header('Access-Control-Allow-Origin: https://observium');

require_once('uptimerobot.class.php');

$UR = new UptimeRobot("");        
$UR->setFormat('json');   
$apiKey     = "u12345-12331232312312312312"; /*replace with your apiKey*/
$UR->setApiKey($apiKey);            


try {
	$response = $UR->getMonitors();

    $result = json_decode($response);
} 
catch (Exception $ex) {
    switch ($ex->getCode()) {
        case 1:
            echo $ex->getMessage();
            break;
        case 2:
            echo "You should try specifying an apiKey for once!";
            break;
        case 3:
            echo "You forgot a required key";
            break;
        default:
            echo $ex->getCode(). ": ". $ex->getMessage();        
    }  
}

$monitors = $result->monitors->monitor;


echo <<<EOD

<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/html4/strict.dtd">
<html>
	<head>
		<meta http-equiv="content-type" content="text/html; charset=utf-8" />
		<title>Server Statuses</title>
		<script
			  src="//code.jquery.com/jquery-3.1.1.min.js"
			  integrity="sha256-hVVnYaiADRTO2PzUGmuLJr8BLUSjGIZsDYGmIJLv2b8="
			  crossorigin="anonymous"></script>
			  <link href="//cdn.rawgit.com/basic612/featherlight/1.5.0/release/featherlight.min.css" type="text/css" rel="stylesheet" />
		<script type="text/javascript" language="javascript" src="//cdn.datatables.net/1.10.12/js/jquery.dataTables.min.js"></script>
		<script type="text/javascript" language="javascript" src="//cdn.datatables.net/1.10.12/js/dataTables.jqueryui.min.js"></script>
		<script src="//cdn.rawgit.com/noelboss/featherlight/1.5.0/release/featherlight.min.js" type="text/javascript" charset="utf-8"></script>
		<script type="text/javascript" charset="utf-8">
			$(document).ready(function() {
				$('#uptime').dataTable( {
					"aaSorting": [[ 1, "asc" ], [0, "asc"]],
					"iDisplayLength": -1,
					"aLengthMenu": [[10, 20, -1], [10, 20, "All"]]
				} );
				$('.monitorUrl').featherlight( {
					'type': 'iframe',
					'iframeWidth': 1500,
					'iframeHeight': 700,
				} );
			} );
		</script>
		<style type="text/css" title="currentStyle">
			@import "uptime.css";
		</style>
		<script language="JavaScript">
<!--

var sURL = unescape(window.location.pathname);
var checkInterval = 60; // seconds
function doLoad()
{
    // the timeout value should be the same as in the "refresh" meta-tag
    var timoutId = setTimeout( "refresh()", checkInterval*1000 );
}

function doPause()
{
    // the timeout value should be the same as in the "refresh" meta-tag
    clearTimeout( timoutId );
}

function refresh()
{
    //  This version does NOT cause an entry in the browser's
    //  page view history.  Most browsers will always retrieve
    //  the document from the web-server whether it is already
    //  in the browsers page-cache or not.
    //  
    window.location.replace( sURL );
}
//-->
</script>
	</head>
	<body id="dt_example" onload="doLoad()">
<iframe id="helpframe" src='' height='0' width='0' frameborder='0'></iframe>

<script type="text/javascript">
  function iframeResizePipe()
  {
     // What's the page height?
     var height = document.body.scrollHeight;

     // Going to 'pipe' the data to the parent through the helpframe..
     var pipe = document.getElementById('helpframe');

     // Cachebuster a precaution here to stop browser caching interfering
     pipe.src = '//observium/dashboards/resizer.html?height='+height+'&cacheb='+Math.random();

  }
</script>
EOD;


if ($result->stat != 'ok') { // count($monitors) == 0) {

$error_result = print_r($result,true);
$error_response = print_r($response,true);

echo <<<EOD
		<div id="container">
			<div id="demo">
				<h1>Public Services Uptime Summary</h1>
				<p>Error - unable to collect results from monitoring API service. Last check: <script language="JavaScript">
<!--
    // we put this here so we can see something change
    document.write('<b>' + (new Date).toLocaleString() + '</b>');
//-->
</script></p>
				<h2>Debug Result:</h2>
				<pre>{$error_result}</pre>
				<h2>Debug Response:</h2>
				<p>{$error_response}</p>
			</div>
			<div class="spacer"></div>
			
			

		</div>
	</body>
</html>
EOD;
				
 	die();
 
}


echo <<<EOD
		<div id="container">
			<div id="demo">
				<h1>Public Services Uptime Summary</h1>
				<p>Historical stats are available from <a href="https://stats.uptimerobot.com/">Uptime Robot status page</a>. Management of alert checkers is done at <a href="https://uptimerobot.com/">Uptime Robot</a>.</p>
				<p>Monitors are tested every 5 minutes. This page will auto refresh every <script language="JavaScript"><!--
    document.write('<b>' + checkInterval + '</b>');
//--></script> seconds. Last updated: <script language="JavaScript"><!-- // we put this here so we can see something change
    document.write('<b>' + (new Date).toLocaleString() + '</b>');
//--></script><span id="refreshControl"></span><br />
NB: History indicates uptime % since monitoring started. Any failed or paused services will appear at the top of the list.</p>
<p>Email and SMS alerts are available on any monitors if required.</p>
<table cellpadding="0" cellspacing="0" border="0" class="display" id="uptime" width="100%">
	<thead>
		<tr>
			<th>System</th>
			<th>Status</th>
			<th>History</th>
			<th>Type</th>
			<th>Test</th>
			<th>Expect</th>
		</tr>
	</thead>
	<tbody>
EOD;

foreach ($monitors as $monitor) {
	
	/*
	0 - paused
1 - not checked yet
2 - up
8 - seems down
9 - down
*/

	switch ($monitor->status) {

		case 0:
			$status = 'Paused';
			$class = 'U';
		break;
		
		case 1:
			$status = 'Checking';
			$class = 'B';
		break;
		
		case 2:
			$status = 'Up';
			$class = 'A';
		break;
		
		case 8:
			$status = 'Failing (Will verify ASAP)';
			$class = 'X';
		break;
		
		case 9:
			$status = 'Fail';
			$class = 'X';
		break;
		
		default:
			$status = "??? ({$monitor->status})";
			$class = 'U';
		break;
			
	
	}

$monitorURL = $monitorExpect = '';

	switch ($monitor->type) {

		case 1:
		case 2:
			$monitorURL = "<a href='$monitor->url' class='monitorUrl'>$monitor->url</a>";
			$monitorExpect = $monitor->keywordvalue;
		break;

		case 3:
			$monitorURL = "ICMP Ping to {$monitor->url}";
			$monitorExpect = '';
		break;

		case 4:
			$monitorURL = "TCP connect to {$monitor->url}:{$monitor->port}";
			$monitorExpect = '';
		break;

		default:
			$monitorURL = "Error for {$monitor->url}";
			$monitorExpect = 'Uknown $monitor-type';
		break;

	}	

	$types = array (
		1 => 'HTTP(s)',
		2 => 'Keyword',
		3 => 'Ping',
		4 => 'Port',
		);

	echo <<<EOD

		<tr class="grade{$class}">
			<td>{$monitor->friendlyname}</td>
			<td>$status</td>
			<td>{$monitor->alltimeuptimeratio}</td>
			<td>{$types[$monitor->type]}</td>
			<td>$monitorURL</td>
			<td>$monitorExpect</td>
		</tr>
EOD;

// echo "\n\n<!----------------------------------------------------------------------\n";
// print_r($monitor);
// echo "\n---------------------------------------------------------------------->\n\n";

}

echo <<<EOD

	</tbody>
	<tfoot>
		<tr>
			<th>System</th>
			<th>Status</th>
			<th>History</th>
			<th>Type</th>
			<th>Test</th>
			<th>Expect</th>
		</tr>
	</tfoot>
</table>
			</div>
			<div class="spacer"></div>
			
			

		</div>
	</body>
</html>
EOD;


?>
