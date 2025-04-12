<?php

namespace app\api\controller;

use app\admin\model\EidToken;
use app\admin\model\Sms;
use app\admin\model\User;
use app\api\basic\Base;
use Carbon\Carbon;
use EasyWeChat\MiniApp\Application;
use plugin\admin\app\common\Util;
use support\Log;
use support\Request;
use TencentCloud\Common\Credential;
use TencentCloud\Common\Profile\ClientProfile;
use TencentCloud\Common\Profile\HttpProfile;
use TencentCloud\Faceid\V20180301\FaceidClient;
use TencentCloud\Faceid\V20180301\Models\GetEidResultRequest;
use Tinywan\Jwt\JwtToken;

class AccountController extends Base
{

    protected array $noNeedLogin = ['login', 'register', 'changePassword', 'refreshToken'];

    function login(Request $request)
    {
        $truename = $request->post('truename');
        $idcard = $request->post('idcard');
        $trade_password = $request->post('trade_password');

        $user = User::where('idcard', $idcard)->where('truename', $truename)->first();
        if (!$user){
            return $this->fail('用户不存在');
        }
        if (!Util::passwordVerify($trade_password, $user->trade_password)) {
            return $this->fail('交易密码错误');
        }

        $user->last_time = Carbon::now()->toDateTimeString();
        $user->last_ip = $request->getRealIp();
        $user->save();

        $token = JwtToken::generateToken([
            'id' => $user->id,
            'client' => JwtToken::TOKEN_CLIENT_MOBILE,
            'openid' => $user->openid,
        ]);
        return $this->success('登录成功', ['user' => $user, 'token' => $token]);
    }

    function register(Request $request)
    {
        $truename = $request->post('truename');
        $idcard = $request->post('idcard');
        $trade_password = $request->post('trade_password');
        $confirm_trade_password = $request->post('confirm_trade_password');
        $code = $request->post('code');
        $eid_token = $request->post('eid_token');

        if ($trade_password != $confirm_trade_password) {
            return $this->fail('两次交易密码不一致');
        }
        if (strlen($trade_password) != 6) {
            return $this->fail('交易密码长度必须是6位');
        }

        $exists = User::where('idcard', $idcard)->first();
        if ($exists) {
            return $this->fail('用户已存在');
        }


        $eidToken = EidToken::where('token',$eid_token)->first();
        if(!$eidToken){
            return $this->fail('人脸核身Token不存在');
        }
        if ($eidToken->truename !== $truename || $eidToken->idcard !== $idcard){
            return $this->fail('身份与核验信息不符');
        }

        $cred = new Credential('AKIDoVGvRlurcAqTXSBj5FDzZyEKH6kCVijY', 'gTF043sX1JPKl6NZaP2a1JXo5OdhbKrC');
        $httpProfile = new HttpProfile();
        $httpProfile->setEndpoint('faceid.tencentcloudapi.com');
        $clientProfile = new ClientProfile();
        $clientProfile->setHttpProfile($httpProfile);
        $client = new FaceidClient($cred, '', $clientProfile);
        $req = new GetEidResultRequest();
        $params = ['EidToken' => $eid_token];
        $req->fromJsonString(json_encode($params));
        $resp = $client->GetEidResult($req);
        $ErrCode = $resp->Text->ErrCode;
        if($ErrCode !== 0){
            return $this->fail('人脸核身失败');
        }


        $config = config('wechat.UserMiniApp');
        $app = new Application($config);
        $ret = $app->getUtils()->codeToSession($code);
        $openid = $ret['openid'];

        $user = User::create([
            'nickname' => '用户' . mt_rand(100000, 999999),
            'avatar' => '/app/admin/avatar.png',
            'join_time' => Carbon::now(),
            'join_ip' => $request->getRealIp(),
            'last_time' => Carbon::now(),
            'last_ip' => $request->getRealIp(),
            'trade_password' => Util::passwordHash($trade_password),
            'truename' => $truename,
            'idcard' => $idcard,
            'openid' => $openid,
        ]);

        $token = JwtToken::generateToken([
            'id' => $user->id,
            'client' => JwtToken::TOKEN_CLIENT_MOBILE,
            'openid' => $user->openid,
        ]);
        return $this->success('注册成功', ['user' => $user, 'token' => $token]);
    }

    #更改密码
    function changePassword(Request $request)
    {
        $truename = $request->post('truename');
        $idcard = $request->post('idcard');
        $trade_password = $request->post('trade_password');
        $confirm_trade_password = $request->post('confirm_trade_password');
        $eid_token = $request->post('eid_token');
        if ($trade_password != $confirm_trade_password) {
            return $this->fail('两次交易密码不一致');
        }
        if (strlen($trade_password) != 6) {
            return $this->fail('交易密码长度必须是6位');
        }

        $user = User::where('idcard', $idcard)->where('truename', $truename)->first();
        if (!$user) {
            return $this->fail('用户不存在');
        }

        $eidToken = EidToken::where('token',$eid_token)->first();
        if(!$eidToken){
            return $this->fail('人脸核身Token不存在');
        }
        if ($eidToken->truename !== $truename || $eidToken->idcard !== $idcard){
            return $this->fail('身份与核验信息不符');
        }

        $cred = new Credential('AKIDoVGvRlurcAqTXSBj5FDzZyEKH6kCVijY', 'gTF043sX1JPKl6NZaP2a1JXo5OdhbKrC');
        $httpProfile = new HttpProfile();
        $httpProfile->setEndpoint('faceid.tencentcloudapi.com');
        $clientProfile = new ClientProfile();
        $clientProfile->setHttpProfile($httpProfile);
        $client = new FaceidClient($cred, '', $clientProfile);
        $req = new GetEidResultRequest();
        $params = ['EidToken' => $eid_token];
        $req->fromJsonString(json_encode($params));
        $resp = $client->GetEidResult($req);
        $ErrCode = $resp->Text->ErrCode;
        if($ErrCode !== 0){
            return $this->fail('人脸核身失败');
        }

        $user->trade_password = Util::passwordHash($trade_password);
        $user->save();
        return $this->success('修改成功');
    }


    function refreshToken(Request $request)
    {
        $res = JwtToken::refreshToken();
        return $this->success('刷新成功', $res);
    }
}
