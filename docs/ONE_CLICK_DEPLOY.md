# 一键部署 / 安装向导

这个项目带了一键安装向导。**部署时不需要手动导入数据库**。把项目上传到服务器后，访问域名即可看到安装页面。

## 部署流程

### 1. 在 HBuilder 里编译 Web

1. 用 HBuilder 打开 `is/` 项目
2. 菜单：**发行 → 网站 - PC Web 或手机 H5**（标准发行即可）
3. 产物在 `is/unpackage/dist/build/web/`

### 2. 把 Web 产物复制到 `public/`

在项目根目录执行：

```bash
php scripts/deploy_web.php
```

脚本会：
- 清理 `public/index.html`、`public/assets/`、`public/static/`
- 从 `is/unpackage/dist/build/web/` 复制最新的 `index.html`、`assets/`、`static/` 到 `public/`

**为什么不直接放在 `is/`**：`public/` 是 Apache 的 Web 根。把 uni-app 编译产物放这里，访问 `https://你的域名/` 就能直接看到前端首页，且 `/api/*` 仍然走 ThinkPHP。

### 3. 上传项目到服务器

把整个项目（**不要**上传 `is/unpackage/`、`database/*.db`、`config/install.lock`）上传到服务器，例如：

```
/var/www/h5.saile.icu/
├── app/            ← ThinkPHP 业务代码
├── config/         ← 配置
├── database/       ← SQL 文件 + SQLite 数据库（运行时生成）
├── public/         ← Web 根（含 uni-app 编译产物 + index.php）
├── scripts/        ← 部署脚本
├── runtime/        ← 运行时缓存
├── vendor/         ← composer 依赖
├── .env            ← 运行时配置（安装后由向导生成）
└── ...
```

> **别忘了**：`vendor/` 在本地 `composer install` 后再上传；服务器上 `storage/`、`runtime/` 要有写权限。

### 4. 浏览器访问域名

#### 情况 A：未安装（首次部署）

打开 `https://h5.saile.icu/`，浏览器会**自动重定向到 `/install`**，显示安装向导。

向导流程：
1. **选数据库类型**：MySQL 或 SQLite
2. **填数据库信息**：
   - MySQL：host、port、db_name、user、pass、charset
   - SQLite：数据库文件名（默认 `xos_piksell_cn.db`）
3. **点 "Test Connection"**：先测试连通性（避免装到一半失败）
4. **填管理员账号**：用户名、昵称、密码
5. **填 JWT 信息**：Secret 自动生成，Issuer 默认填域名
6. **点 "Install Now"**：向导自动
   - 创建数据库（如果选 MySQL 且没权限，提示先去创建）
   - 导入表结构（MySQL 跑 `database/schema.sql` 等；SQLite 用内置的 SQLite DDL）
   - 创建管理员账号
   - 写 `.env`
   - 创建 `config/install.lock`（锁定安装器）
7. 5 秒后自动跳转到 `/login.html`（后台登录）

#### 情况 B：已安装

直接打开 `https://h5.saile.icu/` → uni-app 编译后的 Web 前端首页。
打开 `https://h5.saile.icu/login.html` → 后台登录。
打开 `https://h5.saile.icu/install` → 显示「已安装」状态页（不会重新安装）。

## MySQL vs SQLite 怎么选

| 维度 | MySQL | SQLite |
|---|---|---|
| 适用场景 | 生产 / 多服务器 | 演示 / 单机 / 不想装 MySQL |
| 性能 | 更好 | 一般，但足够中等流量 |
| 备份 | `mysqldump` | 直接复制 `.db` 文件 |
| 部署 | 需要 MySQL 服务 | PHP 自带，零依赖 |
| 多写入 | 支持 | 单写多读 |

**建议**：生产用 MySQL；演示 / 小项目用 SQLite 更快上手。

## 重新安装

1. 删 `config/install.lock`
2. 如果是 MySQL，删/清空数据库
3. 访问 `https://h5.saile.icu/install`

## 文件结构速查

| 路径 | 作用 |
|---|---|
| `app/service/InstallerService.php` | 安装核心逻辑（建库、建表、写 .env、加锁），支持 MySQL / SQLite |
| `app/index/controller/Install.php` | 安装向导 UI（`/install`、`/elyas`） |
| `app/index/middleware/InstallMiddleware.php` | 未安装时拦截所有请求跳到 `/install`；API 请求返 503 JSON |
| `config/database.php` | 数据库连接配置（同时声明 mysql、sqlite 两个连接） |
| `scripts/deploy_web.php` | 部署脚本：uni-app 编译产物 → `public/` |
| `public/.htaccess` | Apache 路由：`/` → `index.html` 优先，否则 rewrite 到 `index.php` |
| `public/index.html` | uni-app 前端首页（部署脚本生成） |
| `public/assets/` | uni-app 前端 JS / CSS（部署脚本生成） |
| `public/login.html` | 后台登录页（已有，手写） |
| `public/admin*.html` | 后台各管理页（已有） |

## 故障排查

- **访问 `/` 还是显示 JSON**：`public/index.html` 没生成。先 `php scripts/deploy_web.php`，再清浏览器缓存。
- **Test Connection 失败**：先在 MySQL 客户端验证账号、密码、host、port。
- **SQLite 报 "Permission denied"**：`database/` 目录没有写权限。`chmod -R 775 database/`。
- **API 返 503 + `installed: false`**：未完成安装。访问 `/install`。
- **安装后登录报 401**：检查 `.env` 里 `DB_DRIVER`、`DB_TYPE` 是否对（MySQL 应该是 `mysql`，SQLite 是 `sqlite`）。
- **切换数据库类型后仍报错**：清 `runtime/` 缓存。`rm -rf runtime/* && php scripts/clear_cache.php`（如有）。
- **SQLite 报 "no such table"**：本项目已修复 ThinkPHP SQLite builder 的反引号 bug（修改 `vendor/topthink/think-orm/src/db/builder/Sqlite.php`）。如果是干净的 composer 仓库请重新打补丁。

## 注意事项

- **vendor/ 改动持久化**：项目修复了 `think-orm` 的 SQLite builder（反引号 → 双引号），修改在 `vendor/topthink/think-orm/src/db/builder/Sqlite.php`。`composer update` 会覆盖这个改动。如需长期稳定，建议在 `composer.json` 引入 `cweagans/composer-patches` 之类的补丁插件。
- **uni-app 重新发布后**：必须再跑一次 `php scripts/deploy_web.php`。
- **首次访问被 `https` 拦截**：当前 manifest.json 配置的 `web.devServer.https=false` 适合本地调试；部署到生产时，HTTPS 证书由 web server（Nginx/Apache）负责，不是 dev server。
