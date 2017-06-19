<?php
require_once('require/class.Connection.php');
require_once('require/class.Spotter.php');
require_once('require/class.Stats.php');
require_once('require/class.Language.php');
if (!isset($_GET['aircraft_manufacturer'])) {
        header('Location: '.$globalURL.'/manufacturer');
        die();
}
$Spotter = new Spotter();
$manufacturer = ucwords(str_replace("-", " ", filter_input(INPUT_GET,'aircraft_manufacturer',FILTER_SANITIZE_STRING)));

$sort = filter_input(INPUT_GET,'sort',FILTER_SANITIZE_STRING);
$spotter_array = $Spotter->getSpotterDataByManufacturer($manufacturer,"0,1", $sort);

if (!empty($spotter_array))
{
	$title = sprintf(_("Most Common Time of Day from %s"),$manufacturer);
	require_once('header.php');
	print '<div class="select-item">';
	print '<form action="'.$globalURL.'/manufacturer" method="post">';
	print '<select name="aircraft_manufacturer" class="selectpicker" data-live-search="true">';
	$Stats = new Stats();
	$all_manufacturers = $Stats->getAllManufacturers();
	if (empty($all_manufacturers)) $all_manufacturers = $Spotter->getAllManufacturers();
	foreach($all_manufacturers as $all_manufacturer)
	{
		if($_GET['aircraft_manufacturer'] == strtolower(str_replace(" ", "-", $all_manufacturer['aircraft_manufacturer'])))
		{
			print '<option value="'.strtolower(str_replace(" ", "-", $all_manufacturer['aircraft_manufacturer'])).'" selected="selected">'.$all_manufacturer['aircraft_manufacturer'].'</option>';
		} else {
			print '<option value="'.strtolower(str_replace(" ", "-", $all_manufacturer['aircraft_manufacturer'])).'">'.$all_manufacturer['aircraft_manufacturer'].'</option>';
		}
	}
	print '</select>';
	print '<button type="submit"><i class="fa fa-angle-double-right"></i></button>';
	print '</form>';
	print '</div>';

	print '<div class="info column">';
	print '<h1>'.$manufacturer.'</h1>';
	print '</div>';

	include('manufacturer-sub-menu.php');
	print '<div class="column">';
	print '<h2>'._("Most Common Time of Day").'</h2>';
	print '<p>'.sprintf(_("The statistic below shows the most common time of day from <strong>%s</strong>."),$manufacturer).'</p>';

	$hour_array = $Spotter->countAllHoursByManufacturer($manufacturer);
	print '<link href="'.$globalURL.'/css/c3.min.css" rel="stylesheet" type="text/css">';
	print '<script type="text/javascript" src="'.$globalURL.'/js/d3.min.js"></script>';
	print '<script type="text/javascript" src="'.$globalURL.'/js/c3.min.js"></script>';
	print '<div id="chartHour" class="chart" width="100%"></div><script>';
	$hour_data = '';
	$hour_cnt = '';
	$last = 0;
	foreach($hour_array as $hour_item)
	{
		while($last != $hour_item['hour_name']) {
			$hour_data .= '"'.$last.':00",';
			$hour_cnt .= '0,';
			$last++;
		}
		$last++;
		$hour_data .= '"'.$hour_item['hour_name'].':00",';
		$hour_cnt .= $hour_item['hour_count'].',';
	}
	$hour_data = "['x',".substr($hour_data, 0, -1)."]";
	$hour_cnt = "['flights',".substr($hour_cnt,0,-1)."]";
	print 'c3.generate({
	    bindto: "#chartHour",
	    data: {
		x : "x",
		xFormat: "%H:%M",
		columns: ['.$hour_cnt.','.$hour_data.'], types: { flights: "area"}, colors: { flights: "#1a3151"}
	    },
	    axis: { 
		x: { type: "timeseries", tick: { format: "%H:%M" }},
		y: { label: "# of Flights",tick: { format: d3.format("d") }}
	    },
	    legend: { show: false }
	});';
	print '</script>';
	if (!empty($hour_array))
	{
		print '<div class="table-responsive">';
		print '<table class="common-hour table-striped">';
		print '<thead>';
		print '<th>'._("Hour").'</th>';
		print '<th>'._("Number").'</th>';
		print '</thead>';
		print '<tbody>';
		$i = 1;
		foreach($hour_array as $hour_item)
		{
			print '<tr>';
			print '<td>'.$hour_item['hour_name'].':00</td>';
			print '<td>'.$hour_item['hour_count'].'</td>';
			print '</tr>';
			$i++;
		}
		print '<tbody>';
		print '</table>';
		print '</div>';
	}
	print '</div>';
} else {
	$title = _("Manufacturer");
	require_once('header.php');
	print '<h1>'._("Error").'</h1>';
	print '<p>'._("Sorry, the aircraft manufacturer does not exist in this database. :(").'</p>';
}

require_once('footer.php');
?>