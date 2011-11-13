<?php

/**
 * This file is part of the Geocoder package.
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @license    MIT License
 */

namespace Geocoder\HttpAdapter;

/**
 * @author William Durand <william.durand1@gmail.com>
 */
class ZendHttpAdapter implements HttpAdapterInterface
{
    /**
     * @var \Zend_Http_Client_Adapter_Interface
     */
    protected $adapter;

    /**
     * @param \Buzz\Browser $browser
     */
    public function __construct(\Zend_Http_Client_Adapter_Interface $adapter = null)
    {
        if (null === $adapter) {
            $this->adapter = new \Zend_Http_Client_Adapter_Socket();
        } else {
            $this->adapter = $adapter;
        }
    }

    /**
     * {@inheritDoc}
     */
    public function getContent($url)
    {
        try {
            $http = new \Zend_Http_Client($url, array(
                'adapter' => $this->adapter
            ));
            $reponse = $http->request();

            if ($reponse->isSuccessful()) {
                $content = $reponse->getBody();
            } else {
                $content = null;
            }
        } catch (\Zend_Http_Client_Exception $e) {
            $content = null;
        }

        return $content;
    }

    /**
     * {@inheritDoc}
     */
    public function getName()
    {
        return 'zend';
    }
}
