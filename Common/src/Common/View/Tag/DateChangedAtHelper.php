<?php

namespace Common\View\Tag;

class DateChangedAtHelper
{

    public function format($date = null, $format = 'd/m/y H:i')
    {   
        if ( ! $date instanceOf \DateTime ) {
            return '';
        }

        $html = sprintf(
            '<div><div class="sp7"></div><div class="status-owner">Changed at %s</div></div>',
            $date->format($format)
        );

        return $html;
    }

}
