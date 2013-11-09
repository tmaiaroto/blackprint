<?php
namespace blackprint\extensions\helper;

use lithium\util\Inflector;
use lithium\template\View;
use lithium\core\Libraries;
use lithium\util\Set;

class BlackprintForm extends \lithium\template\helper\Form {

	/**
	 * This is a convenience wrapper around the Form::field() helper method.
	 * It will essentially define a template that uses Twitter Bootstrap markup
	 * for a form input field. There's also additional options now available.
	 * 
	 * NOTE: For this helper to fill in the current value, you must use $this->BootstrapForm->create()
	 * when creating forms.
	 * 
	 * help - The help text below the input field
	 * helpElement - By default, "small" will be used, but this can be changed to say "p" for example
	 * helpClass - An optional class to apply to the help text element
	 * class - Is still the same class applied to the input element, by default it is "input-xlarge" but can be changed
	 * 
	 * @see \lithium\template\helper\Form::field()
	 */
	public function field($name, array $options = array()) {
		$options += array(
			'help' => false,
			'helpClass' => '',
			'class' => 'form-control',
			'groupClass' => 'form-group',
			'groupStyle' => '',
			'size' => false,
			'type' => 'text',
			'options' => array() // select/radio options
		);
		if(!isset($options['helpElement'])) {
			$options['helpElement'] = $options['type'] == 'checkbox' ? 'p':'small';
		}
		
		$help = $options['help'];
		$helpElement = $options['helpElement'];
		$helpClass = $options['helpClass'];
		$groupClass = $options['groupClass'];
		$groupStyle = $options['groupStyle'];
		$size = $options['size'];
		if($size) {
			$groupClass .= ' col-md-' . $size;
		}
		
		// Allow an option of 'list' or 'options' ... list is consistent with the parent helper.
		// Options is confusing but makes more sense.
		$list = isset($options['list']) ? $options['list']:$options['options'];
		
		// Don't pass along these options...
		unset($options['help']);
		unset($options['helpElement']);
		unset($options['helpClass']);
		unset($options['options']);
		unset($options['size']);
		unset($options['groupStyle']);
		
		$prepend = isset($options['prepend']) ? $options['prepend']:'';
		$append = isset($options['append']) ? $options['append']:'';
		$inputDivClass = '';
		if(!empty($prepend)) {
			$inputDivClass .= ' input-prepend';
		}
		if(!empty($append)) {
			$inputDivClass .= ' input-append';
		}
		trim($inputDivClass);
		unset($options['prepend']);
		unset($options['append']);
		
		$options['template'] = '<div class="' . $groupClass . '" style="' . $groupStyle . '"{:wrap}>{:label}<div class="' . $inputDivClass . '">' . $prepend . '{:input}' . $append . '</div>';
			if($help) {
				$options['template'] .= '<' . $helpElement . ' class="' . $helpClass . '">' . $help . '</' . $helpElement . '>';
			}
		$options['template'] .= '{:error}</div>';

		// Format for checkboxes.
		if($options['type'] == 'checkbox') {
			$options['class'] = ''; // no input class for checkboxes
			$options['template'] = '<div class="' . $groupClass . '" style="' . $groupStyle . '"><strong>{:label}</strong><div class="checkbox" {:wrap}><label>{:input}';
				if($help) {
					$options['template'] .= $help;
				}
			$options['template'] .= '</label>';
			$options['template'] .= '</div>{:error}</div>';
		}
		
		// Format for radio buttons.
		if($options['type'] == 'radio') {
			$options['class'] = ''; // no input class for radio buttons
			$options['template'] = '<div class="' . $groupClass . '" style="' . $groupStyle . '" {:wrap}><div>{:label}';
				$i = 1;
				foreach($list as $value => $valueLabel) {
					$rOptions = $options;
					$rOptions['value'] = $value;
					$rOptions['label'] = $valueLabel;
					$rOptions['id'] = $name . $i;
					$rOptions['type'] = 'radio';
					$i++;
					
					if (!isset($rOptions['checked']) && ($bound = $this->binding($name)->data)) {
						$rOptions['checked'] = ($bound == $value);
					}
					$rOptions['template'] = '<label class="radio' . (isset($options['inline']) && $options['inline'] === true ? ' inline':'') . '">{:input}<span class="' . (isset($options['optionClass']) ? $options['optionClass']:'') . '">' . $valueLabel . '</span></label>';
					
					$options['template'] .= parent::field($name, $rOptions);
				}
				if($help) {
					$options['template'] .= '<' . $helpElement . ' class="' . $helpClass . '">' . $help . '</' . $helpElement . '>';
				}
			$options['template'] .= '</div>{:error}</div>';
		}
		
		// Format for select lists.
		// NOTE: parent::error() call will only show first error like this...Not all... FIX.
		if($options['type'] == 'select') {
			$selectOptions = $options;
			unset($selectOptions['template']);
			unset($selectOptions['label']);
			unset($selectOptions['type']);
			
			$options['template'] = '<div class="' . $groupClass . '" style="' . $groupStyle . '" {:wrap}>';
			$label = isset($options['label']) ? $options['label']:Inflector::humanize(preg_replace('/[\[\]\.]/', '_', $name));
			$options['template'] .= parent::label($name, $label);
			$options['template'] .= parent::select($name, $list, $selectOptions);
				if($help) {
					$options['template'] .= '<br /><' . $helpElement . ' class="' . $helpClass . '">' . $help . '</' . $helpElement . '>';
				}
			$options['template'] .= '<div></div>';
			$options['template'] .= parent::error($name);
			$options['template'] .= '</div>';
		}
		
		if($options['type'] == 'select') {
			return parent::select($name, $list, $options);
		}

		// Format datepicker, using: http://www.eyecon.ro/bootstrap-datepicker/
		// Chrome has a really awesome datepicker when input type="date" but sadly other browsers don't, so we need to set it to "text" and use some JavaScript.
		if($options['type'] == 'date') {
			$options['type'] = 'text';
			// ensure the datepicker class is set
			if(!strstr($optinos['class'], 'datepicker')) {
				$options['class'] .= ' datepicker'; 
			}
			// Other options include; data-date-format, data-date, data-date-viewmode, data-date-minviewmode, etc.
			// @see http://www.eyecon.ro/bootstrap-datepicker/ for more...
			// But catch a special option, 'append'
			$appendClass = '';
			if(!empty($append)) {
				$appendClass = !empty($inputDivClass) ? ' input-append':'input-append';
				$append = (is_string($append) && !empty($append)) ? $append:'<span class="add-on"><i class="fa fa-calendar"></i></span>';
			}

			$options['template'] = '<div class="' . $groupClass . '" style="' . $groupStyle . '"{:wrap}>{:label}<div class="' . $inputDivClass .  $appendClass . '">' . $prepend . '{:input}' . $append . '</div>';
				if($help) {
					$options['template'] .= '<' . $helpElement . ' class="' . $helpClass . '">' . $help . '</' . $helpElement . '>';
				}
			$options['template'] .= '{:error}</div>';
		}
		
		// Use the parent helper's method.
		return parent::field($name, $options);
	}
	
