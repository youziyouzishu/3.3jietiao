<?php

namespace app\admin\model;


use Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;
use plugin\admin\app\model\Base;


/**
 *
 *
 * @property int $id 主键
 * @property int $user_id 用户
 * @property string $class_name 分类名称
 * @property string $content 内容
 * @property string|null $images 图片
 * @property string|null $truename 姓名
 * @property string|null $mobile 电话
 * @property Carbon|null $created_at 创建时间
 * @property Carbon|null $updated_at 更新时间
 * @property-read User|null $user
 * @method static Builder<static>|Advice newModelQuery()
 * @method static Builder<static>|Advice newQuery()
 * @method static Builder<static>|Advice query()
 * @mixin Eloquent
 */
class Advice extends Base
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'wa_advice';

    /**
     * The primary key associated with the table.
     *
     * @var string
     */
    protected $primaryKey = 'id';


    protected  $fillable = [
        'user_id',
        'class_name',
        'content',
        'images',
        'truename',
        'mobile',
    ];

    function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }


}
