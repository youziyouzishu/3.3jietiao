<?php

namespace app\admin\model;


use plugin\admin\app\model\Base;
use support\Db;


/**
 * 
 *
 * @property int $id 主键
 * @property string $username 用户名
 * @property string $nickname 昵称
 * @property string $password 密码
 * @property string $sex 性别
 * @property string|null $avatar 头像
 * @property string|null $email 邮箱
 * @property string|null $mobile 手机
 * @property int $level 等级
 * @property string|null $birthday 生日
 * @property string $money 余额(元)
 * @property int $score 积分
 * @property string|null $last_time 登录时间
 * @property string|null $last_ip 登录ip
 * @property string|null $join_time 注册时间
 * @property string|null $join_ip 注册ip
 * @property string|null $token token
 * @property \Illuminate\Support\Carbon|null $created_at 创建时间
 * @property \Illuminate\Support\Carbon|null $updated_at 更新时间
 * @property int $role 角色
 * @property int $status 禁用
 * @property string|null $openid 微信OPENID
 * @property string|null $truename 姓名
 * @property string|null $idcard 身份证号
 * @property string|null $trade_password 交易密码
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User query()
 * @property-read \app\admin\model\UsersFeedback|null $feedback
 * @property int $contract_status 合同管理状态:0=关闭,1=开启
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \app\admin\model\Receipt> $receipt
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \app\admin\model\Receipt> $toReceipt
 * @mixin \Eloquent
 */
class User extends Base
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'wa_users';


    /**
     * The primary key associated with the table.
     *
     * @var string
     */
    protected $primaryKey = 'id';

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'last_time' => 'datetime',
        'join_time' => 'datetime',
    ];

    protected $fillable = [
        'username',
        'nickname',
        'password',
        'sex',
        'avatar',
        'email',
        'mobile',
        'level',
        'birthday',
        'money',
        'score',
        'last_time',
        'last_ip',
        'join_time',
        'join_ip',
        'token',
        'created_at',
        'updated_at',
        'role',
        'status',
        'openid',
        'truename',
        'idcard',
        'trade_password',
    ];



    /**
     * 变更会员积分
     * @param numeric $score 积分
     * @param int $user_id 会员ID
     * @param string $memo 备注
     * @param string $type
     * @throws \Throwable
     */
    public static function score($score, $user_id, $memo, $type)
    {
        Db::connection('plugin.admin.mysql')->beginTransaction();
        try {
            $user = self::lockForUpdate()->find($user_id);
            if ($user && $score != 0) {
                $before = $user->$type;
                $after = $user->$type + $score;
                //更新会员信息
                $user->$type = $after;
                $user->save();
                //写入日志
                UsersScoreLog::create(['user_id' => $user_id, 'score' => $score, 'before' => $before, 'after' => $after, 'memo' => $memo, 'type' => $type]);
            }
            Db::connection('plugin.admin.mysql')->commit();
        } catch (\Throwable $e) {
            Db::connection('plugin.admin.mysql')->rollback();
        }
    }

    function feedback()
    {
        return $this->hasOne(UsersFeedback::class, 'user_id', 'id');
    }

    function receipt()
    {
        return $this->hasMany(Receipt::class, 'user_id', 'id');
    }

    function toReceipt()
    {
        return $this->hasMany(Receipt::class, 'to_user_id', 'id');
    }


}