	/**
	 * Returns an array of values for US state select options.
	 * 
	 * @return array
	 */
	public function states($provinces=false, $specificOrder=false) {
		$codes = array(
			'AL'=>'Alabama', 
			'AK'=>'Alaska', 
			'AZ'=>'Arizona', 
			'AR'=>'Arkansas', 
			'CA'=>'California', 
			'CO'=>'Colorado', 
			'CT'=>'Connecticut', 
			'DE'=>'Delaware', 
			'DC'=>'District Of Columbia', 
			'FL'=>'Florida', 
			'GA'=>'Georgia', 
			'HI'=>'Hawaii', 
			'ID'=>'Idaho', 
			'IL'=>'Illinois', 
			'IN'=>'Indiana', 
			'IA'=>'Iowa', 
			'KS'=>'Kansas', 
			'KY'=>'Kentucky', 
			'LA'=>'Louisiana', 
			'ME'=>'Maine', 
			'MD'=>'Maryland', 
			'MA'=>'Massachusetts', 
			'MI'=>'Michigan', 
			'MN'=>'Minnesota', 
			'MS'=>'Mississippi', 
			'MO'=>'Missouri', 
			'MT'=>'Montana',
			'NE'=>'Nebraska',
			'NV'=>'Nevada',
			'NH'=>'New Hampshire',
			'NJ'=>'New Jersey',
			'NM'=>'New Mexico',
			'NY'=>'New York',
			'NC'=>'North Carolina',
			'ND'=>'North Dakota',
			'OH'=>'Ohio', 
			'OK'=>'Oklahoma', 
			'OR'=>'Oregon', 
			'PA'=>'Pennsylvania', 
			'RI'=>'Rhode Island', 
			'SC'=>'South Carolina', 
			'SD'=>'South Dakota',
			'TN'=>'Tennessee', 
			'TX'=>'Texas', 
			'UT'=>'Utah', 
			'VT'=>'Vermont', 
			'VA'=>'Virginia', 
			'WA'=>'Washington', 
			'WV'=>'West Virginia', 
			'WI'=>'Wisconsin', 
			'WY'=>'Wyoming'
		);
		
		if($specificOrder) {
			$codes = $this->_sortArrayByArray($codes, $specificOrder);
		}
		
		if($provinces) {
			$states = $codes;
			$codes = array();
			$codes['State'] = $states;
			$codes['Province'] = array(
				"AB" => "Alberta",
				"BC" => "British Columbia",
				"MB" => "Manitoba",
				"NB" => "New Burnswick",
				"NL" => "Newfoundland and Labrador",
				"NS" => "Nova Scotia",
				"NT" => "Northwest Territories",
				"NU" => "Nunavut",
				"ON" => "Ontario",
				"PE" => "Prince Edward Island",
				"QC" => "Quebec",
				"SK" => "Saskatchewan",
				"YT" => "Yukon"
			);
		}
		
		return $codes;
	}
	
