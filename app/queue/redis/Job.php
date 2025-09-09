<?php

namespace app\queue\redis;

use app\admin\model\Receipt;
use Carbon\Carbon;
use setasign\Fpdi\PdfParser\CrossReference\CrossReferenceException;
use setasign\Fpdi\PdfParser\Filter\FilterException;
use setasign\Fpdi\PdfParser\PdfParserException;
use setasign\Fpdi\PdfParser\Type\PdfTypeException;
use setasign\Fpdi\PdfReader\PdfReaderException;
use setasign\Fpdi\Tfpdf\Fpdi;
use Spatie\PdfToImage\Exceptions\PdfDoesNotExist;
use Spatie\PdfToImage\Pdf;
use support\Db;
use support\Log;
use Webman\RedisQueue\Consumer;

class Job implements Consumer
{
    // 要消费的队列名
    public $queue = 'job';

    // 连接名，对应 plugin/webman/redis-queue/redis.php 里的连接`
    public $connection = 'default';

    // 消费

    /**
     * @throws PdfDoesNotExist
     * @throws PdfTypeException
     * @throws CrossReferenceException
     * @throws \Throwable
     * @throws PdfReaderException
     * @throws PdfParserException
     * @throws FilterException
     */
    public function consume($data)
    {
        try {

            // 重新连接数据库，防止常驻进程断线
            $this->reconnectDatabase();

            $event = $data['event'] ?? '';
            $id = $data['id'] ?? null;

            if (!$id) {
                Log::warning('队列任务缺少 ID', $data);
                return false;
            }

            $receipt = Receipt::find($id);
            if (!$receipt) {
                Log::warning("找不到 Receipt ID: {$id}");
                return false;
            }

            switch ($event) {
                case 'generate_pdf':
                    $this->handleGeneratePdf($receipt);
                    break;

                case 'receipt_expire':
                    $this->handleReceiptExpire($receipt);
                    break;

                default:
                    Log::warning("未知队列事件: {$event}", $data);
            }

            Log::info('队列任务成功', ['event' => $event, 'id' => $id]);

        } catch (\Throwable $e) {
            Log::error('队列任务失败', [
                'data' => $data,
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            throw $e; // 可以继续抛给 Workerman 队列处理失败逻辑
        }
    }

    /**
     * 重新连接数据库（Webman Eloquent 直接用 DB ）
     */
    protected function reconnectDatabase()
    {

            try {
                Db::connection('plugin.admin.mysql')->reconnect();
            } catch (\Throwable $e) {
                Log::error("重连数据库失败：{'plugin.admin.mysql'}", ['error' => $e->getMessage()]);
            }

    }

    /**
     * 生成 PDF 和图片
     */
    protected function handleGeneratePdf(Receipt $receipt)
    {
        // 生成借款协议 PDF
        $borrowPdfPath = public_path("/borrow/{$receipt->id}.pdf");
        $this->generatePdfFromTemplate(public_path('借款协议.pdf'), $borrowPdfPath, $receipt);

        // 生成授权确认书 PDF
        $certPdfPath = public_path("/cert/{$receipt->id}.pdf");
        $this->generatePdfFromTemplate(public_path('授权确认书.pdf'), $certPdfPath, $receipt);

        // 设置 PDF 路径到模型
        $receipt->borrow_rule = "/borrow/{$receipt->id}.pdf";
        $receipt->cert_rule = "/cert/{$receipt->id}.pdf";
        $receipt->clause_rule = '/出借人重要条款提示.pdf';

        // 转换 PDF 为图片
        $receipt->borrow_images = implode(',', $this->convertPdfToImages($borrowPdfPath, '/borrow/', $receipt->id));
        $receipt->cert_images = implode(',', $this->convertPdfToImages($certPdfPath, '/cert/', $receipt->id));
        $receipt->clause_images = implode(',', $this->convertPdfToImages(public_path($receipt->clause_rule), '/clause/', $receipt->id));

        $receipt->save();
    }

    /**
     * 处理过期 Receipt
     */
    protected function handleReceiptExpire(Receipt $receipt)
    {
        if ($receipt->status == 0) {
            $receipt->status = 4;
            $receipt->cancel_time = Carbon::now();
            $receipt->save();
        }
    }

    /**
     * 通用 PDF 生成方法
     */
    protected function generatePdfFromTemplate(string $templatePath, string $outputPath, Receipt $receipt)
    {
        $pdf = new Fpdi();
        $pageCount = $pdf->setSourceFile($templatePath);

        for ($i = 1; $i <= $pageCount; $i++) {
            $templateId = $pdf->importPage($i);
            $pdf->AddPage();
            $pdf->useTemplate($templateId);
            $pdf->AddFont('SimHei', '', 'SimHei.ttf', true);
            $pdf->SetFont('SimHei');
            $pdf->SetFontSize(10);

            // 根据页码写入文本（可自定义）
            if ($i == 1) {
                $pdf->Text(56, 45.5, $receipt->ordersn ?? '');
                $pdf->Text(56, 78.3, $receipt->toUser->truename ?? '');
                $pdf->Text(56, 83.7, $receipt->toUser->idcard ?? '');
                $pdf->Text(56, 89, $receipt->user->truename ?? '');
                $pdf->Text(56, 94.7, $receipt->user->idcard ?? '');
                $pdf->Text(56, 134, $receipt->amount ?? '');
                $pdf->Text(56, 140, $receipt->rate ?? '');
                $pdf->Text(56, 147, $receipt->interest ?? '');
                $pdf->Text(56, 153.5, $receipt->amount_and_interest ?? '');
                $pdf->Text(56, 160, $receipt->outstanding_amount ?? '');
                $pdf->Text(56, 173.5, $receipt->start_date->toDateString() ?? '');
                $pdf->Text(56, 180, $receipt->end_date->toDateString() ?? '');
                $pdf->Text(56, 186.5, $receipt->reason ?? '');
            }

            if ($i == 4) {
                $pdf->Text(56, 167, $receipt->toUser->truename ?? '');
                $pdf->Text(56, 172, $receipt->user->truename ?? '');
                $pdf->Text(56, 178, date('Y-m-d'));
            }
        }

        $pdf->Output($outputPath, 'F'); // 保存文件
    }

    /**
     * PDF 转图片
     */
    protected function convertPdfToImages(string $pdfPath, string $dir, $id)
    {
        $images = [];
        $pdf = new Pdf($pdfPath);
        $pageCount = $pdf->pageCount();

        for ($page = 1; $page <= $pageCount; $page++) {
            $tempImagePath = "{$dir}{$id}_{$page}.jpg";
            $images[] = $tempImagePath;
            $pdf->selectPage($page)->save(public_path($tempImagePath));
        }

        return $images;
    }

}
