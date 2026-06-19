# 微信小程序登录配置指南

## 一、数据库配置

### 1. 执行数据库迁移

运行以下命令添加微信登录相关字段：

```bash
php database/migrate_wx_fields.php
```

或者手动执行SQL文件：

```bash
mysql -u用户名 -p密码 数据库名 < database/add_wx_fields.sql
```

### 2. 添加的字段

- `wx_openid` - 微信openid（唯一标识）
- `wx_unionid` - 微信unionid（开放平台统一标识）
- `wx_session_key` - 微信session_key（用于解密）

## 二、后端配置

### 1. 配置微信小程序信息

在 `.env` 文件中添加：

```env
WX_MINI_APP_ID=你的小程序AppID
WX_MINI_APP_SECRET=你的小程序AppSecret
```

### 2. 获取微信小程序AppID和AppSecret

1. 登录[微信公众平台](https://mp.weixin.qq.com/)
2. 进入"开发" -> "开发管理" -> "开发设置"
3. 复制 AppID 和 AppSecret

## 三、前端配置

### 1. 配置小程序AppID

在 `is/manifest.json` 中配置：

```json
{
  "mp-weixin": {
    "appid": "你的小程序AppID"
  }
}
```

### 2. 配置服务器域名

在微信公众平台配置合法域名：

1. 登录微信公众平台
2. 进入"开发" -> "开发管理" -> "开发设置" -> "服务器域名"
3. 添加 request 合法域名：`https://xos.piksell.cn` 或 `http://xos.piksell.cn`

## 四、API接口说明

### 1. 微信登录接口

**接口地址：** `POST /api/user/wx/login`

**请求参数：**

```json
{
  "code": "微信登录凭证",
  "encryptedData": "加密数据（可选）",
  "iv": "加密算法初始向量（可选）",
  "rawData": "原始数据（可选）",
  "signature": "签名（可选）"
}
```

**返回数据：**

```json
{
  "code": 0,
  "msg": "登录成功",
  "data": {
    "token": "JWT Token",
    "user": {
      "id": 1,
      "username": "wx_12345678",
      "nickname": "微信用户",
      "avatar": "头像URL",
      "energy": 100
    }
  }
}
```

### 2. 获取用户信息

**接口地址：** `GET /api/user/wx/info`

**请求头：**

```
Authorization: Bearer {token}
```

**返回数据：**

```json
{
  "code": 0,
  "msg": "success",
  "data": {
    "id": 1,
    "username": "wx_12345678",
    "nickname": "微信用户",
    "avatar": "头像URL",
    "phone": "",
    "email": "",
    "energy": 100,
    "invite_code": "ABC123",
    "invite_count": 0,
    "created_at": "2024-01-01 00:00:00"
  }
}
```

### 3. 更新用户信息

**接口地址：** `POST /api/user/wx/info`

**请求头：**

```
Authorization: Bearer {token}
```

**请求参数：**

```json
{
  "nickname": "新昵称",
  "avatar": "新头像URL",
  "phone": "手机号",
  "email": "邮箱"
}
```

## 五、前端使用方法

### 1. 导入工具类

```typescript
import {
  wxLogin,
  isWxLoggedIn,
  getWxUserInfo,
  getWxUserToken,
  wxLogout
} from '../../utils/wx-auth.uts'
```

### 2. 执行登录

```typescript
// 完整登录（获取用户信息）
wxLogin().then((data) => {
  console.log('登录成功', data)
  // data.token - JWT Token
  // data.user - 用户信息
}).catch((error) => {
  console.error('登录失败', error)
})

// 静默登录（仅使用code）
wxSilentLogin().then((data) => {
  console.log('登录成功', data)
}).catch((error) => {
  console.error('登录失败', error)
})
```

### 3. 检查登录状态

```typescript
if (isWxLoggedIn()) {
  console.log('已登录')
  const userInfo = getWxUserInfo()
  console.log('用户信息', userInfo)
} else {
  console.log('未登录')
}
```

### 4. 退出登录

```typescript
wxLogout()
console.log('已退出登录')
```

## 六、注意事项

1. **开发环境测试**
   - 在微信开发者工具中，需要关闭"不校验合法域名"选项进行真实测试
   - 或者在"详情" -> "本地设置"中勾选"不校验合法域名、web-view（业务域名）、TLS 版本以及 HTTPS 证书"

2. **新用户奖励**
   - 新用户注册自动赠送 100 能量
   - 可在 `WxAuthService.php` 中修改

3. **用户名生成规则**
   - 格式：`wx_` + openid后8位
   - 示例：`wx_12345678`

4. **安全建议**
   - 生产环境必须使用 HTTPS
   - 定期更新 AppSecret
   - 不要在前端暴露 AppSecret

## 七、常见问题

### 1. 登录失败：获取openid失败

**原因：** AppID 或 AppSecret 配置错误

**解决：** 检查 `.env` 文件中的配置是否正确

### 2. 签名验证失败

**原因：** rawData 或 signature 不匹配

**解决：** 确保前端传递的数据完整且未被篡改

### 3. 解密失败

**原因：** encryptedData、iv 或 sessionKey 不正确

**解决：** 确保使用最新的 code 获取 sessionKey

### 4. 网络请求失败

**原因：** 服务器域名未配置或不合法

**解决：** 在微信公众平台配置合法域名

## 八、测试流程

1. 配置好 AppID 和 AppSecret
2. 执行数据库迁移
3. 在微信开发者工具中打开小程序
4. 点击"登录"按钮
5. 授权获取用户信息
6. 查看控制台输出和数据库记录

## 九、后台管理

用户登录后，可在后台管理系统中查看：

- 访问：`/admin` 路径
- 用户管理模块可查看所有微信登录用户
- 可查看用户的 openid、昵称、头像等信息
- 可管理用户的能量、状态等
