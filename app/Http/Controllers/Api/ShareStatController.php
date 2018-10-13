<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;

class ShareStatController extends Controller {
    public function test() {
//        return response()->json(err('对应的资源不存在'));
        return response()->json(['status'=> 200, 'msg'=>'查询成功！']);
    }


    public function shareStat() {
        $data = DB::table('shares')->get();
        $total = DB::table('share_totals')->first();
        $totalData = [
            'share_total' => (int)$total->share_total,
            'click_total' => (int)$total->click_total,
        ];
        return response()->json(['l'=>$data, 'total'=>$totalData]);
    }
}