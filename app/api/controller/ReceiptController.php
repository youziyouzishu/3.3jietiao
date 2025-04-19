<?php

namespace app\api\controller;

use app\admin\model\Receipt;
use app\admin\model\User;
use app\api\basic\Base;
use app\api\service\Pay;
use Carbon\Carbon;
use plugin\admin\app\common\Util;
use setasign\Fpdi\Tfpdf\Fpdi;
use Spatie\PdfToImage\Pdf;
use support\Db;
use support\Log;
use support\Request;
use support\Response;
use Webman\RedisQueue\Client;

class ReceiptController extends Base
{

    function getUserByIdcard(Request $request)
    {
        $idcard = $request->post('idcard');
        $truename = $request->post('truename');
        $user = User::select(['id', 'nickname'])->where(['idcard' => $idcard, 'truename' => $truename])->first();
        return $this->success('获取成功', $user);
    }

    function insert(Request $request): Response
    {
        $type = $request->post('type');#类型:1=我是出借方,2=我是借款方
        $to_user_id = $request->post('to_user_id');#对方
        $amount = $request->post('amount');#欠款金额
        $repayment_type = $request->post('repayment_type');#还款方式:1=一次性还本付息,2=分期还款
        $rate = $request->post('rate');#年化利率
        $start_date = $request->post('start_date');#起始日期
        $end_date = $request->post('end_date');#还款日期
        $reason = $request->post('reason');#欠款原因
        $mark = $request->post('mark');#欠款详情
        $stage = $request->post('stage');#分期期数
        $stage_day = $request->post('stage_day');#分期时长
        $stage_amount = $request->post('stage_amount');#每期应收
        $trade_password = $request->post('trade_password');
        if (empty($trade_password)) {
            return $this->fail('交易密码不能为空');
        }
        if (!in_array($type, [1, 2])) {
            return $this->fail('类型错误');
        }
        if (empty($to_user_id)) {
            return $this->fail('对方id不能为空');
        }
        if (empty($amount)) {
            return $this->fail('欠款金额不能为空');
        }
        if ($amount < 100 || $amount > 5000000) {
            return $this->fail('金额范围100-5000000');
        }
        if (!in_array($repayment_type, [1, 2])) {
            return $this->fail('还款方式错误');
        }
        if (empty($rate)) {
            return $this->fail('利率不能为空');
        }
        if (empty($reason)) {
            return $this->fail('欠款原因不能为空');
        }
        if (empty($mark)) {
            return $this->fail('欠款详情不能为空');
        }
        if ($repayment_type == 1) {
            if (empty($start_date)) {
                return $this->fail('起始日期不能为空');
            }
            if (empty($end_date)) {
                return $this->fail('还款日期不能为空');
            }
        }
        if ($repayment_type == 2) {
            return  $this->fail('分期还款暂未开放');
            if (empty($stage)) {
                return $this->fail('分期期数不能为空');
            }
            if (empty($stage_day)) {
                return $this->fail('分期时长不能为空');
            }
            if (empty($stage_amount)) {
                return $this->fail('每期应收不能为空');
            }
        }
        $user = User::find($request->user_id);

        if (!Util::passwordVerify($trade_password,$user->trade_password)) {
            return $this->fail('交易密码错误');
        }
        if ($amount > 100 && $amount < 10000) {
            $pay_amount = 19.8;
        }
        if ($amount >= 10000 && $amount < 20000) {
            $pay_amount = 29.8;
        }
        if ($amount >= 20000 && $amount < 30000) {
            $pay_amount = 39.8;
        }
        if ($amount >= 30000 && $amount < 40000) {
            $pay_amount = 49.8;
        }
        if ($amount >= 40000 && $amount < 50000) {
            $pay_amount = 59.8;
        }
        if ($amount >= 50000 && $amount < 60000) {
            $pay_amount = 69.8;
        }
        if ($amount >= 60000 && $amount < 1000000) {
            $pay_amount = 79.8;
        }
        if ($amount >= 1000000) {
            $pay_amount = 99.8;
        }
        if (!isset($pay_amount)){
            return $this->fail('金额范围错误');
        }
        $start_date = Carbon::parse($start_date);
        $end_date = Carbon::parse($end_date);
        if ($start_date->gt($end_date)) {
            return $this->fail('起始日期不能大于还款日期');
        }
        $interest = $rate * $rate / 100  * $start_date->diffInDays($end_date);
        $amount_and_interest = $amount + $interest;
        DB::connection('plugin.admin.mysql')->beginTransaction();
        try {
            $receipt = Receipt::create([
                'user_id' => $type == 1 ? $request->user_id : $to_user_id,
                'to_user_id' => $type == 1 ? $to_user_id : $request->user_id,
                'amount' => $amount,
                'repayment_type' => $repayment_type,
                'rate' => $rate,
                'start_date' => $start_date,
                'end_date' => $end_date,
                'reason' => $reason,
                'mark' => $mark,
                'stage' => $stage,
                'stage_day' => $stage_day,
                'stage_amount' => $stage_amount,
                'ordersn' => Pay::generateOrderSn(),
                'pay_amount' => $pay_amount,
                'interest' => $interest,
                'amount_and_interest' => $amount_and_interest,
                'repaid_amount' => 0,
                'outstanding_amount' => $amount_and_interest,
            ]);
            Client::send('job', ['id' => $receipt->id, 'event' => 'receipt_expire'],60*60*24);
            DB::connection('plugin.admin.mysql')->commit();
        }catch (\Throwable $e){
            DB::connection('plugin.admin.mysql')->rollBack();
            Log::error($e->getMessage());
            return $this->fail('失败');
        }


        return $this->success('添加成功', $receipt);
    }

