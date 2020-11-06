<?php
    function dateFormatter($date){
        $newDate = strtotime("now");
        $result = $newDate - $date;
        $minut = round($result / 60);
        if($minut > 59){
            $hours = $minut / 60;
            return $hours.' soat';
        }
        return $minut.' minut';
    }