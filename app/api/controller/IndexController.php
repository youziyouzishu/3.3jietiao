<?php

namespace app\api\controller;

use app\admin\model\Receipt;
use app\api\basic\Base;


use setasign\Fpdi\PdfReader\PdfReaderException;
use setasign\Fpdi\Tfpdf\Fpdi;

use Spatie\PdfToImage\Pdf;
use support\Request;

class IndexController extends Base
{
    protected array $noNeedLogin = ['*'];


    function index(Request $request)
    {
        $id = 13;
        $receipt = Receipt::with(['user','toUser'])->find($id);


        // PDF 文件路径
        $pdfFile = public_path($receipt->borrow_rule);
        // 初始化 PDF 对象
        $pdf = new Pdf($pdfFile);
        // 获取总页数
        $pageCount = $pdf->pageCount();
        $base64Images = [];
        // 遍历每一页并转换为 Base64
        for ($page = 1; $page <= $pageCount; $page++) {
            // 创建临时文件路径
            $tempImagePath = tempnam(sys_get_temp_dir(), 'pdf_image') . '.jpg';
            try {
                // 将当前页保存为图片
                $pdf->selectPage($page)->save($tempImagePath);
                // 读取图片内容并编码为 Base64
                $imageData = file_get_contents($tempImagePath);
                $base64Images[] = 'data:image/jpeg;base64,'.base64_encode($imageData);
            } finally {
                // 删除临时文件
                if (file_exists($tempImagePath)) {
                    unlink($tempImagePath);
                }
            }
        }
        $receipt->setAttribute('borrow_rule_images',$base64Images);

        $pdfFile = public_path($receipt->cert_rule);
        // 初始化 PDF 对象
        $pdf = new Pdf($pdfFile);
        // 获取总页数
        $pageCount = $pdf->pageCount();
        $base64Images = [];
        // 遍历每一页并转换为 Base64
        for ($page = 1; $page <= $pageCount; $page++) {
            // 创建临时文件路径
            $tempImagePath = tempnam(sys_get_temp_dir(), 'pdf_image') . '.jpg';
            try {
                // 将当前页保存为图片
                $pdf->selectPage($page)->save($tempImagePath);
                // 读取图片内容并编码为 Base64
                $imageData = file_get_contents($tempImagePath);
                $base64Images[] = 'data:image/jpeg;base64,'.base64_encode($imageData);
            } finally {
                // 删除临时文件
                if (file_exists($tempImagePath)) {
                    unlink($tempImagePath);
                }
            }
        }
        $receipt->setAttribute('cert_rule_images',$base64Images);
        $pdfFile = public_path($receipt->clause_rule);
        // 初始化 PDF 对象
        $pdf = new Pdf($pdfFile);
        // 获取总页数
        $pageCount = $pdf->pageCount();
        $base64Images = [];
        // 遍历每一页并转换为 Base64
        for ($page = 1; $page <= $pageCount; $page++) {
            // 创建临时文件路径
            $tempImagePath = tempnam(sys_get_temp_dir(), 'pdf_image') . '.jpg';
            try {
                // 将当前页保存为图片
                $pdf->selectPage($page)->save($tempImagePath);
                // 读取图片内容并编码为 Base64
                $imageData = file_get_contents($tempImagePath);
                $base64Images[] = 'data:image/jpeg;base64,'.base64_encode($imageData);
            } finally {
                // 删除临时文件
                if (file_exists($tempImagePath)) {
                    unlink($tempImagePath);
                }
            }
        }
        $receipt->setAttribute('clause_rule_images',$base64Images);
        if (empty($receipt)) {
            return $this->fail('凭证不存在');
        }
        return $this->success('获取成功', $receipt);
    }

    function shouquan(Request $request)
    {
        try {
            $receipt = Receipt::find(1);
            // 初始化 FPDI
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
            $pdf->Output(public_path('/edited.pdf'), 'F'); // 保存为文件
//            $pdf->Output('I', 'edited.pdf'); // 直接输出到浏览器

            echo 'PDF edited successfully!';
        } catch (PdfReaderException $e) {
            echo 'Error: ' . $e->getMessage();
        }
    }
    public function jiekuan(Request $request)
    {
        try {

            $receipt = Receipt::find(1);
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
            $pdf->Output(public_path('/edited.pdf'), 'F'); // 保存为文件
//            $pdf->Output('I', 'edited.pdf'); // 直接输出到浏览器

            echo 'PDF edited successfully!';
        } catch (PdfReaderException $e) {
            echo 'Error: ' . $e->getMessage();
        }

    }

}
