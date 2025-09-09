<?php

namespace app\admin\model;


use Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;
use plugin\admin\app\model\Base;

/**
 * 
 *
 * @property int $id 主键
 * @property int $user_id 出借人
 * @property int $to_user_id 借款方
 * @property string $amount 欠款金额
 * @property int $repayment_type 还款方式:1=一次性还本付息,2=分期还款
 * @property float $rate 年化利率
 * @property string|null $reason 欠款原因
 * @property string|null $mark 原因详情
 * @property int|null $stage 分期期数
 * @property int|null $stage_day 分期时长
 * @property string|null $stage_amount 每期应收
 * @property Carbon|null $created_at 创建时间
 * @property Carbon|null $updated_at 更新时间
 * @method static Builder<static>|Receipt newModelQuery()
 * @method static Builder<static>|Receipt newQuery()
 * @method static Builder<static>|Receipt query()
 * @property string $ordersn 订单编号
 * @property-read User|null $toUser
 * @property-read User|null $user
 * @property string $pay_amount 支付金额
 * @property int $pay_type 支付类型:0=无,1=微信
 * @property-read mixed $status_text
 * @property string|null $deleted_at 删除时间
 * @method static Builder<static>|Receipt onlyTrashed()
 * @method static Builder<static>|Receipt withTrashed()
 * @method static Builder<static>|Receipt withoutTrashed()
 * @property Carbon|null $start_date 起始日期
 * @property Carbon|null $end_date 还款日期
 * @property-read mixed $repayment_type_text
 * @property string|null $clause_rule 条款协议
 * @property string|null $borrow_rule 借款协议
 * @property string|null $cert_rule 授权协议
 * @property-read mixed $pay_type_text
 * @property string|null $sign 签名
 * @property int $status 状态:0=待确认,1=待还款,2=已逾期,3=已还款,4=已失效
 * @property string|null $clause_images 条款协议图片
 * @property string|null $cert_images 授权协议图片
 * @property string|null $borrow_images 借款协议图片
 * @property string|null $interest 利息
 * @property string|null $amount_and_interest 本息
 * @property string|null $repaid_amount 已还金额
 * @property string|null $outstanding_amount 待还金额
 * @property Carbon|null $pay_time 支付时间
 * @property Carbon|null $cancel_time 取消时间
 * @mixin Eloquent
 */
class Receipt extends Base
{
    use SoftDeletes;
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'wa_receipt';

    /**
     * The primary key associated with the table.
     *
     * @var string
     */
    protected $primaryKey = 'id';

    protected $casts = [
        'start_date' => 'date:Y-m-d',
        'end_date' => 'date:Y-m-d',
        'pay_time' => 'datetime:Y-m-d H:i:s',
        'cancel_time' => 'datetime:Y-m-d H:i:s',
    ];

    protected $fillable = [
        'user_id',
        'to_user_id',
        'amount',
        'repayment_type',
        'rate',
        'start_date',
        'end_date',
        'reason',
        'mark',
        'stage',
        'stage_day',
        'stage_amount',
        'status',
        'created_at',
        'updated_at',
        'ordersn',
        'pay_amount',
        'pay_type',
        'clause_rule',
        'borrow_rule',
        'cert_rule',
        'pay_time',
        'sign',
        'clause_images',
        'cert_images',
        'borrow_images',
        'interest',
        'amount_and_interest',
        'repaid_amount',
        'outstanding_amount',
        'cancel_time',
    ];

    protected $appends = [
        'status_text',
        'repayment_type_text',
        'pay_type_text',
    ];

    function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    function toUser()
    {
        return $this->belongsTo(User::class, 'to_user_id', 'id');
    }

    function getStatusTextAttribute($value)
    {
        $value = $value ? $value : $this->status;
        $list = $this->getStatusList();
        return $list[$value] ?? '';
    }

    function getStatusList()
    {
        return [
            0 => '待确认',
            1 => '待还款',
            2 => '已逾期',
            3 => '已还款',
            4 => '已失效',
        ];
    }

    function getRepaymentTypeTextAttribute($value)
    {
        $value = $value ? $value : $this->repayment_type;
        $list = $this->getRepaymentTypeList();
        return $list[$value] ?? '';
    }

    function getRepaymentTypeList()
    {
        return [
            1 => '一次性还本付息',
            2 => '分期还款',
        ];
    }

    function getPayTypeTextAttribute($value)
    {
        $value = $value ? $value : $this->pay_type;
        $list = $this->getPayTypeList();
        return $list[$value] ?? '';
    }

    function getPayTypeList()
    {
        return [
            0 => '无',
            1 => '微信',
        ];
    }



}
