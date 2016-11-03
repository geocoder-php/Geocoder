<?php

/**
 * This file is part of the Geocoder package.
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @license    MIT License
 */

namespace Geocoder\Provider;

use Geocoder\Exception\InvalidCredentials;
use Geocoder\Exception\NoResult;
use Geocoder\Exception\UnsupportedOperation;
use Http\Client\HttpClient;

/**
 * @author Andrea Cristaudo <andrea.cristaudo@gmail.com>
 */
final class MaxMind extends AbstractHttpProvider implements Provider
{
    /**
     * @var string Country, City, ISP and Organization
     */
    const CITY_EXTENDED_SERVICE = 'f';

    /**
     * @var string Extended
     */
    const OMNI_SERVICE = 'e';

    /**
     * @var string
     */
    const GEOCODE_ENDPOINT_URL = 'http://geoip.maxmind.com/%s?l=%s&i=%s';

    /**
     * @var string
     */
    const GEOCODE_ENDPOINT_URL_SSL = 'https://geoip.maxmind.com/%s?l=%s&i=%s';

    /**
     * @var string
     */
    private $apiKey = null;

    /**
     * @var string
     */
    private $service = null;

    /**
     * @var bool
     */
    private $useSsl = false;

    /**
     * @param HttpClient $client  An HTTP adapter.
     * @param string     $apiKey  An API key.
     * @param string     $service The specific Maxmind service to use (optional).
     * @param bool       $useSsl  Whether to use an SSL connection (optional).
     */
    public function __construct(HttpClient $client, $apiKey, $service = self::CITY_EXTENDED_SERVICE, $useSsl = false)
    {
        parent::__construct($client);

        $this->apiKey  = $apiKey;
        $this->service = $service;
        $this->useSsl  = $useSsl;
    }

    /**
     * {@inheritDoc}
     */
    public function geocode($address)
    {
        if (null === $this->apiKey) {
            throw new InvalidCredentials('No API Key provided.');
        }

        if (!filter_var($address, FILTER_VALIDATE_IP)) {
            throw new UnsupportedOperation('The MaxMind provider does not support street addresses, only IP addresses.');
        }

        if (in_array($address, array('127.0.0.1', '::1'))) {
            return $this->returnResults([ $this->getLocalhostDefaults() ]);
        }

        $query = sprintf(
            $this->useSsl ? self::GEOCODE_ENDPOINT_URL_SSL : self::GEOCODE_ENDPOINT_URL,
            $this->service, $this->apiKey, $address
        );

        return $this->executeQuery($query);
    }

    /**
     * {@inheritDoc}
     */
    public function reverse($latitude, $longitude)
    {
        throw new UnsupportedOperation('The MaxMind provider is not able to do reverse geocoding.');
    }

    /**
     * {@inheritDoc}
     */
    public function getName()
    {
        return 'maxmind';
    }

    /**
     * @param string $query
     */
    private function executeQuery($query)
    {
        $request = $this->getMessageFactory()->createRequest('GET', $query);
        $content = (string) $this->getHttpClient()->sendRequest($request)->getBody();
        $fields  = $this->fieldsForService($this->service);

        if (null === $content || '' === $content) {
            throw new NoResult(sprintf('Could not execute query "%s".', $query));
        }

        $data = str_getcsv($content);

        if (in_array(end($data), array('INVALID_LICENSE_KEY', 'LICENSE_REQUIRED'))) {
            throw new InvalidCredentials('API Key provided is not valid.');
        }

        if ('IP_NOT_FOUND' === end($data)) {
            throw new NoResult('Could not retrieve information for the supplied IP address.');
        }

        if (count($fields) !== count($data)) {
            throw new NoResult('Invalid result returned by the API.');
        }

        $data = array_combine($fields, $data);
        $data = array_map(function ($value) {
            return '' === $value ? null : $value;
        }, $data);

        if (empty($data['country']) && !empty($data['countryCode'])) {
            $data['country'] = $this->countryCodeToCountryName($data['countryCode']);
        }

        $data = $this->replaceAdmins($data);

        return $this->returnResults([
            $this->fixEncoding(array_merge($this->getDefaults(), $data))
        ]);
    }

