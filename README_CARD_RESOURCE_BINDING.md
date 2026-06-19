# 商品卡密资源绑定功能

## 📋 功能概述

为 Piksell 发卡系统的商品管理模块添加了卡密资源绑定功能。管理员可以在商品添加/编辑页面将商品与卡密资源进行关联。

### 主要特性

- ✅ 搜索卡密资源（按标题关键词）
- ✅ 选择卡密资源进行绑定
- ✅ 解除卡密资源绑定
- ✅ 显示当前绑定状态
- ✅ 可选绑定（不强制）
- ✅ 支持回车键快速搜索
- ✅ 响应式设计，支持移动端

## 🚀 快速开始

### 第一步：执行数据库迁移

```bash
# 如果 PHP 在 PATH 中
php database/add_card_resource_binding.php

# 如果 PHP 不在 PATH 中，使用完整路径
D:\phpstudy_pro\Extensions\php\php8.0.2nts\php.exe database/add_card_resource_binding.php
```

或者直接在数据库中执行 SQL：

```sql
ALTER TABLE `products` 
ADD COLUMN `card_resource_id` BIGINT UNSIGNED NOT NULL DEFAULT 0 COMMENT '关联的卡密资源ID' 
AFTER `kind_category_id`;

ALTER TABLE `products` 
ADD INDEX `idx_card_resource_id` (`card_resource_id`);
```

### 第二步：修改前端代码

详细步骤请查看：[前端实现指南](docs/CARD_RESOURCE_BINDING_FRONTEND_GUIDE.md)

**快速集成**：

1. 引入 CSS 样式（在 `admin-products.html` 的 `<head>` 中）：
```html
<link rel="stylesheet" href="/static/admin/css/card-resource-binding.css">
```

2. 按照指南修改 `public/static/admin/js/card-admin.js` 中的 `openProductModal` 函数

3. 参考 `public/static/admin/js/card-resource-binding-patch.js` 中的实现代码

### 第三步：测试功能

1. 打开管理后台商品管理页面
2. 点击"新增商品"或"编辑"按钮
3. 在表单中找到"卡密资源绑定"区域
4. 测试搜索、选择、解绑功能

## 📁 项目结构

```
xos.piksell.cn/
├── app/
│   ├── admin/
│   │   ├── controller/
│   │   │   └── Product.php              (已修改 - 添加卡密资源相关方法)
│   │   └── route/
│   │       └── route.php                (已修改 - 添加新路由)
│   └── model/
│       └── Product.php                  (已修改 - 添加 cardResource 关联)
├── database/
│   └── add_card_resource_binding.php    (新建 - 数据库迁移脚本)
├── docs/
│   ├── CARD_RESOURCE_BINDING_SUMMARY.md           (功能总结)
│   ├── CARD_RESOURCE_BINDING_FRONTEND_GUIDE.md    (前端实现指南)
│   ├── CARD_RESOURCE_BINDING_ARCHITECTURE.md      (架构说明)
│   └── DATABASE_MIGRATION_GUIDE.md                (数据库迁移指南)
├── public/
│   └── static/
│       └── admin/
│           ├── js/
│           │   ├── card-admin.js                      (待修改)
│           │   └── card-resource-binding-patch.js    (新建 - 补丁代码)
│           └── css/
│               └── card-resource-binding.css         (新建 - 样式文件)
├── 商品卡密资源绑定功能-实现说明.md    (中文说明)
└── README_CARD_RESOURCE_BINDING.md     (本文档)
```

## 🔌 API 接口

### 1. 获取可用卡密资源列表

```http
GET /admin/products/card-resources?keyword=xxx
Authorization: Bearer <token>
```

**响应示例**：
```json
{
  "code": 0,
  "msg": "success",
  "data": [
    {
      "id": 1,
      "title": "Roblox账号资源",
      "module_type": "account",
      "status": 1,
      "product_id": 0,
      "is_common": 1
    }
  ]
}
```

### 2. 解除卡密资源绑定

```http
POST /admin/products/:id/unbind-card-resource
Authorization: Bearer <token>
```

**响应示例**：
```json
{
  "code": 0,
  "msg": "已解除绑定",
  "data": {
    "id": 1,
    "card_resource_id": 0
  }
}
```

### 3. 创建/更新商品（已支持 card_resource_id）

```http
POST /admin/products
PUT /admin/products/:id
Authorization: Bearer <token>
Content-Type: application/x-www-form-urlencoded

name=Roblox 1000 Robux
category_id=1
card_resource_id=5
exchange_energy=100
status=1
```

## 💻 前端实现示例

### HTML 结构

```html
<div class="product-form-section">
    <div class="product-form-section-title">卡密资源绑定</div>
    <div class="product-form-grid one">
        <div class="form-row full">
            <label>绑定卡密资源（可选）</label>
            <input id="productCardResourceId" type="hidden" value="0">
            <div class="product-card-resource-selector">
                <input id="productCardResourceSearch" placeholder="搜索卡密资源标题...">
                <button type="button" class="btn btn-small" id="searchCardResourceBtn">搜索</button>
            </div>
            <div id="productCardResourceCurrent" class="product-card-resource-current">
                <div class="muted">未绑定卡密资源</div>
            </div>
            <div id="productCardResourceResults" class="product-card-resource-results"></div>
        </div>
    </div>
</div>
```

### JavaScript 事件处理

```javascript
// 搜索卡密资源
searchCardResourceBtn.addEventListener('click', async function () {
    const keyword = cardResourceSearchInput.value.trim();
    const data = await api('/admin/products/card-resources', {
        query: { keyword: keyword }
    });
    
    // 渲染搜索结果
    cardResourceResultsDiv.innerHTML = renderCardResourceList(data);
});

// 选择卡密资源
document.querySelectorAll('[data-select-card-resource]').forEach(function (btn) {
    btn.addEventListener('click', function () {
        const resourceId = this.dataset.selectCardResource;
        cardResourceIdInput.value = resourceId;
        // 更新UI显示
    });
});
```

