<?php

  /**
  * Class that lists all available countries at the moment with their codes and english names
  *
  * @version 1.0
  * @author Ilija Studen <ilija.studen@gmail.com>
  */
  class CountryCodes {
  
    /**
    * Countries array
    *
    * @var array
    */
    static $countries = array(
      'us' => 'United States',
      'ca' => 'Canada',
      'af' => 'Afghanistan',
      'al' => 'Albania',
      'dz' => 'Algeria',
      'as' => 'American Samoa',
      'ad' => 'Andorra',
      'ao' => 'Angola',
      'ai' => 'Anguilla',
      'aq' => 'Antarctica',
      'ag' => 'Antigua and Barbuda',
      'ar' => 'Argentina',
      'am' => 'Armenia',
      'aw' => 'Aruba',
      'au' => 'Australia',
      'at' => 'Austria',
      'az' => 'Azerbaijan',
      'bs' => 'Bahamas',
      'bh' => 'Bahrain',
      'bd' => 'Bangladesh',
      'bb' => 'Barbados',
      'by' => 'Belarus',
      'be' => 'Belgium',
      'bz' => 'Belize',
      'bj' => 'Benin',
      'bm' => 'Bermuda',
      'bt' => 'Bhutan',
      'bo' => 'Bolivia',
      'ba' => 'Bosnia and Herzegovina',
      'bw' => 'Botswana',
      'bv' => 'Bouvet Island',
      'br' => 'Brazil',
      'io' => 'British Indian Ocean Territory',
      'vg' => 'British Virgin Islands',
      'bn' => 'Brunei',
      'bg' => 'Bulgaria',
      'bf' => 'Burkina Faso',
      'bi' => 'Burundi',
      'kh' => 'Cambodia',
      'cm' => 'Cameroon',
      'cv' => 'Cape Verde',
      'ky' => 'Cayman Islands',
      'cf' => 'Central African Republic',
      'td' => 'Chad',
      'cl' => 'Chile',
      'cn' => 'China',
      'cx' => 'Christmas Island',
      'cc' => 'Cocos Islands',
      'co' => 'Colombia',
      'km' => 'Comoros',
      'cg' => 'Congo',
      'ck' => 'Cook Islands',
      'cr' => 'Costa Rica',
      'hr' => 'Croatia',
      'cu' => 'Cuba',
      'cy' => 'Cyprus',
      'cz' => 'Czech Republic',
      'dk' => 'Denmark',
      'dj' => 'Djibouti',
      'dm' => 'Dominica',
      'do' => 'Dominican Republic',
      'tl' => 'East Timor',
      'ec' => 'Ecuador',
      'eg' => 'Egypt',
      'sv' => 'El Salvador',
      'gq' => 'Equatorial Guinea',
      'er' => 'Eritrea',
      'ee' => 'Estonia',
      'et' => 'Ethiopia',
      'fk' => 'Falkland Islands',
      'fo' => 'Faroe Islands',
      'fj' => 'Fiji',
      'fi' => 'Finland',
      'fr' => 'France',
      'gf' => 'French Guiana',
      'pf' => 'French Polynesia',
      'tf' => 'French Southern Territories',
      'ga' => 'Gabon',
      'gm' => 'Gambia',
      'ge' => 'Georgia',
      'de' => 'Germany',
      'gh' => 'Ghana',
      'gi' => 'Gibraltar',
      'gr' => 'Greece',
      'gl' => 'Greenland',
      'gd' => 'Grenada',
      'gp' => 'Guadeloupe',
      'gu' => 'Guam',
      'gt' => 'Guatemala',
      'gn' => 'Guinea',
      'gw' => 'Guinea-Bissau',
      'gy' => 'Guyana',
      'ht' => 'Haiti',
      'hm' => 'Heard and McDonald Islands',
      'hn' => 'Honduras',
      'hk' => 'Hong Kong',
      'hu' => 'Hungary',
      'is' => 'Iceland',
      'in' => 'India',
      'id' => 'Indonesia',
      'ir' => 'Iran',
      'iq' => 'Iraq',
      'ie' => 'Ireland',
      'il' => 'Israel',
      'it' => 'Italy',
      'ci' => 'Ivory Coast',
      'jm' => 'Jamaica',
      'jp' => 'Japan',
      'jo' => 'Jordan',
      'kz' => 'Kazakhstan',
      'ke' => 'Kenya',
      'ki' => 'Kiribati',
      'kp' => 'North Korea',
      'kr' => 'South Korea',
      'kw' => 'Kuwait',
      'kg' => 'Kyrgyzstan',
      'la' => 'Laos',
      'lv' => 'Latvia',
      'lb' => 'Lebanon',
      'ls' => 'Lesotho',
      'lr' => 'Liberia',
      'ly' => 'Libya',
      'li' => 'Liechtenstein',
      'lt' => 'Lithuania',
      'lu' => 'Luxembourg',
      'mo' => 'Macau',
      'mk' => 'Macedonia',
      'mg' => 'Madagascar',
      'mw' => 'Malawi',
      'my' => 'Malaysia',
      'mv' => 'Maldives',
      'ml' => 'Mali',
      'mt' => 'Malta',
      'mh' => 'Marshall Islands',
      'mq' => 'Martinique',
      'mr' => 'Mauritania',
      'mu' => 'Mauritius',
      'yt' => 'Mayotte',
      'mx' => 'Mexico',
      'fm' => 'Micronesia',
      'md' => 'Moldova',
      'mc' => 'Monaco',
      'mn' => 'Mongolia',
      'me' => 'Montenegro',
      'ms' => 'Montserrat',
      'ma' => 'Morocco',
      'mz' => 'Mozambique',
      'mm' => 'Myanmar',
      'na' => 'Namibia',
      'nr' => 'Nauru',
      'np' => 'Nepal',
      'nl' => 'Netherlands',
      'an' => 'Netherlands Antilles',
      'nc' => 'New Caledonia',
      'nz' => 'New Zealand',
      'ni' => 'Nicaragua',
      'ne' => 'Niger',
      'ng' => 'Nigeria',
      'nu' => 'Niue',
      'nf' => 'Norfolk Island',
      'mp' => 'Northern Mariana Islands',
      'no' => 'Norway',
      'om' => 'Oman',
      'pk' => 'Pakistan',
      'pw' => 'Palau',
      'pa' => 'Panama',
      'pg' => 'Papua New Guinea',
      'py' => 'Paraguay',
      'pe' => 'Peru',
      'ph' => 'Philippines',
      'pn' => 'Pitcairn Island',
      'pl' => 'Poland',
      'pt' => 'Portugal',
      'pr' => 'Puerto Rico',
      'qa' => 'Qatar',
      're' => 'Reunion',
      'ro' => 'Romania',
      'ru' => 'Russia',
      'rw' => 'Rwanda',
      'gs' => 'S. Georgia and S. Sandwich Isls.',
      'kn' => 'Saint Kitts & Nevis',
      'lc' => 'Saint Lucia',
      'vc' => 'Saint Vincent and The Grenadines',
      'ws' => 'Samoa',
      'sm' => 'San Marino',
      'st' => 'Sao Tome and Principe',
      'sa' => 'Saudi Arabia',
      'sn' => 'Senegal',
      'rs' => 'Serbia',
      'sc' => 'Seychelles',
      'sl' => 'Sierra Leone',
      'sg' => 'Singapore',
      'sk' => 'Slovakia',
      'si' => 'Slovenia',
      'sb' => 'Solomon Islands',
      'so' => 'Somalia',
      'za' => 'South Africa',
      'es' => 'Spain',
      'lk' => 'Sri Lanka',
      'sh' => 'St. Helena',
      'pm' => 'St. Pierre and Miquelon',
      'sd' => 'Sudan',
      'sr' => 'Suriname',
      'sj' => 'Svalbard and Jan Mayen Islands',
      'sz' => 'Swaziland',
      'se' => 'Sweden',
      'ch' => 'Switzerland',
      'sy' => 'Syria',
      'tw' => 'Taiwan',
      'tj' => 'Tajikistan',
      'tz' => 'Tanzania',
      'th' => 'Thailand',
      'tg' => 'Togo',
      'tk' => 'Tokelau',
      'to' => 'Tonga',
      'tt' => 'Trinidad and Tobago',
      'tn' => 'Tunisia',
      'tr' => 'Turkey',
      'tm' => 'Turkmenistan',
      'tc' => 'Turks and Caicos Islands',
      'tv' => 'Tuvalu',
      'um' => 'U.S. Minor Outlying Islands',
      'ug' => 'Uganda',
      'ua' => 'Ukraine',
      'ae' => 'United Arab Emirates',
      'uk' => 'United Kingdom',
      'uy' => 'Uruguay',
      'uz' => 'Uzbekistan',
      'vu' => 'Vanuatu',
      'va' => 'Vatican City',
      've' => 'Venezuela',
      'vn' => 'Vietnam',
      'vi' => 'Virgin Islands',
      'wf' => 'Wallis and Futuna Islands',
      'eh' => 'Western Sahara',
      'ye' => 'Yemen',
      'cd' => 'Democratic Republic of The Congo',
      'zm' => 'Zambia',
      'zw' => 'Zimbabwe'
    ); // array
    
    /**
    * Return array of countries
    *
    * @access public
    * @param void
    * @return array
    */
    static function getAll() {
      return self::$countries;
    } // getAll
    
    /**
    * Find specific country by country code
    *
    * @access public
    * @param string $code
    * @return string
    */
    static function getCountryNameByCode($code) {
      return array_var(self::$countries, $code);
    } // getCountryNameByCode
  
  	static function getCountryCodeByName($countryName) {
		$country_codes = array_keys(self::$countries);
		if (in_array($countryName, $country_codes)) { //name is a code
			return $countryName;
		} else {
			foreach ($country_codes as $code) {
				if (strtolower(lang("country $code")) == strtolower($countryName)) return $code;
			}
		}
		return '';
	}
  } // CountryCodes

?>