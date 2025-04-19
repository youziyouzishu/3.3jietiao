<?php

namespace app\api\controller;

use app\admin\model\Advice;
use app\admin\model\EidToken;
use app\admin\model\Sms;
use app\admin\model\User;
use app\admin\model\UsersScoreLog;
use app\admin\model\UsersWithdraw;
use app\api\basic\Base;
use app\api\service\Pay;
use Carbon\Carbon;
use EasyWeChat\OpenPlatform\Application;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Str;
use plugin\admin\app\common\Util;
use plugin\admin\app\model\Option;
use support\Request;
use TencentCloud\Common\Credential;
use TencentCloud\Common\Profile\ClientProfile;
use TencentCloud\Common\Profile\HttpProfile;
use TencentCloud\Faceid\V20180301\FaceidClient;
use TencentCloud\Faceid\V20180301\Models\GetEidTokenRequest;

class UserController extends Base
{

    
    #获取个人信息
    function getUserInfo(Request $request)
    {
        $user_id = $request->post('user_id');
        if (!empty($user_id)) {
            $request->user_id = $user_id;
        }
        $row = User::with(['feedback'])->find($request->user_id);
        return $this->success('成功', $row);
    }

    #编辑个人信息
    function editUserInfo(Request $request)
    {
        $data = $request->post();
        $row = User::find($request->user_id);
        if (!$row) {
            return $this->fail('用户不存在');
        }

        $userAttributes = $row->getAttributes();
        foreach ($data as $key => $value) {
            if (array_key_exists($key, $userAttributes) && (!empty($value) || $value === 0)) {
                $row->setAttribute($key, $value);
            }
        }
        $row->save();
        return $this->success('成功');
    }

    /**
     * 提交满意度调查
     * @param Request $request
     * @return \support\Response
     */
    function setFeedback(Request $request)
    {
       $field1 = $request->post('field1');
       $field2 = $request->post('field2');
       $field3 = $request->post('field3');
       $field4 = $request->post('field4');
       $field5 = $request->post('field5');
       $field6 = $request->post('field6');
       $row = User::find($request->user_id);
       $row->feedback()->create([
           'field1' => $field1,
           'field2' => $field2,
           'field3' => $field3,
           'field4' => $field4,
           'field5' => $field5,
           'field6' => $field6,
       ]);
       return $this->success('成功');
    }






    #获取账变记录
    function getMoneyList(Request $request)
    {
        $type = $request->post('type');#money = 余额
        $date = $request->post('date');
        $status = $request->post('status'); #0=全部 1=支出，2=收入
        $date = Carbon::parse($date);
        // 提取年份和月份
        $year = $date->year;
        $month = $date->month;
        $rows = UsersScoreLog::where(['type' => $type])
            ->when(!empty($status), function (Builder $query) use ($status) {
                if ($status == 1) {
                    $query->where('score', '<', 0);
                } else {
                    $query->where('score', '>', 0);
                }
            })
            ->whereYear('created_at', $year)
            ->whereMonth('created_at', $month)
            ->where('user_id', $request->user_id)
            ->orderByDesc('id')
            ->paginate()
            ->getCollection()
            ->each(function (UsersScoreLog $item) {
                if ($item->score > 0) {
                    $item->score = '+' . $item->score;
                }
            });
        return $this->success('获取成功', $rows);
    }

    #提现
    function doWithdraw(Request $request)
    {
        $withdraw_amount = $request->post('withdraw_amount');
        $user = User::find($request->user_id);
        if ($user->vip_status != 1) {
            return $this->fail('非会员不能提现');
        }
        if (empty($user->openid)) {
            return $this->fail('请先绑定微信');
        }

        if ($user->money < $withdraw_amount) {
            return $this->fail('余额不足');
        }
        $chance_rate = 0.06;
        $chance_amount = $withdraw_amount * $chance_rate;
        $into_amount = $withdraw_amount - $chance_amount;
        User::score(-$withdraw_amount, $request->user_id, '用户提现', 'money');
        UsersWithdraw::create([
            'user_id' => $request->user_id,
            'withdraw_amount' => $withdraw_amount,
            'chance_amount' => $chance_amount,
            'into_amount' => $into_amount,
            'chance_rate' => $chance_rate,
        ]);
        return $this->success('提交成功');
    }

    #获取提现记录
    function getWithdrawList(Request $request)
    {
        $rows = UsersWithdraw::where('user_id', $request->user_id)
            ->orderByDesc('id')
            ->paginate()
            ->items();
        return $this->success('获取成功', $rows);
    }

    #绑定微信
    function bindWechat(Request $request)
    {
        $code = $request->post('code');
        $config = config('wechat');
        $app = new Application($config);
        $oauth = $app->getOauth();
        try {
            $response = $oauth->userFromCode($code);
        } catch (\Throwable $e) {
            return $this->fail($e->getMessage());
        }
        $user = User::find($request->user_id);
        $user->openid = $response->getId();
        $user->save();
        return $this->success('绑定成功');
    }

    #获取邀请海报
    function getPoster(Request $request)
    {
        $user = User::find($request->user_id);

        $writer = new PngWriter();
        $qrCode = new QrCode(
            data: 'https://zhying.top/register/register.html#/?invitecode=' . $user->invitecode,
            encoding: new Encoding('UTF-8'),
            errorCorrectionLevel: ErrorCorrectionLevel::Low,
            size: 100,
            margin: 10,
            roundBlockSizeMode: RoundBlockSizeMode::Margin,
            foregroundColor: new Color(0, 0, 0),
            backgroundColor: new Color(255, 255, 255)
        );
        $base64 = $writer->write($qrCode)->getDataUri();

        return $this->success('获取成功', [
            'base64' => $base64,
        ]);
    }

