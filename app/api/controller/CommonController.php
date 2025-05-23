<?php

namespace app\api\controller;

use app\admin\model\Banner;
use app\api\basic\Base;
use plugin\admin\app\model\Dict;
use plugin\admin\app\model\Option;
use support\Request;
use support\Response;
use TencentCloud\Common\Credential;
use TencentCloud\Common\Profile\ClientProfile;
use TencentCloud\Common\Profile\HttpProfile;
use TencentCloud\Faceid\V20180301\FaceidClient;
use TencentCloud\Faceid\V20180301\Models\GetEidTokenRequest;

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
        return $this->success('成功', $config);
    }

    function getEidToken(Request $request)
    {
        $cred = new Credential('AKIDoVGvRlurcAqTXSBj5FDzZyEKH6kCVijY', 'gTF043sX1JPKl6NZaP2a1JXo5OdhbKrC');
        $httpProfile = new HttpProfile();
        $httpProfile->setEndpoint('faceid.tencentcloudapi.com');
        $clientProfile = new ClientProfile();
        $clientProfile->setHttpProfile($httpProfile);
        $client = new FaceidClient($cred, '', $clientProfile);
        $req = new GetEidTokenRequest();
        $params = [
            'MerchantId' => '00EI2504070903535929',
            'Config'=>[
                'InputType'=>'3',
            ]
        ];
        $req->fromJsonString(json_encode($params));
        $resp = $client->GetEidToken($req);
        return $this->success('成功',$resp);
    }


    public function getDict(Request $request): Response
    {
        $name = $request->post('name');
        return $this->json(0, 'ok', (array)Dict::get($name));
    }



}
