<?php
class Booki_Helper{
	const PATH = '/assets/admin/emails/%s.txt';
	public static function startsWith($haystack, $needle)
	{
		return strpos($haystack, $needle) === 0;
	}
	
	public static function endsWith($haystack, $needle)
	{
		return substr($haystack, -strlen($needle)) == $needle;
	}
	
	public static function convertToIntArray($values){
		$intArray = array();
		foreach($values as $value){
			array_push($intArray, intval($value));
		}
		return $intArray;
	}
	
	
	public static function getUrlDelimiter($url){
		if(strpos($url, '?') === false){
			return '?';
		}
		return '&';
	}
	
	public static function appendReferrer($url, $param = null){
		if($param === null){
			$param = 'booki_continue';
		}
		$referrer = self::appendScheme($_SERVER["HTTP_HOST"] . $_SERVER['REQUEST_URI']);
		$delimiter = self::getUrlDelimiter($url);
		$url .= $delimiter . $param . '=' . urlencode($referrer) . '&booki=true';
		return $url;
	}
	
	public static function redirect($pageName, $appendReferrer = false){
		$loc = self::getUrl($pageName);
		if($appendReferrer){
			$loc = self::appendReferrer($loc);
		}
		wp_redirect($loc);
	}
	
	public static function getUrl($pageName){
		if(self::startsWith($pageName, 'http://') ||
			self::startsWith($pageName, 'https://'))
		{
			return $pageName;
		}
		$url = '';
		$args = array( 
			'child_of' => 0, 'sort_order' => 'ASC',
			'sort_column' => 'post_title', 'hierarchical' => 1,
			'exclude' => array(), 'include' => array(),
			'meta_key' => 'booki_page_type', 'meta_value' => $pageName,
			'authors' => '', 'parent' => -1, 'exclude_tree' => array(),
			'number' => '', 'offset' => 0,
			'post_type' => 'page', 'post_status' => 'publish',
		);
		
		$pages = get_pages($args);
		if($pages && count($pages) > 0){
			try{
				$url = get_page_link($pages[0]->ID);
			}catch(Exception $ex){
				Booki_EventsLogProvider::insert($ex);
			}
		}
		$baseUrl = is_ssl() ? 'https://' : 'http://';
		$baseUrl .= $_SERVER['HTTP_HOST'];
		if(!self::startsWith($url, $baseUrl))
		{
			return $baseUrl . $url;
		}
		return $url;
	}
	
	public static function appendScheme($url)
	{
		if(self::startsWith($url, 'http://') ||
			self::startsWith($url, 'https://'))
		{
			return $url;
		}
		
		$scheme = is_ssl() ? 'https://' : 'http://';
		return parse_url($url, PHP_URL_SCHEME) === null ? $scheme . $url : $url;
	}
	
	public static function getLocaleInfo(){
		$currency = self::getCurrency();
		$symbol = self::getCurrencySymbol();
		$locale = localeconv();
		/*Paypal: Decimal separator must be a period (.)
				  Thousands separator must be a comma (,)
		*/
		$locale['decimal_point'] = '.';
		$locale['thousands_sep'] = ',';
		return array(
			'currency'=>$currency
			, 'currencySymbol'=>$symbol
			, 'locale'=>$locale
		);
	}
	
	public static function percentage($percentage, $amount)
	{
		return ($amount / 100) * $percentage;
	}
	public static function calcDiscount($discount, $totalAmount){
		$discount = ($totalAmount / 100) * $discount;
		if($discount < $totalAmount){
			$totalAmount -= $discount;
		}
		return $totalAmount;
	}
	public static function calcDeposit($deposit, $cost){
		if($deposit > 0){
			return ($cost/100)*$deposit;
		}
		return $cost;
	}
	public static function toMoney($val, $precision = 2)
	{
		$decimal = '.';
		$thousands = ',';
		$precision = self::getCurrencyPrecision($precision);
		/*Paypal: Decimal separator must be a period (.)
				  Thousands separator must be a comma (,)
		*/
		return number_format(floatval($val), $precision, $decimal, $thousands);
	}
	