    #我的团队
    function getTeamList(Request $request)
    {
        $user = User::find($request->user_id);
        $today = Carbon::today();
        // 本周直推人数
        $startOfWeek = Carbon::now()->startOfWeek();
        $endOfWeek = Carbon::now()->endOfWeek();
        // 本月直推人数
        $startOfMonth = Carbon::now()->startOfMonth();
        $endOfMonth = Carbon::now()->endOfMonth();

        $total_direct = UsersLayer::where('parent_id', $user->id)->where('layer', 1)->count();#直推总人数
        $total_indirect = UsersLayer::where('parent_id', $user->id)->where('layer', '>', 1)->count();#间推总人数


        $today_direct = UsersLayer::where('parent_id', $user->id)->whereDate('created_at', $today)->where('layer', 1)->count();
        $today_indirect = UsersLayer::where('parent_id', $user->id)->whereDate('created_at', $today)->where('layer', '>', 1)->count();

        $week_direct = UsersLayer::where('parent_id', $user->id)->whereBetween('created_at', [$startOfWeek, $endOfWeek])->where('layer', 1)->count();
        $week_indirect = UsersLayer::where('parent_id', $user->id)->whereBetween('created_at', [$startOfWeek, $endOfWeek])->where('layer', '>', 1)->count();

        $month_direct = UsersLayer::where('parent_id', $user->id)->whereBetween('created_at', [$startOfMonth, $endOfMonth])->where('layer', 1)->count();
        $month_indirect = UsersLayer::where('parent_id', $user->id)->whereBetween('created_at', [$startOfMonth, $endOfMonth])->where('layer', '>', 1)->count();

        return $this->success('获取成功', [
            'total_team' => $total_direct + $total_direct,
            'total_direct' => $total_direct,
            'total_indirect' => $total_indirect,
            'today_direct' => $today_direct,
            'today_indirect' => $today_indirect,
            'week_direct' => $week_direct,
            'week_indirect' => $week_indirect,
            'month_direct' => $month_direct,
            'month_indirect' => $month_indirect,
        ]);
    }

    #申请成为店长
    function applyShoper(Request $request)
    {
        $image = $request->post('image');
        $name = $request->post('name');
        $wechat = $request->post('wechat');
        $address = $request->post('address');

        $user = User::find($request->user_id);
        if ($user->vip_status != 1) {
            return $this->fail('会员功能，请充值会员');
        }
        $count = UsersLayer::where(['parent_id' => $request->user_id])->whereHas('user', function ($query) {
            $query->whereHas('vipOrders', function ($query) {
                $query->where('status', 1);
            })->where('vip_expire_time', '>', Carbon::now());
        })->count();


        $configname = 'admin_config';
        $config = Option::where('name', $configname)->value('value');
        $config = json_decode($config);
        $shop_require = $config->shop_require;
        if ($count < $shop_require) {
            return $this->fail('未满足条件');
        }

        $row = UsersShoper::where(['user_id' => $request->user_id])->where('status', 0)->exists();
        if ($row) {
            return $this->fail('您已经提交过申请，请耐心等待审核');
        }
        $row = UsersShoper::where(['user_id' => $request->user_id])->where('status', 1)->exists();
        if ($row) {
            return $this->fail('您已经申请过，请勿重复申请');
        }

        UsersShoper::create([
            'address' => $address,
            'user_id' => $request->user_id,
            'image' => $image,
            'name' => $name,
            'wechat' => $wechat,
        ]);
        return $this->success('申请成功，请耐心等待审核');
    }

    #修改手机号
    function changeMobile(Request $request)
    {
        $old_mobile = $request->post('old_mobile');
        $old_captcha = $request->post('old_captcha');
        $new_mobile = $request->post('new_mobile');
        $new_captcha = $request->post('new_captcha');

        $ret = Sms::check($old_mobile, $old_captcha, 'checkmobile');
        if (!$ret) {
            return $this->fail('验证码错误');
        }
        $ret = Sms::check($new_mobile, $new_captcha, 'changemobile');
        if (!$ret) {
            return $this->fail('验证码错误');
        }
        $thisuser = User::find($request->user_id);
        $user = User::where(['mobile' => $old_mobile])->first();
        if (!$user || $user->id != $thisuser->id) {
            return $this->fail('号码与当前用户不一致');
        }
        $user->mobile = $new_mobile;
        $user->username = $new_mobile;
        $user->save();
        return $this->success('修改成功');
    }


    function changePassword(Request $request)
    {
        $old_password = $request->post('old_password');
        $new_password = $request->post('new_password');
        $confirm_password = $request->post('confirm_password');
        if (strlen($new_password) != 6) {
            return $this->fail('交易密码长度必须是6位');
        }
        if ($new_password != $confirm_password) {
            return $this->fail('两次密码不一致');
        }
        $user = User::find($request->user_id);
        if (!Util::passwordVerify($old_password, $user->trade_password)) {
            return $this->fail('原密码错误');
        }
        $user->trade_password = Util::passwordHash($new_password);
        $user->save();
        return $this->success('修改成功');
    }

    function adviceAdd(Request $request)
    {
        $class_name = $request->post('class_name');
        $content = $request->post('content');
        $images = $request->post('images');
        $truename = $request->post('truename');
        $mobile = $request->post('mobile');

        Advice::create([
            'class_name' => $class_name,
            'content' => $content,
            'user_id' => $request->user_id,
            'images' => $images,
            'truename' => $truename,
            'mobile' => $mobile,
        ]);
        return $this->success('反馈成功');
    }

    function getAdviceList(Request $request)
    {
        $rows = Advice::where('user_id', $request->user_id)->latest()->paginate()->items();
        return $this->success('获取成功', $rows);
    }


}
