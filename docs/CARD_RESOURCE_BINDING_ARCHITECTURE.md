# 卡密资源绑定功能 - 架构说明

## 系统架构图

```
┌─────────────────────────────────────────────────────────────┐
│                        前端管理页面                           │
│                  (admin-products.html)                       │
│                                                              │
│  ┌────────────────────────────────────────────────────┐    │
│  │          商品添加/编辑表单                           │    │
│  │                                                     │    │
│  │  ┌──────────────────────────────────────────┐     │    │
│  │  │  基础信息（名称、价格、状态等）            │     │    │
│  │  └──────────────────────────────────────────┘     │    │
│  │                                                     │    │
│  │  ┌──────────────────────────────────────────┐     │    │
│  │  │  卡密资源绑定（新增功能）                 │     │    │
│  │  │  ┌────────────────────────────────┐      │     │    │
│  │  │  │ 搜索框 + 搜索按钮               │      │     │    │
│  │  │  └────────────────────────────────┘      │     │    │
│  │  │  ┌────────────────────────────────┐      │     │    │
│  │  │  │ 当前绑定状态显示                │      │     │    │
│  │  │  └────────────────────────────────┘      │     │    │
│  │  │  ┌────────────────────────────────┐      │     │    │
│  │  │  │ 搜索结果列表（可选择）          │      │     │    │
│  │  │  └────────────────────────────────┘      │     │    │
│  │  └──────────────────────────────────────────┘     │    │
│  │                                                     │    │
│  │  ┌──────────────────────────────────────────┐     │    │
│  │  │  图片资源、商品规格、商品介绍等           │     │    │
│  │  └──────────────────────────────────────────┘     │    │
│  └────────────────────────────────────────────────────┘    │
└─────────────────────────────────────────────────────────────┘
                            │
                            │ AJAX 请求
                            ▼
┌─────────────────────────────────────────────────────────────┐
│                        后端 API                              │
│                  (Product Controller)                        │
│                                                              │
│  ┌────────────────────────────────────────────────────┐    │
│  │  GET /admin/products/card-resources                │    │
│  │  - 获取可用卡密资源列表                             │    │
│  │  - 支持关键词搜索                                   │    │
│  └────────────────────────────────────────────────────┘    │
│                                                              │
│  ┌────────────────────────────────────────────────────┐    │
│  │  POST /admin/products                              │    │
│  │  PUT /admin/products/:id                           │    │
│  │  - 创建/更新商品                                    │    │
│  │  - 支持 card_resource_id 字段                      │    │
│  └────────────────────────────────────────────────────┘    │
│                                                              │
│  ┌────────────────────────────────────────────────────┐    │
│  │  POST /admin/products/:id/unbind-card-resource     │    │
│  │  - 解除卡密资源绑定                                 │    │
│  └────────────────────────────────────────────────────┘    │
└─────────────────────────────────────────────────────────────┘
                            │
                            │ ORM 查询
                            ▼
┌─────────────────────────────────────────────────────────────┐
│                        数据库层                              │
│                      (MySQL 8.0)                             │
│                                                              │
│  ┌────────────────────────────────────────────────────┐    │
│  │  products 表                                        │    │
│  │  ┌──────────────────────────────────────────┐     │    │
│  │  │ id (主键)                                 │     │    │
│  │  │ name (商品名称)                           │     │    │
│  │  │ category_id (分类ID)                      │     │    │
│  │  │ card_resource_id (卡密资源ID) ← 新增      │     │    │
│  │  │ exchange_energy (兑换能量)                │     │    │
│  │  │ status (状态)                             │     │    │
│  │  │ ...                                       │     │    │
│  │  └──────────────────────────────────────────┘     │    │
│  └────────────────────────────────────────────────────┘    │
│                            │                                 │
│                            │ 外键关联                         │
│                            ▼                                 │
│  ┌────────────────────────────────────────────────────┐    │
│  │  card_resources 表                                  │    │
│  │  ┌──────────────────────────────────────────┐     │    │
│  │  │ id (主键)                                 │     │    │
│  │  │ title (卡密资源标题)                      │     │    │
│  │  │ module_type (类型)                        │     │    │
│  │  │ product_id (关联商品ID)                   │     │    │
│  │  │ status (状态)                             │     │    │
│  │  │ ...                                       │     │    │
│  │  └──────────────────────────────────────────┘     │    │
│  └────────────────────────────────────────────────────┘    │
└─────────────────────────────────────────────────────────────┘
```

## 数据流程图

### 1. 搜索卡密资源流程

```
用户输入关键词
    │
    ▼
点击"搜索"按钮
    │
    ▼
前端发送 AJAX 请求
GET /admin/products/card-resources?keyword=xxx
    │
    ▼
后端 Product Controller
    │
    ├─ 查询 card_resources 表
    ├─ 过滤条件：status=1（启用）
    ├─ 关键词匹配：title LIKE %keyword%
    └─ 排序：sort ASC, id DESC
    │
    ▼
返回 JSON 数据
    │
    ▼
前端渲染搜索结果列表
    │
    ▼
用户点击"选择"按钮
    │
    ▼
更新隐藏字段 card_resource_id
更新绑定状态显示
```

### 2. 保存商品流程