	/**
	 * Returns ISO 639 Language codes.
	 * 
	 * @return array
	 */
	public function iso639($specificOrder=false) {
		$codes = array(
			'aa' => 'Afar',
			'ab' => 'Abkhaz',
			'ae' => 'Avestan',
			'af' => 'Afrikaans',
			'ak' => 'Akan',
			'am' => 'Amharic',
			'an' => 'Aragonese',
			'ar' => 'Arabic',
			'as' => 'Assamese',
			'av' => 'Avaric',
			'ay' => 'Aymara',
			'az' => 'Azerbaijani',
			'ba' => 'Bashkir',
			'be' => 'Belarusian',
			'bg' => 'Bulgarian',
			'bh' => 'Bihari',
			'bi' => 'Bislama',
			'bm' => 'Bambara',
			'bn' => 'Bengali',
			'bo' => 'Tibetan Standard, Tibetan, Central',
			'br' => 'Breton',
			'bs' => 'Bosnian',
			'ca' => 'Catalan; Valencian',
			'ce' => 'Chechen',
			'ch' => 'Chamorro',
			'co' => 'Corsican',
			'cr' => 'Cree',
			'cs' => 'Czech',
			'cu' => 'Old Church Slavonic, Church Slavic, Church Slavonic, Old Bulgarian, Old Slavonic',
			'cv' => 'Chuvash',
			'cy' => 'Welsh',
			'da' => 'Danish',
			'de' => 'German',
			'dv' => 'Divehi; Dhivehi; Maldivian;',
			'dz' => 'Dzongkha',
			'ee' => 'Ewe',
			'el' => 'Greek, Modern',
			'en' => 'English',
			'eo' => 'Esperanto',
			'es' => 'Spanish; Castilian',
			'et' => 'Estonian',
			'eu' => 'Basque',
			'fa' => 'Persian',
			'ff' => 'Fula; Fulah; Pulaar; Pular',
			'fi' => 'Finnish',
			'fj' => 'Fijian',
			'fo' => 'Faroese',
			'fr' => 'French',
			'fy' => 'Western Frisian',
			'ga' => 'Irish',
			'gd' => 'Scottish Gaelic; Gaelic',
			'gl' => 'Galician',
			'gn' => 'GuaranÃ­',
			'gu' => 'Gujarati',
			'gv' => 'Manx',
			'ha' => 'Hausa',
			'he' => 'Hebrew (modern)',
			'hi' => 'Hindi',
			'ho' => 'Hiri Motu',
			'hr' => 'Croatian',
			'ht' => 'Haitian; Haitian Creole',
			'hu' => 'Hungarian',
			'hy' => 'Armenian',
			'hz' => 'Herero',
			'ia' => 'Interlingua',
			'id' => 'Indonesian',
			'ie' => 'Interlingue',
			'ig' => 'Igbo',
			'ii' => 'Nuosu',
			'ik' => 'Inupiaq',
			'io' => 'Ido',
			'is' => 'Icelandic',
			'it' => 'Italian',
			'iu' => 'Inuktitut',
			'ja' => 'Japanese (ja)',
			'jv' => 'Javanese (jv)',
			'ka' => 'Georgian',
			'kg' => 'Kongo',
			'ki' => 'Kikuyu, Gikuyu',
			'kj' => 'Kwanyama, Kuanyama',
			'kk' => 'Kazakh',
			'kl' => 'Kalaallisut, Greenlandic',
			'km' => 'Khmer',
			'kn' => 'Kannada',
			'ko' => 'Korean',
			'kr' => 'Kanuri',
			'ks' => 'Kashmiri',
			'ku' => 'Kurdish',
			'kv' => 'Komi',
			'kw' => 'Cornish',
			'ky' => 'Kirghiz, Kyrgyz',
			'la' => 'Latin',
			'lb' => 'Luxembourgish, Letzeburgesch',
			'lg' => 'Luganda',
			'li' => 'Limburgish, Limburgan, Limburger',
			'ln' => 'Lingala',
			'lo' => 'Lao',
			'lt' => 'Lithuanian',
			'lu' => 'Luba-Katanga',
			'lv' => 'Latvian',
			'mg' => 'Malagasy',
			'mh' => 'Marshallese',
			'mi' => 'Maori',
			'mk' => 'Macedonian',
			'ml' => 'Malayalam',
			'mn' => 'Mongolian',
			'mr' => 'Marathi (Mara?hi)',
			'ms' => 'Malay',
			'mt' => 'Maltese',
			'my' => 'Burmese',
			'na' => 'Nauru',
			'nb' => 'Norwegian BokmÃ¥l',
			'nd' => 'North Ndebele',
			'ne' => 'Nepali',
			'ng' => 'Ndonga',
			'nl' => 'Dutch',
			'nn' => 'Norwegian Nynorsk',
			'no' => 'Norwegian',
			'nr' => 'South Ndebele',
			'nv' => 'Navajo, Navaho',
			'ny' => 'Chichewa; Chewa; Nyanja',
			'oc' => 'Occitan',
			'oj' => 'Ojibwe, Ojibwa',
			'om' => 'Oromo',
			'or' => 'Oriya',
			'os' => 'Ossetian, Ossetic',
			'pa' => 'Panjabi, Punjabi',
			'pi' => 'Pali',
			'pl' => 'Polish',
			'ps' => 'Pashto, Pushto',
			'pt' => 'Portuguese',
			'qu' => 'Quechua',
			'rm' => 'Romansh',
			'rn' => 'Kirundi',
			'ro' => 'Romanian, Moldavian, Moldovan',
			'ru' => 'Russian',
			'rw' => 'Kinyarwanda',
			'sa' => 'Sanskrit (Sa?sk?ta)',
			'sc' => 'Sardinian',
			'sd' => 'Sindhi',
			'se' => 'Northern Sami',
			'sg' => 'Sango',
			'si' => 'Sinhala, Sinhalese',
			'sk' => 'Slovak',
			'sl' => 'Slovene',
			'sm' => 'Samoan',
			'sn' => 'Shona',
			'so' => 'Somali',
			'sq' => 'Albanian',
			'sr' => 'Serbian',
			'ss' => 'Swati',
			'st' => 'Southern Sotho',
			'su' => 'Sundanese',
			'sv' => 'Swedish',
			'sw' => 'Swahili',
			'ta' => 'Tamil',
			'te' => 'Telugu',
			'tg' => 'Tajik',
			'th' => 'Thai',
			'ti' => 'Tigrinya',
			'tk' => 'Turkmen',
			'tl' => 'Tagalog',
			'tn' => 'Tswana',
			'to' => 'Tonga (Tonga Islands)',
			'tr' => 'Turkish',
			'ts' => 'Tsonga',
			'tt' => 'Tatar',
			'tw' => 'Twi',
			'ty' => 'Tahitian',
			'ug' => 'Uighur, Uyghur',
			'uk' => 'Ukrainian',
			'ur' => 'Urdu',
			'uz' => 'Uzbek',
			've' => 'Venda',
			'vi' => 'Vietnamese',
			'vo' => 'VolapÃ¼k',
			'wa' => 'Walloon',
			'wo' => 'Wolof',
			'xh' => 'Xhosa',
			'yi' => 'Yiddish',
			'yo' => 'Yoruba',
			'za' => 'Zhuang, Chuang',
			'zh' => 'Chinese',
			'zu' => 'Zulu',
		);
		
		if($specificOrder) {
			$codes = $this->_sortArrayByArray($codes, $specificOrder);
		}
		
		return $codes;
	}
	
