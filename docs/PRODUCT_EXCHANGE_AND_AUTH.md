# 商品兑换与用户认证功能文档

## 功能概述
完善商品详情页面的兑换功能，添加兑换确认弹窗，检测用户登录状态，支持邮箱注册登录和微信一键登录。

## 功能流程

### 1. 商品兑换流程
```
用户点击"使用XX能量兑换" 
  ↓
弹出确认对话框
  ↓
用户点击"确定兑换"
  ↓
检查登录状态
  ↓
├─ 已登录 → 跳转到订单创建页面
└─ 未登录 → 提示登录 → 跳转到登录页面
```

### 2. 用户注册/登录流程
```
用户进入登录页面
  ↓
选择登录方式：
├─ 邮箱/用户名登录
│   ├─ 输入用户名
│   ├─ 输入密码
│   └─ 点击登录
└─ 微信一键登录
    ├─ 点击"微信一键登录"
    ├─ 授权获取微信信息
    └─ 自动创建/登录账号
```

## 前端实现

### 商品详情页面 (`is/pages/product/detail.uvue`)

#### 1. 兑换按钮修改
```vue
<button class="primary-button action-button" @click="showExchangeConfirm">
  使用 {{ detail.exchange_energy || 0 }} 能量兑换
</button>
```

#### 2. 确认弹窗
```vue
<view class="modal-overlay" v-if="showConfirmModal" @click="hideExchangeConfirm">
  <view class="modal-container" @click.stop>
    <view class="modal-header">
      <text class="modal-title">确认兑换</text>
    </view>
    <view class="modal-body">
      <text class="modal-message">确定使用 {{ detail.exchange_energy || 0 }} 能量兑换该资源吗？</text>
      <view class="modal-product-info">
        <image class="modal-product-image" :src="cover(detailIcon())" mode="aspectFill"></image>
        <view class="modal-product-details">
          <text class="modal-product-name">{{ detail.name }}</text>
          <text class="modal-product-energy">需要 {{ detail.exchange_energy || 0 }} 能量</text>
        </view>
      </view>
    </view>
    <view class="modal-footer">
      <button class="modal-button cancel-button" @click="hideExchangeConfirm">取消</button>
      <button class="modal-button confirm-button" @click="confirmExchange">确定兑换</button>
    </view>
  </view>
</view>
```

#### 3. 登录状态检查
```typescript
function confirmExchange() {
  hideExchangeConfirm()
  
  // 检查用户登录状态
  if (!hasUserSession()) {
    uni.showModal({
      title: '需要登录',
      content: '请先登录后再进行兑换',
      confirmText: '去登录',
      cancelText: '取消',
      success: (res) => {
        if (res.confirm) {
          openAuthPage('login')
        }
      }
    })
    return
  }
  
  // 已登录，跳转到订单创建页面
  goOrder()
}
```

### 认证页面 (`is/pages/auth/index.uvue`)

#### 1. 表单字段
**登录模式**:
- 用户名 (必填)
- 密码 (必填，至少6个字符)

**注册模式**:
- 用户名 (必填，至少3个字符)
- 昵称 (必填)
- 邮箱 (选填)
- 密码 (必填，至少6个字符)
- 确认密码 (必填，需与密码一致)

#### 2. 微信登录按钮
```vue
<button class="wx-login-button" @click="wxLogin" v-if="canUseWxLogin()">
  <text class="wx-login-text">微信一键登录</text>
</button>
```

#### 3. 表单验证
```typescript
function validateForm(): string {
  if (form.username == '') {
    return '请输入用户名'
  }
  if (form.username.length < 3) {
    return '用户名至少3个字符'
  }
  if (form.password.length < 6) {
    return '密码至少6个字符'
  }
  if (mode.value == 'register') {
    if (form.confirmPassword != form.password) {
      return '两次输入的密码不一致'
    }
    if (form.nickname == '') {
      return '请输入昵称'
    }
  }
  return ''
}
```

#### 4. 微信登录逻辑
```typescript
function wxLogin() {
  if (submitting.value) {
    return
  }

  submitting.value = true
  uni.showLoading({
    title: '微信登录中...',
  })

  performWxLogin().then(() => {
    uni.showToast({
      title: '登录成功',
      icon: 'none',
    })
    setTimeout(() => {
      uni.navigateBack()
    }, 500)
  }).catch((message) => {
    uni.showToast({
      title: String(message || '微信登录失败'),
      icon: 'none',
    })
  }).finally(() => {
    submitting.value = false
    uni.hideLoading()
  })
}
```