    /**
     * 展期
     * @param Request $request
     * @return Response
     */
    function lengthen(Request $request)
    {
        $id = $request->post('id');
        $end_date = $request->post('end_date');
        $receipt = Receipt::find($id);
        if ($receipt->user_id != $request->user_id) {
            return $this->fail('只能出借方操作');
        }
        if (!in_array($receipt->status,[1,2])) {
            return $this->fail('凭证状态异常');
        }
        if (empty($end_date)) {
            return $this->fail('还款日期不能为空');
        }
        $receipt->end_date = $end_date;
        $receipt->save();
        return $this->success('延长成功');
    }

    function writeAccount(Request $request)
    {
        $id = $request->post('id');
        $amount = $request->post('amount');
        $receipt = Receipt::find($id);
        if ($receipt->user_id != $request->user_id) {
            return $this->fail('只能出借方操作');
        }
        if (!in_array($receipt->status,[1,2])) {
            return $this->fail('凭证状态异常');
        }
        $receipt->outstanding_amount -= $amount;
        $receipt->repaid_amount += $amount;
        if ($receipt->outstanding_amount <= 0){
            $receipt->status = 3;
        }
        $receipt->save();
        return $this->success('销账成功');
    }

    function cancel(Request $request)
    {
        $id = $request->post('id');
        $receipt = Receipt::find($id);
        if ($receipt->user_id != $request->user_id) {
            return $this->fail('只能出借方操作');
        }
        if (!in_array($receipt->status,[0,1,2])) {
            return $this->fail('凭证状态异常');
        }
        $receipt->status = 4;
        $receipt->cancel_time = Carbon::now();
        $receipt->save();
        return $this->success('取消成功');
    }


    /**
     * 支付
     * @param Request $request
     * @return Response
     */
    function pay(Request $request)
    {
        $ordersn = $request->post('ordersn');
        $pay_type = $request->post('pay_type');# 1微信
        $sign = $request->post('sign');
        if (empty($sign)) {
            return $this->fail('签名不能为空');
        }
        $receipt = Receipt::where(['ordersn' => $ordersn])->first();
        if (empty($receipt)) {
            return $this->fail('凭证不存在');
        }
        if ($receipt->status != 0) {
            return $this->fail('凭证状态异常');
        }
        if ($receipt->to_user_id != $request->user_id) {
            return $this->fail('只能借款方支付');
        }
        if ($pay_type == 1) {
            try {
                $result = Pay::pay($pay_type, $receipt->pay_amount, $receipt->ordersn, '出证费', 'receipt');
                $receipt->sign = $sign;
                $receipt->save();
            } catch (\Throwable $e) {
                Log::error('支付失败');
                Log::error($e->getMessage());
                return $this->fail('支付失败');
            }
        } else {
            return $this->fail('支付类型错误');
        }
        return $this->success('支付成功', $result);
    }

//    function sign(Request $request)
//    {
//        $id = $request->post('id');
//        $sign = $request->post('sign');
//        $receipt = Receipt::find($id);
//        if (empty($receipt)) {
//            return $this->fail('凭证不存在');
//        }
//        if ($receipt->status != 5) {
//            return $this->fail('凭证状态异常');
//        }
//        $receipt->sign = $sign;
//        $receipt->status = 1;
//        $receipt->save();
//        return $this->success('签名成功');
//    }

    function select(Request $request): Response
    {
        $type = $request->post('type');#1=借入,2=借出
        $status = $request->post('status');#0=全部,1=待确认,2=待还款,3=已逾期,4=已还款,5=已失效
        $field = $request->post('field', 'id');
        $order = $request->post('order', 'desc');
        $truename = $request->post('truename');

        $total = Receipt::where(function ($query) use ($type, $request) {
            if ($type == 1) {
                $query->where('to_user_id', $request->user_id);
            } else {
                $query->where('user_id', $request->user_id);
            }
        })->get();
        $count = $total->count();
        $amount = $total->sum('amount');
        $rows = Receipt::with(['toUser','user'])->where(function ($query) use ($type, $request,$truename) {
            if ($type == 1) {
                $query->where('to_user_id', $request->user_id);
                if (!empty($truename)){
                    $query->whereHas('user', function ($query) use ($truename) {
                        $query->where('truename', $truename);
                    });
                }
            } else {
                $query->where('user_id', $request->user_id);
                if (!empty($truename)){
                    $query->whereHas('toUser', function ($query) use ($truename) {
                        $query->where('truename', $truename);
                    });
                }
            }
        })
            ->when($status, function ($query) use ($status) {
                if ($status == 1) {
                    $query->where('status', 0);
                }
                if ($status == 2) {
                    $query->where('status', 1);
                }
                if ($status == 3) {
                    $query->where('status', 2);
                }
                if ($status == 4) {
                    $query->where('status', 3);
                }
                if ($status == 5) {
                    $query->where('status', 4);
                }
            })
            ->orderBy($field, $order)
            ->paginate()
            ->items();
        return $this->success('获取成功', ['count'=>$count,'amount'=>$amount,'list'=>$rows]);
    }


    function detail(Request $request)
    {
        $id = $request->post('id');
        $receipt = Receipt::with(['user','toUser'])->find($id);
        if (empty($receipt)) {
            return $this->fail('凭证不存在');
        }
        return $this->success('获取成功', $receipt);
    }


}