    private function countryCodeToCountryName($code)
    {
        $countryNames = $this->getCountryNames();

        return $countryNames[$code];
    }

    private function replaceAdmins($data)
    {
        $adminLevels = [];

        $region = \igorw\get_in($data, ['region']);
        $regionCode = \igorw\get_in($data, ['regionCode']);
        unset($data['region'], $data['regionCode']);

        if (null !== $region || null !== $regionCode) {
            $adminLevels[] = ['name' => $region, 'code' => $regionCode, 'level' => 1];
        }

        $data['adminLevels'] = $adminLevels;

        return $data;
    }

    /**
     * We do not support Country and City services because they do not return much fields.
     * @see http://dev.maxmind.com/geoip/web-services
     *
     * @param string $service
     *
     * @return string[]
     */
    private function fieldsForService($service)
    {
        switch ($service) {
            case self::CITY_EXTENDED_SERVICE:
                return array(
                    'countryCode',
                    'regionCode',
                    'locality',
                    'postalCode',
                    'latitude',
                    'longitude',
                    'metroCode',
                    'areaCode',
                    'isp',
                    'organization'
                );
            case self::OMNI_SERVICE:
                return array(
                    'countryCode',
                    'countryName',
                    'regionCode',
                    'region',
                    'locality',
                    'latitude',
                    'longitude',
                    'metroCode',
                    'areaCode',
                    'timezone',
                    'continentCode',
                    'postalCode',
                    'isp',
                    'organization',
                    'domain',
                    'asNumber',
                    'netspeed',
                    'userType',
                    'accuracyRadius',
                    'countryConfidence',
                    'cityConfidence',
                    'regionConfidence',
                    'postalConfidence',
                    'error'
                );
            default:
                throw new UnsupportedOperation(sprintf('Unknown MaxMind service %s', $service));
        }
    }