## 后端实现

### 数据库变更 (`database/add_user_auth_fields.sql`)

#### 用户表新增字段
```sql
-- 密码字段
password varchar(255) NOT NULL DEFAULT '' COMMENT '密码hash'

-- 邀请码相关
invite_code varchar(20) NOT NULL DEFAULT '' COMMENT '邀请码'
inviter_id bigint unsigned NOT NULL DEFAULT 0 COMMENT '邀请人ID'
invite_count int unsigned NOT NULL DEFAULT 0 COMMENT '邀请人数'

-- 签到日期
last_signin_date date DEFAULT NULL COMMENT '最后签到日期'

-- 微信相关
wx_openid varchar(100) NOT NULL DEFAULT '' COMMENT '微信openid'
wx_unionid varchar(100) NOT NULL DEFAULT '' COMMENT '微信unionid'
wx_nickname varchar(100) NOT NULL DEFAULT '' COMMENT '微信昵称'
wx_avatar varchar(255) NOT NULL DEFAULT '' COMMENT '微信头像'
wx_session_key varchar(255) NOT NULL DEFAULT '' COMMENT '微信session_key'
```

#### 索引
```sql
idx_wx_openid (wx_openid)
idx_wx_unionid (wx_unionid)
idx_email (email)
idx_invite_code (invite_code)
idx_inviter_id (inviter_id)
```

### 用户注册/登录 (`app/index/service/UserCenterService.php`)

#### 注册逻辑
```php
public function register(array $data, string $ip): array
{
    $username = trim((string)($data['username'] ?? ''));
    if (User::where('username', $username)->find() !== null) {
        throw new RuntimeException('Username already exists');
    }

    $user = User::create([
        'username' => trim((string)$data['username']),
        'password' => password_hash((string)$data['password'], PASSWORD_DEFAULT),
        'nickname' => trim((string)($data['nickname'] ?? '')) ?: trim((string)$data['username']),
        'email' => trim((string)($data['email'] ?? '')),
        'energy' => 0,
        'status' => 1,
        'last_login_ip' => $ip,
        'last_login_at' => date('Y-m-d H:i:s'),
    ]);

    return $this->buildAuthResponse($user->refresh());
}
```

#### 登录逻辑
```php
public function login(string $username, string $password, string $ip): array
{
    $user = User::where('username', trim($username))->find();
    if ($user === null || !password_verify($password, (string)$user->password)) {
        throw new RuntimeException('Invalid username or password');
    }

    if ((int)$user->status !== 1) {
        throw new RuntimeException('User account is disabled');
    }

    $user->save([
        'last_login_ip' => $ip,
        'last_login_at' => date('Y-m-d H:i:s'),
    ]);

    return $this->buildAuthResponse($user->refresh());
}
```

### 微信登录 (`app/index/service/WxAuthService.php`)

#### 微信登录流程
1. 前端调用 `uni.login()` 获取 `code`
2. 前端调用 `uni.getUserProfile()` 获取用户信息
3. 发送 `code` 和用户信息到后端
4. 后端调用微信API获取 `openid` 和 `session_key`
5. 根据 `openid` 查找或创建用户
6. 生成 JWT Token 返回给前端

#### 核心代码
```php
public function login(
    string $code,
    string $encryptedData,
    string $iv,
    string $rawData,
    string $signature,
    string $loginIp
): array {
    // 调用微信接口获取 session_key 和 openid
    $url = "https://api.weixin.qq.com/sns/jscode2session?appid={$appId}&secret={$appSecret}&js_code={$code}&grant_type=authorization_code";
    
    $response = $this->httpGet($url);
    $result = json_decode($response, true);

    $openid = $result['openid'] ?? '';
    $sessionKey = $result['session_key'] ?? '';
    $unionid = $result['unionid'] ?? '';

    // 查找或创建用户
    $user = User::where('wx_openid', $openid)->find();
    
    if (!$user) {
        // 创建新用户
        $user = User::create([
            'username' => 'wx_' . substr($openid, -8),
            'password' => '',
            'nickname' => $userInfo['nickName'] ?? '微信用户',
            'avatar' => $userInfo['avatarUrl'] ?? '',
            'wx_openid' => $openid,
            'wx_unionid' => $unionid,
            'wx_session_key' => $sessionKey,
            'energy' => 100, // 新用户赠送100能量
            'status' => 1,
            'last_login_ip' => $loginIp,
            'last_login_at' => date('Y-m-d H:i:s'),
        ]);
    }

    // 生成 JWT Token
    $token = (new TokenService())->createToken([
        'id' => $user->id,
        'username' => $user->username,
        'type' => 'user',
    ]);

    return [
        'token' => $token,
        'user' => [
            'id' => $user->id,
            'username' => $user->username,
            'nickname' => $user->nickname,
            'avatar' => $user->avatar,
            'energy' => $user->energy,
        ],
    ];
}
```

