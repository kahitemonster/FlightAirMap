<?php
/*
Copyright 2014 Aaron Gong Hsien-Joen <aaronjxz@gmail.com>

Licensed under the Apache License, Version 2.0 (the "License");
you may not use this file except in compliance with the License.
You may obtain a copy of the License at

    http://www.apache.org/licenses/LICENSE-2.0

Unless required by applicable law or agreed to in writing, software
distributed under the License is distributed on an "AS IS" BASIS,
WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
See the License for the specific language governing permissions and
limitations under the License.
*/
/*
Modified in 2017 by Ycarus <ycarus@zugaina.org>
Original version come from https://github.com/ais-one/phpais
*/
class AIS {
/* AIS Decoding
- Receive and get ITU payload
- Organises the binary bits of the Payload into 6-bit strings,
- Converts the 6-bit strings into their representative "valid characters" – see IEC 61162-1, table 7,
- Assembles the valid characters into an encapsulation string, and
- Transfers the encapsulation string using the VDM sentence formatter.
*/

	private function make_latf($temp) { // unsigned long 
		$flat = 0.0; // float
		$temp = $temp & 0x07FFFFFF;
		if ($temp & 0x04000000) {
			$temp = $temp ^ 0x07FFFFFF;
			$temp += 1;
			$flat = (float)($temp / (60.0 * 10000.0));
			$flat *= -1.0;
		} else $flat = (float)($temp / (60.0 * 10000.0));
		return $flat; // float
	}

	private function make_lonf($temp) { // unsigned long
		$flon = 0.0; // float
		$temp = $temp & 0x0FFFFFFF;
		if ($temp & 0x08000000) {
			$temp = $temp ^ 0x0FFFFFFF;
			$temp += 1;
			$flon = (float)($temp / (60.0 * 10000.0));
			$flon *= -1.0;
		} else $flon = (float)($temp / (60.0 * 10000.0));
		return $flon;
	}

	private function ascii_2_dec($chr) {
		$dec=ord($chr);//get decimal ascii code
		$hex=dechex($dec);//convert decimal to hex
		return ($dec);
	}
	
    /*
    $ais_map64 = array(
       '0', '1', '2', '3', '4', '5', '6', '7', '8', '9', // 48
       ':', ';', '<', '=', '>', '?', '@', 'A', 'B', 'C',
       'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L', 'M',
       'N', 'O', 'P', 'Q', 'R', 'S', 'T', 'U', 'V', 'W', // 87
       '`', 'a', 'b', 'c', 'd', 'e', 'f', 'g', 'h', 'i', // 96
       'j', 'k', 'l', 'm', 'n', 'o', 'p', 'q', 'r', 's',
       't', 'u', 'v', 'w' // 119
    ); // char 64
    */
	private function asciidec_2_8bit($ascii) {
		//only process in the following range: 48-87, 96-119
		if ($ascii < 48) { }
		else {
			if($ascii>119) { }
			else {
				if ($ascii>87 && $ascii<96) ;
				else {
					$ascii=$ascii+40;
					if ($ascii>128){
						$ascii=$ascii+32;
					} else {
						$ascii=$ascii+40;
					}
				}
			}
		}
		return ($ascii);
	}

	private function dec_2_6bit($dec) {
		$bin=decbin($dec);
		return(substr($bin, -6)); 
	}

	private function binchar($_str, $_start, $_size) {
		//  ' ' --- '?', // 0x20 - 0x3F
		//  '@' --- '_', // 0x40 - 0x5F
		$ais_chars = array(
		    '@', 'A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I',
		    'J', 'K', 'L', 'M', 'N', 'O', 'P', 'Q', 'R', 'S',
		    'T', 'U', 'V', 'W', 'X', 'Y', 'Z', '[', '\\', ']',
		    '^', '_', ' ', '!', '\"', '#', '$', '%', '&', '\'',
		    '(', ')', '*', '+', ',', '-', '.', '/', '0', '1',
		    '2', '3', '4', '5', '6', '7', '8', '9', ':', ';',
		    '<', '=', '>', '?'
		);
		// "
		$rv = '';
		if ($_size % 6 == 0) {
			$len = $_size / 6;
			for ($i=0; $i<$len; $i++) {
				$offset = $i * 6;
				$rv .= $ais_chars[ bindec(substr($_str,$_start + $offset,6)) ];
			}
		}
		return $rv;
	}

