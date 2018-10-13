<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class share_total extends Model
{
    //
    protected $table = 'share_totals';

    public $timestamps = false;

    protected $filelable = [
        'share_total', 'click_total',
    ];
}
