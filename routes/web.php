<?php

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
    return view('auth/login');
});
Route::any('/index', "ChannelController@channelDetail")->middleware('auth');
Route::any('/home', "ChannelController@channelDetail")->middleware('auth');

Route::any('/rechargedetail', "ChannelController@rechargeDetail")->middleware('auth');
Route::any('/rechargeDetailDate/{date}', "ChannelController@rechargeDetailDate")->middleware('auth');
Route::any('/test', "ChannelController@test");

Route::any('/totalPay', "ChannelController@totalPay")->middleware('auth');
Route::any('/totalPayDate/{date}', "ChannelController@totalPayDate")->middleware('auth');

Route::any('/addChannel', "ChannelController@addChannel")->middleware('auth');
Route::any('/channelPay', "ChannelController@channelPay")->middleware('auth');
Route::any('/channelList', "ChannelController@channelList")->middleware('auth');
Route::any('/changeChannel/{id}', "ChannelController@changeChannel")->middleware('auth');
Route::any('/channelData/{id}', "ChannelController@channelData")->middleware('auth');

Route::any('/addUser', "ChannelController@addUser")->middleware('auth');
Route::any('/userList', "ChannelController@userList")->middleware('auth');
Route::any('/changeUser/{id}', "ChannelController@changeUser")->middleware('auth');
Route::any('/moreGame', "ChannelController@moreGames")->middleware('auth');
Route::any('/channelServer', "ChannelController@channelServer")->middleware('auth');


Route::get('/user', 'HomeController@getUser');
Route::get('/redis', 'RedisController@test');

Route::get('/testRedis','RedisController@testRedis')->name('testRedis');


Auth::routes();
$this->get('registerbearjoy', 'Auth\RegisterController@showRegistrationForm')->name('register');
$this->post('registerbearjoy', 'Auth\RegisterController@register');
$this->get('register', 'Auth\LoginController@showLoginForm')->name('login');
$this->post('register', 'Auth\LoginController@login');

//Route::get('/home', 'HomeController@index')->name('home')->middleware('auth');
Route::get('/HomeLogins', 'HomeController@homeLogins')->middleware('auth');
//Route::get('/login', 'ChannelController@channelDetail');
