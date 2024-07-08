<?php

define('WLMLEADGEN_VERSION', '0.01');

// Extension Information.
$WLMExtension = [
    'Name'        => 'Lead Gen Discs',
    'URL'         => '',
    'Version'     => WLMLEADGEN_VERSION,
    'Description' => 'WishList Member Extension that allows integration with Lead Gen Discs',
    'Author'      => 'WishList Products',
    'AuthorURL'   => 'http://www.wishlistproducts.com/',
    'File'        => __FILE__,
];

if (! class_exists('WLMLeadGenCore')) {

    /**
     * WishList Member LeadGen Core Class
     */
    class WLMLeadGenCore
    {
        /**
         * LeadGen Country List (populated by Constructor)
         *
         * @var array
         */
        public $countries = [];

        /**
         * Errors array
         *
         * @var array
         */
        public $errors = [];

        /**
         * Lead Gen Discs UNIQUE URL
         *
         * @var string
         */
        public $url;

        /**
         * LeadGen CID
         *
         * @var string
         */
        public $cid;

        /**
         * LeadGen API Key
         *
         * @var string
         */
        public $api;

        /**
         * LeadGen Transaction Mode (Just in case we needed it later to
         * determine sample data from actual data sent)
         *
         * @var string
         */
        public $mode;

        /**
         * Constructor
         *
         * @param string $cid  LeadGen CID
         * @param string $api  LeadGen API Key
         * @param string $url  LeadGen UNIQUE URL
         * @param string $mode LeadGen Transaction Mode.  Values can be LIVE or TEST
         */
        public function __construct($cid, $api, $url, $mode = 'LIVE')
        {
            $this->cid = wlm_trim($cid);
            $this->api = wlm_trim($api);
            $this->url = wlm_trim($url);
            $mode      = trim(strtoupper($mode));
            if ('LIVE' !== $mode) {
                $mode = 'TEST';
            }
            $this->mode      = $mode;
            $this->countries = [
                'USA' => 'United States',
                'AFG' => 'Afghanistan',
                'ALB' => 'Albania',
                'DZA' => 'Algeria',
                'ASM' => 'American Samoa',
                'AND' => 'Andorra',
                'AGO' => 'Angola',
                'AIA' => 'Anguilla',
                'ATA' => 'Antarctica',
                'ATG' => 'Antigua and Barbuda',
                'ARG' => 'Argentina',
                'ARM' => 'Armenia',
                'ABW' => 'Aruba',
                'AUS' => 'Australia',
                'AUT' => 'Austria',
                'AZE' => 'Azerbaijan',
                'BHS' => 'Bahamas',
                'BHR' => 'Bahrain',
                'BGD' => 'Bangladesh',
                'BRB' => 'Barbados',
                'BLR' => 'Belarus',
                'BEL' => 'Belgium',
                'BLZ' => 'Belize',
                'BEN' => 'Benin',
                'BMU' => 'Bermuda',
                'BTN' => 'Bhutan',
                'BOL' => 'Bolivia',
                'BIH' => 'Bosnia and Herzegowina',
                'BWA' => 'Botswana',
                'BVT' => 'Bouvet Island',
                'BRA' => 'Brazil',
                'IOT' => 'British Indian Ocean Territory',
                'BRN' => 'Brunei Darussalam',
                'BGR' => 'Bulgaria',
                'BFA' => 'Burkina Faso',
                'BDI' => 'Burundi',
                'KHM' => 'Cambodia',
                'CMR' => 'Cameroon',
                'CAN' => 'Canada',
                'CPV' => 'Cape Verde',
                'CYM' => 'Cayman Islands',
                'CAF' => 'Central African Republic',
                'TCD' => 'Chad',
                'CHL' => 'Chile',
                'CHN' => 'China',
                'CXR' => 'Christmas Island',
                'CCK' => 'Cocos (Keeling) Islands',
                'COL' => 'Colombia',
                'COM' => 'Comoros',
                'COD' => 'Congo, Democratic Republic of (was Zaire)',
                'COG' => 'Congo, People\'s Republic of',
                'COK' => 'Cook Islands',
                'CRI' => 'Costa Rica',
                'CIV' => 'Cote D\'Ivoire',
                'HRV' => 'Croatia',
                'CUB' => 'Cuba',
                'CYP' => 'Cyprus',
                'CZE' => 'Czech Republic',
                'DNK' => 'Denmark',
                'DJI' => 'Djibouti',
                'DMA' => 'Dominica',
                'DOM' => 'Dominican Republic',
                'TLS' => 'East Timor',
                'ECU' => 'Ecuador',
                'EGY' => 'Egypt',
                'SLV' => 'El Salvador',
                'GNQ' => 'Equatorial Guinea',
                'ERI' => 'Eritrea',
                'EST' => 'Estonia',
                'ETH' => 'Ethiopia',
                'FLK' => 'Falkland Islands',
                'FRO' => 'Faroe Islands',
                'FJI' => 'Fiji',
                'FIN' => 'Finland',
                'FRA' => 'France',
                'GUF' => 'French Guiana',
                'PYF' => 'French Polynesia',
                'ATF' => 'French Southern Territories',
                'GAB' => 'Gabon',
                'GMB' => 'Gambia',
                'GEO' => 'Georgia',
                'DEU' => 'Germany',
                'GHA' => 'Ghana',
                'GIB' => 'Gibraltar',
                'GRC' => 'Greece',
                'GRL' => 'Greenland',
                'GRD' => 'Grenada',
                'GLP' => 'Guadeloupe',
                'GUM' => 'Guam',
                'GTM' => 'Guatemala',
                'GIN' => 'Guinea',
                'GNB' => 'Guinea-Bissau',
                'GUY' => 'Guyana',
                'HTI' => 'Haiti',
                'HMD' => 'Heard and McDonald Islands',
                'HND' => 'Honduras',
                'HKG' => 'Hong Kong',
                'HUN' => 'Hungary',
                'ISL' => 'Iceland',
                'IND' => 'India',
                'IDN' => 'Indonesia',
                'IRN' => 'Iran',
                'IRQ' => 'Iraq',
                'IRL' => 'Ireland',
                'ISR' => 'Israel',
                'ITA' => 'Italy',
                'JAM' => 'Jamaica',
                'JPN' => 'Japan',
                'JOR' => 'Jordan',
                'KAZ' => 'Kazakhstan',
                'KEN' => 'Kenya',
                'KIR' => 'Kiribati',
                'PRK' => 'Korea, Democratic People\'s Republic of',
                'KOR' => 'Korea, Republic of',
                'KWT' => 'Kuwait',
                'KGZ' => 'Kyrgyzstan',
                'LAO' => 'Lao People\'s Democratic Republic',
                'LVA' => 'Latvia',
                'LBN' => 'Lebanon',
                'LSO' => 'Lesotho',
                'LBR' => 'Liberia',
                'LBY' => 'Libyan Arab Jamahiriya',
                'LIE' => 'Liechtenstein',
                'LTU' => 'Lithuania',
                'LUX' => 'Luxembourg',
                'MAC' => 'Macau',
                'MKD' => 'Macedonia',
                'MDG' => 'Madagascar',
                'MWI' => 'Malawi',
                'MYS' => 'Malaysia',
                'MDV' => 'Maldives',
                'MLI' => 'Mali',
                'MLT' => 'Malta',
                'MHL' => 'Marshall Islands',
                'MTQ' => 'Martinique',
                'MRT' => 'Mauritania',
                'MUS' => 'Mauritius',
                'MYT' => 'Mayotte',
                'MEX' => 'Mexico',
                'FSM' => 'Micronesia, Federated States of',
                'MDA' => 'Moldova, Republic of',
                'MCO' => 'Monaco',
                'MNG' => 'Mongolia',
                'MSR' => 'Montserrat',
                'MAR' => 'Morocco',
                'MOZ' => 'Mozambique',
                'MMR' => 'Myanmar',
                'NAM' => 'Namibia',
                'NRU' => 'Nauru',
                'NPL' => 'Nepal',
                'NLD' => 'Netherlands',
                'ANT' => 'Netherlands Antilles',
                'NCL' => 'New Caledonia',
                'NZL' => 'New Zealand',
                'NIC' => 'Nicaragua',
                'NER' => 'Niger',
                'NGA' => 'Nigeria',
                'NIU' => 'Niue',
                'NFK' => 'Norfolk Island',
                'MNP' => 'Northern Mariana Islands',
                'NOR' => 'Norway',
                'OMN' => 'Oman',
                'PAK' => 'Pakistan',
                'PLW' => 'Palau',
                'PAN' => 'Panama',
                'PNG' => 'Papua New Guinea',
                'PRY' => 'Paraguay',
                'PER' => 'Peru',
                'PHL' => 'Philippines',
                'PCN' => 'Pitcairn',
                'POL' => 'Poland',
                'PRT' => 'Portugal',
                'PRI' => 'Puerto Rico',
                'QAT' => 'Qatar',
                'REU' => 'Reunion',
                'ROU' => 'Romania',
                'RUS' => 'Russian Federation',
                'RWA' => 'Rwanda',
                'KNA' => 'Saint Kitts and Nevis',
                'LCA' => 'Saint Lucia',
                'VCT' => 'Saint Vincent and The Grenadines',
                'WSM' => 'Samoa',
                'SMR' => 'San Marino',
                'STP' => 'Sao Tome and Principe',
                'SAU' => 'Saudi Arabia',
                'SEN' => 'Senegal',
                'SYC' => 'Seychelles',
                'SLE' => 'Sierr Leone',
                'SGP' => 'Singapore',
                'SVK' => 'Slovakia',
                'SVN' => 'Slovenia',
                'SLB' => 'Solomon Islands',
                'SOM' => 'Somalia',
                'ZAF' => 'South Africa',
                'SGS' => 'South Georgia and the South Sandwich Islands',
                'ESP' => 'Spain',
                'LKA' => 'Sri Lanka',
                'SHN' => 'St. Helena',
                'SPM' => 'St. Pierre and Miquelon',
                'SDN' => 'Sudan',
                'SUR' => 'Suriname',
                'SJM' => 'Svalbard and Jan Mayen Islands',
                'SWZ' => 'Swaziland',
                'SWE' => 'Sweden',
                'CHE' => 'Switzerland',
                'SYR' => 'Syrian Arab Republic',
                'TWN' => 'Taiwan',
                'TJK' => 'Tajikistan',
                'TZA' => 'Tanzania, United Republic of',
                'THA' => 'Thailand',
                'TGO' => 'Togo',
                'TKL' => 'Tokelau',
                'TON' => 'Tonga',
                'TTO' => 'Trinidad and Tobago',
                'TUN' => 'Tunisia',
                'TUR' => 'Turkey',
                'TKM' => 'Turkmenistan',
                'TCA' => 'Turks and Caicos Islands',
                'TUV' => 'Tuvalu',
                'UGA' => 'Uganda',
                'UKR' => 'Ukraine',
                'ARE' => 'United Arab Emirates',
                'GBR' => 'United Kingdom',
                'UMI' => 'United States Minor Outlying Islands',
                'URY' => 'Uruguay',
                'UZB' => 'Uzbekistan',
                'VUT' => 'Vanuatu',
                'VAT' => 'Vatican City State (Holy See)',
                'VEN' => 'Venezuela',
                'VNM' => 'VietNam',
                'VGB' => 'Virgin Islands (British)',
                'VIR' => 'Virgin Islands (U.S.)',
                'WLF' => 'Wallis and Futuna Islands',
                'ESH' => 'Western Sahara',
                'YEM' => 'Yemen',
                'YUG' => 'Yugoslavia',
                'ZMB' => 'Zambia',
                'ZWE' => 'Zimbabwe',
            ];
        }

        /**
         * PlaceOrder
         *
         * @param  string $userid
         * @param  string $fname
         * @param  string $lname
         * @param  string $email
         * @param  string $company
         * @param  string $address1
         * @param  string $address2
         * @param  string $city
         * @param  string $state
         * @param  string $zip
         * @param  string $country
         * @param  string $ContactHome
         * @param  string $ContactWork
         * @param  string $ContactFax
         * @param  string $date
         * @param  string $shipping
         * @param  string $product_id
         * @param  string $quantity
         * @return array Array containing error code, status , key
         */
        public function PlaceOrder($userid, $fname, $lname, $email, $company, $address1, $address2, $city, $state, $zip, $country, $ContactHome, $ContactWork, $ContactFax, $date, $shipping, $product_id, $quantity)
        {
            $products = func_get_args();
            for ($i = 0; $i < 16; $i++) {
                array_shift($products);
            }

            $xml = <<<STRING
<sales>
   <sale>
      <cid>%s</cid>
      <apikey>%s</apikey>
      <miscID>%s</miscID>
      <email>%s</email>
      <bill>
         <contact>
            <fname>%s</fname>
            <lname>%s</lname>
            <organization>%s</organization>
         </contact>
         <address>
            <line1>%s</line1>
            <line2>%s</line2>
            <city>%s</city>
            <state>%s</state>
            <zip>%s</zip>
            <country>%s</country>
         </address>
      </bill>
      <phonehome>%s</phonehome>
      <phonework>%s</phonework>
      <phonefax>%s</phonefax>
      <orderDate>%s</orderDate>
      <items>
					%s
      </items>
      <ship>
         <method>%s</method>
      </ship>
   </sale>
</sales>
STRING;

            // Check country against Leadgen list.
            if ('' == $this->countries[ $country ]) {
                $dum = $this->Err('INVALID COUNTRY');
            }
            // State/region is required.
            if (empty($city)) {
                $dum = $this->Err('NO CITY SPECIFIED');
            }
            // State/region is required.
            if (empty($state)) {
                $dum = $this->Err('NO STATE/REGION SPECIFIED');
            }
            // Zip code required.
            if (empty($zip)) {
                $dum = $this->Err('NO ZIP CODE SPECIFIED');
            }

            $prods = $this->PrepareProducts($products);
            if (! $prods) {
                return false;
            }

            $miscID = $userid . '{' . wlm_date('mdY-hisA') . '}';
            $xml    = sprintf($xml, $this->cid, $this->api, wlm_trim($userid), $miscID, wlm_trim($fname), wlm_trim($lname), wlm_trim($company), wlm_trim($address1), wlm_trim($address2), wlm_trim($city), $state, $zip, $country, $ContactHome, $ContactWork, $ContactFax, $date, $prods, wlm_trim($shipping));
            $result = $this->XMLRequest($xml);

            $return = [];
            preg_match('/<code>(.*?)<\/code>/i', $result, $match);
            $return['ErrCode'] = $match[1];
            preg_match('/<key>(.*?)<\/key>/i', $result, $match);
            $return['Key'] = $match[1];
            preg_match('/<msg>(.*?)<\/msg>/i', $result, $match);
            $return['MSG'] = $match[1];
            if ('SUCCESS' !== $return['ErrCode']) {
                $dum = $this->Err('LeadGen Error: ' . $return['Key']);
            }
            if (count($this->errors) > 0) {
                return $this->errors;
            }

            return $miscID;
        }

        public function PrepareProducts($products)
        {
            // Products array must be in pairs of product_id and quantity.
            if (count($products) % 2) {
                return $this->Err('INVALID PRODUCT LIST');
            }
            $xml         = <<<STRING
	<item>
		<sku>%s</sku>
		<quantity>%s</quantity>
	</item>
STRING;
            $numproducts = count($products);
            $prods       = '';
            for ($i = 0; $i < $numproducts; $i += 2) {
                $prods .= sprintf($xml, wlm_trim($products[ $i ]), wlm_trim($products[ $i + 1 ]));
            }
            return $prods;
        }

        /**
         * XMLRequest
         *
         * @param  <type> $request XML data
         * @return <type>
         */
        public function XMLRequest($request)
        {
            $post_var = 'xml=' . wlm_trim($request);
            $ch       = curl_init($this->url);
            curl_setopt($ch, CURLOPT_HTTPHEADER, 0);
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $post_var);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            $result = curl_exec($ch);
            curl_close($ch);
            return $result;
        }

        /**
         * Err
         *
         * @param  <type> $err Error message
         * @return boolean Always false
         */
        public function Err($err)
        {
            $this->errors[] = $err;
            return false;
        }
    }

}