	// function for decoding the AIS Message ITU Payload
	private function decode_ais($_aisdata) {
		$ro = new stdClass(); // return object
		$ro->cls = 0; // AIS class undefined, also indicate unparsed msg
		$ro->name = '';
		$ro->status = '';
		$ro->callsign = '';
		$ro->imo = '';
		$ro->typeid = '';
		$ro->type = '';
		$ro->sog = -1.0;
		$ro->cog = 0.0;
		$ro->lon = 0.0;
		$ro->lat = 0.0;
		$ro->heading = '';
		$ro->destination = '';
		$ro->eta_month = '';
		$ro->eta_day = '';
		$ro->eta_hour = '';
		$ro->eta_minute = '';
		$ro->ts = time();
		$ro->id = bindec(substr($_aisdata,0,6));
		$ro->mmsi = bindec(substr($_aisdata,8,30));
		if ($ro->id >= 1 && $ro->id <= 3) {
			$ro->cog = bindec(substr($_aisdata,116,12))/10;
			$ro->sog = bindec(substr($_aisdata,50,10))/10;
			$ro->lon = $this->make_lonf(bindec(substr($_aisdata,61,28)));
			$ro->lat = $this->make_latf(bindec(substr($_aisdata,89,27)));
			$ro->cls = 1; // class A
		} else if ($ro->id == 4) {
			$ro->lon = $this->make_lonf(bindec(substr($_aisdata,79,28)));
			$ro->lat = $this->make_latf(bindec(substr($_aisdata,107,27)));
			$ro->cls = 1; // class A
		} else if ($ro->id == 5) {
			$ro->imo = bindec(substr($_aisdata,40,30));
			$ro->callsign = $this->binchar($_aisdata,70,42);
			$ro->name = $this->binchar($_aisdata,112,120);
			$ro->typeid = bindec(substr($_aisdata,232,8));
			$ro->type = $this->getShipType($ro->typeid);
			//$ro->to_bow = bindec(substr($_aisdata,240,9));
			//$ro->to_stern = bindec(substr($_aisdata,249,9));
			//$ro->to_port = bindec(substr($_aisdata,258,6));
			//$ro->to_starboard = bindec(substr($_aisdata,264,6));
			$ro->eta_month = bindec(substr($_aisdata,274,4));
			$ro->eta_day = bindec(substr($_aisdata,278,5));
			$ro->eta_hour = bindec(substr($_aisdata,283,5));
			$ro->eta_minute = bindec(substr($_aisdata,288,6));
			//$ro->draught = bindec(substr($_aisdata,294,8));
			$ro->destination = $this->binchar($_aisdata,302,120);
			$ro->cls = 1; // class A
		} else if ($ro->id == 9) {
			// Search and Rescue aircraft position report
		} else if ($ro->id == 18) {
			$ro->cog = bindec(substr($_aisdata,112,12))/10;
			$ro->sog = bindec(substr($_aisdata,46,10))/10;
			$ro->lon = $this->make_lonf(bindec(substr($_aisdata,57,28)));
			$ro->lat = $this->make_latf(bindec(substr($_aisdata,85,27)));
			$ro->heading = bindec(substr($_aisdata,124,9));
			if ($ro->heading == 511) $ro->heading = '';
			$ro->cls = 2; // class B
		} else if ($ro->id == 19) {
			$ro->cog = bindec(substr($_aisdata,112,12))/10;
			$ro->sog = bindec(substr($_aisdata,46,10))/10;
			$ro->lon = $this->make_lonf(bindec(substr($_aisdata,61,28)));
			$ro->lat = $this->make_latf(bindec(substr($_aisdata,89,27)));
			$ro->name = $this->binchar($_aisdata,143,120);
			$ro->cls = 2; // class B
			$ro->heading = bindec(substr($_aisdata,124,9));
			if ($ro->heading == 511) $ro->heading = '';
			$ro->typeid = bindec(substr($_aisdata,263,8));
			$ro->type = $this->getShipType($ro->typeid);
			//$ro->to_bow = bindec(substr($_aisdata,271,9));
			//$ro->to_stern = bindec(substr($_aisdata,280,9));
			//$ro->to_port = bindec(substr($_aisdata,289,6));
			//$ro->to_starboard = bindec(substr($_aisdata,295,6));
		} else if ($ro->id == 21) {
			$ro->lon = $this->make_lonf(bindec(substr($_aisdata,164,28)));
			$ro->lat = $this->make_latf(bindec(substr($_aisdata,192,27)));
			$ro->name = $this->binchar($_aisdata,43,120);
			//$ro->to_bow = bindec(substr($_aisdata,219,9));
			//$ro->to_stern = bindec(substr($_aisdata,228,9));
			//$ro->to_port = bindec(substr($_aisdata,237,6));
			//$ro->to_starboard = bindec(substr($_aisdata,243,6));
			$ro->cls = 2; // class B
		} else if ($ro->id == 24) {
			$pn = bindec(substr($_aisdata,38,2));
			if ($pn == 0) {
				$ro->name = $this->binchar($_aisdata,40,120);
			}
			$ro->typeid = bindec(substr($_aisdata,40,8));
			$ro->type = $this->getShipType($ro->typeid);
			$ro->callsign = $this->binchar($_aisdata,90,42);
			//$ro->to_bow = bindec(substr($_aisdata,132,9));
			//$ro->to_stern = bindec(substr($_aisdata,141,9));
			//$ro->to_port = bindec(substr($_aisdata,150,6));
			//$ro->to_starboard = bindec(substr($_aisdata,156,6));
			$ro->cls = 2; // class B
		} else if ($ro->id == 27) {
			$ro->cog = bindec(substr($_aisdata,85,9));
			if ($ro->cog == 511) $ro->cog = 0.0;
			$ro->sog = bindec(substr($_aisdata,79,6));
			if ($ro->sog == 63) $ro->sog = 0.0;
			$ro->lon = $this->make_lonf(bindec(substr($_aisdata,44,18))*10);
			$ro->lat = $this->make_latf(bindec(substr($_aisdata,62,17))*10);
			$ro->cls = 1; // class A
		
		}
		$ro->statusid = bindec(substr($_aisdata,38,4));
		$ro->status = $this->getStatus($ro->statusid);
		//var_dump($ro); // dump results here for demo purpose
		return $ro;
	}