	public static function getCurrency()
	{
		$paypalSettings = BOOKIAPP()->paypalSettings;
		if(!$paypalSettings){
			return $globalSettings->currencyCode;
		}
		$currency = $paypalSettings->currency;
		$globalSettings = BOOKIAPP()->globalSettings;
		if(!$globalSettings->enablePayments && $globalSettings->currencyCode){
			$currency = $globalSettings->currencyCode;
		}
		return $currency;
	}
	
	public static function formatCurrencySymbol($value, $shortFormat = false){
		$pos = self::getCurrencySymbolPosition();
		$currency = self::getCurrency();
		$symbol = self::getCurrencySymbol();
		if($pos === 0/*left*/){
			if($shortFormat){
				return sprintf('%s %s', $symbol, $value);
			}
			return sprintf('%s %s %s', $symbol, $value, $currency);
		}
		return sprintf('%s %s', $value, $symbol);
	}
	
	public static function getCurrencySymbolPosition(){
		$currency = self::getCurrency();
		$pos = 1/*right*/;
		switch($currency){
			case 'AUD' :
			case 'CAD' :
			case 'EUR' :
			case 'GBP' :
			case 'JPY' :
			case 'USD' :
			case 'NZD' :
			case 'HKD' :
			case 'SGD' :
			case 'MXN' :
			case 'BRL' :
			case 'ZAR' :
			case 'KRW' :
				$pos = 0/*left*/;
			break;
		}
		return $pos;
	}
	public static function getCurrencyPrecision($precision = 2){
		$currency = self::getCurrency();
		switch($currency){
			case 'HUF':
			case 'JPY':
			case 'TWD':
			$precision = 0;
			break;
			case 'KRW':
			$precision = 3;
			break;
		}
		return $precision;
	}
	public static function getCurrencySymbol()
	{
		$paypalSettings = BOOKIAPP()->paypalSettings;
		if(!$paypalSettings){
			return $globalSettings->currencySymbol;
		}
		$symbol = self::toCurrencySymbol($paypalSettings->currency);
		$globalSettings = BOOKIAPP()->globalSettings;
		if(!$globalSettings->enablePayments && $globalSettings->currencySymbol){
			$symbol = $globalSettings->currencySymbol;
		}
		return $symbol;
	}
	
	public static function formatDate($date){
		return date_i18n(get_option('date_format') ,strtotime($date->format(DateTime::ISO8601)));
	}
	
	public static function readEmailTemplate($fileName){
		$path = BOOKI_ROOT  . self::PATH;
		return Booki_Helper::readText(sprintf($path, preg_replace('/\s+/', '', $fileName)));
	}
	
	public static function toCurrencySymbol($code){
		$currencies = array(
			 'AUD'=>'$'
			,'CAD'=>'$'
			,'EUR'=>'&euro;'
			,'GBP'=>'&pound;'
			,'JPY'=>'&yen;'
			,'USD'=>'$'
			,'NZD'=>'$'
			,'CHF'=>'S&#8355;'//Swiss Franc SFr or SF
			,'HKD'=>'$'
			,'SGD'=>'$'
			,'SEK'=>'kr'//Swedish Krona
			,'DKK'=>'kr.'//Danish Krone
			,'PLN'=>'z&#322;'//Polish Zloty
			,'NOK'=>'kr'//Norwegian Krone
			,'HUF'=>'Ft'//Hungarian Forint
			,'CZK'=>'K&#269;'//Czech Koruna
			,'ILS'=>'&acirc;&sbquo;&ordf;'//Israeli New Shekel
			,'MXN'=>'$'//Mexican Peso
			,'BRL'=>'R$ '//Brazilian Real (only for Brazilian members)
			,'MYR'=>'RM'//Malaysian Ringgit (only for Malaysian members)
			,'PHP'=>'&#8369;'//Philippine Peso
			,'TWD'=>'$'//New Taiwan Dollar
			,'THB'=>'&#3647;'//Thai Baht
			,'TRY'=>'TL'//Turkish Lira (only for Turkish members)  TNL for Turkish New Lira
			, 'RUB'=>'&#8381;'//Russian ruble
		);
		return $currencies[$code];
	}
	
	public static function readText($path){
		$content = '';
		if ($handle = fopen($path, 'rb')) {
			$len = filesize($path);
			if ($len > 0){
				$content = fread($handle, $len);
			}
			fclose($handle);
		}
		return trim($content);
	}
	
