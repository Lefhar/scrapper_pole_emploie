<?php

namespace App;

class Extraction
{
    public function extstres($content, $start, $end)
    {
        if ((($content and $start) and $end)) {
            $r = explode($start, $content);
            if (isset($r[1])) {
                $r = explode($end, $r[1]);
                return $r[0];
            }
            return '';
        } else {
            return false;
        }
    }
}