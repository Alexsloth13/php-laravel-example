<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class StreamsModel extends Model
{
	protected $table = 'streams';

	public function insert_stream( array $data )
	{
	return DB::table($this->table)->insert([
		'user_id' 		=> $data['user_id'],
		'title' 	    => $data['title'],
		'safe_page' 	=> $data['safe_page'],
		'offer_page' 	=> $data['offer_page'],
		'geo' 	        => $data['geo'],
		'on_off' 		=> $data['on_off'],
		'ipv6' 		    => $data['ipv6'],
		'device' 		=> $data['device']

		]);
	}

	public function update_stream( array $data )
	{
		return DB::table($this->table)
		->where('stream_id', $data['stream_id'])
		->where('user_id', $data['user_id'])
		->update([
		    'title' 	    => $data['title'],
		    'safe_page' 	=> $data['safe_page'],
				'offer_page' 	=> $data['offer_page'],
				'geo' 	        => $data['geo'],
		    'on_off' 		=> $data['on_off'],
		    'ipv6' 		    => $data['ipv6'],
		    'device' 		=> $data['device']
		]);
	}

	public function select_streams( int $user_id )
	{
		return DB::table($this->table)
		->where('user_id', $user_id)
		->orderByDesc('stream_id')
		->get();
	}

	public function streams_count( int $user_id )
	{
		return DB::table($this->table)
		->select('stream_id')
		->where('user_id', $user_id)
		->count();
	}

	public function select_stream_by_id( int $stream_id, int $user_id )
	{
		return DB::table($this->table)
		->where('user_id', $user_id)
		->where('stream_id', $stream_id)
		->get();
	}

	// Получаем массив с id потоками пользователя по его user_id
	public function selectStreamIdsByUserId( int $user_id, $numToStr = false)
	{
		$results = DB::table($this->table)
		->select('stream_id')
		->where('user_id', $user_id)
		->orderBy('stream_id', 'desc')
		->get();

		$array = array();
		if($numToStr){
		    foreach ($results as $result) {
			$array[] = strval($result->stream_id);
		    }
		}else{
		    foreach ($results as $result) {
			$array[] = $result->stream_id;
		    }
		}
		return $array; 
		}

		public function delete_stream( int $stream_id, int $user_id )
		{
		return DB::table($this->table)
		->where('stream_id', $stream_id)
		->where('user_id', $user_id)
		->delete();
	}

	public function create_device_options( $device )
	{
		$device_options = explode( ',', $device );

		$code = '<option value="{{devices}}"{{selected}}>{{name}}</option>';
		$devices = array('desktop','mobile','tablet');
		$names = array('Desktop','Mobile','Tablet');

		for ($i = 0, $options = '', $count = count($devices); $i < $count; $i++) {
		    if(in_array($devices[$i], $device_options))
			$selected[$i] = ' selected="selected"';
		    else
			$selected[$i] = '';
		    $code = '<option value="{{geo}}"{{selected}}>{{name}}</option>';
		    $order	= array("{{geo}}", "{{name}}","{{selected}}");
		    $replace	= array($devices[$i] , $names[$i], $selected[$i]);
		    $getcode = str_ireplace($order, $replace, $code);
		    $options .= $getcode;
		}

		return $options;
	}

	public function create_geo_options( $geo )
	{	
		$geo_options = explode( ', ', $geo );

		$code = '<option value="{{geo}}"{{selected}}>{{name}}</option>';
		$geos = array("AD","AE","AF","AG","AI","AL","AM","AO","AP","AQ","AR","AS","AT","AU","AW","AX","AZ","BA","BB","BD","BE","BF","BG","BH","BI","BJ","BL","BM","BN","BO","BQ","BR","BS","BT","BV","BW","BY","BZ","CA","CC","CD","CF","CG","CH","CI","CK","CL","CM","CN","CO","CR","CU","CV","CW","CX","CY","CZ","DE","DJ","DK","DM","DO","DZ","EC","EE","EG","EH","ER","ES","ET","EU","FI","FJ","FK","FM","FO","FR","GA","GB","GD","GE","GF","GG","GH","GI","GL","GM","GN","GP","GQ","GR","GS","GT","GU","GW","GY","HK","HM","HN","HR","HT","HU","ID","IE","IL","IM","IN","IO","IQ","IR","IS","IT","JE","JM","JO","JP","KE","KG","KH","KI","KM","KN","KP","KR","KW","KY","KZ","LA","LB","LC","LI","LK","LR","LS","LT","LU","LV","LY","MA","MC","MD","ME","MF","MG","MH","MK","ML","MM","MN","MO","MP","MQ","MR","MS","MT","MU","MV","MW","MX","MY","MZ","NA","NC","NE","NF","NG","NI","NL","NO","NP","NR","NU","NZ","OM","PA","PE","PF","PG","PH","PK","PL","PM","PN","PR","PS","PT","PW","PY","QA","RE","RO","RS","RU","RW","SA","SB","SC","SD","SE","SG","SH","SI","SJ","SK","SL","SM","SN","SO","SR","SS","ST","SV","SX","SY","SZ","TC","TD","TF","TG","TH","TJ","TK","TL","TM","TN","TO","TR","TT","TV","TW","TZ","UA","UG","UM","US","UY","UZ","VA","VC","VE","VG","VI","VN","VU","WF","WS","YE","YT","ZA","ZM","ZW","A1","A2","O1");
		$names = array("Andorra","United Arab Emirates","Afghanistan","Antigua and Barbuda","Anguilla","Albania","Armenia","Angola","Asia/Pacific Region","Antarctica","Argentina","American Samoa","Austria","Australia","Aruba","Aland Islands","Azerbaijan","Bosnia and Herzegovina","Barbados","Bangladesh","Belgium","Burkina Faso","Bulgaria","Bahrain","Burundi","Benin","Saint Barthelemy","Bermuda","Brunei Darussalam","Bolivia","Bonaire, Saint Eustatius and Saba","Brazil","Bahamas","Bhutan","Bouvet Island","Botswana","Belarus","Belize","Canada","Cocos (Keeling) Islands","Congo, The Democratic Republic of the","Central African Republic","Congo","Switzerland","Cote d'Ivoire","Cook Islands","Chile","Cameroon","China","Colombia","Costa Rica","Cuba","Cape Verde","Curacao","Christmas Island","Cyprus","Czech Republic","Germany","Djibouti","Denmark","Dominica","Dominican Republic","Algeria","Ecuador","Estonia","Egypt","Western Sahara","Eritrea","Spain","Ethiopia","Europe","Finland","Fiji","Falkland Islands (Malvinas)","Micronesia, Federated States of","Faroe Islands","France","Gabon","United Kingdom","Grenada","Georgia","French Guiana","Guernsey","Ghana","Gibraltar","Greenland","Gambia","Guinea","Guadeloupe","Equatorial Guinea","Greece","South Georgia and the South Sandwich Islands","Guatemala","Guam","Guinea-Bissau","Guyana","Hong Kong","Heard Island and McDonald Islands","Honduras","Croatia","Haiti","Hungary","Indonesia","Ireland","Israel","Isle of Man","India","British Indian Ocean Territory","Iraq","Iran, Islamic Republic of","Iceland","Italy","Jersey","Jamaica","Jordan","Japan","Kenya","Kyrgyzstan","Cambodia","Kiribati","Comoros","Saint Kitts and Nevis","Korea, Democratic People's Republic of","Korea, Republic of","Kuwait","Cayman Islands","Kazakhstan","Lao People's Democratic Republic","Lebanon","Saint Lucia","Liechtenstein","Sri Lanka","Liberia","Lesotho","Lithuania","Luxembourg","Latvia","Libyan Arab Jamahiriya","Morocco","Monaco","Moldova, Republic of","Montenegro","Saint Martin","Madagascar","Marshall Islands","Macedonia","Mali","Myanmar","Mongolia","Macao","Northern Mariana Islands","Martinique","Mauritania","Montserrat","Malta","Mauritius","Maldives","Malawi","Mexico","Malaysia","Mozambique","Namibia","New Caledonia","Niger","Norfolk Island","Nigeria","Nicaragua","Netherlands","Norway","Nepal","Nauru","Niue","New Zealand","Oman","Panama","Peru","French Polynesia","Papua New Guinea","Philippines","Pakistan","Poland","Saint Pierre and Miquelon","Pitcairn","Puerto Rico","Palestinian Territory","Portugal","Palau","Paraguay","Qatar","Reunion","Romania","Serbia","Russian Federation","Rwanda","Saudi Arabia","Solomon Islands","Seychelles","Sudan","Sweden","Singapore","Saint Helena","Slovenia","Svalbard and Jan Mayen","Slovakia","Sierra Leone","San Marino","Senegal","Somalia","Suriname","South Sudan","Sao Tome and Principe","El Salvador","Sint Maarten","Syrian Arab Republic","Swaziland","Turks and Caicos Islands","Chad","French Southern Territories","Togo","Thailand","Tajikistan","Tokelau","Timor-Leste","Turkmenistan","Tunisia","Tonga","Turkey","Trinidad and Tobago","Tuvalu","Taiwan","Tanzania, United Republic of","Ukraine","Uganda","United States Minor Outlying Islands","United States","Uruguay","Uzbekistan","Holy See (Vatican City State)","Saint Vincent and the Grenadines","Venezuela","Virgin Islands, British","Virgin Islands, U.S.","Vietnam","Vanuatu","Wallis and Futuna","Samoa","Yemen","Mayotte","South Africa","Zambia","Zimbabwe","Anonymous Proxy","Satellite Provider","Other Country");

		for ($i = 0, $options = '', $count = count($geos); $i < $count; $i++) {
				if(in_array($geos[$i], $geo_options))
					$selected[$i] = ' selected="selected"';
				else
					$selected[$i] = '';
				$code = '<option value="{{geo}}"{{selected}}>{{name}}</option>';
				$order	= array("{{geo}}", "{{name}}","{{selected}}");
				$replace	= array($geos[$i] , $names[$i], $selected[$i]);
				$getcode = str_ireplace($order, $replace, $code);
				$options .= $getcode;
		}

		return $options;
	}
}
