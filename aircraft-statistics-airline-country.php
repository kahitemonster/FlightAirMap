<?php
require_once('require/class.Connection.php');
require_once('require/class.Spotter.php');
require_once('require/class.Stats.php');
require_once('require/class.Language.php');
$Spotter = new Spotter();

if (!isset($_GET['aircraft_type'])) {
	header('Location: '.$globalURL.'/aircraft');
	die();
}
$aircraft_type = filter_input(INPUT_GET,'aircraft_type',FILTER_SANITIZE_STRING);
$spotter_array = $Spotter->getSpotterDataByAircraft($aircraft_type,"0,1","");


if (!empty($spotter_array))
{
	$title = sprintf(_("Most Common Airlines by Country from %s (%s)"),$spotter_array[0]['aircraft_name'],$spotter_array[0]['aircraft_type']);
	require_once('header.php');
    
	print '<div class="select-item">';
	print '<form action="'.$globalURL.'/aircraft" method="post">';
	print '<select name="aircraft_type" class="selectpicker" data-live-search="true">';
    	print '<option></option>';
    	$Stats = new Stats();
    	$aircraft_types = $Stats->getAllAircraftTypes();
    	if (empty($aircraft_types)) $aircraft_types = $Spotter->getAllAircraftTypes();
    	foreach($aircraft_types as $aircrafttype)
    	{
    		if($aircraft_type == $aircrafttype['aircraft_icao'])
    		{
    			print '<option value="'.$aircrafttype['aircraft_icao'].'" selected="selected">'.$aircrafttype['aircraft_name'].' ('.$aircrafttype['aircraft_icao'].')</option>';
    		} else {
    			print '<option value="'.$aircrafttype['aircraft_icao'].'">'.$aircrafttype['aircraft_name'].' ('.$aircrafttype['aircraft_icao'].')</option>';
    		}
	}
	print '</select>';
	print '<button type="submit"><i class="fa fa-angle-double-right"></i></button>';
	print '</form>';
	print '</div>';

	if ($aircraft_type != "NA")
	{
		print '<div class="info column">';
		print '<h1>'.$spotter_array[0]['aircraft_name'].' ('.$spotter_array[0]['aircraft_type'].')</h1>';
		print '<div><span class="label">Name</span>'.$spotter_array[0]['aircraft_name'].'</div>';
		print '<div><span class="label">ICAO</span>'.$spotter_array[0]['aircraft_type'].'</div>'; 
		print '<div><span class="label">Manufacturer</span><a href="'.$globalURL.'/manufacturer/'.strtolower(str_replace(" ", "-", $spotter_array[0]['aircraft_manufacturer'])).'">'.$spotter_array[0]['aircraft_manufacturer'].'</a></div>';
		print '</div>';
	} else {
		print '<div class="alert alert-warning">'._("This special aircraft profile shows all flights in where the aircraft type is unknown.").'</div>';
	}
	include('aircraft-sub-menu.php');
	print '<div class="column">';
	print '<h2>'._("Most Common Airlines by Country").'</h2>';
	print '<p>'.sprintf(_("The statistic below shows the most common airlines by Country of origin of flights from <strong>%s (%s)</strong>."),$spotter_array[0]['aircraft_name'],$spotter_array[0]['aircraft_type']).'</p>';

	$airline_array = $Spotter->countAllAirlineCountriesByAircraft($aircraft_type);
	print '<script type="text/javascript" src="https://www.google.com/jsapi"></script>';
	print '<div id="chartCountry" class="chart" width="100%"></div>
	    <script> 
		google.load("visualization", "1", {packages:["geochart"]});
        	google.setOnLoadCallback(drawChart);
        	function drawChart() {
        	    var data = google.visualization.arrayToDataTable([
            		["Country", "# of times"], ';
            		$country_data = '';
	foreach($airline_array as $airline_item)
	{
		$country_data .= '[ "'.$airline_item['airline_country'].'",'.$airline_item['airline_country_count'].'],';
	}
	$country_data = substr($country_data, 0, -1);
	print $country_data;
	print ']);
		    var options = {
            		legend: {position: "none"},
            		chartArea: {"width": "80%", "height": "60%"},
            		height:500,
            		colors: ["#8BA9D0","#1a3151"]
        	    };
		    var chart = new google.visualization.GeoChart(document.getElementById("chartCountry"));
		    chart.draw(data, options);
		}
		$(window).resize(function(){
    		     drawChart();
    		});
	     </script>';
	if (!empty($airline_array))
	{
		print '<div class="table-responsive">';
		print '<table class="common-country">';
		print '<thead>';
		print '<th></th>';
		print '<th>'._("Country").'</th>';
		print '<th>'._("# of times").'</th>';
		print '</thead>';
		print '<tbody>';
		$i = 1;
		foreach($airline_array as $airline_item)
		{
			print '<tr>';
			print '<td><strong>'.$i.'</strong></td>';
			print '<td>';
			print '<a href="'.$globalURL.'/country/'.strtolower(str_replace(" ", "-", $airline_item['airline_country'])).'">'.$airline_item['airline_country'].'</a>';
			print '</td>';
			print '<td>';
			print $airline_item['airline_country_count'];
			print '</td>';
			print '</tr>';
			$i++;
		}
		print '<tbody>';
		print '</table>';
		print '</div>';
	}
	print '</div>';
} else {
	$title = _("Aircraft Type");
	require_once('header.php');
	print '<h1>'._("Error").'</h1>';
	print '<p>'._("Sorry, the aircraft type does not exist in this database. :(").'</p>';  
}
require_once('footer.php');
?>