<?php

namespace app\process;

use app\admin\model\Receipt;
use Illuminate\Database\Eloquent\Builder;
use support\Log;
use Workerman\Crontab\Crontab;

class Task
{
    public function onWorkerStart()
    {
        new Crontab('0 */5 * * * *', function(){
            $date = date('Y-m-d');
            $receipt = Receipt::where('status', 1)->where('end_date', '<=', $date)->get();
            foreach ($receipt as $item) {
                $item->status = 2;
                $item->save();
            }
        });

        new Crontab('0 23 * * *', function(){
            $receipt = Receipt::where('status', 3)->where(function (Builder $query){
                $query->orWhereHas('user', function (Builder $query){
                    $query->where('contract_status',1);
                })->orWhereHas('toUser', function (Builder $query){
                    $query->where('contract_status',1);
                });
            })->get();
            foreach ($receipt as $item) {
                $item->delete();
            }
        });



    }
}