	public function getStatus($statusid) {
		if ($statusid == 0) {
			return 'under way using engine';
		} elseif ($statusid == 1) {
			return 'at anchor';
		} elseif ($statusid == 2) {
			return 'not under command';
		} elseif ($statusid == 3) {
			return 'restricted maneuverability';
		} elseif ($statusid == 4) {
			return 'constrained by her draught';
		} elseif ($statusid == 5) {
			return 'moored';
		} elseif ($statusid == 6) {
			return 'aground';
		} elseif ($statusid == 7) {
			return 'engaged in fishing';
		} elseif ($statusid == 8) {
			return 'under way sailing';
		} elseif ($statusid == 9) {
			return 'reserved for future amendment of navigational status for ships carrying DG, HS, or MP, or IMO hazard or pollutant category C, high speed craft (HSC)';
		} elseif ($statusid == 10) {
			return 'reserved for future amendment of navigational status for ships carrying dangerous goods (DG), harmful substances (HS) or marine pollutants (MP), or IMO hazard or pollutant category A, wing in ground (WIG)';
		} elseif ($statusid == 11) {
			return 'power-driven vessel towing astern (regional use)';
		} elseif ($statusid == 12) {
			return 'power-driven vessel pushing ahead or towing alongside (regional use)';
		} elseif ($statusid == 13) {
			return 'reserved for future use';
		} elseif ($statusid == 14) {
			return 'AIS-SART (active), MOB-AIS, EPIRB-AIS';
		} elseif ($statusid == 15) {
			return 'undefined = default (also used by AIS-SART, MOB-AIS and EPIRB-AIS under test)';
		}
	}
	
