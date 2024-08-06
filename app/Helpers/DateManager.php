<?php
namespace App\Helpers;

use Carbon\Carbon;

class DateManager{
    public function verificateIfDateIsValid($start_date,$end_date){
        $start_hour = Carbon::createFromFormat('d-m-Y H:i', $start_date);
        $end_hour = Carbon::createFromFormat('d-m-Y H:i', $end_date);

         
        if($start_hour >= $end_hour){
            return false;
        }
        if(now()>=$start_hour){
            return false;
        }
        return true;
        

    }
}

