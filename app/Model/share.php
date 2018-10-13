<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class share extends Model
{
    //
    //
    protected $table = 'shares';

    public $timestamps = false;

    protected $filelable = [
        'shareid', 'share_people_num', 'share_num',
        'click_num','click_people_num', 'register_num',
    ];

//    protected  function getDateFormat(){
//        return time();
//    }
//
//    protected function asDateTime($value){
//        return $value;
//    }
}
