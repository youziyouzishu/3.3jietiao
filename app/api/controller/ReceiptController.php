<?php

namespace app\api\controller;

use app\admin\model\Receipt;
use app\admin\model\User;
use app\api\basic\Base;
use app\api\service\Pay;
use plugin\admin\app\common\Util;
use setasign\Fpdi\Tfpdf\Fpdi;
use support\Log;
use support\Request;
use support\Response;

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
        if ($amount > 10000 && $amount < 20000) {
            $pay_amount = 29.8;
        }
        if ($amount > 20000 && $amount < 30000) {
            $pay_amount = 39.8;
        }
        if ($amount > 30000 && $amount < 40000) {
            $pay_amount = 49.8;
        }
        if ($amount > 40000 && $amount < 50000) {
            $pay_amount = 59.8;
        }
        if ($amount > 50000 && $amount < 60000) {
            $pay_amount = 69.8;
        }
        if ($amount > 60000 && $amount < 1000000) {
            $pay_amount = 79.8;
        }
        if ($amount > 1000000) {
            $pay_amount = 99.8;
        }
        if (!isset($pay_amount)){
            return $this->fail('金额范围错误');
        }


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
        ]);
        $receipt->refresh();
        // 初始化 FPDI
        $pdf = new Fpdi();
        // 导入现有 PDF 文件的第一页
        $pageCount = $pdf->setSourceFile(public_path('借款协议.pdf'));
        for ($i = 1; $i <= $pageCount; $i++) {
            // 导入 PDF 文件的每一页
            $templateId = $pdf->importPage($i);
            $pdf->AddPage();
            $pdf->useTemplate($templateId);
            $pdf->AddFont('SIMHEI','','SIMHEI.TTF',true);
            $pdf->SetFont('SIMHEI', );
            if ($i == 1){
                $pdf->SetFontSize(10);
                $pdf->Text(56,45.5, $receipt->ordersn);
                $pdf->Text(56,78.3, $receipt->toUser->truename);
                $pdf->Text(56,83.7, $receipt->toUser->idcard);
                $pdf->Text(56,89, $receipt->user->truename);
                $pdf->Text(56,94.7, $receipt->user->idcard);

                $pdf->Text(56,134, $receipt->amount);
                $pdf->Text(56,140, $receipt->rate);
                $pdf->Text(56,147, $receipt->rate * $receipt->rate / 100  * $receipt->start_date->diffInDays($receipt->end_date));
                $pdf->Text(56,153.5, $receipt->rate * $receipt->rate / 100  * $receipt->start_date->diffInDays($receipt->end_date) + $receipt->amount);
                $pdf->Text(56,160, $receipt->rate * $receipt->rate / 100  * $receipt->start_date->diffInDays($receipt->end_date) + $receipt->amount);
                $pdf->Text(56,166.5, $receipt->repayment_type_text);
                $pdf->Text(56,173.5, $receipt->start_date->toDateString());
                $pdf->Text(56,180, $receipt->end_date->toDateString());
                $pdf->Text(56,186.5, $receipt->reason);
            }
            if ($i == 4){
                $pdf->SetFontSize(10);
                $pdf->Text(56,167, $receipt->user->truename);
                $pdf->Text(56,172, $receipt->toUser->truename);
                $pdf->Text(56,178, date('Y-m-d'));
            }
        }
        // 输出 PDF 文件
        $pdf->Output(public_path("/borrow/$receipt->id.pdf"), 'F'); // 保存为文件


        // 导入现有 PDF 文件的第一页
        $pageCount = $pdf->setSourceFile(public_path('授权确认书.pdf'));
        for ($i = 1; $i <= $pageCount; $i++) {
            // 导入 PDF 文件的每一页
            $templateId = $pdf->importPage($i);
            // 添加新页面（基于导入的页面）
            $pdf->AddPage();
            // 使用模板
            $pdf->useTemplate($templateId);
            $pdf->AddFont('SIMHEI','','SIMHEI.TTF',true);
            $pdf->SetFont('SIMHEI', );
            if ($i == 1){
                $pdf->SetFontSize(10);
                // 在页面上添加文本
                $pdf->Text(60,45.3, $receipt->ordersn);
                $pdf->Text(60,73, $receipt->toUser->truename);
                $pdf->Text(60,78.5, $receipt->toUser->idcard);
            }
            if ($i == 7){
                $pdf->SetFontSize(10);
                // 在页面上添加文本
                $pdf->Text(60,40.5, '叁凯商贸');
                $pdf->Text(60,46, date('Y-m-d'));
            }

        }
        // 输出 PDF 文件
        $pdf->Output(public_path("/cert/$receipt->id.pdf"), 'F'); // 保存为文件
        $receipt->clause_rule = '/出借人重要条款提示.pdf';
        $receipt->borrow_rule = "/borrow/$receipt->id.pdf";
        $receipt->cert_rule = "/cert/$receipt->id.pdf";
        $receipt->save();

        return $this->success('添加成功', $receipt);
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
        $receipt = Receipt::where(['ordersn' => $ordersn])->first();
        if (empty($receipt)) {
            return $this->fail('凭证不存在');
        }
        if ($receipt->status != 0) {
            return $this->fail('凭证状态异常');
        }
        if ($receipt->to_user_id != $request->user_id) {
            return $this->fail('只能欠款方支付');
        }
        if ($pay_type == 1) {
            try {
                $result = Pay::pay($pay_type, $receipt->pay_amount, $receipt->ordersn, '出证费', 'receipt');
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
