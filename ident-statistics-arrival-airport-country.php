<?php
require_once('require/class.Connection.php');
require_once('require/class.Spotter.php');
require_once('require/class.Language.php');
if (!isset($_GET['ident'])) {
        header('Location: '.$globalURL.'/ident');
        die();
}
$Spotter = new Spotter();
$sort = filter_input(INPUT_GET,'sort',FILTER_SANITIZE_STRING);
$ident = filter_input(INPUT_GET,'ident',FILTER_SANITIZE_STRING);
$spotter_array = $Spotter->getSpotterDataByIdent($ident,"0,1", $sort);

if (!empty($spotter_array))
{
	$title = sprintf(_("Most Common Arrival Airports by Country of %s"),$spotter_array[0]['ident']);
	require_once('header.php');
	print '<div class="info column">';
	print '<h1>'.$spotter_array[0]['ident'].'</h1>';
	print '<div><span class="label">'._("Ident").'</span>'.$spotter_array[0]['ident'].'</div>';
	print '<div><span class="label">'._("Airline").'</span><a href="'.$globalURL.'/airline/'.$spotter_array[0]['airline_icao'].'">'.$spotter_array[0]['airline_name'].'</a></div>'; 
	print '</div>';

	include('ident-sub-menu.php');
	print '<div class="column">';
	print '<h2>'._("Most Common Arrival Airports by Country").'</h2>';
	print '<p>'.sprintf(_("The statistic below shows all arrival airports by Country of origin of flights with the ident/callsign <strong>%s</strong>."),$spotter_array[0]['ident']).'</p>';
	$airport_country_array = $Spotter->countAllArrivalAirportCountriesByIdent($ident);
	//print '<script type="text/javascript" src="https://www.google.com/jsapi"></script>';
	print '<script type="text/javascript" src="'.$globalURL.'/js/d3.min.js"></script>';
	print '<script type="text/javascript" src="'.$globalURL.'/js/topojson.v2.min.js"></script>';
	print '<script type="text/javascript" src="'.$globalURL.'/js/datamaps.world.min.js"></script>';

	print '<div id="chartCountry" class="chart" width="100%"></div><script>'; 
/*
	print 'google.load("visualization", "1", {packages:["geochart"]});
          google.setOnLoadCallback(drawChart);
          function drawChart() {
            var data = google.visualization.arrayToDataTable([
            	["'._("Country").'", "'._("# of times").'"], ';
	$country_data = '';
	foreach($airport_country_array as $airport_item)
	{
		$country_data .= '[ "'.$airport_item['arrival_airport_country'].'",'.$airport_item['airport_arrival_country_count'].'],';
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
    			});';
*/
	print 'var series = [';
	$country_data = '';
	foreach($airport_country_array as $airport_item)
	{
		$country_data .= '[ "'.$airport_item['arrival_airport_country_iso3'].'",'.$airport_item['airport_arrival_country_count'].'],';
	}
	$country_data = substr($country_data, 0, -1);
	print $country_data;
	print '];';
	print 'var dataset = {};var onlyValues = series.map(function(obj){ return obj[1]; });var minValue = Math.min.apply(null, onlyValues), maxValue = Math.max.apply(null, onlyValues);';
	print 'var paletteScale = d3.scale.log().domain([minValue,maxValue]).range(["#EFEFFF","#001830"]);';
	print 'series.forEach(function(item){var iso = item[0], value = item[1]; dataset[iso] = { numberOfThings: value, fillColor: paletteScale(value) };});';
	print 'new Datamap({
        element: document.getElementById("chartCountry"),
        projection: "mercator", // big world map
        fills: { defaultFill: "#F5F5F5" },
        data: dataset,
        responsive: true,
        geographyConfig: {
        borderColor: "#DEDEDE",
        highlightBorderWidth: 2,
        highlightFillColor: function(geo) {
        return geo["fillColor"] || "#F5F5F5";
        },
        highlightBorderColor: "#B7B7B7",
        done: function(datamap) {
        datamap.svg.call(d3.behavior.zoom().on("zoom", redraw));
        function redraw() {
            datamap.svg.selectAll("g").attr("transform", "translate(" + d3.event.translate + ")scale(" + d3.event.scale + ")");
        }
        },
        popupTemplate: function(geo, data) {
        if (!data) { return ; }
        return ['."'".'<div class="hoverinfo">'."','<strong>', geo.properties.name, '</strong>','<br>Count: <strong>', data.numberOfThings, '</strong>','</div>'].join('');
	    }
    }
        });";
	print '</script>';

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
			print '<a href="'.$globalURL.'/country/'.strtolower(str_replace(" ", "-", $airport_item['arrival_airport_country'])).'">'.$airport_item['arrival_airport_country'].'</a>';
			print '</td>';
			print '<td>';
			print $airport_item['airport_arrival_country_count'];
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
	$title = _("Ident");
	require_once('header.php');
	print '<h1>'._("Error").'</h1>';
	print '<p>'._("Sorry, this ident/callsign is not in the database. :(").'</p>';  
}

require_once('footer.php');
?>