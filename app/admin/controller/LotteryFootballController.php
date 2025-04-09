<?php

namespace app\admin\controller;

use Carbon\Carbon;
use plugin\admin\app\model\Option;
use support\Request;
use support\Response;
use app\admin\model\LotteryFootball;
use plugin\admin\app\controller\Crud;
use support\exception\BusinessException;

/**
 * 竞彩足球
 */
class LotteryFootballController extends Crud
{

    /**
     * @var LotteryFootball
     */
    protected $model = null;

    /**
     * 构造函数
     * @return void
     */
    public function __construct()
    {
        $this->model = new LotteryFootball;
    }

    /**
     * 浏览
     * @return Response
     */
    public function index(): Response
    {
        return view('lottery-football/index');
    }

    /**
     * 插入
     * @param Request $request
     * @return Response
     * @throws BusinessException
     */
    public function insert(Request $request): Response
    {
        if ($request->method() === 'POST') {
            $images = $request->post('images');
            if (empty($images)) {
                return $this->fail('请上传图片');
            }
            $images = explode(',', $images);
            $name = 'admin_config';
            $config = Option::where('name', $name)->value('value');
            $config = json_decode($config);
            $early_time_range = explode(' - ', $config->early_time); // ["00:00", "06:59"]
            $end_time_range = explode(' - ', $config->end_time); // ["07:00", "23:59"]
            $current_time = Carbon::now();
            $early_time_start = Carbon::parse($current_time->toDateString() . ' ' . $early_time_range[0]);
            $early_time_end = Carbon::parse($current_time->toDateString() . ' ' . $early_time_range[1]);
            $end_time_start = Carbon::parse($current_time->toDateString() . ' ' . $end_time_range[0]);
            $end_time_end = Carbon::parse($current_time->toDateString() . ' ' . $end_time_range[1]);
            if ($current_time->between($early_time_start, $early_time_end)) {
                $type = 1;
            } // 判断当前时间是否在 end_time 范围内
            elseif ($current_time->between($end_time_start, $end_time_end)) {
                $type = 2;
            } else {
                return $this->fail('当前时间不在配置早晚场时间范围');
            }
            foreach ($images as $image) {
                LotteryFootball::create([
                    'image' => $image,
                    'type' => $type,
                ]);
            }
            return $this->json(0, 'ok');
        }
        return view('lottery-football/insert');
    }

    /**
     * 更新
     * @param Request $request
     * @return Response
     * @throws BusinessException
     */
    public function update(Request $request): Response
    {
        if ($request->method() === 'POST') {
            return parent::update($request);
        }
        return view('lottery-football/update');
    }

}
