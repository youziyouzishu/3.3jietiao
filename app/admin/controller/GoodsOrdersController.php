<?php

namespace app\admin\controller;

use Carbon\Carbon;
use support\Request;
use support\Response;
use app\admin\model\GoodsOrders;
use plugin\admin\app\controller\Crud;
use support\exception\BusinessException;

/**
 * 订单管理 
 */
class GoodsOrdersController extends Crud
{
    
    /**
     * @var GoodsOrders
     */
    protected $model = null;

    /**
     * 构造函数
     * @return void
     */
    public function __construct()
    {
        $this->model = new GoodsOrders;
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
        $query = $this->doSelect($where, $field, $order)->with(['user','address'=>function ($query) {
            $query->withTrashed();
        }]);
        return $this->doFormat($query, $format, $limit);
    }
    
    /**
     * 浏览
     * @return Response
     */
    public function index(): Response
    {
        return view('goods-orders/index');
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
        return view('goods-orders/insert');
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
            $status = $request->post('status');
            $id = $request->post('id');
            $order = GoodsOrders::find($id);
            if ($order->status == 1 && $status == 3){
                //发货
                $request->setParams('post',[
                    'delivery_time'=>Carbon::now()
                ]);
            }
            return parent::update($request);
        }
        return view('goods-orders/update');
    }

}
