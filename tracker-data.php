<?php
require_once('require/class.Connection.php');
require_once('require/class.Tracker.php');
require_once('require/class.Language.php');
require_once('require/class.TrackerLive.php');
require_once('require/class.TrackerArchive.php');
require_once('require/class.Elevation.php');
$TrackerLive = new TrackerLive();
$TrackerArchive = new TrackerArchive();
$Elevation = new Elevation();

$from_archive = false;
if (isset($_GET['ident'])) {
	$ident = urldecode(filter_input(INPUT_GET,'ident',FILTER_SANITIZE_STRING));
	if (isset($_GET['currenttime'])) {
		$currenttime = filter_input(INPUT_GET,'currenttime',FILTER_SANITIZE_NUMBER_INT);
		$currenttime = round($currenttime/1000);
		$spotter_array = $TrackerLive->getDateLiveTrackerDataByIdent($ident,$currenttime);
		if (empty($spotter_array)) {
			$from_archive = true;
			$spotter_array = $TrackerArchive->getDateArchiveTrackerDataByIdent($ident,$currenttime);
		}
		
	} else {
		$spotter_array = $TrackerLive->getLastLiveTrackerDataByIdent($ident);
		if (empty($spotter_array)) {
			$from_archive = true;
			$spotter_array = $TrackerArchive->getLastArchiveTrackerDataByIdent($ident);
		}
	}
}
if (isset($_GET['famtrackid'])) {
	$famtrackid = urldecode(filter_input(INPUT_GET,'famtrackid',FILTER_SANITIZE_STRING));
	if (isset($_GET['currenttime'])) {
		$currenttime = filter_input(INPUT_GET,'currenttime',FILTER_SANITIZE_NUMBER_INT);
		$currenttime = round($currenttime/1000);
		$spotter_array = $TrackerLive->getDateLiveTrackerDataById($famtrackid,$currenttime);
		
		if (empty($spotter_array)) {
			$from_archive = true;
//			$spotter_array = $SpotterArchive->getLastArchiveSpotterDataById($flightaware_id);
			$spotter_array = $TrackerArchive->getDateArchiveTrackerDataById($flightaware_id,$currenttime);
		}
		
	} else {
		$spotter_array = $TrackerLive->getLastLiveTrackerDataById($famtrackid);
		if (empty($spotter_array)) {
			$from_archive = true;
			$spotter_array = $TrackerArchive->getLastArchiveTrackerDataById($flightaware_id);
		}
	}
}
 ?>
<div class="alldetails">
<button type="button" class="close">&times;</button>
<?php
$spotter_item = $spotter_array[0];
date_default_timezone_set('UTC');
if (isset($spotter_item['image_thumbnail']) && $spotter_item['image_thumbnail'] != "")
{
	if ($spotter_item['image_source'] == 'flickr' || $spotter_item['image_source'] == 'wikimedia' || $spotter_item['image_source'] == 'devianart') {
		$image = preg_replace("/^http:/i","https:",$spotter_item['image_thumbnail']);
	} else $image = $spotter_item['image_thumbnail'];

}
/* else {
	$image = "images/placeholder_thumb.png";
} */

print '<div class="top">';
if (isset($image)) {
	print '<div class="left"><img src="'.$image.'" alt="'.$spotter_item['registration'].' '.$spotter_item['aircraft_name'].'" title="'.$spotter_item['registration'].' '.$spotter_item['aircraft_name'].' Image &copy; '.$spotter_item['image_copyright'].'"/><br />Image &copy; '.$spotter_item['image_copyright'].'</div>';
}
//print '<div class="right"><div class="callsign-details"><div class="callsign"><a href="'.$globalURL.'/redirect/'.$spotter_item['famtrackid'].'" target="_blank">'.$spotter_item['ident'].'</a></div>';
print '<div class="right"><div class="callsign-details"><div class="callsign">'.$spotter_item['ident'].'</div>';
print '</div>';
print '</div></div>';
print '<div class="details">';
print '<div><span>'._("Altitude").'</span>';
if (isset($globalGroundAltitude) && $globalGroundAltitude) {
    try {
	$groundAltitude = $Elevation->getElevation($spotter_item['latitude'],$spotter_item['longitude']);
    } catch(Exception $e) {
    }
}
if ((!isset($_COOKIE['unitaltitude']) && isset($globalUnitAltitude) && $globalUnitAltitude == 'feet') || (isset($_COOKIE['unitaltitude']) && $_COOKIE['unitaltitude'] == 'feet')) {
	print $spotter_item['altitude'].' feet (FL'.$spotter_item['altitude'].')';
} else {
	print round($spotter_item['altitude']*0.3048).' m (FL'.round($spotter_item['altitude']/100).')';
}
if (isset($groundAltitude) && $groundAltitude < $spotter_item['altitude']*0.3048) {
    print '<br>';
    print '<span>'._("Ground Altitude").'</span>';
    if ((!isset($_COOKIE['unitaltitude']) && isset($globalUnitAltitude) && $globalUnitAltitude == 'feet') || (isset($_COOKIE['unitaltitude']) && $_COOKIE['unitaltitude'] == 'feet')) {
	print round($groundAltitude*3.28084).' feet';
    } else {
	print round($groundAltitude).' m';
    }
    print '</i>';
}
print '</div>';
print '<div><span>'._("Speed").'</span>';
if ((!isset($_COOKIE['unitspeed']) && isset($globalUnitSpeed) && $globalUnitSpeed == 'mph') || (isset($_COOKIE['unitspeed']) && $_COOKIE['unitspeed'] == 'mph')) {
	print round($spotter_item['ground_speed']*0.621371).' mph';
} elseif ((!isset($_COOKIE['unitspeed']) && isset($globalUnitSpeed) && $globalUnitSpeed == 'knots') || (isset($_COOKIE['unitspeed']) && $_COOKIE['unitspeed'] == 'knots')) {
	print round($spotter_item['ground_speed']*0.539957).' knots';
} else {
	print $spotter_item['ground_speed'].' km/h';
}
print '</div>';
print '<div><span>'._("Coordinates").'</span>'.$spotter_item['latitude'].', '.$spotter_item['longitude'].'</div>';
print '<div><span>'._("Type").'</span>'.$spotter_item['type'].'</div>';
print '<div><span>'._("Heading").'</span>'.$spotter_item['heading'].'°</div>';
if (isset($spotter_item['over_country']) && $spotter_item['over_country'] != '') {
	print '<div><span>'._("Over country").'</span>';
	print $spotter_item['over_country'];
	print '</div>';
}
if (isset($spotter_item['source_name']) && $spotter_item['source_name'] != '') {
	print '<div><span>'._("Source").'</span>';
	print $spotter_item['source_name'];
	print '</div>';
}
if (isset($spotter_item['comment']) && $spotter_item['comment'] != '') {
	print '<div><span>'._("Comment").'</span>';
	print $spotter_item['comment'];
	print '</div>';
}
if (isset($_COOKIE['MapFormat']) && $_COOKIE['MapFormat'] == '3d') {
	print '<a href="?3d&trackerid='.$spotter_item['famtrackid'].'">Track it link !</a>';
} else {
	print '<a href="?2d&trackerid='.$spotter_item['famtrackid'].'">Track it link !</a>';
}
print '</div>';
print '</div>';
?>
</div>