	public function getShipType($code) {
		if ($code == 0) return 'Not available (default)';
		elseif ($code >= 1 && $code <= 19) return 'Reserved for future use';
		elseif ($code == 20) return 'Wing in ground (WIG), all ships of this type';
		elseif ($code == 21) return 'Wing in ground (WIG), Hazardous category A';
		elseif ($code == 22) return 'Wing in ground (WIG), Hazardous category B';
		elseif ($code == 23) return 'Wing in ground (WIG), Hazardous category C';
		elseif ($code == 24) return 'Wing in ground (WIG), Hazardous category D';
		elseif ($code == 25) return 'Wing in ground (WIG), Reserved for future use';
		elseif ($code == 26) return 'Wing in ground (WIG), Reserved for future use';
		elseif ($code == 27) return 'Wing in ground (WIG), Reserved for future use';
		elseif ($code == 28) return 'Wing in ground (WIG), Reserved for future use';
		elseif ($code == 29) return 'Wing in ground (WIG), Reserved for future use';
		elseif ($code == 30) return 'Fishing';
		elseif ($code == 31) return 'Towing';
		elseif ($code == 32) return 'Towing: length exceeds 200m or breadth exceeds 25m';
		elseif ($code == 33) return 'Dredging or underwater ops';
		elseif ($code == 34) return 'Diving ops';
		elseif ($code == 35) return 'Military ops';
		elseif ($code == 36) return 'Sailing';
		elseif ($code == 37) return 'Pleasure Craft';
		elseif ($code == 38) return 'Reserved';
		elseif ($code == 39) return 'Reserved';
		elseif ($code == 40) return 'High speed craft (HSC), all ships of this type';
		elseif ($code == 41) return 'High speed craft (HSC), Hazardous category A';
		elseif ($code == 42) return 'High speed craft (HSC), Hazardous category B';
		elseif ($code == 43) return 'High speed craft (HSC), Hazardous category C';
		elseif ($code == 44) return 'High speed craft (HSC), Hazardous category D';
		elseif ($code == 45) return 'High speed craft (HSC), Reserved for future use';
		elseif ($code == 46) return 'High speed craft (HSC), Reserved for future use';
		elseif ($code == 47) return 'High speed craft (HSC), Reserved for future use';
		elseif ($code == 48) return 'High speed craft (HSC), Reserved for future use';
		elseif ($code == 49) return 'High speed craft (HSC), No additional information';
		elseif ($code == 50) return 'Pilot Vessel';
		elseif ($code == 51) return 'Search and Rescue vessel';
		elseif ($code == 52) return 'Tug';
		elseif ($code == 53) return 'Port Tender';
		elseif ($code == 54) return 'Anti-pollution equipment';
		elseif ($code == 55) return 'Law Enforcement';
		elseif ($code == 56) return 'Spare - Local Vessel';
		elseif ($code == 57) return 'Spare - Local Vessel';
		elseif ($code == 58) return 'Medical Transport';
		elseif ($code == 59) return 'Noncombatant ship according to RR Resolution No. 18';
		elseif ($code == 60) return 'Passenger, all ships of this type';
		elseif ($code == 61) return 'Passenger, Hazardous category A';
		elseif ($code == 62) return 'Passenger, Hazardous category B';
		elseif ($code == 63) return 'Passenger, Hazardous category C';
		elseif ($code == 64) return 'Passenger, Hazardous category D';
		elseif ($code == 65) return 'Passenger, Reserved for future use';
		elseif ($code == 66) return 'Passenger, Reserved for future use';
		elseif ($code == 67) return 'Passenger, Reserved for future use';
		elseif ($code == 68) return 'Passenger, Reserved for future use';
		elseif ($code == 69) return 'Passenger, No additional information';
		elseif ($code == 70) return 'Cargo, all ships of this type';
		elseif ($code == 71) return 'Cargo, Hazardous category A';
		elseif ($code == 72) return 'Cargo, Hazardous category B';
		elseif ($code == 73) return 'Cargo, Hazardous category C';
		elseif ($code == 74) return 'Cargo, Hazardous category D';
		elseif ($code == 75) return 'Cargo, Reserved for future use';
		elseif ($code == 76) return 'Cargo, Reserved for future use';
		elseif ($code == 77) return 'Cargo, Reserved for future use';
		elseif ($code == 78) return 'Cargo, Reserved for future use';
		elseif ($code == 79) return 'Cargo, No additional information';
		elseif ($code == 80) return 'Tanker, all ships of this type';
		elseif ($code == 81) return 'Tanker, Hazardous category A';
		elseif ($code == 82) return 'Tanker, Hazardous category B';
		elseif ($code == 83) return 'Tanker, Hazardous category C';
		elseif ($code == 84) return 'Tanker, Hazardous category D';
		elseif ($code == 85) return 'Tanker, Reserved for future use';
		elseif ($code == 86) return 'Tanker, Reserved for future use';
		elseif ($code == 87) return 'Tanker, Reserved for future use';
		elseif ($code == 88) return 'Tanker, Reserved for future use';
		elseif ($code == 89) return 'Tanker, No additional information';
		elseif ($code == 90) return 'Other Type, all ships of this type';
		elseif ($code == 91) return 'Other Type, Hazardous category A';
		elseif ($code == 92) return 'Other Type, Hazardous category B';
		elseif ($code == 93) return 'Other Type, Hazardous category C';
		elseif ($code == 94) return 'Other Type, Hazardous category D';
		elseif ($code == 95) return 'Other Type, Reserved for future use';
		elseif ($code == 96) return 'Other Type, Reserved for future use';
		elseif ($code == 97) return 'Other Type, Reserved for future use';
		elseif ($code == 98) return 'Other Type, Reserved for future use';
		elseif ($code == 99) return 'Other Type, no additional information';
	}

