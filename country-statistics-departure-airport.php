<?php
require_once('require/class.Connection.php');
require_once('require/class.Spotter.php');
require_once('require/class.Language.php');
if (!isset($_GET['country'])) {
        header('Location: '.$globalURL.'/country');
        die();
}
$Spotter = new Spotter();
$country = ucwords(str_replace("-", " ", filter_input(INPUT_GET,'country',FILTER_SANITIZE_STRING)));
$sort = filter_input(INPUT_GET,'sort',FILTER_SANITIZE_STRING);

if (isset($_GET['sort'])) {
	$spotter_array = $Spotter->getSpotterDataByCountry($country, "0,1", $sort);
} else {
	$spotter_array = $Spotter->getSpotterDataByCountry($country, "0,1", '');
}

if (!empty($spotter_array))
{
	$title = sprintf(_("Most Common Departure Airports from %s"),$country);
	require_once('header.php');
	print '<div class="select-item">';
	print '<form action="'.$globalURL.'/country" method="post">';
	print '<select name="country" class="selectpicker" data-live-search="true">';
	print '<option></option>';
	$all_countries = $Spotter->getAllCountries();
	foreach($all_countries as $all_country)
	{
		if($country == $all_country['country'])
		{
			print '<option value="'.strtolower(str_replace(" ", "-", $all_country['country'])).'" selected="selected">'.$all_country['country'].'</option>';
		} else {
			print '<option value="'.strtolower(str_replace(" ", "-", $all_country['country'])).'">'.$all_country['country'].'</option>';
		}
	}
	print '</select>';
	print '<button type="submit"><i class="fa fa-angle-double-right"></i></button>';
	print '</form>';
	print '</div>';
	if ($_GET['country'] != "NA")
	{
		print '<div class="info column">';
		print '<h1>'.sprintf(_("Airports &amp; Airlines from %s"),$country).'</h1>';
		print '</div>';
	} else {
		print '<div class="alert alert-warning">'._("This special country profile shows all flights that do <u>not</u> have a country of a airline or departure/arrival airport associated with them.").'</div>';
	}

	include('country-sub-menu.php');
	print '<div class="column">';
	print '<h2>'._("Most Common Departure Airports").'</h2>';
	print '<p>'.sprintf(_("The statistic below shows all departure airports of flights of airports &amp; airlines from <strong>%s</strong>."),$country).'</p>';

	$airport_airport_array = $Spotter->countAllDepartureAirportsByCountry($country);
	print '<script type="text/javascript" src="'.$globalURL.'/js/d3.min.js"></script>';
	print '<script type="text/javascript" src="'.$globalURL.'/js/topojson.v2.min.js"></script>';
	print '<script type="text/javascript" src="'.$globalURL.'/js/datamaps.world.min.js"></script>';
	print '<div id="chartAirport" class="chart" width="100%"></div>';
	print '<script>';
	print 'var series = [';
	$airport_data = '';
	foreach($airport_airport_array as $airport_item)
	{
		$airport_data .= '[ "'.$airport_item['airport_departure_icao_count'].'", "'.$airport_item['airport_departure_icao'].'",'.$airport_item['airport_departure_latitude'].','.$airport_item['airport_departure_longitude'].'],';
	}
	$airport_data = substr($airport_data, 0, -1);
	print $airport_data;
	print '];'."\n";
	print 'var onlyValues = series.map(function(obj){ return obj[0]; });var minValue = Math.min.apply(null, onlyValues), maxValue = Math.max.apply(null, onlyValues);'."\n";
	print 'var paletteScale = d3.scale.log().domain([minValue,maxValue]).range(["#EFEFFF","#001830"]);'."\n";
	print 'var radiusScale = d3.scale.log().domain([minValue,maxValue]).range([2,20]);'."\n";
	print 'var dataset = [];'."\n";
	print 'var colorset = [];'."\n";
	print 'colorset["defaultFill"] = "#F5F5F5";';
	print 'series.forEach(function(item){'."\n";
	print 'var cnt = item[0], nm = item[1], lat = item[2], long = item[3];'."\n";
	print 'colorset[nm] = paletteScale(cnt);';
	print 'dataset.push({ count: cnt, name: nm, radius: Math.floor(radiusScale(cnt)), latitude: lat, longitude: long, fillKey: nm });'."\n";
	print '});'."\n";
	print 'var bbl = new Datamap({
	    element: document.getElementById("chartAirport"),
	    projection: "mercator", // big world map
	    fills: colorset,
	    responsive: true,
	    geographyConfig: {
		borderColor: "#DEDEDE",
		highlightBorderWidth: 2,
		highlightFillColor: function(geo) {
		    return geo["fillColor"] || "#F5F5F5";
		},
		highlightBorderColor: "#B7B7B7"},
		done: function(datamap) {
		    datamap.svg.call(d3.behavior.zoom().on("zoom", redraw));
		    function redraw() {
			datamap.svg.selectAll("g").attr("transform", "translate(" + d3.event.translate + ")scale(" + d3.event.scale + ")");
		    }
		}
	    });
	    bbl.bubbles(dataset,{
		popupTemplate: function(geo, data) {
		    if (!data) { return ; }
		    return ['."'".'<div class="hoverinfo">'."','<strong>', data.name, '</strong>','<br>Count: <strong>', data.count, '</strong>','</div>'].join('');
		}
	    });";
	print '</script>';
	print '<div class="table-responsive">';
	print '<table class="common-airport table-striped">';
	print '<thead>';
	print '<th></th>';
	print '<th>'._("Airport").'</th>';
	print '<th>'._("Country").'</th>';
	print '<th>'._("# of times").'</th>';
	print '</thead>';
	print '<tbody>';
	$i = 1;
	foreach($airport_airport_array as $airport_item)
	{
		print '<tr>';
		print '<td><strong>'.$i.'</strong></td>';
		print '<td>';
		print '<a href="'.$globalURL.'/airport/'.$airport_item['airport_departure_icao'].'">'.$airport_item['airport_departure_name'].', '.$airport_item['airport_departure_country'].' ('.$airport_item['airport_departure_icao'].')</a>';
		print '</td>';
		print '<td>';
		print '<a href="'.$globalURL.'/country/'.strtolower(str_replace(" ", "-", $airport_item['airport_departure_country'])).'">'.$airport_item['airport_departure_country'].'</a>';
		print '</td>';
		print '<td>';
		print $airport_item['airport_departure_icao_count'];
		print '</td>';
		print '</tr>';
		$i++;
	}
	print '<tbody>';
	print '</table>';
	print '</div>';
	print '</div>';
} else {
	$title = _("Country");
	require_once('header.php');
	print '<h1>'._("Error").'</h1>';
	print '<p>'._("Sorry, the country does not exist in this database. :(").'</p>';  
}
require_once('footer.php');
?>