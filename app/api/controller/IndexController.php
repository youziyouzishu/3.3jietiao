<?php

namespace app\api\controller;

use app\admin\model\Receipt;
use app\api\basic\Base;
use FPDF;
use setasign\Fpdi\Tfpdf\Fpdi;
use Spatie\PdfToImage\Pdf;
use support\Log;
use support\Request;
use Webman\RedisQueue\Client;


class IndexController extends Base
{
    protected array $noNeedLogin = ['*'];



}
