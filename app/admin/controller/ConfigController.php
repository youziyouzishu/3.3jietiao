<?php

namespace app\admin\controller;

use plugin\admin\app\common\Util;
use plugin\admin\app\model\Option;
use support\Request;
use support\Response;
use plugin\admin\app\controller\Crud;
use support\exception\BusinessException;

/**
 * 系统配置
 */
class ConfigController extends Crud
{

    /**
     * 浏览
     * @return Response
     */
    public function index(): Response
    {
        return view('config/index');
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
            return parent::insert($request);
        }
        return view('config/insert');
    }

    /**
     * 更改
     * @param Request $request
     * @return Response
     * @throws BusinessException
     */
    public function update(Request $request): Response
    {
        $post = $request->post();
        $data['user_agreement'] = $post['user_agreement'] ?? '';
        $data['privacy_policy'] = $post['privacy_policy'] ?? '';
        $data['lengthen_rule'] = $post['lengthen_rule'] ?? '';
        $data['kefu_qr'] = $post['kefu_qr'] ?? '';
        $data['app_name'] = $post['app_name'] ?? '';
        $data['app_year'] = $post['app_year'] ?? '';
        $data['user_count'] = $post['user_count'] ?? '';
        $data['receipt_count'] = $post['receipt_count'] ?? '';
        $name = 'admin_config';
        Option::where('name', $name)->update([
            'value' => json_encode($data)
        ]);
        return $this->json(0);
    }

    /**
     * 获取配置
     * @return Response
     */
    public function get(): Response
    {
        $name = 'admin_config';
        $config = Option::where('name', $name)->value('value');
        if ($config === null){
            $config = Option::insert([
                'name'=>$name,
                'value' => ''
            ]);
        }
        $config = json_decode($config,true) ?: [];

        return $this->success('成功', $config);
    }




}
