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
 * @property string $truename 姓名
 * @property string $idcard 身份证号
 * @property string $token token
 * @property Carbon|null $created_at 创建时间
 * @property Carbon|null $updated_at 更新时间
 * @method static Builder<static>|EidToken newModelQuery()
 * @method static Builder<static>|EidToken newQuery()
 * @method static Builder<static>|EidToken query()
 * @mixin Eloquent
 */
class EidToken extends Base
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'wa_eid_token';

    /**
     * The primary key associated with the table.
     *
     * @var string
     */
    protected $primaryKey = 'id';

    protected $fillable = [
        'truename',
        'idcard',
        'token',
    ];
    
    
    
}
