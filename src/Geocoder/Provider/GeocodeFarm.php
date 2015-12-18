<?php
/**
 * @author      Tom Lous <tomlous@gmail.com>
 * @copyright   2015 Tom Lous
 * @package     package
 * Datetime:     07/12/15 09:41
 */

namespace Geocoder\Provider;

use Exception;
use Geocoder\Exception\InvalidCredentials;
use Geocoder\Exception\NoResult;
use Geocoder\Exception\QuotaExceeded;
//use Geocoder\Exception\UnsupportedOperation;
use Ivory\HttpAdapter\HttpAdapterInterface;

class GeocodeFarm extends AbstractHttpProvider implements LocaleAwareProvider
{

    /**
     * @var string
     */
    const ENDPOINT_URL = '%s://www.geocode.farm/v3/json/forward/?addr=%s';

    /**
     * @var string
     */
    const REVERSE_ENDPOINT_URL = '%s://www.geocode.farm/v3/json/reverse/?lat=%F&lon=%F';

    /**
     * @var string
     */
    const ROOT_ELEMENT = 'geocoding_results';

    use LocaleTrait;

    /**
     * @var string
     */
    private $region = null;

    /**
     * @var bool
     */
    private $useSsl = false;

    /**
     * @var string
     */
    private $apiKey = null;

    /**
     * @var string
     */
    private $protocol = null;

    /**
     * @var int
     */
    private $count = null;

    /**
     * @var string
     */
    private $lang = null;

    /**
     * @param HttpAdapterInterface $adapter An HTTP adapter.
     * @param string $locale A locale (optional).
     * @param string $region Region biasing (optional).
     * @param bool $useSsl Whether to use an SSL connection (optional)
     * @param string $apiKey Google Geocoding API key (optional)
     */
    public function __construct(HttpAdapterInterface $adapter, $locale = null, $region = null,  $useSsl = false, $apiKey = null)
    {
        parent::__construct($adapter, $locale);


        $this->lang = 'en';
        if(strpos(strtolower($locale), 'de')){
            $this->lang = 'de';
        }
        $this->region = $region;
        $this->useSsl = $useSsl;
        $this->apiKey = $apiKey;
        $this->protocol    = $useSsl ? 'https' : 'http';
    }

    /**
     * {@inheritDoc}
     */
    public function geocode($address){
        return $this->getGeocodedData(self::ENDPOINT_URL, array($address));
    }

    /**
     * {@inheritDoc}
     */
    public function reverse($latitude, $longitude)
    {
        return $this->getGeocodedData(self::REVERSE_ENDPOINT_URL, array($latitude, $longitude));
    }


    /**
     * {@inheritDoc}
     */
    public function getGeocodedData($url, $data)
    {
        $data = array_map('rawurlencode', $data);
        array_unshift($data, $this->protocol);
        $query = vsprintf($url,$data);

        return $this->executeQuery($query);
    }


    /**
     * {@inheritDoc}
     */
    public function getName()
    {
        return 'geocode_farm';
    }

    /**
     * @param string $query
     *
     * @return string Query with extra params
     */
    protected function buildQuery($query)
    {

        if (null !== $this->getLocale()) {
            $query = sprintf('%s&lang=%s', $query, $this->getLocale());
        }

        if (null !== $this->getRegion()) {
            $query = sprintf('%s&country=%s', $query, $this->getRegion());
        }

        if (null !== $this->getCount()) {
            $query = sprintf('%s&count=%s', $query, $this->getCount());
        }

        if (null !== $this->apiKey) {
            $query = sprintf('%s&key=%s', $query, $this->apiKey);
        }

        return $query;
    }

