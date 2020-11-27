<?php

namespace Common\Tool;

class KeywordsTool
{

    /**
     * @var string
     */
    public static $charset = 'UTF-8';

    /**
     * @var int
     */
    public static $minWordLength = 4;

    /**
     * Get limited text phrase from long text
     * 
     * @access public
     * @param string $text
     * @param int $length
     * @param string $endChar
     * @param object|null $escaper
     * @return string
     */
    public static function text($text, $length = 160, $endChar = '', $escaper = null)
    {
        return self::limitChars(self::cleanText($text, $escaper), $length, $endChar, true);
    } 

    /**
     * Get the keywords from the text
     * 
     * @access public
     * @param string $text
     * @param int $maxKeys
     * @param object|null $escaper
     * @param array $bannedWords
     * @return string
     */
    public static function keywords($text, $maxKeys = 25, $escaper = null, $bannedWords = [])
    {
        $cleanedText = self::cleanText($text, $escaper);

        //$wordcount = array_count_values(str_word_count($cleanedText, 1));
        $wordcount = array_count_values(self::wordCount($cleanedText, 1));

        // remove small or banned words
        foreach ($wordcount as $key => $value) {
            if ( (strlen((string)$key) <= self::$minWordLength) || in_array(mb_strtolower($key), $bannedWords) ) {
                unset($wordcount[$key]);
            }
        }

        // sort keywords from most repetitions to less 
        uasort($wordcount, ['self','cmp']);

        // keep only X keywords
        $wordcount = array_slice($wordcount,0, $maxKeys);

        return implode(', ', array_keys($wordcount));
    }

    /**
     * Cleans an string from HTML spaces etc...
     * 
     * @access public
     * @param string $text
     * @param object $escaper
     * @return string
     */
    public static function cleanText($text, $escaper = null)
    {
        $text = html_entity_decode($text, ENT_QUOTES, self::$charset);
        $text = strip_tags($text); // erases any html markup
        $text = str_replace(['\r\n', '\n', '+'], ',', $text); // replace possible returns 
        $text = strip_tags(nl2br($text)); // erases any html markup
        $text = str_replace([',', '.', '!', '?', '(', ')', '[', ']', '»', '«', '"', "'", '-', '+', '=', '„', '”'], ' ', $text);
        $text = preg_replace('/\s\s+/', ' ', $text); // erase possible duplicated white spaces

        if (is_object($escaper)) {
            return preg_replace('/\s+/', ' ', $escaper->escapeHtmlAttr(trim(strip_tags($text))));
        }

        return trim(strip_tags($text)); 
    } 

    /**
     * Sort for uasort descendent numbers, compares values
     * 
     * @access private
     * @param int $a
     * @param int $b
     * @return int
     */
    private static function cmp($a, $b) 
    {
        if ($a == $b) {
            return 0; 
        }

        return ($a < $b) ? 1 : -1; 
    }

    private static function wordCount($str, $n = "0")
    {
        $m = strlen($str)/2;
        $a = 1;
        while ($a < $m) {
            $str = str_replace("  ", " ", $str);
            $a++;
        }

        $b = explode(" ", $str);
        $i = 0;
        foreach ($b as $v) {
            $i++;
        }

        if ($n == 1) {
            return $b;
        }

        return $i;
    }

    /**
     * Limits a phrase to a given number of characters.
     * 
     * @access private
     * @param string $str
     * @param int $limit
     * @param string $endChar
     * @param boolean $preserveWords
     * @return string
     */
    private static function limitChars($str, $limit = 100, $endChar = null, $preserveWords = false)
    {
        $endChar = ($endChar === null) ? '...' : $endChar;

        $limit = (int) $limit;

        if (trim($str) === '' || mb_strlen($str) <= $limit) {
            return $str;
        }

        if ($limit <= 0) {
            return $endChar;
        }

        if ($preserveWords === false) {
            return rtrim(mb_substr($str, 0, $limit)).$endChar;
        }

        if (!preg_match('/^.{0,'.$limit.'}\s/us', $str, $matches)) {
            return $endChar;
        }

        return rtrim($matches[0]) . ((strlen($matches[0]) === strlen($str)) ? '' : $endChar);
    }

}