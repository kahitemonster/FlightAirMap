<?php
require_once('require/class.Connection.php');
require_once('require/class.Spotter.php');
require_once('require/class.Language.php');
$Spotter = new Spotter();
$sort = filter_input(INPUT_GET,'sort',FILTER_SANITIZE_STRING);
$date = filter_input(INPUT_GET,'date',FILTER_SANITIZE_STRING);
$spotter_array = $Spotter->getSpotterDataByDate($date,"0,1", $sort);

if (!empty($spotter_array))
{
	$title = sprintf(_("Most Common Departure Airports by Country on %s"),date("l F j, Y", strtotime($spotter_array[0]['date_iso_8601'])));

	require_once('header.php');
	print '<div class="select-item">';
	print '<form action="'.$globalURL.'/date" method="post">';
	print '<label for="date">'._("Select a Date").'</label>';
	print '<input type="text" id="date" name="date" value="'.$date.'" size="8" readonly="readonly" class="custom" />';
	print '<button type="submit"><i class="fa fa-angle-double-right"></i></button>';
	print '</form>';
	print '</div>';

	print '<div class="info column">';
	print '<h1>'.sprintf(_("Flights from %s"),date("l F j, Y", strtotime($spotter_array[0]['date_iso_8601']))).'</h1>';
	print '</div>';

	include('date-sub-menu.php');
	print '<div class="column">';
	print '<h2>'._("Most Common Departure Airports by Country").'</h2>';
	print '<p>'.sprintf(_("The statistic below shows all departure airports by Country of origin of flights on <strong>%s</strong>."),date("l F j, Y", strtotime($spotter_array[0]['date_iso_8601']))).'</p>';
	$airport_country_array = $Spotter->countAllDepartureAirportCountriesByDate($date);
	print '<script type="text/javascript" src="https://www.google.com/jsapi"></script>';
	print '<div id="chartCountry" class="chart" width="100%"></div>
      	<script> 
      		google.load("visualization", "1", {packages:["geochart"]});
          google.setOnLoadCallback(drawChart);
          function drawChart() {
            var data = google.visualization.arrayToDataTable([
            	["'._("Country").'", "'._("# of times").'"], ';
	$country_data = '';
	foreach($airport_country_array as $airport_item)
	{
		$country_data .= '[ "'.$airport_item['departure_airport_country'].'",'.$airport_item['airport_departure_country_count'].'],';
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

	if (!empty($airport_country_array))
	{
		print '<div class="table-responsive">';
		print '<table class="common-country table-striped">';
		print '<thead>';
		print '<th></th>';
		print '<th>'._("Country").'</th>';
		print '<th>'._("# of times").'</th>';
		print '</thead>';
		print '<tbody>';
		$i = 1;
		foreach($airport_country_array as $airport_item)
		{
			print '<tr>';
			print '<td><strong>'.$i.'</strong></td>';
			print '<td>';
			print '<a href="'.$globalURL.'/country/'.strtolower(str_replace(" ", "-", $airport_item['departure_airport_country'])).'">'.$airport_item['departure_airport_country'].'</a>';
			print '</td>';
			print '<td>';
			print $airport_item['airport_departure_country_count'];
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
	$title = _("Unknown Date");
	require_once('header.php');
	print '<h1>'._("Error").'</h1>';
	print '<p>'._("Sorry, this date does not exist in this database. :(").'</p>'; 
}

require_once('footer.php');
?>