<?php

namespace app\admin\controller;

use support\Request;
use support\Response;
use app\admin\model\GoodsOrdersSubs;
use plugin\admin\app\controller\Crud;
use support\exception\BusinessException;

/**
 * 子订单列表 
 */
class GoodsOrdersSubsController extends Crud
{
    
    /**
     * @var GoodsOrdersSubs
     */
    protected $model = null;

    /**
     * 构造函数
     * @return void
     */
    public function __construct()
    {
        $this->model = new GoodsOrdersSubs;
    }

    /**
     * 查询
     * @param Request $request
     * @return Response
     * @throws BusinessException
     */
    public function select(Request $request): Response
    {
        [$where, $format, $limit, $field, $order] = $this->selectInput($request);
        $query = $this->doSelect($where, $field, $order)->with(['goods']);
        return $this->doFormat($query, $format, $limit);
    }
    
    /**
     * 浏览
     * @return Response
     */
    public function index(): Response
    {
        return view('goods-orders-subs/index');
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
        return view('goods-orders-subs/insert');
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
        return view('goods-orders-subs/update');
    }

}
