<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class total_serverlist extends Model
{
    //
    protected $table = 'total_serverlists';

    public $timestamps = false;

    protected $filelable = [
        'c_date', 'channle_id', 'total_insl_sum', 'new_add_user',
    ];

    protected  function getDateFormat(){
        return time();
    }

    protected function asDateTime($value){
        return $value;
    }
}