	public function process_ais_itu($_itu, $_len, $_filler, $aux /*, $ais_ch*/) {
		global $port; // tcpip port...
		
		static $debug_counter = 0;
		$aisdata168='';//six bit array of ascii characters
		$ais_nmea_array = str_split($_itu); // convert to an array
		foreach ($ais_nmea_array as $value) {
			$dec = $this->ascii_2_dec($value);
			$bit8 = $this->asciidec_2_8bit($dec);
			$bit6 = $this->dec_2_6bit($bit8);
			//echo $value ."-" .$bit6 ."";
			$aisdata168 .=$bit6;
		}
		//echo $aisdata168 . "<br/>";
		//return $this->decode_ais($aisdata168, $aux);
		return $this->decode_ais($aisdata168);
	}

	// char* - AIS \r terminated string
	// TCP based streams which send messages in full can use this instead of calling process_ais_buf
	public function process_ais_raw($rawdata, $aux = '') { // return int
		static $num_seq; // 1 to 9
		static $seq; // 1 to 9
		static $pseq; // previous seq
		static $msg_sid = -1; // 0 to 9, indicate -1 at start state of device, do not process messages
		static $cmsg_sid; // current msg_sid
		static $itu; // buffer for ITU message

		$filler = 0; // fill bits (int)
		$chksum = 0;
		// raw data without the \n
		// calculate checksum after ! till *
		// assume 1st ! is valid
		// find * ensure that it is at correct position
		$end = strrpos ( $rawdata , '*' );
		if ($end === FALSE) return -1; // check for NULLS!!!
		$cs = substr( $rawdata, $end + 1 );
		if ( strlen($cs) != 2 ) return -1; // correct cs length
		$dcs = (int)hexdec( $cs );
		for ( $alias=1; $alias<$end; $alias++) $chksum ^= ord( $rawdata[$alias] ); // perform XOR for NMEA checksum
		if ( $chksum == $dcs ) { // NMEA checksum pass
			$pcs = explode(',', $rawdata);
			// !AI??? identifier
			$num_seq = (int)$pcs[1]; // number of sequences
			$seq = (int)$pcs[2]; // get sequence
			// get msg sequence id
			if ($pcs[3] == '') $msg_sid = -1; // non-multipart message, set to -1
			else $msg_sid = (int)$pcs[3]; // multipart message
			$ais_ch = $pcs[4]; // get AIS channel
			// message sequence checking
			if ($num_seq < 1 || $num_seq > 9) {
				echo "ERROR,INVALID_NUMBER_OF_SEQUENCES ".time()." $rawdata\n";
				return -1;
			} else if ($seq < 1 || $seq > 9) { // invalid sequences number
				echo "ERROR,INVALID_SEQUENCES_NUMBER ".time()." $rawdata\n";
				return -1;
			} else if ($seq > $num_seq) {
				echo "ERROR,INVALID_SEQUENCE_NUMBER_OR_INVALID_NUMBER_OF_SEQUENCES ".time()." $rawdata\n";
				return -1;
			} else { // sequencing ok, handle single/multi-part messaging
				if ($seq == 1) { // always init to 0 at first sequence
					$filler = 0; // ?
					$itu = ""; // init message length
					$pseq = 0; // note previous sequence number
					$cmsg_sid = $msg_sid; // note msg_sid
				}
				if ($num_seq > 1) { // for multipart messages
					if ($cmsg_sid != $msg_sid // different msg_sid
					    || $msg_sid == -1 // invalid initial msg_sid
					    || ($seq - $pseq) != 1 // not insequence
					) {  // invalid for multipart message
						$msg_sid = -1;
						$cmsg_sid = -1;
						echo "ERROR,INVALID_MULTIPART_MESSAGE ".time()." $rawdata\n";
						return -1;
					} else {
						$pseq++;
					}
				}
				$itu = $itu.$pcs[5]; // get itu message
				$filler += (int)$pcs[6][0]; // get filler
				if ($num_seq == 1 // valid single message
				    || $num_seq == $pseq // valid multi-part message
				) {
					if ($num_seq != 1) { // test
						//echo $rawdata;
					}
					return $this->process_ais_itu($itu, strlen($itu), $filler, $aux /*, $ais_ch*/);
				}
			} // end process raw AIS string (checksum passed)
		}
		return -1;
	}