	/**
		@description DateTime->diff for php 5.2
		@param $date1 needs to be date in the format 'Y-m-d'
		@param $date2 needs to be date in the format 'Y-m-d'
	*/
	public static function date_diff($date1, $date2) { 
		$current = $date1; 
		$datetime2 = date_create($date2); 
		$count = 0; 
		while(date_create($current) < $datetime2){ 
			$current = gmdate('Y-m-d', strtotime('+1 day', strtotime($current))); 
			$count++; 
		}	
		return $count; 
	} 
	
	public static function json_encode_response($data = NULL, $key = NULL){
		if (method_exists($data, 'toArray')){
			$data = $data->toArray();
		}
		if (! is_null($key)){
			return json_encode(array($key=>$data));
		}
		return json_encode($data);
	}
	
	public static function json_decode_request($data, $assoc = false){
		return json_decode(urldecode($data), $assoc);
	}
	
	public static function getCountries(){
		return array(
			'AF'=>'AFGHANISTAN',
			'AL'=>'ALBANIA',
			'DZ'=>'ALGERIA',
			'AS'=>'AMERICAN SAMOA',
			'AD'=>'ANDORRA',
			'AO'=>'ANGOLA',
			'AI'=>'ANGUILLA',
			'AQ'=>'ANTARCTICA',
			'AG'=>'ANTIGUA AND BARBUDA',
			'AR'=>'ARGENTINA',
			'AM'=>'ARMENIA',
			'AW'=>'ARUBA',
			'AU'=>'AUSTRALIA',
			'AT'=>'AUSTRIA',
			'AZ'=>'AZERBAIJAN',
			'BS'=>'BAHAMAS',
			'BH'=>'BAHRAIN',
			'BD'=>'BANGLADESH',
			'BB'=>'BARBADOS',
			'BY'=>'BELARUS',
			'BE'=>'BELGIUM',
			'BZ'=>'BELIZE',
			'BJ'=>'BENIN',
			'BM'=>'BERMUDA',
			'BT'=>'BHUTAN',
			'BO'=>'BOLIVIA',
			'BA'=>'BOSNIA AND HERZEGOVINA',
			'BW'=>'BOTSWANA',
			'BV'=>'BOUVET ISLAND',
			'BR'=>'BRAZIL',
			'IO'=>'BRITISH INDIAN OCEAN TERRITORY',
			'BN'=>'BRUNEI DARUSSALAM',
			'BG'=>'BULGARIA',
			'BF'=>'BURKINA FASO',
			'BI'=>'BURUNDI',
			'KH'=>'CAMBODIA',
			'CM'=>'CAMEROON',
			'CA'=>'CANADA',
			'CV'=>'CAPE VERDE',
			'KY'=>'CAYMAN ISLANDS',
			'CF'=>'CENTRAL AFRICAN REPUBLIC',
			'TD'=>'CHAD',
			'CL'=>'CHILE',
			'CN'=>'CHINA',
			'CX'=>'CHRISTMAS ISLAND',
			'CC'=>'COCOS (KEELING) ISLANDS',
			'CO'=>'COLOMBIA',
			'KM'=>'COMOROS',
			'CG'=>'CONGO',
			'CD'=>'CONGO, THE DEMOCRATIC REPUBLIC OF THE',
			'CK'=>'COOK ISLANDS',
			'CR'=>'COSTA RICA',
			'CI'=>'COTE D IVOIRE',
			'HR'=>'CROATIA',
			'CU'=>'CUBA',
			'CY'=>'CYPRUS',
			'CZ'=>'CZECH REPUBLIC',
			'DK'=>'DENMARK',
			'DJ'=>'DJIBOUTI',
			'DM'=>'DOMINICA',
			'DO'=>'DOMINICAN REPUBLIC',
			'TP'=>'EAST TIMOR',
			'EC'=>'ECUADOR',
			'EG'=>'EGYPT',
			'SV'=>'EL SALVADOR',
			'GQ'=>'EQUATORIAL GUINEA',
			'ER'=>'ERITREA',
			'EE'=>'ESTONIA',
			'ET'=>'ETHIOPIA',
			'FK'=>'FALKLAND ISLANDS (MALVINAS)',
			'FO'=>'FAROE ISLANDS',
			'FJ'=>'FIJI',
			'FI'=>'FINLAND',
			'FR'=>'FRANCE',
			'GF'=>'FRENCH GUIANA',
			'PF'=>'FRENCH POLYNESIA',
			'TF'=>'FRENCH SOUTHERN TERRITORIES',
			'GA'=>'GABON',
			'GM'=>'GAMBIA',
			'GE'=>'GEORGIA',
			'DE'=>'GERMANY',
			'GH'=>'GHANA',
			'GI'=>'GIBRALTAR',
			'GR'=>'GREECE',
			'GL'=>'GREENLAND',
			'GD'=>'GRENADA',
			'GP'=>'GUADELOUPE',
			'GU'=>'GUAM',
			'GT'=>'GUATEMALA',
			'GN'=>'GUINEA',
			'GW'=>'GUINEA-BISSAU',
			'GY'=>'GUYANA',
			'HT'=>'HAITI',
			'HM'=>'HEARD ISLAND AND MCDONALD ISLANDS',
			'VA'=>'HOLY SEE (VATICAN CITY STATE)',
			'HN'=>'HONDURAS',
			'HK'=>'HONG KONG',
			'HU'=>'HUNGARY',
			'IS'=>'ICELAND',
			'IN'=>'INDIA',
			'ID'=>'INDONESIA',
			'IR'=>'IRAN, ISLAMIC REPUBLIC OF',
			'IQ'=>'IRAQ',
			'IE'=>'IRELAND',
			'IL'=>'ISRAEL',
			'IT'=>'ITALY',
			'JM'=>'JAMAICA',
			'JP'=>'JAPAN',
			'JO'=>'JORDAN',
			'KZ'=>'KAZAKSTAN',
			'KE'=>'KENYA',
			'KI'=>'KIRIBATI',
			'KP'=>'KOREA DEMOCRATIC PEOPLES REPUBLIC OF',
			'KR'=>'KOREA REPUBLIC OF',
			'KW'=>'KUWAIT',
			'KG'=>'KYRGYZSTAN',
			'LA'=>'LAO PEOPLES DEMOCRATIC REPUBLIC',
			'LV'=>'LATVIA',
			'LB'=>'LEBANON',
			'LS'=>'LESOTHO',
			'LR'=>'LIBERIA',
			'LY'=>'LIBYAN ARAB JAMAHIRIYA',
			'LI'=>'LIECHTENSTEIN',
			'LT'=>'LITHUANIA',
			'LU'=>'LUXEMBOURG',
			'MO'=>'MACAU',
			'MK'=>'MACEDONIA, THE FORMER YUGOSLAV REPUBLIC OF',
			'MG'=>'MADAGASCAR',
			'MW'=>'MALAWI',
			'MY'=>'MALAYSIA',
			'MV'=>'MALDIVES',
			'ML'=>'MALI',
			'MT'=>'MALTA',
			'MH'=>'MARSHALL ISLANDS',
			'MQ'=>'MARTINIQUE',
			'MR'=>'MAURITANIA',
			'MU'=>'MAURITIUS',
			'YT'=>'MAYOTTE',
			'MX'=>'MEXICO',
			'FM'=>'MICRONESIA, FEDERATED STATES OF',
			'MD'=>'MOLDOVA, REPUBLIC OF',
			'MC'=>'MONACO',
			'MN'=>'MONGOLIA',
			'MS'=>'MONTSERRAT',
			'MA'=>'MOROCCO',
			'MZ'=>'MOZAMBIQUE',
			'MM'=>'MYANMAR',
			'NA'=>'NAMIBIA',
			'NR'=>'NAURU',
			'NP'=>'NEPAL',
			'NL'=>'NETHERLANDS',
			'AN'=>'NETHERLANDS ANTILLES',
			'NC'=>'NEW CALEDONIA',
			'NZ'=>'NEW ZEALAND',
			'NI'=>'NICARAGUA',
			'NE'=>'NIGER',
			'NG'=>'NIGERIA',
			'NU'=>'NIUE',
			'NF'=>'NORFOLK ISLAND',
			'MP'=>'NORTHERN MARIANA ISLANDS',
			'NO'=>'NORWAY',
			'OM'=>'OMAN',
			'PK'=>'PAKISTAN',
			'PW'=>'PALAU',
			'PS'=>'PALESTINIAN TERRITORY, OCCUPIED',
			'PA'=>'PANAMA',
			'PG'=>'PAPUA NEW GUINEA',
			'PY'=>'PARAGUAY',
			'PE'=>'PERU',
			'PH'=>'PHILIPPINES',
			'PN'=>'PITCAIRN',
			'PL'=>'POLAND',
			'PT'=>'PORTUGAL',
			'PR'=>'PUERTO RICO',
			'QA'=>'QATAR',
			'RE'=>'REUNION',
			'RO'=>'ROMANIA',
			'RU'=>'RUSSIAN FEDERATION',
			'RW'=>'RWANDA',
			'SH'=>'SAINT HELENA',
			'KN'=>'SAINT KITTS AND NEVIS',
			'LC'=>'SAINT LUCIA',
			'PM'=>'SAINT PIERRE AND MIQUELON',
			'VC'=>'SAINT VINCENT AND THE GRENADINES',
			'WS'=>'SAMOA',
			'SM'=>'SAN MARINO',
			'ST'=>'SAO TOME AND PRINCIPE',
			'SA'=>'SAUDI ARABIA',
			'SN'=>'SENEGAL',
			'SC'=>'SEYCHELLES',
			'SL'=>'SIERRA LEONE',
			'SG'=>'SINGAPORE',
			'SK'=>'SLOVAKIA',
			'SI'=>'SLOVENIA',
			'SB'=>'SOLOMON ISLANDS',
			'SO'=>'SOMALIA',
			'ZA'=>'SOUTH AFRICA',
			'GS'=>'SOUTH GEORGIA AND THE SOUTH SANDWICH ISLANDS',
			'ES'=>'SPAIN',
			'LK'=>'SRI LANKA',
			'SD'=>'SUDAN',
			'SR'=>'SURINAME',
			'SJ'=>'SVALBARD AND JAN MAYEN',
			'SZ'=>'SWAZILAND',
			'SE'=>'SWEDEN',
			'CH'=>'SWITZERLAND',
			'SY'=>'SYRIAN ARAB REPUBLIC',
			'TW'=>'TAIWAN, PROVINCE OF CHINA',
			'TJ'=>'TAJIKISTAN',
			'TZ'=>'TANZANIA, UNITED REPUBLIC OF',
			'TH'=>'THAILAND',
			'TG'=>'TOGO',
			'TK'=>'TOKELAU',
			'TO'=>'TONGA',
			'TT'=>'TRINIDAD AND TOBAGO',
			'TN'=>'TUNISIA',
			'TR'=>'TURKEY',
			'TM'=>'TURKMENISTAN',
			'TC'=>'TURKS AND CAICOS ISLANDS',
			'TV'=>'TUVALU',
			'UG'=>'UGANDA',
			'UA'=>'UKRAINE',
			'AE'=>'UNITED ARAB EMIRATES',
			'GB'=>'UNITED KINGDOM',
			'US'=>'UNITED STATES',
			'UM'=>'UNITED STATES MINOR OUTLYING ISLANDS',
			'UY'=>'URUGUAY',
			'UZ'=>'UZBEKISTAN',
			'VU'=>'VANUATU',
			'VE'=>'VENEZUELA',
			'VN'=>'VIET NAM',
			'VG'=>'VIRGIN ISLANDS, BRITISH',
			'VI'=>'VIRGIN ISLANDS, U.S.',
			'WF'=>'WALLIS AND FUTUNA',
			'EH'=>'WESTERN SAHARA',
			'YE'=>'YEMEN',
			'YU'=>'YUGOSLAVIA',
			'ZM'=>'ZAMBIA',
			'ZW'=>'ZIMBABWE',
		  );
	}
	