    /**
     * @param string $query
     * @return array
     * @throws InvalidCredentials
     * @throws NoResult
     * @throws QuotaExceeded
     */
    protected function executeQuery($query)
    {
        $query = $this->buildQuery($query);


        $content =  (string) $this->getAdapter()->get($query)->getBody();

        if (null === $content) {
            throw new NoResult(sprintf('Could not execute query %s', $query));
        }

        $json = json_decode($content);

        // API error
        if (!isset($json) || !isset($json->{self::ROOT_ELEMENT})) {
            throw new NoResult(sprintf('Could not execute query %s', $query));
        }

        $json = $json->{self::ROOT_ELEMENT};


        $status = $json->STATUS;

        // Throw exception if API_KEY_INVALID — You entered your API Key incorrectly. Please double-check that your API Key entered matches that shown in the Dashboard.
        if ($status->access == "API_KEY_INVALID") {
            throw new InvalidCredentials(sprintf('Invalid client ID / API Key %s', $query));
        }

        // Throw exception if ACCOUNT_NOT_ACTIVE — You need to check your email for the activation link for your account. Clicking on this link will activate your account and this message should then stop appearing. Ensure you check your "Spam" or "Junk" folders to ensure that your Spam Filter did not tag our email as Spam.
        if ($status->access == "ACCOUNT_NOT_ACTIVE") {
            throw new InvalidCredentials(sprintf('Account inactive %s', $query));
        }

        // Throw exception if BILL_PAST_DUE — You are on a Paid Plan and your bill has not been paid. Please Login to your Dashboard and pay the monthly fee to restore your API access.
        if ($status->access == "BILL_PAST_DUE") {
            throw new InvalidCredentials(sprintf('Account inactive (Bill due) %s', $query));
        }

        // Throw exception if OVER_QUERY_LIMIT — You have hit your daily limit for today. Upgrade your Plan if this occurs frequently or very often. This is a 24 hour limit, so if you haven't processed many queries in a while and are getting this, you're probably close to the 24hour mark, otherwise, you may need to up your limit.
        if ($status->access == "OVER_QUERY_LIMIT") {
            throw new QuotaExceeded(sprintf('Daily quota exceeded %s', $query));
        }

        // no result
        if (!isset($json->RESULTS) || !count($json->RESULTS) || 'SUCCESS' !== $status->status) {
            throw new NoResult(sprintf('No results %s', $query));
        }

        $results = array();


        foreach ($json->RESULTS as $result) {
            $resultset = $this->getDefaults();

            // update address components
            foreach ($result->ADDRESS as $type => $value) {
                $this->updateAddressComponent($resultset, $type, $value);
            }

            // update coordinates
            $coordinates = $result->COORDINATES;
            $resultset['latitude'] = $coordinates->latitude;
            $resultset['longitude'] = $coordinates->longitude;


            $resultset['timezone'] = isset($result->LOCATION_DETAILS) && isset($result->LOCATION_DETAILS->timezone_short) ? $result->LOCATION_DETAILS->timezone_short : null;

            $resultset['accuracy'] = $this->getAccuracy($result->accuracy);

            $resultset['bounds'] = null;
            if (isset($result->BOUNDARIES)) {
                $resultset['bounds'] = array(
                    'south' => $result->BOUNDARIES->southwest_latitude,
                    'west' => $result->BOUNDARIES->southwest_longitude,
                    'north' => $result->BOUNDARIES->northeast_latitude,
                    'east' => $result->BOUNDARIES->northeast_longitude
                );
            } elseif ('EXACT_MATCH' === $result->accuracy) {
                // Fake bounds
                $resultset['bounds'] = array(
                    'south' => $coordinates->latitude,
                    'west' => $coordinates->longitude,
                    'north' => $coordinates->latitude,
                    'east' => $coordinates->longitude
                );
            }

            $results[] = array_merge($this->getDefaults(), $resultset);
        }

        return $results;
    }

    /**
     * Update current resultset with given key/value.
     *
     * @param array $resultset Resultset to update.
     * @param string $type Component type.
     * @param string $value The component value;
     *
     * @return array
     */
    protected function updateAddressComponent(&$resultset, $type, $value)
    {

        switch ($type) {
            case 'postal_code':
                $resultset['zipcode'] = $value;
                break;

            case 'locality':
                $resultset['city'] = $value;
                break;

            case 'admin_2':
                $resultset['county'] = $value;
                break;

            case 'admin_1':
                $resultset['region'] = $value;
                break;

            case 'country':
                $resultset['country'] = $value;
                break;

            case 'street_number':
                $resultset['streetNumber'] = $value;
                break;

            case 'street_name':
                $resultset['streetName'] = $value;
                break;

            case 'admin_3':
                $resultset['cityDistrict'] = $value;
                break;

            case 'neighborhood':
                $resultset['cityDistrict'] = $value;
                break;


            default:
        }

        return $resultset;
    }

    /**
     * Returns the configured region or null.
     *
     * @return string|null
     */
    protected function getRegion()
    {
        return $this->region;
    }


    /**
     * Returns the configured count or null.
     *
     * @return int|null
     */
    protected function getCount()
    {
        return $this->count;
    }

    protected function getAccuracy($accuracyTerm){
        $accuracy = null;


        switch ($accuracyTerm) {
            // EXACT_MATCH — This is the highest level of accuracy and usually indicates a spot-on match.
            case 'EXACT_MATCH':
                $accuracy = 1;
                break;

            // HIGH_ACCURACY — This is the second highest level of accuracy and usually indicates a range match, within a few hundred feet most.
            case 'HIGH_ACCURACY':
                $accuracy = 0.8;
                break;

            // MEDIUM_ACCURACY — This is the third level of accuracy and usually indicates a geographical area match, such as the metro area, town, or city.
            case 'MEDIUM_ACCURACY':
                $accuracy = 0.3;
                break;

            // UNKNOWN_ACCURACY — The accuracy of this result is unable to be determined and an exact match may or may not have
            case 'UNKNOWN_ACCURACY':
                $accuracy = null;
                break;


            default:
        }
        return $accuracy;

    }


}