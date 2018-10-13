<?php
/**
 * Created by PhpStorm.
 * User: hwp
 * Date: 18/7/20
 * Time: 上午11:00
 */
namespace App\Model;
use Illuminate\Database\Eloquent\Model;

class Channel extends Model {
    //
    protected $table = 'channel';

    public $timestamps = true;

    protected $filelable = [
        'user_id', 'channel_id', 'channel_name',
    ];

    protected function getDateFormat(){
        return time();
    }

    protected function asDateTime($value) {
        return $value;
    }
}