<?php

namespace app\api\controller;

use app\admin\model\Banner;
use app\admin\model\EidToken;
use app\api\basic\Base;
use plugin\admin\app\model\Option;
use support\Request;
use TencentCloud\Common\Credential;
use TencentCloud\Common\Profile\ClientProfile;
use TencentCloud\Common\Profile\HttpProfile;
use TencentCloud\Faceid\V20180301\FaceidClient;
use TencentCloud\Faceid\V20180301\Models\GetEidResultRequest;
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
        $truename = $request->input('truename');
        $idcard = $request->input('idcard');
        $cred = new Credential('AKIDoVGvRlurcAqTXSBj5FDzZyEKH6kCVijY', 'gTF043sX1JPKl6NZaP2a1JXo5OdhbKrC');
        $httpProfile = new HttpProfile();
        $httpProfile->setEndpoint('faceid.tencentcloudapi.com');
        $clientProfile = new ClientProfile();
        $clientProfile->setHttpProfile($httpProfile);
        $client = new FaceidClient($cred, '', $clientProfile);
        $req = new GetEidTokenRequest();
        $params = [
            'MerchantId' => '00EI2504070903535929',
            'IdCard' => $idcard,
            'Name' => $truename,
            'Config'=>[
                'InputType'=>'3',
            ]
        ];
        $req->fromJsonString(json_encode($params));
        $resp = $client->GetEidToken($req);

        EidToken::create([
            'token'=>$resp->getEidToken(),
            'truename'=>$truename,
            'idcard'=>$idcard,
        ]);
        return $this->success('成功',$resp);
    }



}