	/**
	 * Returns a list of country codes.
	 * 
	 * @param array $specificOrder
	 * @return array
	 */
	public function countryCodes($specificOrder=false) {
		$codes = array(
			'AF'=>'Afghanistan',
			'AL'=>'Albania',
			'DZ'=>'Algeria',
			'AS'=>'American Samoa',
			'AD'=>'Andorra',
			'AO'=>'Angola',
			'AI'=>'Anguilla',
			'AQ'=>'Antarctica',
			'AG'=>'Antigua And Barbuda',
			'AR'=>'Argentina',
			'AM'=>'Armenia',
			'AW'=>'Aruba',
			'AU'=>'Australia',
			'AT'=>'Austria',
			'AZ'=>'Azerbaijan',
			'BS'=>'Bahamas',
			'BH'=>'Bahrain',
			'BD'=>'Bangladesh',
			'BB'=>'Barbados',
			'BY'=>'Belarus',
			'BE'=>'Belgium',
			'BZ'=>'Belize',
			'BJ'=>'Benin',
			'BM'=>'Bermuda',
			'BT'=>'Bhutan',
			'BO'=>'Bolivia',
			'BA'=>'Bosnia And Herzegovina',
			'BW'=>'Botswana',
			'BV'=>'Bouvet Island',
			'BR'=>'Brazil',
			'IO'=>'British Indian Ocean Territory',
			'BN'=>'Brunei',
			'BG'=>'Bulgaria',
			'BF'=>'Burkina Faso',
			'BI'=>'Burundi',
			'KH'=>'Cambodia',
			'CM'=>'Cameroon',
			'CA'=>'Canada',
			'CV'=>'Cape Verde',
			'KY'=>'Cayman Islands',
			'CF'=>'Central African Republic',
			'TD'=>'Chad',
			'CL'=>'Chile',
			'CN'=>'China',
			'CX'=>'Christmas Island',
			'CC'=>'Cocos (Keeling) Islands',
			'CO'=>'Columbia',
			'KM'=>'Comoros',
			'CG'=>'Congo',
			'CK'=>'Cook Islands',
			'CR'=>'Costa Rica',
			'CI'=>'Cote D\'Ivorie (Ivory Coast)',
			'HR'=>'Croatia (Hrvatska)',
			'CU'=>'Cuba',
			'CY'=>'Cyprus',
			'CZ'=>'Czech Republic',
			'CD'=>'Democratic Republic Of Congo (Zaire)',
			'DK'=>'Denmark',
			'DJ'=>'Djibouti',
			'DM'=>'Dominica',
			'DO'=>'Dominican Republic',
			'TP'=>'East Timor',
			'EC'=>'Ecuador',
			'EG'=>'Egypt',
			'SV'=>'El Salvador',
			'GQ'=>'Equatorial Guinea',
			'ER'=>'Eritrea',
			'EE'=>'Estonia',
			'ET'=>'Ethiopia',
			'FK'=>'Falkland Islands (Malvinas)',
			'FO'=>'Faroe Islands',
			'FJ'=>'Fiji',
			'FI'=>'Finland',
			'FR'=>'France',
			'FX'=>'France, Metropolitan',
			'GF'=>'French Guinea',
			'PF'=>'French Polynesia',
			'TF'=>'French Southern Territories',
			'GA'=>'Gabon',
			'GM'=>'Gambia',
			'GE'=>'Georgia',
			'DE'=>'Germany',
			'GH'=>'Ghana',
			'GI'=>'Gibraltar',
			'GR'=>'Greece',
			'GL'=>'Greenland',
			'GD'=>'Grenada',
			'GP'=>'Guadeloupe',
			'GU'=>'Guam',
			'GT'=>'Guatemala',
			'GN'=>'Guinea',
			'GW'=>'Guinea-Bissau',
			'GY'=>'Guyana',
			'HT'=>'Haiti',
			'HM'=>'Heard And McDonald Islands',
			'HN'=>'Honduras',
			'HK'=>'Hong Kong',
			'HU'=>'Hungary',
			'IS'=>'Iceland',
			'IN'=>'India',
			'ID'=>'Indonesia',
			'IR'=>'Iran',
			'IQ'=>'Iraq',
			'IE'=>'Ireland',
			'IL'=>'Israel',
			'IT'=>'Italy',
			'JM'=>'Jamaica',
			'JP'=>'Japan',
			'JO'=>'Jordan',
			'KZ'=>'Kazakhstan',
			'KE'=>'Kenya',
			'KI'=>'Kiribati',
			'KW'=>'Kuwait',
			'KG'=>'Kyrgyzstan',
			'LA'=>'Laos',
			'LV'=>'Latvia',
			'LB'=>'Lebanon',
			'LS'=>'Lesotho',
			'LR'=>'Liberia',
			'LY'=>'Libya',
			'LI'=>'Liechtenstein',
			'LT'=>'Lithuania',
			'LU'=>'Luxembourg',
			'MO'=>'Macau',
			'MK'=>'Macedonia',
			'MG'=>'Madagascar',
			'MW'=>'Malawi',
			'MY'=>'Malaysia',
			'MV'=>'Maldives',
			'ML'=>'Mali',
			'MT'=>'Malta',
			'MH'=>'Marshall Islands',
			'MQ'=>'Martinique',
			'MR'=>'Mauritania',
			'MU'=>'Mauritius',
			'YT'=>'Mayotte',
			'MX'=>'Mexico',
			'FM'=>'Micronesia',
			'MD'=>'Moldova',
			'MC'=>'Monaco',
			'MN'=>'Mongolia',
			'MS'=>'Montserrat',
			'MA'=>'Morocco',
			'MZ'=>'Mozambique',
			'MM'=>'Myanmar (Burma)',
			'NA'=>'Namibia',
			'NR'=>'Nauru',
			'NP'=>'Nepal',
			'NL'=>'Netherlands',
			'AN'=>'Netherlands Antilles',
			'NC'=>'New Caledonia',
			'NZ'=>'New Zealand',
			'NI'=>'Nicaragua',
			'NE'=>'Niger',
			'NG'=>'Nigeria',
			'NU'=>'Niue',
			'NF'=>'Norfolk Island',
			'KP'=>'North Korea',
			'MP'=>'Northern Mariana Islands',
			'NO'=>'Norway',
			'OM'=>'Oman',
			'PK'=>'Pakistan',
			'PW'=>'Palau',
			'PA'=>'Panama',
			'PG'=>'Papua New Guinea',
			'PY'=>'Paraguay',
			'PE'=>'Peru',
			'PH'=>'Philippines',
			'PN'=>'Pitcairn',
			'PL'=>'Poland',
			'PT'=>'Portugal',
			'PR'=>'Puerto Rico',
			'QA'=>'Qatar',
			'RE'=>'Reunion',
			'RO'=>'Romania',
			'RU'=>'Russia',
			'RW'=>'Rwanda',
			'SH'=>'Saint Helena',
			'KN'=>'Saint Kitts And Nevis',
			'LC'=>'Saint Lucia',
			'PM'=>'Saint Pierre And Miquelon',
			'VC'=>'Saint Vincent And The Grenadines',
			'SM'=>'San Marino',
			'ST'=>'Sao Tome And Principe',
			'SA'=>'Saudi Arabia',
			'SN'=>'Senegal',
			'SC'=>'Seychelles',
			'SL'=>'Sierra Leone',
			'SG'=>'Singapore',
			'SK'=>'Slovak Republic',
			'SI'=>'Slovenia',
			'SB'=>'Solomon Islands',
			'SO'=>'Somalia',
			'ZA'=>'South Africa',
			'GS'=>'South Georgia And South Sandwich Islands',
			'KR'=>'South Korea',
			'ES'=>'Spain',
			'LK'=>'Sri Lanka',
			'SD'=>'Sudan',
			'SR'=>'Suriname',
			'SJ'=>'Svalbard And Jan Mayen',
			'SZ'=>'Swaziland',
			'SE'=>'Sweden',
			'CH'=>'Switzerland',
			'SY'=>'Syria',
			'TW'=>'Taiwan',
			'TJ'=>'Tajikistan',
			'TZ'=>'Tanzania',
			'TH'=>'Thailand',
			'TG'=>'Togo',
			'TK'=>'Tokelau',
			'TO'=>'Tonga',
			'TT'=>'Trinidad And Tobago',
			'TN'=>'Tunisia',
			'TR'=>'Turkey',
			'TM'=>'Turkmenistan',
			'TC'=>'Turks And Caicos Islands',
			'TV'=>'Tuvalu',
			'UG'=>'Uganda',
			'UA'=>'Ukraine',
			'AE'=>'United Arab Emirates',
			'UK'=>'United Kingdom',
			'US'=>'United States',
			'UM'=>'United States Minor Outlying Islands',
			'UY'=>'Uruguay',
			'UZ'=>'Uzbekistan',
			'VU'=>'Vanuatu',
			'VA'=>'Vatican City (Holy See)',
			'VE'=>'Venezuela',
			'VN'=>'Vietnam',
			'VG'=>'Virgin Islands (British)',
			'VI'=>'Virgin Islands (US)',
			'WF'=>'Wallis And Futuna Islands',
			'EH'=>'Western Sahara',
			'WS'=>'Western Samoa',
			'YE'=>'Yemen',
			'YU'=>'Yugoslavia',
			'ZM'=>'Zambia',
			'ZW'=>'Zimbabwe'
		);
		
		if($specificOrder) {
			$codes = $this->_sortArrayByArray($codes, $specificOrder);
		}
		
		return $codes;
	}
	