```
用户填写商品信息
    │
    ▼
选择卡密资源（可选）
    │
    ▼
点击"保存"按钮
    │
    ▼
前端收集表单数据
包括 card_resource_id
    │
    ▼
发送 POST/PUT 请求
    │
    ▼
后端验证数据
    │
    ├─ 验证必填字段
    ├─ 验证分类ID
    └─ 验证 card_resource_id（可选）
    │
    ▼
保存到数据库
products.card_resource_id = xxx
    │
    ▼
返回成功响应
    │
    ▼
前端关闭弹窗
刷新商品列表
```

### 3. 解除绑定流程

```
用户点击"解除绑定"按钮
    │
    ▼
前端显示确认对话框
    │
    ▼
用户确认
    │
    ▼
发送 POST 请求
/admin/products/:id/unbind-card-resource
    │
    ▼
后端更新数据库
SET card_resource_id = 0
    │
    ▼
返回成功响应
    │
    ▼
前端更新UI
显示"未绑定"状态
```

## 模型关联关系

```
Product (商品)
    │
    │ belongsTo (多对一)
    ▼
CardResource (卡密资源)

关联字段：
- Product.card_resource_id → CardResource.id
- 默认值：0（表示未绑定）
```

## 文件依赖关系

```
前端文件依赖：
admin-products.html
    │
    ├─ card-admin.js (主逻辑)
    │   └─ openProductModal() 函数
    │       └─ 需要添加卡密资源绑定功能
    │
    ├─ card-resource-binding-patch.js (补丁，可选)
    │   └─ CardResourceBindingHelper 辅助类
    │
    └─ card-resource-binding.css (样式)
        └─ 卡密资源选择器样式

后端文件依赖：
Product Controller
    │
    ├─ ProductModel (商品模型)
    │   └─ cardResource() 关联方法
    │
    ├─ CardResourceModel (卡密资源模型)
    │
    └─ CategoryService (分类服务)
```

## 数据库表关系

```sql
-- products 表结构（部分字段）
CREATE TABLE `products` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `category_id` bigint unsigned NOT NULL,
  `card_resource_id` bigint unsigned NOT NULL DEFAULT 0,  -- 新增字段
  `name` varchar(120) NOT NULL,
  `exchange_energy` int NOT NULL DEFAULT 0,
  `status` tinyint NOT NULL DEFAULT 1,
  PRIMARY KEY (`id`),
  KEY `idx_card_resource_id` (`card_resource_id`)  -- 新增索引
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- card_resources 表结构（部分字段）
CREATE TABLE `card_resources` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `title` varchar(120) NOT NULL DEFAULT '',  -- 卡密资源标题
  `module_type` enum('account','download','tutorial') NOT NULL,
  `product_id` bigint unsigned NOT NULL DEFAULT 0,
  `status` tinyint NOT NULL DEFAULT 1,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
```

## 接口规范

### 请求格式

```javascript
// 搜索卡密资源
GET /admin/products/card-resources?keyword=Roblox
Headers: {
  'Authorization': 'Bearer <token>',
  'Accept': 'application/json'
}

// 保存商品
POST /admin/products
Headers: {
  'Authorization': 'Bearer <token>',
  'Content-Type': 'application/x-www-form-urlencoded'
}
Body: {
  name: 'Roblox 1000 Robux',
  category_id: 1,
  card_resource_id: 5,  // 0 表示不绑定
  exchange_energy: 100,
  status: 1
}
```

### 响应格式

```javascript
// 成功响应
{
  "code": 0,
  "msg": "success",
  "data": { ... }
}

// 错误响应
{
  "code": 400,
  "msg": "错误信息",
  "data": null
}
```

## 安全考虑

1. **权限验证**: 所有接口都需要管理员 token 验证
2. **数据验证**: 后端验证所有输入数据
3. **SQL 注入防护**: 使用 ORM 参数化查询
4. **XSS 防护**: 前端使用 escapeHtml 转义输出
5. **CSRF 防护**: 使用 token 验证

## 性能优化

1. **索引优化**: card_resource_id 字段添加索引
2. **关联查询**: 使用 with() 预加载关联数据
3. **分页查询**: 卡密资源列表支持分页
4. **缓存策略**: 可考虑缓存常用卡密资源列表

## 扩展性

1. **多对多关联**: 未来可扩展为商品绑定多个卡密资源
2. **批量操作**: 可添加批量绑定/解绑功能
3. **历史记录**: 可记录绑定变更历史
4. **权限细化**: 可针对不同管理员设置不同权限

## 测试用例

### 单元测试
- [ ] 测试 getCardResources() 方法
- [ ] 测试 unbindCardResource() 方法
- [ ] 测试 Product 模型的 cardResource 关联
- [ ] 测试数据验证规则

### 集成测试
- [ ] 测试完整的绑定流程
- [ ] 测试搜索功能
- [ ] 测试解绑功能
- [ ] 测试边界条件（空关键词、不存在的ID等）

### UI 测试
- [ ] 测试搜索框交互
- [ ] 测试选择按钮功能
- [ ] 测试解绑按钮功能
- [ ] 测试响应式布局

## 部署清单

- [ ] 执行数据库迁移脚本
- [ ] 部署后端代码
- [ ] 部署前端代码
- [ ] 部署 CSS 样式文件
- [ ] 清除缓存
- [ ] 测试所有功能
- [ ] 备份数据库

## 监控指标

- API 响应时间
- 数据库查询性能
- 错误率
- 用户使用频率
