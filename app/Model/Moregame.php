<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class Moregame extends Model
{
    //
    protected $table = 'moregames';

    public $timestamps = false;

    protected $filelable = [
        'more_id', 'more_people_num', 'more_num',
    ];
}
