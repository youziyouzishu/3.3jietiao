<?php

namespace app\queue\redis;

use app\admin\model\Receipt;
use Carbon\Carbon;
use setasign\Fpdi\Tfpdf\Fpdi;
use Spatie\PdfToImage\Pdf;
use support\Log;
use Webman\RedisQueue\Consumer;

class Job implements Consumer
{
    // 要消费的队列名
    public $queue = 'job';

    // 连接名，对应 plugin/webman/redis-queue/redis.php 里的连接`
    public $connection = 'default';

    // 消费
    public function consume($data)
    {
        try {
            $event = $data['event'];
            Log::info($event);
            if ($event == 'generate_pdf') {
                $id = $data['id'];
                $receipt = Receipt::find($id);
                // 初始化 FPDI
                $pdf = new Fpdi();
                // 导入现有 PDF 文件的第一页
                $pageCount = $pdf->setSourceFile(public_path('借款协议.pdf'));
                for ($i = 1; $i <= $pageCount; $i++) {
                    // 导入 PDF 文件的每一页
                    $templateId = $pdf->importPage($i);
                    $pdf->AddPage();
                    $pdf->useTemplate($templateId);
                    $pdf->AddFont('SIMHEI', '', 'SIMHEI.TTF', true);
                    $pdf->SetFont('SIMHEI');
                    if ($i == 1) {
                        $pdf->SetFontSize(10);
                        $pdf->Text(56, 45.5, $receipt->ordersn);
                        $pdf->Text(56, 78.3, $receipt->toUser->truename);
                        $pdf->Text(56, 83.7, $receipt->toUser->idcard);
                        $pdf->Text(56, 89, $receipt->user->truename);
                        $pdf->Text(56, 94.7, $receipt->user->idcard);

                        $pdf->Text(56, 134, $receipt->amount);
                        $pdf->Text(56, 140, $receipt->rate);
                        $pdf->Text(56, 147, $receipt->interest);
                        $pdf->Text(56, 153.5, $receipt->amount_and_interest);
                        $pdf->Text(56, 160, $receipt->outstanding_amount);
                        $pdf->Text(56, 173.5, $receipt->start_date->toDateString());
                        $pdf->Text(56, 180, $receipt->end_date->toDateString());
                        $pdf->Text(56, 186.5, $receipt->reason);
                    }
                    if ($i == 4) {
                        $pdf->SetFontSize(10);
                        $pdf->Text(56, 167, $receipt->user->truename);
                        $pdf->Text(56, 172, $receipt->toUser->truename);
                        $pdf->Text(56, 178, date('Y-m-d'));
                    }
                }
                // 输出 PDF 文件
                $pdf->Output(public_path("/borrow/$receipt->id.pdf"), 'F'); // 保存为文件
                $pdf = new Fpdi();
                // 导入现有 PDF 文件的第一页
                $pageCount = $pdf->setSourceFile(public_path('授权确认书.pdf'));
                for ($i = 1; $i <= $pageCount; $i++) {
                    // 导入 PDF 文件的每一页
                    $templateId = $pdf->importPage($i);
                    // 添加新页面（基于导入的页面）
                    $pdf->AddPage();
                    // 使用模板
                    $pdf->useTemplate($templateId);
                    $pdf->AddFont('SIMHEI', '', 'SIMHEI.TTF', true);
                    $pdf->SetFont('SIMHEI');
                    if ($i == 1) {
                        $pdf->SetFontSize(10);
                        // 在页面上添加文本
                        $pdf->Text(60, 45.3, $receipt->ordersn);
                        $pdf->Text(60, 73, $receipt->toUser->truename);
                        $pdf->Text(60, 78.5, $receipt->toUser->idcard);
                    }
                    if ($i == 7) {
                        $pdf->SetFontSize(10);
                        // 在页面上添加文本
                        $pdf->Text(60, 40.5, '叁凯商贸');
                        $pdf->Text(60, 46, date('Y-m-d'));
                    }

                }
                // 输出 PDF 文件
                $pdf->Output(public_path("/cert/$receipt->id.pdf"), 'F'); // 保存为文件
                $receipt->clause_rule = '/出借人重要条款提示.pdf';
                $receipt->borrow_rule = "/borrow/$receipt->id.pdf";
                $receipt->cert_rule = "/cert/$receipt->id.pdf";
                $borrow_images = [];
                // 初始化 PDF 对象
                $pdf = new Pdf(public_path($receipt->borrow_rule));
                // 获取总页数
                $pageCount = $pdf->pageCount();
                // 遍历每一页并转换为 Base64
                for ($page = 1; $page <= $pageCount; $page++) {
                    $tempImagePath = '/borrow/' . $receipt->id . '_' . $page . '.jpg';
                    $borrow_images[] = $tempImagePath;
                    $pdf->selectPage($page)->save(public_path($tempImagePath));
                }
                $cert_images = [];
                // 初始化 PDF 对象
                $pdf = new Pdf(public_path($receipt->cert_rule));
                // 获取总页数
                $pageCount = $pdf->pageCount();
                // 遍历每一页并转换为 Base64
                for ($page = 1; $page <= $pageCount; $page++) {
                    $tempImagePath = '/cert/' . $receipt->id . '_' . $page . '.jpg';
                    $cert_images[] = $tempImagePath;
                    $pdf->selectPage($page)->save(public_path($tempImagePath));
                }
                $clause_images = [];
                // 初始化 PDF 对象
                $pdf = new Pdf(public_path($receipt->clause_rule));
                // 获取总页数
                $pageCount = $pdf->pageCount();
                // 遍历每一页并转换为 Base64
                for ($page = 1; $page <= $pageCount; $page++) {
                    $tempImagePath = '/clause/' . $receipt->id . '_' . $page . '.jpg';
                    $clause_images[] = $tempImagePath;
                    $pdf->selectPage($page)->save(public_path($tempImagePath));
                }
                $receipt->borrow_images = implode(',', $borrow_images);
                $receipt->cert_images = implode(',', $cert_images);
                $receipt->clause_images = implode(',', $clause_images);
                $receipt->save();
            }
            if ($event == 'receipt_expire'){
                $id = $data['id'];
                $receipt = Receipt::find($id);
                if ($receipt->status == 0){
                    $receipt->status = 4;
                    $receipt->cancel_time = Carbon::now();
                    $receipt->save();
                }
            }
            Log::info('队列成功');
        } catch (\Throwable $e) {
            Log::info('队列失败');
            Log::info($e->getMessage());
        }
    }

}
