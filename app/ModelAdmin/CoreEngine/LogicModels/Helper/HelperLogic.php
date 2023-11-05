<?php


namespace App\ModelAdmin\CoreEngine\LogicModels\Helper;


class HelperLogic {

    public static function getDateDiffPeriod($dataEnd , $date){
        $dataEnd = strtotime($dataEnd);
        $date  = strtotime($date);

        $diff = abs($dataEnd - $date);
        $years = floor($diff / (365*60*60*24));
        $months = floor(($diff - $years * 365*60*60*24)
            / (30*60*60*24));
        $days = floor(($diff - $years * 365*60*60*24 -
                $months*30*60*60*24)/ (60*60*24));
        $hours = floor(($diff - $years * 365*60*60*24
                - $months*30*60*60*24 - $days*60*60*24)
            / (60*60));

        $minutes = floor(($diff - $years * 365*60*60*24
                - $months*30*60*60*24 - $days*60*60*24
                - $hours*60*60)/ 60);
        $seconds = floor(($diff - $years * 365*60*60*24
            - $months*30*60*60*24 - $days*60*60*24
            - $hours*60*60 - $minutes*60));

        return [
            'years'=>(int)$years,
            'months'=>(int)$months,
            'days'=>(int)$days,
            'hours'=>(int)$hours,
            'minutes' =>(int)$minutes,
            'seconds' =>(int)$seconds
        ];
    }
}
