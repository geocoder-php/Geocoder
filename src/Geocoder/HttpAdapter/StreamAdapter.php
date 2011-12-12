<?php
namespace Geocoder\HttpAdapter;

/**
 * @author Markus Bachmann <markus.bachmann@bachi.biz>
 */
class StreamAdapter implements HttpAdapterInterface
{
    /**
     * Returns the content fetched from a given URL.
     *
     * @return string
     */
    public function getContent($url)
    {
        $handle = fopen($url, 'r');

        $rawContent = '';
        while ( ! feof($handle) ) {
            $rawContent .= trim(fgets($handle));
        }

        return $rawContent;
    }

    /**
     * Returns the name of the HTTP Adapter.
     *
     * @return string
     */
    public function getName()
    {
        return 'stream';
    }

}
