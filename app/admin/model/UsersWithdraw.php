<?php

namespace app\admin\model;


use GuzzleHttp\Client;
use plugin\admin\app\common\Util;
use plugin\admin\app\model\Base;
use plugin\admin\app\model\User;


/**
 * 
 *
 * @property int $id 主键
 * @property int $user_id 用户
 * @property string $withdraw_amount 提现金额
 * @property string $chance_amount 手续费
 * @property string $into_amount 到账金额
 * @property string $chance_rate 手续费比例
 * @property int $status 状态:0=待审核,1=已打款,2=驳回
 * @property string $reason 驳回原因
 * @property \Illuminate\Support\Carbon|null $created_at 创建时间
 * @property \Illuminate\Support\Carbon|null $updated_at 更新时间
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UsersWithdraw newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UsersWithdraw newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UsersWithdraw query()
 * @mixin \Eloquent
 */
class UsersWithdraw extends Base
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'wa_users_withdraw';

    /**
     * The primary key associated with the table.
     *
     * @var string
     */
    protected $primaryKey = 'id';

    protected $fillable = [
        'user_id',
        'withdraw_amount',
        'chance_amount',
        'into_amount',
        'chance_rate',
        'status',
        'reason',
    ];
}
