<?php
declare(strict_types=1);

namespace app\admin\service;

use app\model\SystemConfig;
use InvalidArgumentException;

class ConfigService
{
    public const GROUP_WEBSITE = 'website';
    public const GROUP_MINIAPP = 'miniapp';
    public const GROUP_OFFICIAL_ACCOUNT = 'official_account';
    public const GROUP_WECHAT_PAY = 'wechat_pay';
    public const GROUP_EPAY = 'epay';
    public const GROUP_ALIPAY = 'alipay';
    public const GROUP_SERVICE = 'service';

    private const DEFINITIONS = [
        self::GROUP_WEBSITE => [
            'label' => '网站配置',
            'items' => [
                ['config_key' => 'site_name', 'config_name' => '网站名称', 'input_type' => 'text', 'placeholder' => '请输入网站名称', 'sort' => 10, 'remark' => '网站前台、分享页、导航标题等默认显示名称'],
                ['config_key' => 'site_logo', 'config_name' => '网站 Logo', 'input_type' => 'text', 'placeholder' => '请输入网站 Logo 图片地址', 'sort' => 20, 'remark' => '用于首页、头部、品牌区域展示的主 Logo'],
                ['config_key' => 'site_icon', 'config_name' => '网站图标 / 头像', 'input_type' => 'text', 'placeholder' => '请输入网站图标或头像图片地址', 'sort' => 30, 'remark' => '用于头像、角标、浏览器图标、小尺寸品牌图等位置'],
                ['config_key' => 'default_share_image', 'config_name' => '默认分享图', 'input_type' => 'text', 'placeholder' => '请输入默认分享图地址', 'sort' => 40, 'remark' => '用户分享网站或商品时可优先使用的默认图片'],
                ['config_key' => 'home_banner_image', 'config_name' => '首页横幅图', 'input_type' => 'text', 'placeholder' => '请输入首页横幅图地址', 'sort' => 50, 'remark' => '首页没有单独活动图时可使用这张图片作为通用横幅'],
                ['config_key' => 'customer_qr_image', 'config_name' => '客服二维码图片', 'input_type' => 'text', 'placeholder' => '请输入客服二维码图片地址', 'sort' => 60, 'remark' => '前台客服、帮助中心、联系页面可复用这张图片'],
                ['config_key' => 'site_tagline', 'config_name' => '网站简短介绍', 'input_type' => 'text', 'placeholder' => '请输入网站简短介绍', 'sort' => 70, 'remark' => '适合展示在首页标题下方、分享摘要、头部描述的短文案'],
                ['config_key' => 'site_intro', 'config_name' => '网站详细介绍', 'input_type' => 'textarea', 'placeholder' => '请输入网站详细介绍', 'sort' => 80, 'remark' => '适合用于关于我们、落地页、品牌介绍等完整内容说明'],
                ['config_key' => 'service_notice', 'config_name' => '网站公告提示', 'input_type' => 'text', 'placeholder' => '请输入网站全局提示文案', 'sort' => 90, 'remark' => '可作为首页公告条、顶部提示、系统提示语的默认文案'],
                ['config_key' => 'record_number', 'config_name' => '备案号', 'input_type' => 'text', 'placeholder' => '请输入网站备案号', 'sort' => 100, 'remark' => '网站底部备案信息展示'],
                ['config_key' => 'copyright_text', 'config_name' => '版权信息', 'input_type' => 'text', 'placeholder' => '请输入版权信息', 'sort' => 110, 'remark' => '网站底部版权文案，例如 Copyright © 2026 Piksell'],
                ['config_key' => 'contact_email', 'config_name' => '联系邮箱', 'input_type' => 'text', 'placeholder' => '请输入联系邮箱', 'sort' => 120, 'remark' => '站务联系、合作咨询、售后支持邮箱'],
                ['config_key' => 'seo_keywords', 'config_name' => 'SEO 关键词', 'input_type' => 'textarea', 'placeholder' => '请输入 SEO 关键词，多个用逗号隔开', 'sort' => 130, 'remark' => '用于网站搜索关键词配置'],
                ['config_key' => 'seo_description', 'config_name' => 'SEO 描述', 'input_type' => 'textarea', 'placeholder' => '请输入 SEO 描述', 'sort' => 140, 'remark' => '用于搜索引擎描述、分享摘要等场景'],
            ],
        ],
        self::GROUP_MINIAPP => [
            'label' => '小程序配置',
            'items' => [
                ['config_key' => 'app_id', 'config_name' => 'AppID', 'input_type' => 'text', 'placeholder' => '请输入小程序 AppID', 'sort' => 10, 'remark' => '微信小程序唯一标识'],
                ['config_key' => 'app_secret', 'config_name' => 'AppSecret', 'input_type' => 'password', 'placeholder' => '请输入小程序 AppSecret', 'sort' => 20, 'remark' => '小程序接口调用密钥'],
                ['config_key' => 'original_id', 'config_name' => '原始 ID', 'input_type' => 'text', 'placeholder' => '请输入原始 ID', 'sort' => 30, 'remark' => '便于账号主体核对'],
                ['config_key' => 'request_domain', 'config_name' => '业务域名', 'input_type' => 'text', 'placeholder' => '例如 https://xos.piksell.cn', 'sort' => 40, 'remark' => '前台请求接口域名'],
                ['config_key' => 'env_version', 'config_name' => '发布环境', 'input_type' => 'text', 'placeholder' => 'develop / trial / release', 'sort' => 50, 'remark' => '当前小程序投放环境'],
            ],
        ],
        self::GROUP_OFFICIAL_ACCOUNT => [
            'label' => '公众号配置',
            'items' => [
                ['config_key' => 'app_id', 'config_name' => '公众号 AppID', 'input_type' => 'text', 'placeholder' => '请输入公众号 AppID', 'sort' => 10, 'remark' => '公众号唯一标识'],
                ['config_key' => 'app_secret', 'config_name' => '公众号 AppSecret', 'input_type' => 'password', 'placeholder' => '请输入公众号 AppSecret', 'sort' => 20, 'remark' => '公众号接口调用密钥'],
                ['config_key' => 'original_id', 'config_name' => '原始 ID', 'input_type' => 'text', 'placeholder' => '请输入原始 ID', 'sort' => 30, 'remark' => '公众号后台可查看'],
                ['config_key' => 'token', 'config_name' => '服务器 Token', 'input_type' => 'text', 'placeholder' => '请输入微信服务器 Token', 'sort' => 40, 'remark' => '消息校验 Token'],
                ['config_key' => 'aes_key', 'config_name' => 'EncodingAESKey', 'input_type' => 'password', 'placeholder' => '请输入 EncodingAESKey', 'sort' => 50, 'remark' => '消息加解密密钥'],
            ],
        ],
        self::GROUP_WECHAT_PAY => [
            'label' => '微信支付配置',
            'items' => [
                ['config_key' => 'merchant_id', 'config_name' => '商户号', 'input_type' => 'text', 'placeholder' => '请输入微信支付商户号', 'sort' => 10, 'remark' => '微信支付商户平台商户号'],
                ['config_key' => 'merchant_name', 'config_name' => '商户名称', 'input_type' => 'text', 'placeholder' => '请输入商户名称', 'sort' => 20, 'remark' => '支付页面展示名称'],
                ['config_key' => 'api_v3_key', 'config_name' => 'APIv3 Key', 'input_type' => 'password', 'placeholder' => '请输入 APIv3 Key', 'sort' => 30, 'remark' => '微信支付接口加密密钥'],
                ['config_key' => 'merchant_private_key', 'config_name' => '商户API私钥', 'input_type' => 'textarea', 'placeholder' => '请输入 apiclient_key.pem 内容', 'sort' => 40, 'remark' => '用于微信支付 v3 请求签名，请妥善保管'],
                ['config_key' => 'merchant_certificate', 'config_name' => '商户API证书', 'input_type' => 'textarea', 'placeholder' => '可选，请输入 apiclient_cert.pem 内容用于校验证书序列号和私钥是否匹配', 'sort' => 50, 'remark' => '商户 API 证书内容，可用于排查签名错误'],
                ['config_key' => 'cert_serial_no', 'config_name' => '证书序列号', 'input_type' => 'text', 'placeholder' => '请输入商户API证书序列号', 'sort' => 60, 'remark' => '必须填写商户 API 证书序列号，不是微信支付平台证书序列号'],
                ['config_key' => 'notify_url', 'config_name' => '支付通知地址', 'input_type' => 'text', 'placeholder' => '例如 http://xos.piksell.cn/api/pay/wechat/energy-recharge/notify', 'sort' => 70, 'remark' => '微信支付异步通知 URL'],
            ],
        ],
        self::GROUP_ALIPAY => [
            'label' => '支付宝支付配置',
            'items' => [
                ['config_key' => 'app_id', 'config_name' => '支付宝 AppID', 'input_type' => 'text', 'placeholder' => '请输入支付宝 AppID', 'sort' => 10, 'remark' => '支付宝开放平台应用 ID'],
                ['config_key' => 'merchant_private_key', 'config_name' => '应用私钥', 'input_type' => 'password', 'placeholder' => '请输入应用私钥', 'sort' => 20, 'remark' => '商户应用私钥'],
                ['config_key' => 'alipay_public_key', 'config_name' => '支付宝公钥', 'input_type' => 'textarea', 'placeholder' => '请输入支付宝公钥', 'sort' => 30, 'remark' => '支付宝开放平台公钥'],
                ['config_key' => 'notify_url', 'config_name' => '支付通知地址', 'input_type' => 'text', 'placeholder' => '请输入支付回调地址', 'sort' => 40, 'remark' => '支付宝异步通知 URL'],
                ['config_key' => 'return_url', 'config_name' => '同步返回地址', 'input_type' => 'text', 'placeholder' => '请输入同步返回地址', 'sort' => 50, 'remark' => '支付完成跳转地址'],
            ],
        ],
        self::GROUP_EPAY => [
            'label' => '易支付配置',
            'items' => [
                ['config_key' => 'api_url', 'config_name' => '接口地址', 'input_type' => 'text', 'placeholder' => '例如 https://pay.example.com/', 'sort' => 10, 'remark' => '易支付网关地址，系统会优先生成二维码支付页'],
                ['config_key' => 'pid', 'config_name' => '商户ID', 'input_type' => 'text', 'placeholder' => '请输入易支付商户ID', 'sort' => 20, 'remark' => '易支付平台分配的商户 ID'],
                ['config_key' => 'key', 'config_name' => '商户密钥', 'input_type' => 'password', 'placeholder' => '请输入易支付商户密钥', 'sort' => 30, 'remark' => '用于生成和校验支付签名'],
                ['config_key' => 'pay_type', 'config_name' => '默认支付方式', 'input_type' => 'text', 'placeholder' => '可填 wxpay / alipay / qqpay，微信扫码页请填 wxpay', 'sort' => 40, 'remark' => '系统会优先直达易支付二维码页，微信扫码支付请确保易支付商户已开通 wxpay 通道'],
                ['config_key' => 'notify_url', 'config_name' => '异步通知地址', 'input_type' => 'text', 'placeholder' => '留空自动使用 https://域名/api/pay/epay/energy-recharge/notify', 'sort' => 50, 'remark' => '易支付异步通知 URL'],
                ['config_key' => 'return_url', 'config_name' => '同步返回地址', 'input_type' => 'text', 'placeholder' => '留空自动使用 https://域名/api/pay/epay/energy-recharge/return', 'sort' => 60, 'remark' => '支付完成后的同步跳转 URL'],
            ],
        ],
        self::GROUP_SERVICE => [
            'label' => '客服配置',
            'items' => [
                ['config_key' => 'service_name', 'config_name' => '客服名称', 'input_type' => 'text', 'placeholder' => '请输入客服昵称', 'sort' => 10, 'remark' => '前台展示的客服名称'],
                ['config_key' => 'wechat', 'config_name' => '客服微信', 'input_type' => 'text', 'placeholder' => '请输入客服微信号', 'sort' => 20, 'remark' => '主要客服微信'],
                ['config_key' => 'qq', 'config_name' => '客服 QQ', 'input_type' => 'text', 'placeholder' => '请输入客服 QQ', 'sort' => 30, 'remark' => '备用客服联系方式'],
                ['config_key' => 'email', 'config_name' => '客服邮箱', 'input_type' => 'text', 'placeholder' => '请输入客服邮箱', 'sort' => 40, 'remark' => '售后与问题反馈邮箱'],
                ['config_key' => 'work_time', 'config_name' => '客服时间', 'input_type' => 'text', 'placeholder' => '例如 09:00 - 22:00', 'sort' => 50, 'remark' => '对外服务时间'],
            ],
        ],
    ];

