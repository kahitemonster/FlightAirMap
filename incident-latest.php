<?php
require_once('require/class.Connection.php');
require_once('require/class.Accident.php');
require_once('require/class.Language.php');
$Accident = new Accident();
$title = _("Latest incidents");
require_once('header.php');

$page_url = $globalURL.'/accident-latest';

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
    

print '<div class="info column">';
print '<h1>'._("Latest Incidents").'</h1>';
print '</div>';

print '<div class="table column">';
print '<p>'._("The table below shows the latest Incidents.").'</p>';
$spotter_array = $Accident->getAccidentData($limit_start.",".$absolute_difference,'incident');
//print_r($spotter_array);
if (!empty($spotter_array) && $spotter_array[0]['query_number_rows'] != 0) {
	include('table-output.php');
	print '<div class="pagination">';
	if ($limit_previous_1 >= 0) print '<a href="'.$page_url.'/'.$limit_previous_1.','.$limit_previous_2.'">&laquo;'._("Previous Page").'</a>';
	if ($spotter_array[0]['query_number_rows'] == $absolute_difference) print '<a href="'.$page_url.'/'.$limit_end.','.$limit_next.'">'._("Next Page").'&raquo;</a>';
	print '</div>';
}
print '</div>';

require_once('footer.php');
?>