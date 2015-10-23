<?php
require('require/class.Connection.php');
require('require/class.Spotter.php');
$Spotter = new Spotter();
header('Content-Type: text/xml');

date_default_timezone_set('UTC');
$output = '';
if (isset($_GET['type']) && $_GET['type'] == "flight")
{
	$output .= '<?xml version="1.0" encoding="UTF-8"?>';
	$output .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9" xmlns:image="http://www.google.com/schemas/sitemap-image/1.1">';

		$spotter_array = $Spotter->getAllFlightsforSitemap();
		foreach($spotter_array as $spotter_item)
		{
			$output .= '<url>';
			    $output .= '<loc>'.'http://'.$_SERVER['HTTP_HOST'].$globalURL.'/flightid/'.$spotter_item['spotter_id'].'</loc>';
			    $output .= '<lastmod>'.date("c", time()).'</lastmod>';
			    $output .= '<changefreq>weekly</changefreq>';
			$output .= '</url>';
		}
	$output .= '</urlset>';
	
	
} else if (isset($_GET['type']) && $_GET['type'] == "aircraft"){

	$output .= '<?xml version="1.0" encoding="UTF-8"?>';
	$output .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9" xmlns:image="http://www.google.com/schemas/sitemap-image/1.1">';

		$aircraft_types = $Spotter->getAllAircraftTypes();
		foreach($aircraft_types as $aircraft_item)
		{
			$output .= '<url>';
			    $output .= '<loc>'.'http://'.$_SERVER['HTTP_HOST'].$globalURL.'/aircraft/'.$aircraft_item['aircraft_icao'].'</loc>';
			    $output .= '<lastmod>'.date("c", time()).'</lastmod>';
			    $output .= '<changefreq>daily</changefreq>';
			$output .= '</url>';
		}
	$output .= '</urlset>';
	
	
} else if (isset($_GET['type']) && $_GET['type'] == "registration"){

	$output .= '<?xml version="1.0" encoding="UTF-8"?>';
	$output .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9" xmlns:image="http://www.google.com/schemas/sitemap-image/1.1">';

		$aircraft_registrations = $Spotter->getAllAircraftRegistrations();
		foreach($aircraft_registrations as $aircraft_item)
		{
			$output .= '<url>';
			    $output .= '<loc>'.'http://'.$_SERVER['HTTP_HOST'].$globalURL.'/registration/'.$aircraft_item['registration'].'</loc>';
			    $output .= '<lastmod>'.date("c", time()).'</lastmod>';
			    $output .= '<changefreq>daily</changefreq>';
			$output .= '</url>';
		}
	$output .= '</urlset>';
	
} else if (isset($_GET['type']) && $_GET['type'] == "airline"){

	$output .= '<?xml version="1.0" encoding="UTF-8"?>';
	$output .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9" xmlns:image="http://www.google.com/schemas/sitemap-image/1.1">';

		$airline_names = $Spotter->getAllAirlineNames();
		foreach($airline_names as $airline_item)
		{
			$output .= '<url>';
			    $output .= '<loc>'.'http://'.$_SERVER['HTTP_HOST'].$globalURL.'/airline/'.$airline_item['airline_icao'].'</loc>';
			    $output .= '<lastmod>'.date("c", time()).'</lastmod>';
			    $output .= '<changefreq>daily</changefreq>';
			$output .= '</url>';
		}
	$output .= '</urlset>';
	
} else if (isset($_GET['type']) && $_GET['type'] == "airport"){

	$output .= '<?xml version="1.0" encoding="UTF-8"?>';
	$output .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9" xmlns:image="http://www.google.com/schemas/sitemap-image/1.1">';

		$airport_names = $Spotter->getAllAirportNames();
		foreach($airport_names as $airport_item)
		{
			$output .= '<url>';
			    $output .= '<loc>'.'http://'.$_SERVER['HTTP_HOST'].$globalURL.'/airport/'.$airport_item['airport_icao'].'</loc>';
			    $output .= '<lastmod>'.date("c", time()).'</lastmod>';
			    $output .= '<changefreq>daily</changefreq>';
			$output .= '</url>';
		}
	$output .= '</urlset>';
	
} else if (isset($_GET['type']) && $_GET['type'] == "manufacturer"){

	$output .= '<?xml version="1.0" encoding="UTF-8"?>';
	$output .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9" xmlns:image="http://www.google.com/schemas/sitemap-image/1.1">';

		$manufacturer_names = $Spotter->getAllManufacturers();
		foreach($manufacturer_names as $manufacturer_item)
		{
			$output .= '<url>';
			    $output .= '<loc>'.'http://'.$_SERVER['HTTP_HOST'].$globalURL.'/manufacturer/'.strtolower(str_replace(" ", "-", $manufacturer_item['aircraft_manufacturer'])).'</loc>';
			    $output .= '<lastmod>'.date("c", time()).'</lastmod>';
			    $output .= '<changefreq>daily</changefreq>';
			$output .= '</url>';
		}
	$output .= '</urlset>';
	
} else if (isset($_GET['type']) && $_GET['type'] == "country"){

	$output .= '<?xml version="1.0" encoding="UTF-8"?>';
	$output .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9" xmlns:image="http://www.google.com/schemas/sitemap-image/1.1">';

		$country_names = $Spotter->getAllCountries();
		foreach($country_names as $country_item)
		{
			$output .= '<url>';
			    $output .= '<loc>'.'http://'.$_SERVER['HTTP_HOST'].$globalURL.'/country/'.strtolower(str_replace(" ", "-", $country_item['country'])).'</loc>';
			    $output .= '<lastmod>'.date("c", time()).'</lastmod>';
			    $output .= '<changefreq>daily</changefreq>';
			$output .= '</url>';
		}
	$output .= '</urlset>';
	
} else if (isset($_GET['type']) && $_GET['type'] == "ident"){

	$output .= '<?xml version="1.0" encoding="UTF-8"?>';
	$output .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9" xmlns:image="http://www.google.com/schemas/sitemap-image/1.1">';

		$ident_names = $Spotter->getAllIdents();
		foreach($ident_names as $ident_item)
		{
			$output .= '<url>';
			    $output .= '<loc>'.'http://'.$_SERVER['HTTP_HOST'].$globalURL.'/ident/'.$ident_item['ident'].'</loc>';
			    $output .= '<lastmod>'.date("c", time()).'</lastmod>';
			    $output .= '<changefreq>daily</changefreq>';
			$output .= '</url>';
		}
	$output .= '</urlset>';
	
} else if (isset($_GET['type']) && $_GET['type'] == "date"){

	$output .= '<?xml version="1.0" encoding="UTF-8"?>';
	$output .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9" xmlns:image="http://www.google.com/schemas/sitemap-image/1.1">';

		$date_names = $Spotter->getAllDates();
		foreach($date_names as $date_item)
		{
			$output .= '<url>';
			    $output .= '<loc>'.'http://'.$_SERVER['HTTP_HOST'].$globalURL.'/date/'.date("Y-m-d", strtotime($date_item['date'])).'</loc>';
			    $output .= '<lastmod>'.date("c", time()).'</lastmod>';
			    $output .= '<changefreq>daily</changefreq>';
			$output .= '</url>';
		}
	$output .= '</urlset>';
	
} else if (isset($_GET['type']) && $_GET['type'] == "route"){

	$output .= '<?xml version="1.0" encoding="UTF-8"?>';
	$output .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9" xmlns:image="http://www.google.com/schemas/sitemap-image/1.1">';

		$route_names = $Spotter->getAllRoutes();
		foreach($route_names as $route_item)
		{
			$output .= '<url>';
			    $output .= '<loc>'.'http://'.$_SERVER['HTTP_HOST'].$globalURL.'/route/'.$route_item['airport_departure_icao'].'/'.$route_item['airport_arrival_icao'].'</loc>';
			    $output .= '<lastmod>'.date("c", time()).'</lastmod>';
			    $output .= '<changefreq>daily</changefreq>';
			$output .= '</url>';
		}
	$output .= '</urlset>';
	
} else if (isset($_GET['type']) && $_GET['type'] == "static"){

	$output .= '<?xml version="1.0" encoding="UTF-8"?>';
	$output .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9" xmlns:image="http://www.google.com/schemas/sitemap-image/1.1">';
		
		/* STATIC PAGES */
		$output .= '<url>';
		    $output .= '<loc>'.'http://'.$_SERVER['HTTP_HOST'].$globalURL.'/</loc>';
		    $output .= '<lastmod>'.date("c", time()).'</lastmod>';
		    $output .= '<changefreq>daily</changefreq>';
		$output .= '</url>';
		$output .= '<url>';
		    $output .= '<loc>'.'http://'.$_SERVER['HTTP_HOST'].$globalURL.'/latest</loc>';
		    $output .= '<lastmod>'.date("c", time()).'</lastmod>';
		    $output .= '<changefreq>daily</changefreq>';
		$output .= '</url>';
		$output .= '<url>';
		    $output .= '<loc>'.'http://'.$_SERVER['HTTP_HOST'].$globalURL.'/highlights</loc>';
		    $output .= '<lastmod>'.date("c", time()).'</lastmod>';
		    $output .= '<changefreq>daily</changefreq>';
		$output .= '</url>';
		$output .= '<url>';
		    $output .= '<loc>'.'http://'.$_SERVER['HTTP_HOST'].$globalURL.'/aircraft</loc>';
		    $output .= '<lastmod>'.date("c", time()).'</lastmod>';
		    $output .= '<changefreq>daily</changefreq>';
		$output .= '</url>';
		$output .= '<url>';
		    $output .= '<loc>'.'http://'.$_SERVER['HTTP_HOST'].$globalURL.'/airline</loc>';
		    $output .= '<lastmod>'.date("c", time()).'</lastmod>';
		    $output .= '<changefreq>daily</changefreq>';
		$output .= '</url>';
		$output .= '<url>';
		    $output .= '<loc>'.'http://'.$_SERVER['HTTP_HOST'].$globalURL.'/airport</loc>';
		    $output .= '<lastmod>'.date("c", time()).'</lastmod>';
		    $output .= '<changefreq>daily</changefreq>';
		$output .= '</url>';
		$output .= '<url>';
		    $output .= '<loc>'.'http://'.$_SERVER['HTTP_HOST'].$globalURL.'/search</loc>';
		    $output .= '<lastmod>'.date("c", time()).'</lastmod>';
		    $output .= '<changefreq>daily</changefreq>';
		$output .= '</url>';
		$output .= '<url>';
		    $output .= '<loc>'.'http://'.$_SERVER['HTTP_HOST'].$globalURL.'/about</loc>';
		    $output .= '<lastmod>'.date("c", time()).'</lastmod>';
		    $output .= '<changefreq>weekly</changefreq>';
		$output .= '</url>';
		
		
		/* STATISTIC PAGES */
		$output .= '<url>';
		    $output .= '<loc>'.'http://'.$_SERVER['HTTP_HOST'].$globalURL.'/statistics/aircraft</loc>';
		    $output .= '<lastmod>'.date("c", time()).'</lastmod>';
		    $output .= '<changefreq>daily</changefreq>';
		$output .= '</url>';
		$output .= '<url>';
		    $output .= '<loc>'.'http://'.$_SERVER['HTTP_HOST'].$globalURL.'/statistics/registration</loc>';
		    $output .= '<lastmod>'.date("c", time()).'</lastmod>';
		    $output .= '<changefreq>daily</changefreq>';
		$output .= '</url>';
		$output .= '<url>';
		    $output .= '<loc>'.'http://'.$_SERVER['HTTP_HOST'].$globalURL.'/statistics/manufacturer</loc>';
		    $output .= '<lastmod>'.date("c", time()).'</lastmod>';
		    $output .= '<changefreq>daily</changefreq>';
		$output .= '</url>';
		$output .= '<url>';
		    $output .= '<loc>'.'http://'.$_SERVER['HTTP_HOST'].$globalURL.'/statistics/airline</loc>';
		    $output .= '<lastmod>'.date("c", time()).'</lastmod>';
		    $output .= '<changefreq>daily</changefreq>';
		$output .= '</url>';
		$output .= '<url>';
		    $output .= '<loc>'.'http://'.$_SERVER['HTTP_HOST'].$globalURL.'/statistics/airline-country</loc>';
		    $output .= '<lastmod>'.date("c", time()).'</lastmod>';
		    $output .= '<changefreq>daily</changefreq>';
		$output .= '</url>';
		$output .= '<url>';
		    $output .= '<loc>'.'http://'.$_SERVER['HTTP_HOST'].$globalURL.'/statistics/airport-departure</loc>';
		    $output .= '<lastmod>'.date("c", time()).'</lastmod>';
		    $output .= '<changefreq>daily</changefreq>';
		$output .= '</url>';
		$output .= '<url>';
		    $output .= '<loc>'.'http://'.$_SERVER['HTTP_HOST'].$globalURL.'/statistics/airport-departure-country</loc>';
		    $output .= '<lastmod>'.date("c", time()).'</lastmod>';
		    $output .= '<changefreq>daily</changefreq>';
		$output .= '</url>';
		$output .= '<url>';
		    $output .= '<loc>'.'http://'.$_SERVER['HTTP_HOST'].$globalURL.'/statistics/airport-arrival</loc>';
		    $output .= '<lastmod>'.date("c", time()).'</lastmod>';
		    $output .= '<changefreq>daily</changefreq>';
		$output .= '</url>';
		$output .= '<url>';
		    $output .= '<loc>'.'http://'.$_SERVER['HTTP_HOST'].$globalURL.'/statistics/airport-arrival-country</loc>';
		    $output .= '<lastmod>'.date("c", time()).'</lastmod>';
		    $output .= '<changefreq>daily</changefreq>';
		$output .= '</url>';
		$output .= '<url>';
		    $output .= '<loc>'.'http://'.$_SERVER['HTTP_HOST'].$globalURL.'/statistics/route-airport</loc>';
		    $output .= '<lastmod>'.date("c", time()).'</lastmod>';
		    $output .= '<changefreq>daily</changefreq>';
		$output .= '</url>';
		$output .= '<url>';
		    $output .= '<loc>'.'http://'.$_SERVER['HTTP_HOST'].$globalURL.'/statistics/route-waypoint</loc>';
		    $output .= '<lastmod>'.date("c", time()).'</lastmod>';
		    $output .= '<changefreq>daily</changefreq>';
		$output .= '</url>';
		$output .= '<url>';
		    $output .= '<loc>'.'http://'.$_SERVER['HTTP_HOST'].$globalURL.'/statistics/callsign</loc>';
		    $output .= '<lastmod>'.date("c", time()).'</lastmod>';
		    $output .= '<changefreq>daily</changefreq>';
		$output .= '</url>';
		$output .= '<url>';
		    $output .= '<loc>'.'http://'.$_SERVER['HTTP_HOST'].$globalURL.'/statistics/date</loc>';
		    $output .= '<lastmod>'.date("c", time()).'</lastmod>';
		    $output .= '<changefreq>daily</changefreq>';
		$output .= '</url>';
		$output .= '<url>';
		    $output .= '<loc>'.'http://'.$_SERVER['HTTP_HOST'].$globalURL.'/statistics/time</loc>';
		    $output .= '<lastmod>'.date("c", time()).'</lastmod>';
		    $output .= '<changefreq>daily</changefreq>';
		$output .= '</url>';
	$output .= '</urlset>';
	
} else {
	
	
	$output .= '<?xml version="1.0" encoding="UTF-8"?>';
	$output .= '<sitemapindex xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">';
		$output .= '<sitemap>';
	    	$output .= '<loc>'.'http://'.$_SERVER['HTTP_HOST'].$globalURL.'/sitemap/static</loc>';
		$output .= '</sitemap>';
		$output .= '<sitemap>';
	    	$output .= '<loc>'.'http://'.$_SERVER['HTTP_HOST'].$globalURL.'/sitemap/flight</loc>';
		$output .= '</sitemap>';
		$output .= '<sitemap>';
	    	$output .= '<loc>'.'http://'.$_SERVER['HTTP_HOST'].$globalURL.'/sitemap/aircraft</loc>';
		$output .= '</sitemap>';
		$output .= '<sitemap>';
	    	$output .= '<loc>'.'http://'.$_SERVER['HTTP_HOST'].$globalURL.'/sitemap/registration</loc>';
		$output .= '</sitemap>';
		$output .= '<sitemap>';
	    	$output .= '<loc>'.'http://'.$_SERVER['HTTP_HOST'].$globalURL.'/sitemap/airline</loc>';
		$output .= '</sitemap>';
		$output .= '<sitemap>';
	    	$output .= '<loc>'.'http://'.$_SERVER['HTTP_HOST'].$globalURL.'/sitemap/airport</loc>';
		$output .= '</sitemap>';
		$output .= '<sitemap>';
	    	$output .= '<loc>'.'http://'.$_SERVER['HTTP_HOST'].$globalURL.'/sitemap/manufacturer</loc>';
		$output .= '</sitemap>';
		$output .= '<sitemap>';
	    	$output .= '<loc>'.'http://'.$_SERVER['HTTP_HOST'].$globalURL.'/sitemap/country</loc>';
		$output .= '</sitemap>';
		$output .= '<sitemap>';
	    	$output .= '<loc>'.'http://'.$_SERVER['HTTP_HOST'].$globalURL.'/sitemap/ident</loc>';
		$output .= '</sitemap>';
		$output .= '<sitemap>';
	    	$output .= '<loc>'.'http://'.$_SERVER['HTTP_HOST'].$globalURL.'/sitemap/date</loc>';
		$output .= '</sitemap>';
		$output .= '<sitemap>';
	    	$output .= '<loc>'.'http://'.$_SERVER['HTTP_HOST'].$globalURL.'/sitemap/route</loc>';
		$output .= '</sitemap>';
	$output .= '</sitemapindex>';
	
}

print $output;

?>