    public function getGroupOptions(): array
    {
        $items = [];
        foreach (self::DEFINITIONS as $groupKey => $definition) {
            $items[] = [
                'group_key' => $groupKey,
                'label' => (string)$definition['label'],
            ];
        }

        return $items;
    }

    public function getGroup(string $groupKey): array
    {
        $groupKey = $this->normalizeGroupKey($groupKey);
        $definition = self::DEFINITIONS[$groupKey];
        $this->syncDefaults($groupKey);

        $items = SystemConfig::where('group_key', $groupKey)
            ->order('sort', 'asc')
            ->order('id', 'asc')
            ->select()
            ->toArray();

        return [
            'group_key' => $groupKey,
            'group_label' => (string)$definition['label'],
            'group_options' => $this->getGroupOptions(),
            'items' => $items,
        ];
    }

    public function saveGroup(string $groupKey, array $values): array
    {
        $groupKey = $this->normalizeGroupKey($groupKey);
        $this->syncDefaults($groupKey);

        $rows = SystemConfig::where('group_key', $groupKey)->select();
        foreach ($rows as $row) {
            $configKey = (string)$row->config_key;
            $row->save([
                'config_value' => trim((string)($values[$configKey] ?? '')),
            ]);
        }

        return $this->getGroup($groupKey);
    }

    private function syncDefaults(string $groupKey): void
    {
        $definition = self::DEFINITIONS[$groupKey];
        foreach ($definition['items'] as $item) {
            $exists = SystemConfig::where('group_key', $groupKey)
                ->where('config_key', $item['config_key'])
                ->find();
            if ($exists !== null) {
                $exists->save([
                    'config_name' => $item['config_name'],
                    'input_type' => $item['input_type'],
                    'placeholder' => $item['placeholder'],
                    'sort' => (int)$item['sort'],
                    'remark' => $item['remark'],
                ]);
                continue;
            }

            SystemConfig::create([
                'group_key' => $groupKey,
                'config_key' => $item['config_key'],
                'config_name' => $item['config_name'],
                'config_value' => '',
                'input_type' => $item['input_type'],
                'placeholder' => $item['placeholder'],
                'sort' => (int)$item['sort'],
                'status' => 1,
                'remark' => $item['remark'],
            ]);
        }
    }

    private function normalizeGroupKey(string $groupKey): string
    {
        $groupKey = trim(strtolower($groupKey));
        if (!isset(self::DEFINITIONS[$groupKey])) {
            throw new InvalidArgumentException('系统配置分组不存在');
        }

        return $groupKey;
    }
}
