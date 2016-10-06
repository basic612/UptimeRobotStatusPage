<?php
echo "hi";
/*Note: You'll need the ID of the monitor. For that, simply go to "http://api.uptimerobot.com/getMonitors?apiKey=yourApiKey" and get the ID of the monitor to be queried.*/
/*And, this code requires PHP 5+ or PHP 4 with SimpleXML enabled.*/
 
/*Variables - Start*/
$apiKey     = "u12345-12331232312312312312"; /*replace with your apiKey*/
$monitorID  = 1111111; /*replace with your monitorID*/
$url    = "http://api.uptimerobot.com/getMonitors?apiKey=" . $apiKey . "&monitors=" . $monitorID . "&format=xml";
/*Variables - End*/
 
/*Curl Request - Start*/
$c = curl_init($url);
curl_setopt($c, CURLOPT_RETURNTRANSFER, true);
$responseXML = curl_exec($c);
curl_close($c);
/*Curl Request - End*/
 
/*XML Parsing - Start*/
$xml = simplexml_load_string($responseXML);
 
foreach($xml->monitor as $monitor) {
    echo $monitor['alltimeuptimeratio'];
}
/*XML Parsing - End*/
?>
