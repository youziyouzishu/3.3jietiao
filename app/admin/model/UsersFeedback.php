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
 * @property string $field1 常用功能
 * @property string $field2 满意度
 * @property string|null $field3 希望的新功能
 * @property string|null $field4 新功能原因
 * @property string|null $field5 需要改进的功能
 * @property string|null $field6 其他
 * @property Carbon|null $created_at 创建时间
 * @property Carbon|null $updated_at 更新时间
 * @method static Builder<static>|UsersFeedback newModelQuery()
 * @method static Builder<static>|UsersFeedback newQuery()
 * @method static Builder<static>|UsersFeedback query()
 * @mixin Eloquent
 */
class UsersFeedback extends Base
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'wa_users_feedback';

    /**
     * The primary key associated with the table.
     *
     * @var string
     */
    protected $primaryKey = 'id';

    protected $fillable = [
        'user_id', 'field1', 'field2', 'field3', 'field4', 'field5', 'field6'
    ];

    function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

}
