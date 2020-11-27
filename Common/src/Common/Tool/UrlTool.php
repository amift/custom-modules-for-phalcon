<?php

namespace Common\Tool;

class UrlTool
{

    /**
     * Get from url only domain name
     * 
     * @access public
     * @param string $url
     * @param boolean $removeWww
     * @param boolean $shortenerIfLong
     * @param int $longLength
     * @return string
     */
    public static function getOnlyDomainName($url = '', $removeWww = true, $shortenerIfLong = false, $longLength = 50)
    {
        $host = @parse_url($url, PHP_URL_HOST);

        // If the URL can't be parsed, use the original URL
        // Change to "return false" if you don't want that
        if (!$host) {
            $host = $url;
        }

        // The "www." prefix isn't really needed if you're just using
        // this to display the domain to the user
        if ($removeWww && substr($host, 0, 4) === "www.") {
            $host = substr($host, 4);
        }

        // You might also want to limit the length if screen space is limited
        if ($shortenerIfLong && strlen($host) > $longLength) {
            $host = substr($host, 0, $longLength) . '...';
        }

        return $host;
    }

}