	// incoming data from serial or IP comms
	public function process_ais_buf($ibuf) {
		static $cbuf = "";
		$cbuf = $cbuf.$ibuf;
		$last_pos = 0;
		$result = new stdClass();
		while ( ($start = strpos($cbuf,"VDM",$last_pos)) !== FALSE) {
		//while ( ($start = strpos($cbuf,"!AI",$last_pos)) !== FALSE) {
			//DEBUG echo $cbuf;
			if ( ($end = strpos($cbuf,"\r\n", $start)) !== FALSE) { //TBD need to trim?
				$tst = substr($cbuf, $start - 3, ($end - $start + 3));
				//DEBUG echo "[$start $end $tst]\n";
				$result = $this->process_ais_raw( $tst, "" );
				$last_pos = $end + 1;
			} else break;
		}
		if ($last_pos > 0) $cbuf = substr($cbuf, $last_pos); // move...
		if (strlen($cbuf) > 1024) $cbuf = ""; // prevent overflow simple mode...
		return $result;
	}

	// incoming data from serial or IP comms
	public function process_ais_line($cbuf) {
		$result = new stdClass();
		$start = strpos($cbuf,"VDM");
		$tst = substr($cbuf, $start - 3);
		$result = $this->process_ais_raw( $tst, "" );
		return $result;
	}

	/* AIS Encoding
	*/
	private function mk_ais_lat( $lat ) {
		//$lat = 1.2569;
		if ($lat<0.0) {
			$lat = -$lat;
			$neg=true;
		} else $neg=false;
		$latd = 0x00000000;
		$latd = intval ($lat * 600000.0);
		if ($neg==true) {
			$latd = ~$latd;
			$latd+=1;
			$latd &= 0x07FFFFFF;
		}
		return $latd;
	}

