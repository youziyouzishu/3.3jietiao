<?php

namespace app\api\controller;

use app\admin\model\Receipt;
use app\api\basic\Base;
use FPDF;
use setasign\Fpdi\Tfpdf\Fpdi;
use Spatie\PdfToImage\Pdf;
use support\Db;
use support\Log;
use support\Request;
use Webman\RedisQueue\Client;


class IndexController extends Base
{
    protected array $noNeedLogin = ['*'];

    public function index(Request $request)
    {
        return $this->success('hello world');
    }



}
