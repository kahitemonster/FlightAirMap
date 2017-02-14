<?php
require_once('require/class.Connection.php');
require_once('require/class.Spotter.php');
require_once('require/class.Language.php');
//require_once('require/class.Translation.php');
require_once('require/class.Stats.php');
//require_once('require/class.SpotterLive.php');
require_once('require/class.SpotterArchive.php');

if (!isset($_GET['owner'])){
	header('Location: '.$globalURL.'');
} else {
	$Spotter = new Spotter();
	$SpotterArchive = new SpotterArchive();
	//$Translation = new Translation();
	//calculuation for the pagination
	if(!isset($_GET['limit']))
	{
		$limit_start = 0;
		$limit_end = 25;
		$absolute_difference = 25;
	} else {
		$limit_explode = explode(",", $_GET['limit']);
		$limit_start = $limit_explode[0];
		$limit_end = $limit_explode[1];
		if (!ctype_digit(strval($limit_start)) || !ctype_digit(strval($limit_end))) {
			$limit_start = 0;
			$limit_end = 25;
		}
	}
	$absolute_difference = abs($limit_start - $limit_end);
	$limit_next = $limit_end + $absolute_difference;
	$limit_previous_1 = $limit_start - $absolute_difference;
	$limit_previous_2 = $limit_end - $absolute_difference;
	
	$page_url = $globalURL.'/owner/'.$_GET['owner'];
	
	$owner = filter_input(INPUT_GET,'owner',FILTER_SANITIZE_STRING);
	$sort = filter_input(INPUT_GET,'sort',FILTER_SANITIZE_STRING);
	$year = filter_input(INPUT_GET,'year',FILTER_SANITIZE_NUMBER_INT);
	$month = filter_input(INPUT_GET,'month',FILTER_SANITIZE_NUMBER_INT);
	$filter = array();
	if ($year != '') $filter = array_merge($filter,array('year' => $year));
	if ($month != '') $filter = array_merge($filter,array('month' => $month));
	if ($sort != '') 
	{
		$spotter_array = $Spotter->getSpotterDataByOwner($owner,$limit_start.",".$absolute_difference, $sort,$filter);
		if (empty($spotter_array)) {
			$spotter_array = $SpotterArchive->getSpotterDataByOwner($owner,$limit_start.",".$absolute_difference, $sort,$filter);
		}
	} else {
		$spotter_array = $Spotter->getSpotterDataByOwner($owner,$limit_start.",".$absolute_difference,'',$filter);
		if (empty($spotter_array)) {
			$spotter_array = $SpotterArchive->getSpotterDataByOwner($owner,$limit_start.",".$absolute_difference,'',$filter);
		}
	}

	if (!empty($spotter_array))
	{
		$title = sprintf(_("Detailed View for %s"),$spotter_array[0]['aircraft_owner']);
		//$ident = $spotter_array[0]['ident'];
		if (isset($spotter_array[0]['latitude'])) $latitude = $spotter_array[0]['latitude'];
		if (isset($spotter_array[0]['longitude'])) $longitude = $spotter_array[0]['longitude'];
		require_once('header.php');
		/*
		if (isset($globalArchive) && $globalArchive) {
			// Requirement for altitude graph
			print '<script type="text/javascript" src="https://www.google.com/jsapi"></script>';
			$all_data = $SpotterArchive->getAltitudeSpeedArchiveSpotterDataById($spotter_array[0]['flightaware_id']);
			if (isset($globalTimezone)) {
				date_default_timezone_set($globalTimezone);
			} else date_default_timezone_set('UTC');
			if (count($all_data) > 0) {
				print '<div id="chart6" class="chart" width="100%"></div>
                    <script> 
                        google.load("visualization", "1.1", {packages:["line","corechart"]});
                      google.setOnLoadCallback(drawChart6);
                      function drawChart6() {
                        var data = google.visualization.arrayToDataTable([
                            ["Hour","'._("Altitude").'","'._("Speed").'"], ';
                            $altitude_data = '';
				foreach($all_data as $data)
				{
					$altitude_data .= '[ "'.date("G:i",strtotime($data['date']." UTC")).'",'.$data['altitude'].','.$data['ground_speed'].'],';
				}
				$altitude_data = substr($altitude_data, 0, -1);
				print $altitude_data.']);

                        var options = {
                            legend: {position: "none"},
                            series: {
                                0: {axis: "Altitude"},
                                1: {axis: "Speed"}
                            },
                            axes: {
                                y: {
                                    Altitude: {label: "'._("Altitude (FL)").'"},
                                    Speed: {label: "'._("Speed (knots)").'"},
                                }
                            },
                            height:210
                        };

                        var chart = new google.charts.Line(document.getElementById("chart6"));
                        chart.draw(data, options);
                      }
                      $(window).resize(function(){
                              drawChart6();
                            });
				 </script>';
  			}
		}
		*/
		print '<div class="info column">';
		print '<h1>'.$spotter_array[0]['aircraft_owner'].'</h1>';
		//print '<div><span class="label">'._("Owner").'</span>'.$spotter_array[0]['aircraft_owner'].'</div>';
		$Stats = new Stats();
		if ($year == '' && $month == '') $flights = $Stats->getStatsOwner($owner);
		else $flights = 0;
		if ($flights == 0) $flights = $Spotter->countFlightsByOwner($owner,$filter);
		print '<div><span class="label">'._("Flights").'</span>'.$flights.'</div>';
		$aircraft_type = count($Spotter->countAllAircraftTypesByOwner($owner,$filter));
		print '<div><span class="label">'._("Aircrafts type").'</span>'.$aircraft_type.'</div>';
		$aircraft_registration = count($Spotter->countAllAircraftRegistrationByOwner($owner,$filter));
		print '<div><span class="label">'._("Aircrafts").'</span>'.$aircraft_registration.'</div>';
		$aircraft_manufacturer = count($Spotter->countAllAircraftManufacturerByOwner($owner,$filter));
		print '<div><span class="label">'._("Manufacturers").'</span>'.$aircraft_manufacturer.'</div>';
		$airlines = count($Spotter->countAllAirlinesByOwner($owner,$filter));
		print '<div><span class="label">'._("Airlines").'</span>'.$airlines.'</div>';
		$duration = $Spotter->getFlightDurationByOwner($owner,$filter);
		print '<div><span class="label">'._("Total flights spotted duration").'</span>'.$duration.'</div>';
		print '</div>';
	
		include('owner-sub-menu.php');
		print '<div class="table column">';
		print '<p>'.sprintf(_("The table below shows the detailed information of all flights with the owner <strong>%s</strong>."),$spotter_array[0]['aircraft_owner']).'</p>';

		include('table-output.php'); 
		print '<div class="pagination">';
		if ($limit_previous_1 >= 0)
		{
			print '<a href="'.$page_url.'/'.$limit_previous_1.','.$limit_previous_2.'/'.$sort.'">&laquo;'._("Previous Page").'</a>';
		}
		if ($spotter_array[0]['query_number_rows'] == $absolute_difference)
		{
			print '<a href="'.$page_url.'/'.$limit_end.','.$limit_next.'/'.$sort.'">'._("Next Page").'&raquo;</a>';
		}
		print '</div>';
		print '</div>';
	} else {
		$title = _("Owner");
		require_once('header.php');
		print '<h1>'._("Error").'</h1>';
		print '<p>'._("Sorry, this owner is not in the database. :(").'</p>'; 
	}
}
require_once('footer.php');
?>