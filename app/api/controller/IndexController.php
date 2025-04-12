<?php

namespace app\api\controller;

use app\admin\model\User;
use app\api\basic\Base;
use support\Request;
use Workerman\Connection\AsyncTcpConnection;

class IndexController extends Base
{
    protected array $noNeedLogin = ['*'];

    public static $connection;
    public function index(Request $request)
    {

    }

}