### API 路由 (`app/index/route/route.php`)

```php
Route::group('api/user', function (): void {
    // 注册登录
    Route::post('register', 'User/register');
    Route::post('login', 'User/login');
    
    // 微信小程序登录
    Route::post('wx/login', 'WxAuth/login');

    Route::group('', function (): void {
        Route::get('profile', 'User/profile');
        Route::put('profile', 'User/updateProfile');
        Route::post('profile', 'User/updateProfile');
        Route::post('signin', 'User/signIn');
        Route::get('orders', 'User/orders');
        Route::get('energy-logs', 'User/energyLogs');
        
        // 微信用户信息
        Route::get('wx/info', 'WxAuth/getUserInfo');
        Route::post('wx/info', 'WxAuth/updateUserInfo');
    })->middleware(UserAuthMiddleware::class);
});
```

## UI 样式

### 确认弹窗样式
```css
.modal-overlay {
  position: fixed;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background: rgba(0, 0, 0, 0.5);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 9999;
}

.modal-container {
  width: 600rpx;
  max-width: 90%;
  background: #ffffff;
  border-radius: 32rpx;
  overflow: hidden;
}

.modal-button {
  flex: 1;
  height: 88rpx;
  line-height: 88rpx;
  border-radius: 22rpx;
  font-size: 28rpx;
  font-weight: 600;
  border: 0;
}

.cancel-button {
  background: #f3f6fb;
  color: #64738f;
}

.confirm-button {
  background: linear-gradient(135deg, #ff6b3f 0%, #ff8f3f 100%);
  color: #ffffff;
}
```

### 微信登录按钮样式
```css
.wx-login-button {
  width: 100%;
  height: 88rpx;
  margin-top: 20rpx;
  border-radius: 22rpx;
  background: #07c160;
  border: 0;
  display: flex;
  align-items: center;
  justify-content: center;
}

.wx-login-text {
  font-size: 28rpx;
  font-weight: 600;
  color: #ffffff;
}
```

## 部署步骤

### 1. 数据库迁移
```bash
# 执行数据库迁移脚本
mysql -u xospiksell -p xospiksell < database/add_user_auth_fields.sql
```

### 2. 配置微信小程序
编辑 `config/wechat.php`:
```php
return [
    'mini_program' => [
        'app_id' => 'your_app_id',
        'app_secret' => 'your_app_secret',
    ],
];
```

### 3. 前端配置
确保 API 地址配置正确 (`is/config/api-config.uts`)

### 4. 测试流程
1. 测试邮箱注册登录
2. 测试微信一键登录
3. 测试未登录状态下点击兑换
4. 测试已登录状态下点击兑换

## 安全特性

1. **密码加密**: 使用 `password_hash()` 加密存储
2. **JWT Token**: 使用 JWT 进行身份验证
3. **签名验证**: 微信登录时验证数据签名
4. **数据解密**: 安全解密微信加密数据
5. **状态检查**: 检查用户账号状态

## 相关文件

### 前端
- `is/pages/product/detail.uvue` - 商品详情页面
- `is/pages/auth/index.uvue` - 认证页面
- `is/utils/user.uts` - 用户工具类
- `is/utils/wx-auth.uts` - 微信认证工具类

### 后端
- `app/index/controller/User.php` - 用户控制器
- `app/index/controller/WxAuth.php` - 微信认证控制器
- `app/index/service/UserCenterService.php` - 用户中心服务
- `app/index/service/WxAuthService.php` - 微信认证服务
- `app/model/User.php` - 用户模型

### 数据库
- `database/add_user_auth_fields.sql` - 数据库迁移脚本

## 更新日期
2026-04-22
