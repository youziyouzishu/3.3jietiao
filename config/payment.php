<?php

use Yansongda\Pay\Pay;

return [
    'alipay' => [
        'default' => [
            // 必填-支付宝分配的 app_id
            'app_id' => '2021005110663521',
            // 必填-应用私钥 字符串或路径
            // 在 https://open.alipay.com/develop/manage 《应用详情->开发设置->接口加签方式》中设置
            'app_secret_cert' => 'MIIEvgIBADANBgkqhkiG9w0BAQEFAASCBKgwggSkAgEAAoIBAQCP+HF/kkO3GAf5F91LZSKKRpczIQCrxmjmv95/VU+5GrwR5fFC61Ypd0dv872W/c3yOnEI/48yqMS8DT/PLwbPuV6NWX6DENgaEimiy/FF76Ozbr+OwOb1XKhLiJmvd3QabhNOPwW24dSSsjEfhkoRi6ZitTMOLrtWNa9L1dSmKwdp4HTvSwpU941wEvWYHZ33jfPOdNgQbxPPp5R1IKG3wE7CCtHAZYdk2wx6/TPQfnYRAFFYj2vzyZQrRnLERPB3PzwPPGEoVWVouXXMTmPGu9MQuKelHDjU7Yt/TGNQpTZccznJq3xSYjbusiKDG4b50dUpJWljBaJW1N9x04rVAgMBAAECggEBAIfKt5Kx7Hini+2gWrOgQoHVrwskl7NtQshSNJQ5bSaUVssy7OpHek9GF/U6Gppt7XAJO1BXa0NG97i/bm4GVxBaMRp1TORmYM3GT8sjPQDP1MGLNdZ5j93hdUM5Jmvf6Xx8Um08+DDUtGFg/QBU80u1QkxbyDoaTcw0epC2sQnApbseHhupcPoksx4wmyIB3XtzWhgI4qCIcaao/vOQUdMXf5Ma019RhpZzI0p0vVVJ8mD4yee4ULm7ptSnl7DAKWrLwE2KfLdYIQyD2XrOTpDapICzReC9wROgdPEgb7mrRg7L/o7289l12Pwm3PKaxE+/Y2WJJrmMNCO/ybasekECgYEAxgtEUpt1n0tSPyHFRhROisF3tfMQ2x4i1Qm84TZmzTxyLUZkVCVyEM9o9uLDlxMcKe7Ays6jdcpL3o6b8U+7ILITMZZQjVjiWF3QyMhROwNOE0Dvhl7K/No61FJXJG9e43fNz3jXZMhflmd/YzWd1kT5wW0rQy7RcivuNGCCTu0CgYEAuhovdQqjsXG0jsr+3kLXhHdk6iTlupgu/1rEL61KC90YmHbtbG0I/at+8uQSUanQfqGCoUx6aMXFgN14RLT4wrIf8XxY2JT+T1g8BL7YEimtH98PbtX4KzvaerynDSg/MX8DvEmqcSzSCaNm9U0cdA6KfH4klv1NBYkelqG8xokCgYAv/w0I5CpGd8+wAzQ3PramRCsKCqV8JTqV3O1Mu42AkfSt6lBoYEbbshijNOPoGjaKJxXMUPYmlud6QZ+jhQo5605hhydwiikY92uhLYMaRTvHyMYED7csPothZ6884kzh3eggLw4sm68m2WBzH8xn4IGTEi0Y9CDIhcMOKJGMJQKBgQCK1hYpfvJ34pgNHyvcrIkT7e5/L1+jQP1uy0wpNWJUT3GV7MNbbEyU0mg21CdGfSVqExvdMazwlPqvFIow18Htne1uvpGZoi3HC9BayfVwN19Ms6BT05T8y/5GKo+FpUzfpB4pTdu16vYW8qxQvGJJwr9V+NVbP0VCp/UF80td4QKBgGvwRcpuRCxgQKdAUVe0hlYZyGrPrtQUntfRNMUiE0knPc5RseZfLzkRlIH0C7j7zOioCUzI71ohSv2R3JYNsYjfYtB5wNNkPZ0u/p6jFRqtIk6hvfDZ3C/BaWhOaTsInCJPT5xLr3wdAfXupk7BhMtxh1PXlHGwViI2JjeJyGwa',
            // 必填-应用公钥证书 路径
            // 设置应用私钥后，即可下载得到以下3个证书
            'app_public_cert_path' => './config/payment/appCertPublicKey_2021005110663521.crt',
            // 必填-支付宝公钥证书 路径
            'alipay_public_cert_path' => './config/payment/alipayCertPublicKey_RSA2.crt',
            // 必填-支付宝根证书 路径
            'alipay_root_cert_path' => './config/payment/alipayRootCert.crt',
            'return_url' => '',
            'notify_url' => 'https://longh.top/api/notify/alipay',
            // 选填-第三方应用授权token
            'app_auth_token' => '',
            // 选填-服务商模式下的服务商 id，当 mode 为 Pay::MODE_SERVICE 时使用该参数
            'service_provider_id' => '',
            // 选填-默认为正常模式。可选为： MODE_NORMAL, MODE_SANDBOX, MODE_SERVICE
            'mode' => Pay::MODE_NORMAL,
        ]
    ],
    'wechat' => [
        'default' => [
            // 必填-商户号，服务商模式下为服务商商户号
            // 可在 https://pay.weixin.qq.com/ 账户中心->商户信息 查看
            'mch_id' => '1711404024',
            // 选填-v2商户私钥
            'mch_secret_key_v2' => '',
            // 必填-v3 商户秘钥
            // 即 API v3 密钥(32字节，形如md5值)，可在 账户中心->API安全 中设置
            'mch_secret_key' => 'VDLLfaWhb6deiPXoqqZ3wX7uDl2paZYe',
            // 必填-商户私钥 字符串或路径
            // 即 API证书 PRIVATE KEY，可在 账户中心->API安全->申请API证书 里获得
            // 文件名形如：apiclient_key.pem
            'mch_secret_cert' => './config/payment/apiclient_key.pem',
            // 必填-商户公钥证书路径
            // 即 API证书 CERTIFICATE，可在 账户中心->API安全->申请API证书 里获得
            // 文件名形如：apiclient_cert.pem
            'mch_public_cert_path' => './config/payment/apiclient_cert.pem',
            // 必填-微信回调url
            // 不能有参数，如?号，空格等，否则会无法正确回调
            'notify_url' => 'https://longh.top/api/notify/wechat',
            // 选填-公众号 的 app_id
            // 可在 mp.weixin.qq.com 设置与开发->基本配置->开发者ID(AppID) 查看
            'mp_app_id' => '',
            // 选填-小程序 的 app_id
            'mini_app_id' => 'wx667731e184f394f5',
            // 选填-app 的 app_id
            'app_id' => 'wx625b4a5a416cb439',
            // 选填-服务商模式下，子公众号 的 app_id
            'sub_mp_app_id' => '',
            // 选填-服务商模式下，子 app 的 app_id
            'sub_app_id' => '',
            // 选填-服务商模式下，子小程序 的 app_id
            'sub_mini_app_id' => '',
            // 选填-服务商模式下，子商户id
            'sub_mch_id' => '',
            // 选填-微信平台公钥证书路径, optional，强烈建议 php-fpm 模式下配置此参数
            'wechat_public_cert_path' => [
                '45F59D4DABF31918AFCEC556D5D2C6E376675D57' => __DIR__.'/Cert/wechatPublicKey.crt',
            ],
            // 选填-默认为正常模式。可选为： MODE_NORMAL, MODE_SERVICE
            'mode' => Pay::MODE_NORMAL,
        ]
    ],
    'unipay' => [
        'default' => [
            // 必填-商户号
            'mch_id' => '777290058167151',
            // 选填-商户密钥：为银联条码支付综合前置平台配置：https://up.95516.com/open/openapi?code=unionpay
            'mch_secret_key' => '979da4cfccbae7923641daa5dd7047c2',
            // 必填-商户公私钥
            'mch_cert_path' => __DIR__.'/Cert/unipayAppCert.pfx',
            // 必填-商户公私钥密码
            'mch_cert_password' => '000000',
            // 必填-银联公钥证书路径
            'unipay_public_cert_path' => __DIR__.'/Cert/unipayCertPublicKey.cer',
            // 必填
            'return_url' => 'https://yansongda.cn/unipay/return',
            // 必填
            'notify_url' => 'https://yansongda.cn/unipay/notify',
            'mode' => Pay::MODE_NORMAL,
        ],
    ],
    'douyin' => [
        'default' => [
            // 选填-商户号
            // 抖音开放平台 --> 应用详情 --> 支付信息 --> 产品管理 --> 商户号
            'mch_id' => '73744242495132490630',
            // 必填-支付 Token，用于支付回调签名
            // 抖音开放平台 --> 应用详情 --> 支付信息 --> 支付设置 --> Token(令牌)
            'mch_secret_token' => 'douyin_mini_token',
            // 必填-支付 SALT，用于支付签名
            // 抖音开放平台 --> 应用详情 --> 支付信息 --> 支付设置 --> SALT
            'mch_secret_salt' => 'oDxWDBr4U7FAAQ8hnGDm29i4A6pbTMDKme4WLLvA',
            // 必填-小程序 app_id
            // 抖音开放平台 --> 应用详情 --> 支付信息 --> 支付设置 --> 小程序appid
            'mini_app_id' => 'tt226e54d3bd581bf801',
            // 选填-抖音开放平台服务商id
            'thirdparty_id' => '',
            // 选填-抖音支付回调地址
            'notify_url' => 'https://yansongda.cn/douyin/notify',
        ],
    ],
    'jsb' => [
        'default' => [
            // 服务代码
            'svr_code' => '',
            // 必填-合作商ID
            'partner_id' => '',
            // 必填-公私钥对编号
            'public_key_code' => '00',
            // 必填-商户私钥(加密签名)
            'mch_secret_cert_path' => '',
            // 必填-商户公钥证书路径(提供江苏银行进行验证签名用)
            'mch_public_cert_path' => '',
            // 必填-江苏银行的公钥(用于解密江苏银行返回的数据)
            'jsb_public_cert_path' => '',
            //支付通知地址
            'notify_url' => '',
            // 选填-默认为正常模式。可选为： MODE_NORMAL:正式环境, MODE_SANDBOX:测试环境
            'mode' => Pay::MODE_NORMAL,
        ],
    ],
    'logger' => [
        'enable' => true,
        'file' => './runtime/logs/pay.log',
        'level' => 'info', // 建议生产环境等级调整为 info，开发环境为 debug
        'type' => 'single', // optional, 可选 daily.
        'max_file' => 30, // optional, 当 type 为 daily 时有效，默认 30 天
    ],
    'http' => [ // optional
        'timeout' => 5.0,
        'connect_timeout' => 5.0,
        // 更多配置项请参考 [Guzzle](https://guzzle-cn.readthedocs.io/zh_CN/latest/request-options.html)
    ],
];