    /**
     * @return array
     */
    private function getCountryNames()
    {
        return array(
            'A1' => 'Anonymous Proxy',
            'A2' => 'Satellite Provider',
            'O1' => 'Other Country',
            'AD' => 'Andorra',
            'AE' => 'United Arab Emirates',
            'AF' => 'Afghanistan',
            'AG' => 'Antigua and Barbuda',
            'AI' => 'Anguilla',
            'AL' => 'Albania',
            'AM' => 'Armenia',
            'AO' => 'Angola',
            'AP' => 'Asia/Pacific Region',
            'AQ' => 'Antarctica',
            'AR' => 'Argentina',
            'AS' => 'American Samoa',
            'AT' => 'Austria',
            'AU' => 'Australia',
            'AW' => 'Aruba',
            'AX' => 'Aland Islands',
            'AZ' => 'Azerbaijan',
            'BA' => 'Bosnia and Herzegovina',
            'BB' => 'Barbados',
            'BD' => 'Bangladesh',
            'BE' => 'Belgium',
            'BF' => 'Burkina Faso',
            'BG' => 'Bulgaria',
            'BH' => 'Bahrain',
            'BI' => 'Burundi',
            'BJ' => 'Benin',
            'BL' => 'Saint Bartelemey',
            'BM' => 'Bermuda',
            'BN' => 'Brunei Darussalam',
            'BO' => 'Bolivia',
            'BQ' => 'Bonaire, Saint Eustatius and Saba',
            'BR' => 'Brazil',
            'BS' => 'Bahamas',
            'BT' => 'Bhutan',
            'BV' => 'Bouvet Island',
            'BW' => 'Botswana',
            'BY' => 'Belarus',
            'BZ' => 'Belize',
            'CA' => 'Canada',
            'CC' => 'Cocos (Keeling) Islands',
            'CD' => 'Congo, The Democratic Republic of the',
            'CF' => 'Central African Republic',
            'CG' => 'Congo',
            'CH' => 'Switzerland',
            'CI' => 'Cote d\'Ivoire',
            'CK' => 'Cook Islands',
            'CL' => 'Chile',
            'CM' => 'Cameroon',
            'CN' => 'China',
            'CO' => 'Colombia',
            'CR' => 'Costa Rica',
            'CU' => 'Cuba',
            'CV' => 'Cape Verde',
            'CW' => 'Curacao',
            'CX' => 'Christmas Island',
            'CY' => 'Cyprus',
            'CZ' => 'Czech Republic',
            'DE' => 'Germany',
            'DJ' => 'Djibouti',
            'DK' => 'Denmark',
            'DM' => 'Dominica',
            'DO' => 'Dominican Republic',
            'DZ' => 'Algeria',
            'EC' => 'Ecuador',
            'EE' => 'Estonia',
            'EG' => 'Egypt',
            'EH' => 'Western Sahara',
            'ER' => 'Eritrea',
            'ES' => 'Spain',
            'ET' => 'Ethiopia',
            'EU' => 'Europe',
            'FI' => 'Finland',
            'FJ' => 'Fiji',
            'FK' => 'Falkland Islands (Malvinas)',
            'FM' => 'Micronesia, Federated States of',
            'FO' => 'Faroe Islands',
            'FR' => 'France',
            'GA' => 'Gabon',
            'GB' => 'United Kingdom',
            'GD' => 'Grenada',
            'GE' => 'Georgia',
            'GF' => 'French Guiana',
            'GG' => 'Guernsey',
            'GH' => 'Ghana',
            'GI' => 'Gibraltar',
            'GL' => 'Greenland',
            'GM' => 'Gambia',
            'GN' => 'Guinea',
            'GP' => 'Guadeloupe',
            'GQ' => 'Equatorial Guinea',
            'GR' => 'Greece',
            'GS' => 'South Georgia and the South Sandwich Islands',
            'GT' => 'Guatemala',
            'GU' => 'Guam',
            'GW' => 'Guinea-Bissau',
            'GY' => 'Guyana',
            'HK' => 'Hong Kong',
            'HM' => 'Heard Island and McDonald Islands',
            'HN' => 'Honduras',
            'HR' => 'Croatia',
            'HT' => 'Haiti',
            'HU' => 'Hungary',
            'ID' => 'Indonesia',
            'IE' => 'Ireland',
            'IL' => 'Israel',
            'IM' => 'Isle of Man',
            'IN' => 'India',
            'IO' => 'British Indian Ocean Territory',
            'IQ' => 'Iraq',
            'IR' => 'Iran, Islamic Republic of',
            'IS' => 'Iceland',
            'IT' => 'Italy',
            'JE' => 'Jersey',
            'JM' => 'Jamaica',
            'JO' => 'Jordan',
            'JP' => 'Japan',
            'KE' => 'Kenya',
            'KG' => 'Kyrgyzstan',
            'KH' => 'Cambodia',
            'KI' => 'Kiribati',
            'KM' => 'Comoros',
            'KN' => 'Saint Kitts and Nevis',
            'KP' => 'Korea, Democratic People\'s Republic of',
            'KR' => 'Korea, Republic of',
            'KW' => 'Kuwait',
            'KY' => 'Cayman Islands',
            'KZ' => 'Kazakhstan',
            'LA' => 'Lao People\'s Democratic Republic',
            'LB' => 'Lebanon',
            'LC' => 'Saint Lucia',
            'LI' => 'Liechtenstein',
            'LK' => 'Sri Lanka',
            'LR' => 'Liberia',
            'LS' => 'Lesotho',
            'LT' => 'Lithuania',
            'LU' => 'Luxembourg',
            'LV' => 'Latvia',
            'LY' => 'Libyan Arab Jamahiriya',
            'MA' => 'Morocco',
            'MC' => 'Monaco',
            'MD' => 'Moldova, Republic of',
            'ME' => 'Montenegro',
            'MF' => 'Saint Martin',
            'MG' => 'Madagascar',
            'MH' => 'Marshall Islands',
            'MK' => 'Macedonia',
            'ML' => 'Mali',
            'MM' => 'Myanmar',
            'MN' => 'Mongolia',
            'MO' => 'Macao',
            'MP' => 'Northern Mariana Islands',
            'MQ' => 'Martinique',
            'MR' => 'Mauritania',
            'MS' => 'Montserrat',
            'MT' => 'Malta',
            'MU' => 'Mauritius',
            'MV' => 'Maldives',
            'MW' => 'Malawi',
            'MX' => 'Mexico',
            'MY' => 'Malaysia',
            'MZ' => 'Mozambique',
            'NA' => 'Namibia',
            'NC' => 'New Caledonia',
            'NE' => 'Niger',
            'NF' => 'Norfolk Island',
            'NG' => 'Nigeria',
            'NI' => 'Nicaragua',
            'NL' => 'Netherlands',
            'NO' => 'Norway',
            'NP' => 'Nepal',
            'NR' => 'Nauru',
            'NU' => 'Niue',
            'NZ' => 'New Zealand',
            'OM' => 'Oman',
            'PA' => 'Panama',
            'PE' => 'Peru',
            'PF' => 'French Polynesia',
            'PG' => 'Papua New Guinea',
            'PH' => 'Philippines',
            'PK' => 'Pakistan',
            'PL' => 'Poland',
            'PM' => 'Saint Pierre and Miquelon',
            'PN' => 'Pitcairn',
            'PR' => 'Puerto Rico',
            'PS' => 'Palestinian Territory',
            'PT' => 'Portugal',
            'PW' => 'Palau',
            'PY' => 'Paraguay',
            'QA' => 'Qatar',
            'RE' => 'Reunion',
            'RO' => 'Romania',
            'RS' => 'Serbia',
            'RU' => 'Russian Federation',
            'RW' => 'Rwanda',
            'SA' => 'Saudi Arabia',
            'SB' => 'Solomon Islands',
            'SC' => 'Seychelles',
            'SD' => 'Sudan',
            'SE' => 'Sweden',
            'SG' => 'Singapore',
            'SH' => 'Saint Helena',
            'SI' => 'Slovenia',
            'SJ' => 'Svalbard and Jan Mayen',
            'SK' => 'Slovakia',
            'SL' => 'Sierra Leone',
            'SM' => 'San Marino',
            'SN' => 'Senegal',
            'SO' => 'Somalia',
            'SR' => 'Suriname',
            'ST' => 'Sao Tome and Principe',
            'SV' => 'El Salvador',
            'SX' => 'Sint Maarten',
            'SY' => 'Syrian Arab Republic',
            'SZ' => 'Swaziland',
            'TC' => 'Turks and Caicos Islands',
            'TD' => 'Chad',
            'TF' => 'French Southern Territories',
            'TG' => 'Togo',
            'TH' => 'Thailand',
            'TJ' => 'Tajikistan',
            'TK' => 'Tokelau',
            'TL' => 'Timor-Leste',
            'TM' => 'Turkmenistan',
            'TN' => 'Tunisia',
            'TO' => 'Tonga',
            'TR' => 'Turkey',
            'TT' => 'Trinidad and Tobago',
            'TV' => 'Tuvalu',
            'TW' => 'Taiwan',
            'TZ' => 'Tanzania, United Republic of',
            'UA' => 'Ukraine',
            'UG' => 'Uganda',
            'UM' => 'United States Minor Outlying Islands',
            'US' => 'United States',
            'UY' => 'Uruguay',
            'UZ' => 'Uzbekistan',
            'VA' => 'Holy See (Vatican City State)',
            'VC' => 'Saint Vincent and the Grenadines',
            'VE' => 'Venezuela',
            'VG' => 'Virgin Islands, British',
            'VI' => 'Virgin Islands, U.S.',
            'VN' => 'Vietnam',
            'VU' => 'Vanuatu',
            'WF' => 'Wallis and Futuna',
            'WS' => 'Samoa',
            'YE' => 'Yemen',
            'YT' => 'Mayotte',
            'ZA' => 'South Africa',
            'ZM' => 'Zambia',
            'ZW' => 'Zimbabwe',
        );
    }
}