	public static function createUserIfNotExists($userEmail, $firstName = null, $lastName = null){
		$userInfo = get_user_by( 'email', $userEmail );
		$userId = null;
		$isNew = false;
		if($userInfo){
			$userId = $userInfo->ID;
		}else{
			$userName = substr($userEmail, 0, strrpos($userEmail, '@'));
			$userName .= ('_' . uniqid());
			$userName = sanitize_user($userName, true);
			$randomPassword = wp_generate_password( $length=12, $include_standard_special_chars=false );
			$userId = wp_create_user( $userName, $randomPassword, $userEmail );
			if ($firstName){
				update_user_meta($userId, 'first_name', $firstName);
			}
			if ($lastName){
				update_user_meta($userId, 'last_name', $lastName);
			}
			wp_new_user_notification( $userId, null, 'both' );
			$isNew = true;
		}
		return array('userId'=>$userId, 'isNew'=>$isNew);
	}
	
	public static function userInBanList($banList){
		if(!is_user_logged_in()){
			return false;
		}
		$user = wp_get_current_user();
		$userEmails = explode(',', $banList);
		$currentUserEmail = $user->user_email;
		foreach($userEmails as $email){
			if(trim($email) === $user->user_email){
				return true;
			}
		}
		return false;
	}
	public static function getUserInfo($userId = null){
		if($userId === null){
			$userId = get_current_user_id();
		}
		if($userId === 0){
			return null;
		}
		$user = get_userdata($userId);
		$userMeta = get_user_meta($userId);
		$username = $user->user_login;
		$firstname = $userMeta['first_name'][0];
		$lastname = $userMeta['last_name'][0];
		if($firstname)
		{
			$username = $firstname;
			if($lastname)
			{
				$username .= ' ' . $lastname;
			}
		}
		return array('name'=>$username, 'email'=>$user->user_email, 'userId'=>$userId, 'firstname'=>$firstname, 'lastname'=>$lastname);
	}
	