## 🗄️ 数据库设计

### products 表（新增字段）

| 字段名 | 类型 | 默认值 | 说明 |
|--------|------|--------|------|
| card_resource_id | bigint unsigned | 0 | 关联的卡密资源ID，0表示未绑定 |

**索引**：
- `idx_card_resource_id` - 提高查询性能

### 关联关系

```
products.card_resource_id → card_resources.id (多对一)
```

## 📚 文档导航

- **[功能总结](docs/CARD_RESOURCE_BINDING_SUMMARY.md)** - 完整的功能说明和实现清单
- **[前端实现指南](docs/CARD_RESOURCE_BINDING_FRONTEND_GUIDE.md)** - 详细的前端代码修改步骤
- **[架构说明](docs/CARD_RESOURCE_BINDING_ARCHITECTURE.md)** - 系统架构和数据流程图
- **[数据库迁移指南](docs/DATABASE_MIGRATION_GUIDE.md)** - 数据库迁移详细说明
- **[中文实现说明](商品卡密资源绑定功能-实现说明.md)** - 简明的中文说明文档

## ✅ 实现清单

### 后端部分（已完成）

- [x] 数据库迁移脚本
- [x] Product 控制器添加 getCardResources() 方法
- [x] Product 控制器添加 unbindCardResource() 方法
- [x] Product 控制器的 save() 和 update() 方法支持 card_resource_id
- [x] Product 模型添加 cardResource 关联
- [x] 路由配置添加新接口
- [x] 商品列表和详情接口返回 card_resource_name

### 前端部分（待完成）

- [ ] 执行数据库迁移脚本
- [ ] 修改 card-admin.js 的 openProductModal 函数
- [ ] 引入 CSS 样式文件
- [ ] 测试搜索功能
- [ ] 测试选择绑定功能
- [ ] 测试解除绑定功能
- [ ] 测试保存商品功能

### 文档部分（已完成）

- [x] 功能总结文档
- [x] 前端实现指南
- [x] 架构说明文档
- [x] 数据库迁移指南
- [x] 中文实现说明
- [x] README 文档

## 🧪 测试用例

### 功能测试

1. **搜索卡密资源**
   - [ ] 输入关键词搜索
   - [ ] 空关键词搜索（显示全部）
   - [ ] 搜索不存在的关键词（显示空结果）
   - [ ] 回车键快速搜索

2. **选择绑定**
   - [ ] 点击选择按钮绑定卡密资源
   - [ ] 绑定后显示当前绑定状态
   - [ ] 保存商品时 card_resource_id 正确提交

3. **解除绑定**
   - [ ] 点击解除绑定按钮
   - [ ] 确认对话框正常显示
   - [ ] 解绑后状态更新为"未绑定"

4. **编辑商品**
   - [ ] 打开已绑定商品，显示当前绑定信息
   - [ ] 可以修改绑定
   - [ ] 可以解除绑定

5. **创建商品**
   - [ ] 可以不绑定卡密资源创建商品
   - [ ] 可以绑定卡密资源创建商品

### 兼容性测试

- [ ] Chrome 浏览器
- [ ] Firefox 浏览器
- [ ] Edge 浏览器
- [ ] Safari 浏览器
- [ ] 移动端浏览器

## 🐛 常见问题

### Q1: 数据库迁移失败

**A**: 检查以下几点：
1. PHP 路径是否正确
2. 数据库连接配置是否正确（.env 文件）
3. 数据库用户是否有 ALTER TABLE 权限
4. 字段是否已经存在（重复执行）

### Q2: 搜索不到卡密资源

**A**: 检查以下几点：
1. 数据库中是否有卡密资源数据
2. 卡密资源的 status 是否为 1（启用）
3. 搜索关键词是否正确
4. 后端 API 是否正常返回数据

### Q3: 保存商品时 card_resource_id 未提交

**A**: 检查以下几点：
1. 隐藏字段 `productCardResourceId` 的值是否正确
2. 表单提交时是否包含该字段
3. 后端是否正确接收该字段

### Q4: 前端样式显示异常

**A**: 检查以下几点：
1. CSS 文件是否正确引入
2. 浏览器是否缓存了旧的 CSS
3. CSS 选择器是否与现有样式冲突

## 🔧 技术栈

- **后端**: PHP 8 + ThinkPHP 8
- **前端**: 原生 JavaScript (ES5)
- **数据库**: MySQL 8.0
- **样式**: CSS3 + Flexbox

## 📝 更新日志

### v1.0.0 (2026-04-22)

- ✨ 新增商品卡密资源绑定功能
- ✨ 新增卡密资源搜索接口
- ✨ 新增解除绑定接口
- 🔧 修改 Product 控制器和模型
- 📝 完善文档和实现指南

## 👥 贡献者

- Kiro AI Assistant

## 📄 许可证

本项目遵循 Piksell 发卡系统的许可证。

## 🔗 相关链接

- [Piksell 发卡系统](http://xos.piksell.cn)
- [ThinkPHP 8 文档](https://www.kancloud.cn/manual/thinkphp8/1847991)
- [MySQL 8.0 文档](https://dev.mysql.com/doc/refman/8.0/en/)

---

**需要帮助？** 请查看 [前端实现指南](docs/CARD_RESOURCE_BINDING_FRONTEND_GUIDE.md) 或 [中文实现说明](商品卡密资源绑定功能-实现说明.md)
