<?php
require_once('require/class.Connection.php');
require_once('require/class.NOTAM.php');
$NOTAM = new NOTAM();
if (isset($_GET['download']))
{
	header('Content-disposition: attachment; filename="notam.geojson"');
}
header('Content-Type: text/javascript');

if (isset($_GET['coord'])) 
{
	$coords = explode(',',$_GET['coord']);
//	$spotter_array = Spotter::getAllNOTAMbyCoord($coords);
	$spotter_array = $NOTAM->getAllNOTAM();
} else {
	$spotter_array = $NOTAM->getAllNOTAM();
}
      
$output = '{"type": "FeatureCollection","features": [';
            
if (!empty($spotter_array))
{	  
	foreach($spotter_array as $spotter_item)
	{
		date_default_timezone_set('UTC');
		//waypoint plotting
		$output .= '{"type": "Feature",';
		    $output .= '"properties": {';
			$output .= '"ref": "'.$spotter_item['ref'].'",';
			$output .= '"title": "'.$spotter_item['title'].'",';
			$output .= '"fir": "'.$spotter_item['fir'].'",';
			$output .= '"text": "'.$spotter_item['notam_text'].'",';
			$output .= '"latitude": "'.$spotter_item['center_latitude'].'",';
			$output .= '"longitude": "'.$spotter_item['center_longitude'].'",';
//			$output .= '"altitude": "'.$spotter_item['altitude'].'",';
//			$output .= '"popupContent": "'.$spotter_item['ref'].' : '.$spotter_item['title'].'",';
//			$output .= '"type": "'.$spotter_item['type'].'",';
//			$output .= '"icao": "'.$spotter_item['icao'].'",';
//			$output .= '"iata": "'.$spotter_item['iata'].'",';
//			$output .= '"homepage": "'.$spotter_item['home_link'].'",';
//			$output .= '"image_thumb": "'.$spotter_item['image_thumb'].'"';
//			$output .= '"photo": "'.$spotter_item['image_thumbnail'].'",';
//			if ($spotter_item['radius'] > 30) $spotter_item['radius'] = 30;
			if ($spotter_item['scope'] == 'Airport warning') {
			    $output .= '"color": "#EACC04",';
			} elseif ($spotter_item['scope'] == 'Airport/Enroute warning') {
			    $output .= '"color": "#EA7D00",';
			} elseif ($spotter_item['scope'] == 'Navigation warning') {
			    $output .= '"color": "#BBEA00",';
			} else {
			    $output .= '"color": "#FF0000",';
			}
			$radius = $spotter_item['radius']*1852;
			$output .= '"radiusm": "'.$radius.'",';
			$output .= '"radiusnm": "'.$spotter_item['radius'].'",';
			if ($radius > 25000) $radius = 25000;
			$output .= '"radius": "'.$radius.'"';
		    $output .= '},';
		    $output .= '"geometry": {';
			$output .= '"type": "Point",';
			$output .= '"coordinates": [';
			    $output .= $spotter_item['center_longitude'].', '.$spotter_item['center_latitude'];
			$output .= ']';
		    $output .= '}';
		$output .= '},';
	}
	$output  = substr($output, 0, -1);
}
$output .= ']}';

print $output;

?>