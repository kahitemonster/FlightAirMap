<?php
require_once('require/class.Connection.php');
require_once('require/class.Spotter.php');
require_once('require/class.Stats.php');
require_once('require/class.Language.php');
if (!isset($_GET['airline'])) {
	header('Location: '.$globalURL.'/airline');
	die();
}
$airline = filter_input(INPUT_GET,'airline',FILTER_SANITIZE_STRING);
$Spotter = new Spotter();
$alliance = false;
if (strpos($airline,'alliance_') !== FALSE) {
	$alliance = true;
} else {
	$spotter_array = $Spotter->getSpotterDataByAirline($airline,"0,1","");
}

if (!empty($spotter_array) || $alliance === true)
{
	if ($alliance) {
		$title = sprintf(_("Most Common Aircraft Manufacturer from %s"),str_replace('_',' ',str_replace('alliance_','',$airline)));
	} else {
		$title = sprintf(_("Most Common Aircraft Manufacturer from %s (%s)"),$spotter_array[0]['airline_name'],$spotter_array[0]['airline_icao']);
	}
	require_once('header.php');
	print '<div class="select-item">';
	print '<form action="'.$globalURL.'/airline" method="post">';
	print '<select name="airline" class="selectpicker" data-live-search="true">';
	print '<option></option>';
	$alliances = $Spotter->getAllAllianceNames();
	if (!empty($alliances)) {
		foreach ($alliances as $al) {
			if ($alliance && str_replace('_',' ',str_replace('alliance_','',$airline)) == $al['alliance']) {
				print '<option value="'.str_replace(' ','_',$al['alliance']).'" selected>'.$al['alliance'].'</option>';
			} else {
				print '<option value="'.str_replace(' ','_',$al['alliance']).'">'.$al['alliance'].'</option>';
			}
		}
		print '<option disabled>───────────────────</option>';
	}
	$Stats = new Stats();
	$airline_names = $Stats->getAllAirlineNames();
	if (empty($airline_names)) $airline_names = $Spotter->getAllAirlineNames();
	foreach($airline_names as $airline_name)
	{
		if($airline == $airline_name['airline_icao'])
		{
			print '<option value="'.$airline_name['airline_icao'].'" selected="selected">'.$airline_name['airline_name'].' ('.$airline_name['airline_icao'].')</option>';
		} else {
			print '<option value="'.$airline_name['airline_icao'].'">'.$airline_name['airline_name'].' ('.$airline_name['airline_icao'].')</option>';
		}
	}
	print '</select>';
	print '<button type="submit"><i class="fa fa-angle-double-right"></i></button>';
	print '</form>';
	print '</div>';

	if ($airline != "NA")
	{
		if ($alliance === false) {
			print '<div class="info column">';
			print '<h1>'.$spotter_array[0]['airline_name'].' ('.$spotter_array[0]['airline_icao'].')</h1>';
			if ($globalIVAO && @getimagesize($globalURL.'/images/airlines/'.$spotter_array[0]['airline_icao'].'.gif'))
			{
				print '<img src="'.$globalURL.'/images/airlines/'.$spotter_array[0]['airline_icao'].'.gif" alt="'.$spotter_array[0]['airline_name'].' ('.$spotter_array[0]['airline_icao'].')" title="'.$spotter_array[0]['airline_name'].' ('.$spotter_array[0]['airline_icao'].')" class="logo" />';
			}
			elseif (@getimagesize($globalURL.'/images/airlines/'.$spotter_array[0]['airline_icao'].'.png'))
			{
				print '<img src="'.$globalURL.'/images/airlines/'.$spotter_array[0]['airline_icao'].'.png" alt="'.$spotter_array[0]['airline_name'].' ('.$spotter_array[0]['airline_icao'].')" title="'.$spotter_array[0]['airline_name'].' ('.$spotter_array[0]['airline_icao'].')" class="logo" />';
			}
			print '<div><span class="label">'._("Name").'</span>'.$spotter_array[0]['airline_name'].'</div>';
			print '<div><span class="label">'._("Country").'</span>'.$spotter_array[0]['airline_country'].'</div>';
			print '<div><span class="label">'._("ICAO").'</span>'.$spotter_array[0]['airline_icao'].'</div>';
			print '<div><span class="label">'._("IATA").'</span>'.$spotter_array[0]['airline_iata'].'</div>';
			print '<div><span class="label">'._("Callsign").'</span>'.$spotter_array[0]['airline_callsign'].'</div>'; 
			print '<div><span class="label">'._("Type").'</span>'.ucwords($spotter_array[0]['airline_type']).'</div>';        
			print '</div>';
		} else {
			print '<div class="info column">';
			print '<h1>'.str_replace('_',' ',str_replace('alliance_','',$airline)).'</h1>';
			if (@getimagesize($globalURL.'/images/airlines/'.str_replace('alliance_','',$airline).'.png') || @getimagesize('images/airlines/'.str_replace('alliance_','',$airline).'.png'))
			{
				print '<img src="'.$globalURL.'/images/airlines/'.str_replace('alliance_','',$airline).'.png" alt="'.str_replace('_',' ',str_replace('alliance_','',$airline)).'" title="'.str_replace('_',' ',str_replace('alliance_','',$airline)).'" class="logo" />';
			}
			print '<div><span class="label">'._("Name").'</span>'.str_replace('_',' ',str_replace('alliance_','',$airline)).'</div>';
			print '</div>';
		}
	} else {
		print '<div class="alert alert-warning">'._("This special airline profile shows all flights that do <u>not</u> have a airline associated with them.").'</div>';
	}

	include('airline-sub-menu.php');
	print '<div class="column">';
	print '<h2>'._("Most Common Aircraft Manufacturer").'</h2>';
	if ($alliance) {
		print '<p>'.sprintf(_("The statistic below shows the most common Aircraft Manufacturer of flights from <strong>%s</strong>."),str_replace('_',' ',str_replace('alliance_','',$airline))).'</p>';
	} else {
		print '<p>'.sprintf(_("The statistic below shows the most common Aircraft Manufacturer of flights from <strong>%s</strong>."),$spotter_array[0]['airline_name']).'</p>';
	}
	if ($alliance) {
		$manufacturers_array = $Spotter->countAllAircraftManufacturerByAirline('',array('alliance' => str_replace('_',' ',str_replace('alliance_','',$airline))));
	} else {
		$manufacturers_array = $Spotter->countAllAircraftManufacturerByAirline($airline);
	}
	$manufacturers_array = $Stats->countAllAircraftManufacturers(true,$airline);
	if (!empty($manufacturers_array))
	{
		print '<div class="table-responsive">';
		print '<table class="common-manufacturer table-striped">';
		print '<thead>';
		print '<th></th>';
		print '<th>'._("Aircraft Manufacturer").'</th>';
		print '<th>'._("# of times").'</th>';
		print '<th></th>';
		print '</thead>';
		print '<tbody>';
		$i = 1;
		foreach($manufacturers_array as $manufacturer_item)
		{
			print '<tr>';
			print '<td><strong>'.$i.'</strong></td>';
			print '<td>';
			print '<a href="'.$globalURL.'/manufacturer/'.strtolower(str_replace(" ", "-", $manufacturer_item['aircraft_manufacturer'])).'">'.$manufacturer_item['aircraft_manufacturer'].'</a>';
			print '</td>';
			print '<td>';
			print $manufacturer_item['aircraft_manufacturer_count'];
			print '</td>';
			print '<td><a href="'.$globalURL.'/search?manufacturer='.strtolower(str_replace(" ", "-", $manufacturer_item['aircraft_manufacturer'])).'&airline='.$airline.'">'._("Search flights").'</a></td>';
			print '</tr>';
			$i++;
		}
		print '<tbody>';
		print '</table>';
		print '</div>';
	}
	print '</div>';
} else {
	$title = _("Airline Statistic");
	require_once('header.php');
	print '<h1>'._("Error").'</h1>';
	print '<p>'._("Sorry, the airline does not exist in this database. :(").'</p>'; 
}

require_once('footer.php');
?>