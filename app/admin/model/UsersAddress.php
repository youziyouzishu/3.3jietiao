<?php

namespace app\admin\model;


use Illuminate\Database\Eloquent\SoftDeletes;
use plugin\admin\app\model\Base;
use support\Db;


/**
 * 
 *
 * @property int $id 主键
 * @property int $user_id 用户
 * @property int $default 默认:0=否,1=是
 * @property string $province 省
 * @property string $city 市
 * @property string $region 区
 * @property string $mobile 手机号
 * @property string $name 姓名
 * @property string $address 详细地址
 * @property \Illuminate\Support\Carbon|null $created_at 创建时间
 * @property \Illuminate\Support\Carbon|null $updated_at 更新时间
 * @property \Illuminate\Support\Carbon|null $deleted_at 删除时间
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UsersAddress newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UsersAddress newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UsersAddress onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UsersAddress query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UsersAddress withTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UsersAddress withoutTrashed()
 * @property string $lat 纬度
 * @property string $lng 经度
 * @mixin \Eloquent
 */
class UsersAddress extends Base
{

    use SoftDeletes;
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'wa_users_address';

    /**
     * The primary key associated with the table.
     *
     * @var string
     */
    protected $primaryKey = 'id';


    protected $fillable = [
        'user_id',
        'default',
        'province',
        'city',
        'region',
        'mobile',
        'name',
        'address',
        'lat',
        'lng',
    ];

    


}