	public static function getUserEmail($id){
		$user = get_user_by('id', $id);
		return $user ? $user->user_email : '';
	}
	
	public static function getUserInfoByEmail($email){
		$user = get_user_by('email', $email);
		if($user){
			return self::getUserInfo($user->ID);
		}else if (strpos($email, '@') !== false){
			$pair = explode('@', $email);
			return array('name'=>$pair[0], 'email'=>$email);
		}
		return array('name'=>'', 'email'=>$email);
	}
	
	public static function noCache(){
		add_filter('wp_headers', array('Booki_Helper', 'addNoStoreHeaderParam'));
		//nocache_headers();
	}
	
	public static function addNoStoreHeaderParam($headers){
		$headers['Cache-Control'] = 'no-store, no-cache, must-revalidate, post-check=0, pre-check=0';
		return $headers;
	}
	
	public static function systemEmails(){
		return array( 
			Booki_EmailType::MASTER_TEMPLATE
			, Booki_EmailType::BOOKING_RECEIVED_SUCCESSFULLY
			, Booki_EmailType::NEW_BOOKING_RECEIVED_FOR_ADMIN
			, Booki_EmailType::NEW_BOOKING_RECEIVED_FOR_AGENTS
			, Booki_EmailType::ORDER_CONFIRMATION
			, Booki_EmailType::ORDER_CANCELLED
			, Booki_EmailType::PAYMENT_RECEIVED
			, Booki_EmailType::BOOKING_DAY_CONFIRMED
			, Booki_EmailType::BOOKING_DAY_CANCELLED
			, Booki_EmailType::BOOKING_DAY_REFUNDED
			, Booki_EmailType::BOOKING_CANCEL_REQUEST
			, Booki_EmailType::BOOKING_OPTIONAL_ITEM_CONFIRMED
			, Booki_EmailType::BOOKING_OPTIONAL_ITEM_CANCELLED
			, Booki_EmailType::BOOKING_OPTIONAL_ITEM_REFUNDED
			, Booki_EmailType::BOOKING_QUANTITY_ELEMENT_CONFIRMED
			, Booki_EmailType::BOOKING_QUANTITY_ELEMENT_CANCELLED
			, Booki_EmailType::BOOKING_QUANTITY_ELEMENT_REFUNDED
			, Booki_EmailType::BOOKING_REMINDER
			, Booki_EmailType::INVOICE
			, Booki_EmailType::REFUNDED
			, Booki_EmailType::COUPON
		);
	}
	
	public static function base64UrlEncode($value)
	{
		return strtr(base64_encode($value), '+/=', '-_,');
	}

	public static function base64UrlDecode($value)
	{
		return base64_decode(strtr($value, '-_,', '+/='));
	}
}
?>