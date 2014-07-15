<span class="sub-menu-statistic column mobile">
	<a href="#" onclick="showSubMenu(); return false;">Statistics <i class="fa fa-plus"></i></a>
</span>
<div class="sub-menu sub-menu-container">
	<ul class="nav nav-pills">
		<li><a href="<?php print $globalURL; ?>/route/<?php print $_GET['departure_airport']; ?>/<?php print $_GET['arrival_airport']; ?>" <?php if (strtolower($current_page) == "route-overview"){ print 'class="active"'; } ?>>Overview</a></li>
		<li><a href="<?php print $globalURL; ?>/route/detailed/<?php print $_GET['departure_airport']; ?>/<?php print $_GET['arrival_airport']; ?>" <?php if (strtolower($current_page) == "route-detailed"){ print 'class="active"'; } ?>>Detailed</a></li>
		<li class="dropdown">
		    <a class="dropdown-toggle <?php if(strtolower($current_page) == "route-statistics-aircraft" || strtolower($current_page) == "route-statistics-registration" || strtolower($current_page) == "route-statistics-manufacturer"){ print 'active'; } ?>" data-toggle="dropdown" href="#">
		      Aircraft <span class="caret"></span>
		    </a>
		    <ul class="dropdown-menu" role="menu">
		      <li><a href="<?php print $globalURL; ?>/route/statistics/aircraft/<?php print $_GET['departure_airport']; ?>/<?php print $_GET['arrival_airport']; ?>">Aircraft Type</a></li>
					<li><a href="<?php print $globalURL; ?>/route/statistics/registration/<?php print $_GET['departure_airport']; ?>/<?php print $_GET['arrival_airport']; ?>">Registration</a></li>
					<li><a href="<?php print $globalURL; ?>/route/statistics/manufacturer/<?php print $_GET['departure_airport']; ?>/<?php print $_GET['arrival_airport']; ?>">Manufacturer</a></li>
		    </ul>
		</li>
		<li class="dropdown">
		    <a class="dropdown-toggle <?php if(strtolower($current_page) == "route-statistics-airline" || strtolower($current_page) == "route-statistics-airline-country"){ print 'active'; } ?>" data-toggle="dropdown" href="#">
		      Airline <span class="caret"></span>
		    </a>
		    <ul class="dropdown-menu" role="menu">
		      <li><a href="<?php print $globalURL; ?>/route/statistics/airline/<?php print $_GET['departure_airport']; ?>/<?php print $_GET['arrival_airport']; ?>">Airline</a></li>
			  <li><a href="<?php print $globalURL; ?>/route/statistics/airline-country/<?php print $_GET['departure_airport']; ?>/<?php print $_GET['arrival_airport']; ?>">Airline by Country</a></li>
		    </ul>
		</li>
		<li><a href="<?php print $globalURL; ?>/route/statistics/time/<?php print $_GET['departure_airport']; ?>/<?php print $_GET['arrival_airport']; ?>" <?php if (strtolower($current_page) == "route-statistics-time"){ print 'class="active"'; } ?>>Time</a></li>
	</ul>
</div>