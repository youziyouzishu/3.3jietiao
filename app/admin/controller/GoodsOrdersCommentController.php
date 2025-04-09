<?php

namespace app\admin\controller;

use support\Request;
use support\Response;
use app\admin\model\GoodsOrdersComment;
use plugin\admin\app\controller\Crud;
use support\exception\BusinessException;

/**
 * 订单评价列表 
 */
class GoodsOrdersCommentController extends Crud
{
    
    /**
     * @var GoodsOrdersComment
     */
    protected $model = null;

    /**
     * 构造函数
     * @return void
     */
    public function __construct()
    {
        $this->model = new GoodsOrdersComment;
    }
    
    /**
     * 浏览
     * @return Response
     */
    public function index(): Response
    {
        return view('goods-orders-comment/index');
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
        return view('goods-orders-comment/insert');
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
        return view('goods-orders-comment/update');
    }

}
