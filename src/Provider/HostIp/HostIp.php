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
 * @author William Durand <william.durand1@gmail.com>
 */
final class HostIp extends AbstractHostIp
{
    /**
     * @var string
     */
    public const ENDPOINT_URL = 'http://api.hostip.info/get_json.php?ip=%s&position=true';

    public function getName(): string
    {
        return 'host_ip';
    }

    public function getEndpointURL(): string
    {
        return self::ENDPOINT_URL;
    }

    protected function executeQuery(string $url): AddressCollection
    {
        $content = $this->getUrlContents($url);
        $data = \json_decode($content, true);

        if (!$data) {
            return new AddressCollection([]);
        }

        return $this->prepareAddressCollection($data);
    }
}
