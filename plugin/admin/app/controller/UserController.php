<?php

namespace plugin\admin\app\controller;

use Carbon\Carbon;
use plugin\admin\app\common\Util;
use plugin\admin\app\model\User;
use support\exception\BusinessException;
use support\Request;
use support\Response;
use Throwable;

/**
 * 用户管理 
 */
class UserController extends Crud
{
    
    /**
     * @var User
     */
    protected $model = null;

    /**
     * 构造函数
     * @return void
     */
    public function __construct()
    {
        $this->model = new User;
    }

    /**
     * 浏览
     * @return Response
     * @throws Throwable
     */
    public function index(): Response
    {
        return raw_view('user/index');
    }

    /**
     * 插入
     * @param Request $request
     * @return Response
     * @throws BusinessException|Throwable
     */
    public function insert(Request $request): Response
    {
        if ($request->method() === 'POST') {
            $truename = $request->input('truename');
            $trade_password = $request->input('trade_password');
            $idcard = $request->input('idcard');
            if (empty($truename)) {
                return $this->fail('请填写真实姓名');
            }
            if (empty($idcard)) {
                return $this->fail('请填写身份证号');
            }
            if (empty($trade_password)) {
                return $this->fail('请填写交易密码');
            }
            if (User::where('idcard', $idcard)->exists()) {
                return $this->fail('身份证号已存在');
            }
            $request->setParams('post',[
                'trade_password' => Util::passwordHash($trade_password),
                'join_time' => Carbon::now(),
                'join_ip' => $request->getRealIp(),
                'last_time' => Carbon::now(),
                'last_ip' => $request->getRealIp(),
            ]);
            return parent::insert($request);
        }
        return raw_view('user/insert');
    }

    /**
     * 更新
     * @param Request $request
     * @return Response
     * @throws BusinessException|Throwable
     */
    public function update(Request $request): Response
    {
        if ($request->method() === 'POST') {
            return parent::update($request);
        }
        return raw_view('user/update');
    }

}
