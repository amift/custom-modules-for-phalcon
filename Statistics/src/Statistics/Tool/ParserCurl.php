<?php

namespace Statistics\Tool;

use PHPHtmlParser\CurlInterface;
use PHPHtmlParser\Exceptions\CurlException;

class ParserCurl implements CurlInterface
{

    /**
     * @var string
     */
    private $method = 'GET';

    /**
     * @var array
     */
    private $options = [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_SSL_VERIFYHOST => false,
        CURLOPT_SSL_VERIFYPEER => false,
        CURLOPT_MAXREDIRS  => 20,
        CURLOPT_CONNECTTIMEOUT => 60,
        CURLOPT_TIMEOUT => 120,
    ];

    /**
     * @param array $options
     */
    public function __construct($options = [])
    {
        if (is_array($options) && count($options) > 0) {
            $this->setOptions($options);
        }
    }

    /**
     * 
     * @param array $options
     * @throws CurlException
     */
    public function setOptions($options = [])
    {
        if (!is_array($options) || (is_array($options) && count($options) < 1)) {
            throw new CurlException(sprintf('Error: Invalid options given, allowed not empty array with native curl options'));
        }

        $this->options = $options;
    }

    /**
     * 
     * @return array
     */
    public function getOptions()
    {
        return $this->options;
    }

    /**
     * Curl implementation to get the content of the url.
     *
     * @param string $url
     * @return string
     * @throws CurlException
     */
    public function get($url)
    {
        // Init curl
        $ch = curl_init($url);

        // Set options
        curl_setopt_array($ch, $this->getOptions());
        /*if ( ! ini_get('open_basedir')) {
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        }*/

        // Complete the request
        $content = curl_exec($ch);
        if ($content === false) {
            $error = curl_error($ch);
            throw new CurlException('Error: Retrieving "'.$url.'" ('.$error.')');
        }

        return $content;
    }

}
