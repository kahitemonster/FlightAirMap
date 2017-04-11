CREATE TABLE IF NOT EXISTS `marine_live` (
  `marine_live_id` int(11) NOT NULL AUTO_INCREMENT,
  `fammarine_id` varchar(50) NOT NULL,
  `ident` varchar(255) DEFAULT NULL,
  `mmsi` varchar(255) DEFAULT NULL,
  `imo` varchar(255) DEFAULT NULL,
  `type` varchar(255) DEFAULT NULL,
  `type_id` int(11) DEFAULT NULL,
  `departure_port_name` varchar(255) DEFAULT NULL,
  `departure_port_city` varchar(255) DEFAULT NULL,
  `departure_port_country` varchar(255) DEFAULT NULL,
  `departure_port_date` timestamp NULL DEFAULT NULL,
  `arrival_port_name` varchar(255),
  `arrival_port_city` varchar(255),
  `arrival_port_country` varchar(255),
  `arrival_port_date` timestamp NULL DEFAULT NULL,
  `status` varchar(255) DEFAULT NULL,
  `status_id` int(11) DEFAULT NULL,
  `over_country` varchar(255) DEFAULT NULL,
  `date` timestamp NOT NULL DEFAULT 0,
  `latitude` float NOT NULL,
  `longitude` float NOT NULL,
  `heading` int(11) NOT NULL,
  `ground_speed` int(11),
  `format_source` varchar(255) DEFAULT NULL,
  `source_name` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`marine_live_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=0 ;

ALTER TABLE `marine_output` ADD INDEX(`fammarine_id`);
ALTER TABLE `marine_output` ADD INDEX(`date`);
