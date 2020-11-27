<?php

namespace Common\Tool;

class StringTool
{

    public static function createSlug($str)
    {
        setlocale(LC_ALL, 'en_US.UTF8');
        $clean = iconv('UTF-8', 'ASCII//TRANSLIT', $str);
        $clean = preg_replace("/[^a-zA-Z0-9\/_| -]/", '', $clean);
        $clean = strtolower(trim($clean, '-'));
        $clean = preg_replace("/[\/_| -]+/", '-', $clean);

        return $clean;
    }

    public static function createParagraphTags($text = '', $removeEmptyLines = true)
    {
        $withParagraphs = '';

        foreach (preg_split("/((\r?\n)|(\r\n?))/", $text) as $line) {
            $process = true;
            if ($removeEmptyLines && trim($line) === '') {
                $process = false;
            }
            if ($process) {
                $pos = strpos($line, '<p>');
                if ($pos !== false) {
                    $withParagraphs .= $line;
                } else {
                    $withParagraphs .= '<p>'. ($line !== '' ? $line : '&nbsp;') .'</p>';
                }
            }
        }

        return $withParagraphs;
    }

}