	private function mk_ais_lon( $lon ) {
		//$lon = 103.851;
		if ($lon<0.0) {
			$lon = -$lon;
			$neg=true;
		} else $neg=false;
		$lond = 0x00000000;
		$lond = intval ($lon * 600000.0);
		if ($neg==true) {
			$lond = ~$lond;
			$lond+=1;
			$lond &= 0x0FFFFFFF;
		}
		return $lond;
	}

	private function char2bin($name, $max_len) {
		$len = strlen($name);
		if ($len > $max_len) $name = substr($name,0,$max_len);
		if ($len < $max_len) $pad = str_repeat('0', ($max_len - $len) * 6);
		else $pad = '';
		$rv = '';
		$ais_chars = array(
		    '@'=>0, 'A'=>1, 'B'=>2, 'C'=>3, 'D'=>4, 'E'=>5, 'F'=>6, 'G'=>7, 'H'=>8, 'I'=>9,
		    'J'=>10, 'K'=>11, 'L'=>12, 'M'=>13, 'N'=>14, 'O'=>15, 'P'=>16, 'Q'=>17, 'R'=>18, 'S'=>19,
		    'T'=>20, 'U'=>21, 'V'=>22, 'W'=>23, 'X'=>24, 'Y'=>25, 'Z'=>26, '['=>27, '\\'=>28, ']'=>29,
		    '^'=>30, '_'=>31, ' '=>32, '!'=>33, '\"'=>34, '#'=>35, '$'=>36, '%'=>37, '&'=>38, '\''=>39,
		    '('=>40, ')'=>41, '*'=>42, '+'=>43, ','=>44, '-'=>45, '.'=>46, '/'=>47, '0'=>48, '1'=>49,
		    '2'=>50, '3'=>51, '4'=>52, '5'=>53, '6'=>54, '7'=>55, '8'=>56, '9'=>57, ':'=>58, ';'=>59,
		    '<'=>60, '='=>61, '>'=>62, '?'=>63
		);
		// "
		$_a = str_split($name);
		if ($_a) foreach ($_a as $_1) {
			if (isset($ais_chars[$_1])) $dec = $ais_chars[$_1];
			else $dec = 0;
			$bin = str_pad(decbin( $dec ), 6, '0', STR_PAD_LEFT);
			$rv .= $bin;
			//echo "$_1 $dec ($bin)<br/>";
		}
		return $rv.$pad;
	}

	private function mk_ais($_enc, $_part=1,$_total=1,$_seq='',$_ch='A') {
		$len_bit = strlen($_enc);
		$rem6 = $len_bit % 6;
		$pad6_len = 0;
		if ($rem6) $pad6_len = 6 - $rem6;
		//echo  $pad6_len.'<br>';
		$_enc .= str_repeat("0", $pad6_len); // pad the text...
		$len_enc = strlen($_enc) / 6;
		//echo $_enc.' '.$len_enc.'<br/>';
		$itu = '';
		for ($i=0; $i<$len_enc; $i++) {
			$offset = $i * 6;
			$dec = bindec(substr($_enc,$offset,6));
			if ($dec < 40) $dec += 48;
			else $dec += 56;
			//echo chr($dec)." $dec<br/>";
			$itu .= chr($dec);
		}
		// add checksum
		$chksum = 0;
		$itu = "AIVDM,$_part,$_total,$_seq,$_ch,".$itu.",0";
		$len_itu = strlen($itu);
		for ($i=0; $i<$len_itu; $i++) {
			$chksum ^= ord( $itu[$i] );
		}
		$hex_arr = array('0','1','2','3','4','5','6','7','8','9','A','B','C','D','E','F');
		$lsb = $chksum & 0x0F;
		if ($lsb >=0 && $lsb <= 15 ) $lsbc = $hex_arr[$lsb];
		else $lsbc = '0';
		$msb = (($chksum & 0xF0) >> 4) & 0x0F;
		if ($msb >=0 && $msb <= 15 ) $msbc = $hex_arr[$msb];
		else $msbc = '0';
		$itu = '!'.$itu."*{$msbc}{$lsbc}\r\n";
		return $itu;
	}

