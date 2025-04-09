<?php

namespace app\api\controller;

use app\admin\model\Banner;
use app\api\basic\Base;
use plugin\admin\app\model\Option;
use support\Request;

class CommonController extends Base
{
    protected array $noNeedLogin = ['*'];

    #获取轮播图
    function getBannerList(Request $request)
    {
        $rows = Banner::orderByDesc('weigh')->get();
        return $this->success('成功',$rows);
    }

    #获取配置
    function getConfig()
    {
        $name = 'admin_config';
        $config = Option::where('name', $name)->value('value');
        $config = json_decode($config);
        $config->switch = false;
        return $this->success('成功', $config);
    }



}