if (! class_exists('WLMLeadGen')) {

    /**
     * WishList Member LeadGen Class
     */
    class WLMLeadGen
    {
        public $mode;

        public function __construct()
        {
            $this->mode = basename(__FILE__);
        }

        /**
         * LeadGen Menu Action Hook
         *
         * @param  $mode
         * @param  $begin
         * @param  $begin_active
         * @param  $end
         * @return none
         */
        public function Menu($mode, $begin, $begin_active, $end)
        {
            $begin = $mode === $this->mode ? $begin_active : $begin;
            echo wp_kses_post(srintf($begin, $this->mode));
            echo 'LeadGen';
            echo wp_kses_post($end);
        }

        /**
         * LeadGen Page Action Hook
         *
         * @param  $mode
         * @param  $wlm
         * @return none
         */
        public function Page($mode, $wlm)
        {
            if ($mode != $this->mode) {
                return false;
            }
            echo '<h2 style="font-size:18px;margin:0 0 10px 0;border:none">Lead Gen Discs Integration <span style="font-weight:normal;font-family:Arial,Helvetica,sans-serif;font-size:10px;font-style:normal">v' . esc_html(WLMLEADGEN_VERSION) . '</span></h2>';
            if (! function_exists('curl_init')) {
                echo '<p>This feature requires the Curl extension to be enabled in PHP.  Please contact your system adminstrator.';
                return;
            }
            $wpm_levels = $wlm->get_option('wpm_levels');
            $data       = $wlm->get_option('WLMLeadGen');
            switch (wlm_post_data()['WLMLeadGen']) {
                case 'Save':
                    $leadgen                   = $data;
                    $leadgen['wlm_leadgencid'] = wlm_post_data()['wlm_leadgencid'];
                    $leadgen['wlm_leadgenurl'] = wlm_post_data()['wlm_leadgenurl'];
                    if (wlm_post_data()['wlm_leadgenapi']) {
                        $leadgen['wlm_leadgenapi'] = wlm_post_data()['wlm_leadgenapi'];
                    }
                    $leadgen['wlm_leadgenproducts']     = wlm_post_data()['wlm_leadgenproducts'];
                    $leadgen['wlm_leadgenbutton']       = wlm_post_data()['wlm_leadgenbutton'];
                    $leadgen['wlm_leadgennosequential'] = wlm_post_data()['wlm_leadgennosequential'];
                    $leadgen['wlm_leadgennoexisting']   = wlm_post_data()['wlm_leadgennoexisting'];

                    $wlm->save_option('WLMLeadGen', $leadgen);
                    $data = wlm_post_data(true);
                    break;
            }
            ?>
            <!-- Lead Gen Discs Admin Form Starts Here -->
            <form method="post">
                <h3>Step 1. Enter your global Lead Gen Discs Configuration</h3>
                <table class="form-table">
                    <tr valign="top">
                        <th scope="row">Lead Gen Discs Unique URL</th>
                        <td><input size="60" type="text" name="wlm_leadgenurl" value="<?php echo esc_attr($data['wlm_leadgenurl']); ?>" /><br />&nbsp;<small>Leave blank to disable Lead Gen Discs</small></td>
                    </tr>
                    <tr valign="top">
                        <th scope="row">Lead Gen Discs CDI</th>
                        <td><input size="40" type="text" name="wlm_leadgencid" value="<?php echo esc_attr($data['wlm_leadgencid']); ?>" /><br />&nbsp;<small>Leave blank to disable Lead Gen Discs</small></td>
                    </tr>
                    <tr valign="top">
                        <th scope="row">Lead Gen Discs API Key</th>
                        <td><input type="text" value="<?php echo esc_attr($data['wlm_leadgenapi']); ?>" name="wlm_leadgenapi" />&nbsp;<br />&nbsp;<small>Leave blank to keep old API Key</small></td>
                    </tr>
                </table>
                <h3>Step 2. Enter the Lead Gen Discs products to deliver for each level</h3>
                <p>Use the following Format:</p>
                <blockquote>Product SKU,Qty</blockquote>
                <p>Example:</p>
                <blockquote>XX00ABC123,1<br />XX00XYZ456,3</blockquote>
                <table class="widefat">
                    <thead>
                        <tr>
                            <th scope="col">Membership Level</th>
                            <th scope="col">Lead Gen Discs Product SKU</th>
                            <th scope="col">Submit Button</th>
                            <th scope="col" style="text-align:center">Disable if Sequential</th>
                            <th scope="col" style="text-align:center">Disable if Existing<br />Member Registration</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ((array) $wpm_levels as $levelid => $level) : ?>
                            <tr valign="top">
                                <td><b><?php echo esc_html($level['name']); ?></b></td>
                                <td><textarea name="wlm_leadgenproducts[<?php echo esc_attr($levelid); ?>]" rows="3" style="width:250px"><?php echo esc_textarea($data['wlm_leadgenproducts'][ $levelid ]); ?></textarea></td>
                                <td><input type="text" value="<?php echo esc_attr($data['wlm_leadgenbutton'][ $levelid ] ? $data['wlm_leadgenbutton'][ $levelid ] : 'Send my CD'); ?>" name="wlm_leadgenbutton[<?php echo esc_attr($levelid); ?>]" /></td>
                                <td style="text-align:center"><input type="checkbox" name="wlm_leadgennosequential[<?php echo esc_attr($levelid); ?>]" value="1" <?php $wlm->checked(1, $data['wlm_leadgennosequential'][ $levelid ]); ?> />
                                <td style="text-align:center"><input type="checkbox" name="wlm_leadgennoexisting[<?php echo esc_attr($levelid); ?>]" value="1" <?php $wlm->checked(1, $data['wlm_leadgennoexisting'][ $levelid ]); ?> />
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                <input type="hidden" name="WLMLeadGen" value="Save" />
                <p class="submit"><input type="submit" value="Save Settings" /></p>
            </form>
            <!-- Lead Gen Discs Admin Form End -->
            <?php
        }

        public function LeadGenTest(&$leadgencfg, &$wpm_id, &$wlm)
        {
            if ('' === wlm_trim(wlm_arrval($leadgencfg, 'wlm_leadgencid')) || '' === wlm_trim(wlm_arrval($leadgencfg, 'wlm_leadgenurl'))) {
                return false; // No leadgen page when no user id.
            }
            if ('wpm_register_existing' === wlm_post_data()['action'] && wlm_arrval($leadgencfg, 'wlm_leadgennoexisting')[ $wpm_id ]) {
                return false;
            }
            if ('' === wlm_trim(wlm_arrval($leadgencfg, 'wlm_leadgenproducts', $wpm_id))) {
                return false; // No leadgen page when no product configured.
            }
            if (! function_exists('curl_init')) {
                return false; // We need php curl.
            }
            return true;
        }

        /**
         * LeadGen Registration Process Filter Hook
         *
         * @param  $content
         * @param  $wlm
         * @return string Content
         */
        public function Register($content, $wlm)
        {
            $wpm_id     = wlm_post_data()['wpm_id'];
            $leadgencfg = $wlm->get_option('WLMLeadGen');
            if (! $this->LeadGenTest($leadgencfg, $wpm_id, $wlm)) {
                return $content;
            }

            $leadgen = new WLMLeadGenCore($leadgencfg['wlm_leadgencid'], $leadgencfg['wlm_leadgenapi'], $leadgencfg['wlm_leadgenurl']);
            $user    = is_user_logged_in() ? wp_get_current_user() : $wlm->get_user_data(0, wlm_post_data()['username']);
            // Map address from saved address.
            $user->WLMLeadGen['Company']     = $user->wpm_useraddress['company'];
            $user->WLMLeadGen['Address1']    = $user->wpm_useraddress['address1'];
            $user->WLMLeadGen['Address2']    = $user->wpm_useraddress['address2'];
            $user->WLMLeadGen['City']        = $user->wpm_useraddress['city'];
            $user->WLMLeadGen['State']       = $user->wpm_useraddress['state'];
            $user->WLMLeadGen['Zip']         = $user->wpm_useraddress['zip'];
            $user->WLMLeadGen['Country']     = $user->wpm_useraddress['country'];
            $user->WLMLeadGen['ContactHome'] = $user->wpm_useraddress['ContactHome'];
            $user->WLMLeadGen['ContactWork'] = $user->wpm_useraddress['ContactWork'];
            $user->WLMLeadGen['ContactFax']  = $user->wpm_useraddress['ContactFax'];

            if ('SendCD' === wlm_post_data()['WLMLeadGen']) {
                $this->SendProduct($leadgencfg, $leadgen, $user, $wpm_id, $wlm);
            }
            if (in_array('WLMLeadGen', wlm_post_data()['WLMRegHookIDs'])) {
                return $content;
            }

            $countries = $leadgen->countries;
            foreach ((array) $countries as $k => $country) {
                $selected        = $user->WLMLeadGen['Country'] == $country ? ' selected="true" ' : '';
                $countries[ $k ] = '<option value="' . esc_attr($k) . '" ' . $selected . '>' . $country . '</option>';
            }
            $countries = implode('', $countries);
            $wpm_id    = wlm_post_data()['wpm_id'];
            $content   = <<<STRING
<script type="text/javascript">
	function wlmleadgensubmit(f){
		if(f.Address1.value==""){
			f.Address1.focus();
			alert("Address required");
			return false;
		}
		if(f.City.value==""){
			f.City.focus();
			alert("City required");
			return false;
		}
		if(f.Zip.value==""){
			f.Zip.focus();
			alert("Zip Code required");
			return false;
		}
		if(f.Country.selectedIndex==0){
			f.Country.focus();
			alert("Country required");
			return false;
		}
		return true;
	}
	function LeadGenStates(s){
		var f=s.form;
		var xState=f.State.value;
		var td=document.getElementById("LeadGenStatesTD");
		var USStates = new Array(
			new Array("","Select your State"),
			new Array("AK","Alaska"),
			new Array("AL","Alabama"),
			new Array("AR","Arkansas"),
			new Array("AZ","Arizona"),
			new Array("CA","California"),
			new Array("CO","Colorado"),
			new Array("CT","Connecticut"),
			new Array("DC","District of Columbia"),
			new Array("DE","Delaware"),
			new Array("FL","Florida"),
			new Array("GA","Georgia"),
			new Array("HI","Hawaii"),
			new Array("IA","Iowa"),
			new Array("ID","Idaho"),
			new Array("IL","Illinois"),
			new Array("IN","Indiana"),
			new Array("KS","Kansas"),
			new Array("KY","Kentucky"),
			new Array("LA","Louisiana"),
			new Array("MA","Massachusetts"),
			new Array("MD","Maryland"),
			new Array("ME","Maine"),
			new Array("MI","Michigan"),
			new Array("MN","Minnesota"),
			new Array("MO","Missouri"),
			new Array("MS","Mississippi"),
			new Array("MT","Montana"),
			new Array("NC","North Carolina"),
			new Array("ND","North Dakota"),
			new Array("NE","Nebraska"),
			new Array("NH","New Hampshire"),
			new Array("NJ","New Jersey"),
			new Array("NM","New Mexico"),
			new Array("NV","Nevada"),
			new Array("NY","New York"),
			new Array("OH","Ohio"),
			new Array("OK","Oklahoma"),
			new Array("OR","Oregon"),
			new Array("PA","Pennsylvania"),
			new Array("PR","Puerto Rico"),
			new Array("RI","Rhode Island"),
			new Array("SC","South Carolina"),
			new Array("SD","South Dakota"),
			new Array("TN","Tennessee"),
			new Array("TX","Texas"),
			new Array("UT","Utah"),
			new Array("VA","Virginia"),
			new Array("VI","Virgin Islands"),
			new Array("VT","Vermont"),
			new Array("WA","Washington"),
			new Array("WI","Wisconsin"),
			new Array("WV","West Virginia"),
			new Array("WY","Wyoming")
		);
		var CanadaProvinces = new Array(
			new Array("","Select your Province"),
			new Array("AB","Alberta"),
			new Array("BC","British Columbia"),
			new Array("MB","Manitoba"),
			new Array("NB","New Brunswick"),
			new Array("NF","Newfoundland"),
			new Array("NL","Newfoundland and Labrador"),
			new Array("NS","Nova Scotia"),
			new Array("NT","Northwest Territories"),
			new Array("NU","Nunavut"),
			new Array("ON","Ontario"),
			new Array("PE","Prince Edward Island"),
			new Array("QC","Quebec"),
			new Array("SK","Saskatchewan"),
			new Array("YT","Yukon")
		);
		var c=s.options[s.selectedIndex].text;
		if(td.hasChildNodes()){
			while(td.childNodes.length>=1){
				td.removeChild(td.firstChild);
			}
		}
		if(c=="CAN"){
			var e=document.createElement("select");
			for(var i=0;i<CanadaProvinces.length;i++){
				var o=document.createElement("option");
				o.text=CanadaProvinces[i][1];
				o.value=CanadaProvinces[i][0];
				e.options.add(o);
			}
		}else if(c=="USA"){
			var e=document.createElement("select");
			for(var i=0;i<USStates.length;i++){
				var o=document.createElement("option");
				o.text=USStates[i][1];
				o.value=USStates[i][0];
				e.options.add(o);
			}
		}else{
			var e=document.createElement("input");
			e.type="text";
			e.size="20";
		}
		e.name="State";
		setState(e,xState);
		td.appendChild(e);
	}
	function setState(e,xState){
		e.value=xState;
		if(e.value!=xState){
			var xs=xState.replace(/[^A-Za-z]/,'').toUpperCase();
			// Find match.
			for(var i=0;i<e.options.length;i++){
				var ov=e.options[i].value;
				var ot=e.options[i].text.replace(/[^A-Za-z]/,'').toUpperCase();
				if(xs==ov || xs==ot){
					e.value=ov;
					return true;
				}
			}
		}
	}
</script>
<noscript>You need to enable Javascript on your Browser</noscript>
<p>Please enter shipping information for your CD.</p>
<form method="post" onsubmit="return wlmleadgensubmit(this)" id="leadgenform">
<input type="hidden" name="WLMLeadGen" value="SendCD" />
<input type="hidden" name="wpm_id" value="{$wpm_id}" />
STRING;
            $content  .= $wlm->after_reg_hook_id('WLMLeadGen');
            $button    = wlm_trim($leadgencfg['wlm_leadgenbutton'][ $wpm_id ]);
            if (empty($button)) {
                $button = 'Submit';
            }
            $content .= <<<STRING
<table class="wpm_registration">
	<tr valign="top">
		<td>Company</td>
		<td><input type="text" name="Company" size="30" value="{$user->WLMLeadGen[Company]}" /></td>
	</tr>
	<tr valign="top">
		<td>Address *</td>
		<td><input type="text" name="Address1" size="30" value="{$user->WLMLeadGen[Address1]}" /></td>
	</tr>
	<tr valign="top">
		<td>&nbsp;</td>
		<td><input type="text" size="30" name="Address2" value="{$user->WLMLeadGen[Address2]}" /></td>
	</tr>
	<tr valign="top">
		<td>City *</td>
		<td><input type="text" name="City" size="20" value="{$user->WLMLeadGen[City]}" /></td>
	</tr>
	<tr valign="top">
		<td>State/Region  *</td>
		<td id="LeadGenStatesTD"><input type="text" name="State" size="20" value="{$user->WLMLeadGen[State]}" /></td>
	</tr>
	<tr valign="top">
		<td>Zip Code *</td>
		<td><input type="text" name="Zip" size="10" value="{$user->WLMLeadGen[Zip]}" /></td>
	</tr>
	<tr valign="top">
		<td>Country *</td>
		<td>
			<select name="Country" onchange="LeadGenStates(this)">
				<option value='0'>Select your Country</option>
				{$countries}
			</select>
		</td>
	</tr>
 	<tr>
		<td>&nbsp;</td><td>&nbsp;</td>
	</tr>
	<tr>
		<td><b>Contact Numbers</b> (optional)</td>
		<td></td>
	</tr>
	<tr>
		<td>Home</td>
		<td><input type="text" name="ContactHome" size="10" value="{$user->WLMLeadGen[ContactHome]}" /></td>
	</tr>
	<tr>
		<td>Work</td>
		<td><input type="text" name="ContactWork" size="10" value="{$user->WLMLeadGen[ContactWork]}" /></td>
	</tr>
	<tr>
		<td>Fax</td>
		<td><input type="text" name="ContactFax" size="10" value="{$user->WLMLeadGen[ContactFax]}" /></td>
	</tr>
 	<tr>
		<td>&nbsp;</td><td>&nbsp;</td>
	</tr>
	<tr>
		<td></td>
		<td><input type="submit" value="{$button}" /></td>
	</tr>
</table>
</form>
STRING;
            if ($user->WLMLeadGen['Country']) {
                $content .= '<script type="text/javascript">
    LeadGenStates(document.getElementById("leadgenform").Country);
    </script>';
            }
            return $content;
        }

        /**
         * LeadGen Sequential Upgrade Action Hook
         *
         * @param  $level
         * @param  $user
         * @param  $wlm
         * @return none
         */
        public function Sequential($level, $user, $wlm)
        {
            $leadgencfg = $wlm->get_option('WLMLeadGen');
            if ($leadgencfg['wlm_leadgennosequential'][ $level ]) {
                return;
            }
            $leadgen = new WLMLeadGenCore($leadgencfg['wlm_leadgenuserid'], $leadgencfg['wlm_leadgenpassword']);
            $this->SendProduct($leadgencfg, $leadgen, $user, $level, $wlm);
        }

        /**
         * LeadGen Registration Page Filter
         *
         * @param  $content
         * @return string
         */
        public function BreadCrumb($content, $wlm)
        {
            $wpm_id     = wlm_post_data()['wpm_id'] ? wlm_post_data()['wpm_id'] : wlm_get_data()['reg'];
            $leadgencfg = $wlm->get_option('WLMLeadGen');
            if (! $this->LeadGenTest($leadgencfg, $wpm_id, $wlm)) {
                return $content;
            }
            if (in_array('WLMLeadGen', wlm_post_data()['WLMRegHookIDs'])) {
                $content = '<p><img src="' . $wlm->plugin_url . '/extensions/leadgendiscs/leadgen_step3.png" /></p>' . $content;
            } elseif (wlm_post_data()['wpm_id']) {
                $content = '<p><img src="' . $wlm->plugin_url . '/extensions/leadgendiscs/leadgen_step2.png" /></p>' . $content;
            } elseif (! wlm_post_data()['wpm_id']) {
                $content = '<p><img src="' . $wlm->plugin_url . '/extensions/leadgendiscs/leadgen_step1.png" /></p>' . $content;
            }
            return $content;
        }

        /**
         * Send Product Method
         *
         * @param  $leadgencfg LeadGen configuration
         * @param  $leadgen LeadGen Object
         * @param  $user WP_User Object
         * @param  $wpm_id Level to process
         * @param  $wlm WishList Member Object
         * @return none
         */
        public function SendProduct($leadgencfg, &$leadgen, $user, $wpm_id, &$wlm)
        {
            if ($user->ID && ! $user->WLMLeadGen['OrderID'][ $wpm_id ]) {
                $leadG = [];
                if (wlm_post_data(true)) {
                    $leadG['Company']     = stripslashes(wlm_post_data()['Company']);
                    $leadG['Address1']    = stripslashes(wlm_post_data()['Address1']);
                    $leadG['Address2']    = stripslashes(wlm_post_data()['Address2']);
                    $leadG['City']        = stripslashes(wlm_post_data()['City']);
                    $leadG['State']       = stripslashes(wlm_post_data()['State']);
                    $leadG['Zip']         = stripslashes(wlm_post_data()['Zip']);
                    $leadG['Country']     = stripslashes(wlm_post_data()['Country']);
                    $leadG['ContactHome'] = stripslashes(wlm_post_data()['ContactHome']);
                    $leadG['ContactWork'] = stripslashes(wlm_post_data()['ContactWork']);
                    $leadG['ContactFax']  = stripslashes(wlm_post_data()['ContactFax']);
                } else {
                    $leadG['Company']     = $user->wpm_useraddress['company'];
                    $leadG['Address1']    = $user->wpm_useraddress['address1'];
                    $leadG['Address2']    = $user->wpm_useraddress['address2'];
                    $leadG['City']        = $user->wpm_useraddress['city'];
                    $leadG['State']       = $user->wpm_useraddress['state'];
                    $leadG['Zip']         = $user->wpm_useraddress['zip'];
                    $leadG['Country']     = $user->wpm_useraddress['country'];
                    $leadG['ContactHome'] = $user->wpm_useraddress['ContactHome'];
                    $leadG['ContactWork'] = $user->wpm_useraddress['ContactWork'];
                    $leadG['ContactFax']  = $user->wpm_useraddress['ContactFax'];
                }
                $products = preg_split("/[\n\,]/", wlm_trim($leadgencfg['wlm_leadgenproducts'][ $wpm_id ]));
                $shipping = 'USA' === $leadG['Country'] ? 'Domestic' : 'International';
                if (false !== $shipping) {
                    $params = [
                        $user->ID,
                        stripslashes(ucwords(strtolower($user->first_name))),
                        stripslashes(ucwords(strtolower($user->last_name))),
                        $user->user_email,
                        $leadG['Company'],
                        $leadG['Address1'],
                        $leadG['Address2'],
                        $leadG['City'],
                        $leadG['State'],
                        $leadG['Zip'],
                        $leadG['Country'],
                        $leadG['ContactHome'],
                        $leadG['ContactWork'],
                        $leadG['ContactFax'],
                        wlm_date('m/d/Y h:i:s A'),
                        $shipping,
                    ];
                    $params = array_merge($params, $products);
                    $order  = call_user_func_array([&$leadgen, 'PlaceOrder'], $params);
                    if (is_array($order)) {
                        print_r($order);
                        // TODO send error message to admin.
                    } else {
                        $leadG['OrderID']            = $user->WLMLeadGen['OrderID'];
                        $leadG['OrderID'][ $wpm_id ] = $order;
                    }
                }
                $wlm->Update_UserMeta($user->ID, 'wpm_useraddress', $leadG);
                $wlm->Update_UserMeta($user->ID, 'WLMLeadGen', $leadG);
            }
        }
    }

    if (! isset($WLMLeadGen)) {
        $WLMLeadGen = new WLMLeadGen();
        add_action('wishlistmember_extension_page', [&$WLMLeadGen, 'Page'], 10, 2);
        add_action('wishlistmember_sequential', [&$WLMLeadGen, 'Sequential'], 10, 3);
        add_filter('wishlistmember_after_registration_page', [&$WLMLeadGen, 'Register'], 10, 2);
        add_filter('wishlistmember_registration_page', [&$WLMLeadGen, 'BreadCrumb'], 10, 2);
    }
}
?>
