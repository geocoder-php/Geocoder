<?php

/**
 * This file is part of the Geocoder package.
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @license    MIT License
 */

namespace Geocoder\Provider;

use Geocoder\Exception\HttpError;
use Ivory\HttpAdapter\HttpAdapterException;
use Ivory\HttpAdapter\HttpAdapterInterface;

/**
 * @author William Durand <william.durand1@gmail.com>
 */
class AbstractHttpProvider extends AbstractProvider
{
    /**
     * @var HttpAdapterInterface
     */
    private $adapter;

    /**
     * @param HttpAdapterInterface $adapter An HTTP adapter
     */
    public function __construct(HttpAdapterInterface $adapter)
    {
        parent::__construct();

        $this->adapter = $adapter;
    }

    /**
     * Returns the HTTP adapter.
     *
     * @return HttpAdapterInterface
     */
    public function getAdapter()
    {
        return $this->adapter;
    }

    /**
     * @param string $query
     *
     * @return string
     * @throws HttpError
     */
    protected function getQueryContent($query)
    {
        try {
            return (string) $this->getAdapter()->get($query)->getBody();
        } catch (HttpAdapterException $exception) {
            throw new HttpError(sprintf('Could not execute query "%s".', $query), 0, $exception);
        }
    }
}