	/**
	 * Returns an array of countries.
	 * 
	 * @return array
	 */
	public function countries() {
		return array("Afghanistan", "Albania", "Algeria", "American Samoa", "Andorra", "Angola", "Anguilla", "Antarctica", "Antigua and Barbuda", "Argentina", "Armenia", "Aruba", "Australia", "Austria", "Azerbaijan", "Bahamas", "Bahrain", "Bangladesh", "Barbados", "Belarus", "Belgium", "Belize", "Benin", "Bermuda", "Bhutan", "Bolivia", "Bosnia and Herzegowina", "Botswana", "Bouvet Island", "Brazil", "British Indian Ocean Territory", "Brunei Darussalam", "Bulgaria", "Burkina Faso", "Burundi", "Cambodia", "Cameroon", "Canada", "Cape Verde", "Cayman Islands", "Central African Republic", "Chad", "Chile", "China", "Christmas Island", "Cocos (Keeling) Islands", "Colombia", "Comoros", "Congo", "Congo, the Democratic Republic of the", "Cook Islands", "Costa Rica", "Cote d'Ivoire", "Croatia (Hrvatska)", "Cuba", "Cyprus", "Czech Republic", "Denmark", "Djibouti", "Dominica", "Dominican Republic", "East Timor", "Ecuador", "Egypt", "El Salvador", "Equatorial Guinea", "Eritrea", "Estonia", "Ethiopia", "Falkland Islands (Malvinas)", "Faroe Islands", "Fiji", "Finland", "France", "France Metropolitan", "French Guiana", "French Polynesia", "French Southern Territories", "Gabon", "Gambia", "Georgia", "Germany", "Ghana", "Gibraltar", "Greece", "Greenland", "Grenada", "Guadeloupe", "Guam", "Guatemala", "Guinea", "Guinea-Bissau", "Guyana", "Haiti", "Heard and Mc Donald Islands", "Holy See (Vatican City State)", "Honduras", "Hong Kong", "Hungary", "Iceland", "India", "Indonesia", "Iran (Islamic Republic of)", "Iraq", "Ireland", "Israel", "Italy", "Jamaica", "Japan", "Jordan", "Kazakhstan", "Kenya", "Kiribati", "Korea, Democratic People's Republic of", "Korea, Republic of", "Kuwait", "Kyrgyzstan", "Lao, People's Democratic Republic", "Latvia", "Lebanon", "Lesotho", "Liberia", "Libyan Arab Jamahiriya", "Liechtenstein", "Lithuania", "Luxembourg", "Macau", "Macedonia, The Former Yugoslav Republic of", "Madagascar", "Malawi", "Malaysia", "Maldives", "Mali", "Malta", "Marshall Islands", "Martinique", "Mauritania", "Mauritius", "Mayotte", "Mexico", "Micronesia, Federated States of", "Moldova, Republic of", "Monaco", "Mongolia", "Montserrat", "Morocco", "Mozambique", "Myanmar", "Namibia", "Nauru", "Nepal", "Netherlands", "Netherlands Antilles", "New Caledonia", "New Zealand", "Nicaragua", "Niger", "Nigeria", "Niue", "Norfolk Island", "Northern Mariana Islands", "Norway", "Oman", "Pakistan", "Palau", "Panama", "Papua New Guinea", "Paraguay", "Peru", "Philippines", "Pitcairn", "Poland", "Portugal", "Puerto Rico", "Qatar", "Reunion", "Romania", "Russian Federation", "Rwanda", "Saint Kitts and Nevis", "Saint Lucia", "Saint Vincent and the Grenadines", "Samoa", "San Marino", "Sao Tome and Principe", "Saudi Arabia", "Senegal", "Seychelles", "Sierra Leone", "Singapore", "Slovakia (Slovak Republic)", "Slovenia", "Solomon Islands", "Somalia", "South Africa", "South Georgia and the South Sandwich Islands", "Spain", "Sri Lanka", "St. Helena", "St. Pierre and Miquelon", "Sudan", "Suriname", "Svalbard and Jan Mayen Islands", "Swaziland", "Sweden", "Switzerland", "Syrian Arab Republic", "Taiwan, Province of China", "Tajikistan", "Tanzania, United Republic of", "Thailand", "Togo", "Tokelau", "Tonga", "Trinidad and Tobago", "Tunisia", "Turkey", "Turkmenistan", "Turks and Caicos Islands", "Tuvalu", "Uganda", "Ukraine", "United Arab Emirates", "United Kingdom", "United States", "United States Minor Outlying Islands", "Uruguay", "Uzbekistan", "Vanuatu", "Venezuela", "Vietnam", "Virgin Islands (British)", "Virgin Islands (U.S.)", "Wallis and Futuna Islands", "Western Sahara", "Yemen", "Yugoslavia", "Zambia", "Zimbabwe");
	}
	
	/**
	 * Sorts an array by an array (key values).
	 * 
	 * @param array $array
	 * @param array$orderArray
	 * @return array
	 */
	function _sortArrayByArray($array,$orderArray) {
		$ordered = array();
		foreach($orderArray as $key) {
			if(array_key_exists($key,$array)) {
				$ordered[$key] = $array[$key];
				unset($array[$key]);
			}
		}
		return $ordered + $array;
	}
}
?>