<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        error_reporting(E_ALL^E_WARNING^E_NOTICE);
        Validator::extend('channel', function ($field,$channelId) {

            $channle = DB::table('channels')->where('channel_id', $channelId)->first();
            $noExist =  $channle ? false : true;
            return $noExist;
        });

        Validator::extend('userExist', function ($field,$userId) {

            $user = DB::table('users')->where('id', $userId)->first();
            $noExist =  $user ? true : false;
            return $noExist;
        });
    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }
}
