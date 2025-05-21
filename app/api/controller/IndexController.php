<?php

namespace app\api\controller;

use app\admin\model\Receipt;
use app\api\basic\Base;
use setasign\Fpdi\PdfReader\PdfReaderException;
use setasign\Fpdi\Tfpdf\Fpdi;
use support\Request;
use Webman\RedisQueue\Client;


class IndexController extends Base
{
    protected array $noNeedLogin = ['*'];


    function index(Request $request)
    {
        Client::send('job', ['id' => 54, 'event' => 'generate_pdf']);
    }



}
