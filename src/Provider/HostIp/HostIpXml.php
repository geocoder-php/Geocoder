<?php

declare(strict_types=1);

/*
 * This file is part of the Geocoder package.
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @license    MIT License
 */

namespace Geocoder\Provider\HostIp;

use Geocoder\Model\AddressCollection;

/**
 * @author Oleg Andreyev <oleg@andreyev.lv>
 */
final class HostIpXml extends AbstractHostIp
{
    /**
     * @var string
     */
    public const ENDPOINT_URL = 'http://api.hostip.info/get_xml.php?ip=%s&position=true';

    public function getName(): string
    {
        return 'host_ip_xml';
    }

    public function getEndpointURL(): string
    {
        return self::ENDPOINT_URL;
    }

    protected function executeQuery(string $url): AddressCollection
    {
        $content = $this->getUrlContents($url);
        $xml = \simplexml_load_string($content);

        $hostIp = $xml->xpath('/HostipLookupResultSet/gml:featureMember/Hostip');
        if (empty($hostIp[0])) {
            return new AddressCollection([]);
        }
        $hostIp = $hostIp[0];

        $city = (string) $hostIp->xpath('.//gml:name')[0];
        $countryName = (string) $hostIp->xpath('.//countryName')[0];
        $countryCode = (string) $hostIp->xpath('.//countryAbbrev')[0];
        $coords = $hostIp->xpath('.//ipLocation/gml:pointProperty/gml:Point/gml:coordinates');

        if (empty($coords)) {
            list($lng, $lat) = [null, null];
        } else {
            list($lng, $lat) = explode(',', (string) $coords[0], 2);
        }

        return $this->prepareAddressCollection([
            'lat' => $lat,
            'lng' => $lng,
            'city' => $city,
            'country_name' => $countryName,
            'country_code' => $countryCode,
        ]);
    }
}
