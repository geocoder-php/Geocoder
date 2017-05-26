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
 * @author Thijs Scheepers <thijs@label305.com>
 */
class FileGetContentsHttpAdapter implements HttpAdapterInterface
{

    /**
     * {@inheritDoc}
     */
    public function getContent($url)
    {

        $context = stream_context_create(
                        array(
                            'http' => array(
                                'method' => 'GET'
                            )
                        )
                    );

        try {
            $content = file_get_contents($url, false, $context);
        } catch(\Exception $e) {
            return null;
        }

        return $content;
    }

    /**
     * {@inheritDoc}
     */
    public function getName()
    {
        return 'file_get_contents';
    }
}
