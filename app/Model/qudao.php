<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class qudao extends Model
{
    //
    protected $table = 'qudaos';

    public $timestamps = false;

    protected $filelable = [
        'channel_id', 'channel_name',
    ];

    protected function getDateFormat(){
        return time();
    }

    protected function asDateTime($value) {
        return $value;
    }

}