	public function parse($buffer) {
		$data = $this->process_ais_buf($buffer);
		if (!is_object($data)) return array();
		if ($data->lon != 0) $result['longitude'] = $data->lon;
		if ($data->lat != 0) $result['latitude'] = $data->lat;
		$result['ident'] = trim($data->name);
		$result['timestamp'] = $data->ts;
		$result['mmsi'] = $data->mmsi;
		if ($data->sog != -1.0) $result['speed'] = $data->sog;
		if ($data->cog != 0) $result['heading'] = $data->cog;
		/*
		    $ro->cls = 0; // AIS class undefined, also indicate unparsed msg
		    $ro->id = bindec(substr($_aisdata,0,6));
		*/
		return $result;
	}

	public function mmsitype($mmsi) {
		if (strlen($mmsi) == 9) {
			if (substr($mmsi,0,3) == '974') return 'EPIRB (Emergency Position Indicating Radio Beacon) AIS';
			elseif (substr($mmsi,0,3) == '972') return 'MOB (Man Overboard) device';
			elseif (substr($mmsi,0,3) == '970') return 'AIS SART (Search and Rescue Transmitter)';
			elseif (substr($mmsi,0,3) == '111') return 'SAR (Search and Rescue) aircraft';
			elseif (substr($mmsi,0,2) == '98') return 'Auxiliary craft associated with a parent ship';
			elseif (substr($mmsi,0,2) == '99') return 'Aids to Navigation';
			elseif (substr($mmsi,0,2) == '00') return 'Coastal stations';
			elseif (substr($mmsi,0,1) == '0') return 'Group of ships';
			else return 'Ship';
		}

	
	}

	public function parse_line($buffer) {
		global $globalDebug;
		$result = array();
		$data = new stdClass();
		$start = strpos($buffer,"VDM");
		$tst = substr($buffer, $start - 3);
		$data = $this->process_ais_raw( $tst, "" );
		if (!is_object($data)) {
			//if ($globalDebug) echo '==== Line format not supported : '.$buffer."\n";
			return array();
		}
		if ($data->lon != 0) $result['longitude'] = $data->lon;
		if ($data->lat != 0) $result['latitude'] = $data->lat;
		$result['ident'] = trim(str_replace('@','',$data->name));
		$result['timestamp'] = $data->ts;
		$result['mmsi'] = $data->mmsi;
		if (strlen($result['mmsi']) == 8 && substr($result['mmsi'],0,3) == '669') $result['mmsi'] = '3'.$result['mmsi'];
		$result['mmsi_type'] = $this->mmsitype($result['mmsi']);
		if ($data->sog != -1.0) $result['speed'] = $data->sog;
		if ($data->heading != '') $result['heading'] = $data->heading;
		elseif ($data->cog != 0) $result['heading'] = $data->cog;
		if ($data->status != '') $result['status'] = $data->status;
		if ($data->type != '') $result['type'] = $data->type;
		if ($data->typeid != '') $result['typeid'] = $data->typeid;
		if ($data->imo != '') $result['imo'] = $data->imo;
		if ($data->callsign != '') $result['callsign'] = $data->callsign;
		if ($data->eta_month != '' && $data->eta_day != '' && $data->eta_hour != '' && $data->eta_minute != '') {
			$result['eta_ts'] = strtotime(date('Y').'-'.$data->eta_month.'-'.$data->eta_day.' '.$data->eta_hour.':'.$data->eta_minute.':00');
		}
		if ($data->destination != '') $result['destination'] = $data->destination;
		$result['all'] = (array) $data;
		/*
		    $ro->cls = 0; // AIS class undefined, also indicate unparsed msg
		    $ro->id = bindec(substr($_aisdata,0,6));
		*/
		return $result;
	}
}
