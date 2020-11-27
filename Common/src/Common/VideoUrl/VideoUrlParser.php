<?php

namespace Common\VideoUrl;

/**
 * @author https://gist.github.com/astockwell/11055104
 * 
 * Code is refactored for own standarts.
 */
class VideoUrlParser
{

    const SERVICE_YOUTUBE = 'youtube';
    const SERVICE_VIMEO = 'vimeo';

    public static function getListOutputImage($url = '')
    {
        $imageUrl = '';

        $service = self::identifyService($url);
        switch ($service) {
            case self::SERVICE_YOUTUBE :
                $videoId = self::get_youtube_id($url);
                if ($videoId !== null) {
                    $imageUrl = 'https://i.ytimg.com/vi/'.$videoId.'/hqdefault.jpg';
                }
                break;
            case self::SERVICE_VIMEO :
                $videoId = self::get_vimeo_id($url);
                if ($videoId !== null) {
                    $imageUrl = 'https://i.vimeocdn.com/video/'.$videoId.'.jpg?mw=137';
                }
                break;
        }

        return $imageUrl;
    }

    public static function getOutputSource($url = '')
    {
        $result = '';
        $service = self::identifyService($url);

        switch ($service) {
            case self::SERVICE_YOUTUBE :
            case self::SERVICE_VIMEO :
                $result = self::getLayzLoadingSource($service, $url);
                break;
            default :
                $result = self::getIframeSource($service, $url);
                break;
        }

        return $result;
    }

    public static function getLayzLoadingSource($service = null, $url = '')
    {
        if ($service === null) {
            $service = self::identifyService($url);
        }

        if ($service !== null) {
            $id = self::getId($url);
            $mask = '<div class="video video-container"><div class="video-player" data-source="%s" data-id="%s"></div></div>';
            //$mask = '<div class="video-container"><div class="video-player" data-source="%s" data-id="%s"></div></div>';

            return sprintf($mask, $service, $id);
        }

        return self::getIframeSource($service, $url);
    }

    public static function getIframeSource($service = null, $url = '')
    {
        if ($service === null) {
            $service = self::identifyService($url);
        }

        $embedUrl = self::getEmbedUrl($url);
        
        $width = '560';
        $height = '315';
        switch ($service) {
            case self::SERVICE_YOUTUBE :
                $width = '560';
                $height = '315';
                break;
            case self::SERVICE_VIMEO :
                $width = '500';
                $height = '281';
                break;
        }

        $mask = '<div class="video"><iframe src="%s" width="%s" height="%s" frameborder="0" webkitallowfullscreen mozallowfullscreen allowfullscreen></iframe></div>';
        
        return sprintf($mask, $embedUrl, $width, $height);
    }

    /**
     * Determines which cloud video provider is being used based on the passed url.
     *
     * @param string $url The url
     * @return null|string Null on failure to match, the service's name on success
     */
    public static function identifyService($url = '')
    {
        if (preg_match('%youtube|youtu\.be%i', $url)) {
            return self::SERVICE_YOUTUBE;
        } elseif (preg_match('%vimeo%i', $url)) {
            return self::SERVICE_VIMEO;
        }

        return null;
    }

    /**
     * Determines which cloud video provider is being used based on the passed url,
     * and extracts the video id from the url.
     *
     * @param string $url The url
     * @return null|string Null on failure, the video's id on success
     */
    public static function getId($url = '')
    {
        $result = null;

        switch (self::identifyService($url)) {
            case self::SERVICE_YOUTUBE :
                $result = self::get_youtube_id($url);
                break;
            case self::SERVICE_VIMEO :
                $result = self::get_vimeo_id($url);
                break;
        }

        return $result;
    }

    /**
     * Determines which cloud video provider is being used based on the passed url,
     * extracts the video id from the url, and builds an embed url.
     *
     * @param string $url The url
     * @return null|string Null on failure, the video's embed url on success
     */
    public static function getEmbedUrl($url = '')
    {
        $result = null;
        $id = self::getId($url);

        switch (self::identifyService($url)) {
            case self::SERVICE_YOUTUBE :
                $result = self::get_youtube_embed($id);
                break;
            case self::SERVICE_VIMEO :
                $result = self::get_vimeo_embed($id);
                break;
        }

        return $result;
    }

    /**
     * Parses various youtube urls and returns video identifier.
     *
     * @param string $url The url
     * @return string the url's id
     */
    public static function get_youtube_id($url = '')
    {
        $youtube_url_keys = ['v', 'vi'];

        // Try to get ID from url parameters
        $key_from_params = self::parse_url_for_params($url, $youtube_url_keys);
        if ($key_from_params) return $key_from_params;

        // Try to get ID from last portion of url
        return self::parse_url_for_last_element($url);
    }

    /**
     * Builds a Youtube embed url from a video id.
     *
     * @param string $youtube_video_id The video's id
     * @return string the embed url
     */
    public static function get_youtube_embed($youtube_video_id, $autoplay = 0)
    {
        $embed = "https://youtube.com/embed/$youtube_video_id?autoplay=$autoplay";

        return $embed;
    }

    /**
     * Parses various vimeo urls and returns video identifier.
     *
     * @param string $url The url
     * @return string The url's id
     */
    public static function get_vimeo_id($url = '')
    {
        // Try to get ID from last portion of url
        return self::parse_url_for_last_element($url);
    }

    /**
     * Builds a Vimeo embed url from a video id.
     *
     * @param string $vimeo_video_id The video's id
     * @return string the embed url
     */
    public static function get_vimeo_embed($vimeo_video_id, $autoplay = 0)
    {
        $embed = "https://player.vimeo.com/video/$vimeo_video_id?byline=0&amp;portrait=0&amp;autoplay=$autoplay";

        return $embed;
    }

    /**
     * Find the first matching parameter value in a url from the passed params array.
     *
     * @access private
     *
     * @param string $url The url
     * @param array $target_params Any parameter keys that may contain the id
     * @return null|string Null on failure to match a target param, the url's id on success
     */
    private static function parse_url_for_params($url = '', $target_params)
    {
        parse_str( parse_url( $url, PHP_URL_QUERY ), $my_array_of_params );

        foreach ($target_params as $target) {
            if (array_key_exists ($target, $my_array_of_params)) {
                return $my_array_of_params[$target];
            }
        }

        return null;
    }

    /**
     * Find the last element in a url, without any trailing parameters
     *
     * @access private
     *
     * @param string $url The url
     * @return string The last element of the url
     */
    private static function parse_url_for_last_element($url = '')
    {
        $url_parts  = explode("/", $url);
        $prospect   = end($url_parts);

        $prospect_and_params = preg_split("/(\?|\=|\&)/", $prospect);
        if ($prospect_and_params) {
            return $prospect_and_params[0];
        } else {
            return $prospect;
        }

        return $url;
    }

}
