# 卡密资源绑定功能 - 前端实现指南

## 概述
本文档说明如何在管理后台的商品添加/编辑页面中添加卡密资源绑定功能。

## 后端接口（已完成）

### 1. 获取可用卡密资源列表
```
GET /admin/products/card-resources?keyword=xxx
```
**响应示例：**
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
```
POST /admin/products/:id/unbind-card-resource
```

### 3. 商品保存/更新接口已支持 `card_resource_id` 字段
```
POST /admin/products
PUT /admin/products/:id
```
**payload 中添加：**
```json
{
  "card_resource_id": 123  // 0 表示不绑定
}
```

## 前端修改指南

### 文件位置
`public/static/admin/js/card-admin.js`

### 需要修改的函数
`openProductModal(product)` - 约在第 1726 行

### 修改步骤

#### 1. 在函数开头添加卡密资源状态
在 `galleryState` 定义之后添加：
```javascript
const cardResourceState = {
    id: isEdit ? Number(product.card_resource_id || 0) : 0,
    name: isEdit ? String(product.card_resource_name || '') : '',
};
```

#### 2. 在表单中添加卡密资源绑定区域
在"基础信息"section 之后，"图片资源"section 之前添加：
```javascript
+ '<div class="product-form-section">'
+ '<div class="product-form-section-title">卡密资源绑定</div>'
+ '<div class="product-form-grid one">'
+ '<div class="form-row full">'
+ '<label>绑定卡密资源（可选）</label>'
+ '<input id="productCardResourceId" type="hidden" value="' + escapeHtmlAttr(String(cardResourceState.id)) + '">'
+ '<div class="product-card-resource-selector">'
+ '<input id="productCardResourceSearch" placeholder="搜索卡密资源标题..." value="">'
+ '<button type="button" class="btn btn-small" id="searchCardResourceBtn">搜索</button>'
+ (cardResourceState.id > 0
    ? '<button type="button" class="btn btn-danger" id="unbindCardResourceBtn">解除绑定</button>'
    : '')
+ '</div>'
+ '<div id="productCardResourceCurrent" class="product-card-resource-current">'
+ (cardResourceState.id > 0
    ? '<div class="tag tag-green">已绑定：' + escapeHtml(cardResourceState.name) + ' (ID: ' + cardResourceState.id + ')</div>'
    : '<div class="muted">未绑定卡密资源</div>')
+ '</div>'
+ '<div id="productCardResourceResults" class="product-card-resource-results"></div>'
+ '</div>'
+ '</div>'
+ '</div>'
```

#### 3. 在 payload 中添加 card_resource_id 字段
在保存按钮的 onClick 函数中，payload 对象添加：
```javascript
const payload = {
    category_id: valueById('productCategoryId'),
    name: valueById('productName'),
    // ... 其他字段 ...
    description: valueById('productDescription'),
    card_resource_id: valueById('productCardResourceId'),  // 新增这一行
};
```

#### 4. 添加事件处理函数
在 `initProductCategoryCascader(categoryId);` 之后添加：

```javascript
// 卡密资源搜索
const cardResourceSearchInput = document.getElementById('productCardResourceSearch');
const cardResourceIdInput = document.getElementById('productCardResourceId');
const cardResourceCurrentDiv = document.getElementById('productCardResourceCurrent');
const cardResourceResultsDiv = document.getElementById('productCardResourceResults');
const searchCardResourceBtn = document.getElementById('searchCardResourceBtn');
const unbindCardResourceBtn = document.getElementById('unbindCardResourceBtn');

if (searchCardResourceBtn) {
    searchCardResourceBtn.addEventListener('click', async function () {
        const keyword = cardResourceSearchInput.value.trim();
        try {
            const data = await api('/admin/products/card-resources', {
                query: { keyword: keyword }
            });
            
            if (!data || !Array.isArray(data) || data.length === 0) {
                cardResourceResultsDiv.innerHTML = '<div class="empty">未找到匹配的卡密资源</div>';
                return;
            }
            
            cardResourceResultsDiv.innerHTML = '<div class="product-card-resource-list">'
                + data.map(function (item) {
                    return '<div class="product-card-resource-item">'
                        + '<div class="product-card-resource-info">'
                        + '<strong>' + escapeHtml(item.title) + '</strong>'
                        + '<span class="tag tag-blue">' + escapeHtml(item.module_type) + '</span>'
                        + '<span class="tag tag-gray">ID: ' + item.id + '</span>'
                        + '</div>'
                        + '<button type="button" class="btn btn-small" data-select-card-resource="' + item.id + '" data-card-resource-name="' + escapeHtmlAttr(item.title) + '">选择</button>'
                        + '</div>';
                }).join('')
                + '</div>';
            
            // 绑定选择按钮事件
            document.querySelectorAll('[data-select-card-resource]').forEach(function (btn) {
                btn.addEventListener('click', function () {
                    const resourceId = this.dataset.selectCardResource;
                    const resourceName = this.dataset.cardResourceName;
                    cardResourceState.id = Number(resourceId);
                    cardResourceState.name = resourceName;
                    cardResourceIdInput.value = resourceId;
                    cardResourceCurrentDiv.innerHTML = '<div class="tag tag-green">已绑定：' + escapeHtml(resourceName) + ' (ID: ' + resourceId + ')</div>';
                    cardResourceResultsDiv.innerHTML = '';
                    cardResourceSearchInput.value = '';
                    
                    // 显示解除绑定按钮
                    if (unbindCardResourceBtn) {
                        unbindCardResourceBtn.style.display = 'inline-block';
                    }
                });
            });
        } catch (error) {
            alert('搜索失败：' + error.message);
        }
    });
}

if (unbindCardResourceBtn) {
    unbindCardResourceBtn.addEventListener('click', function () {
        if (!confirm('确定要解除卡密资源绑定吗？')) {
            return;
        }
        
        cardResourceState.id = 0;
        cardResourceState.name = '';
        cardResourceIdInput.value = '0';
        cardResourceCurrentDiv.innerHTML = '<div class="muted">未绑定卡密资源</div>';
        cardResourceResultsDiv.innerHTML = '';
        this.style.display = 'none';
    });
}

// 支持回车搜索
if (cardResourceSearchInput) {
    cardResourceSearchInput.addEventListener('keydown', function (event) {
        if (event.key === 'Enter') {
            event.preventDefault();
            searchCardResourceBtn.click();
        }
    });
}
```

### 5. 添加 CSS 样式
在 `public/static/admin/css/card-admin.css` 文件末尾添加：

```css
/* 卡密资源选择器样式 */
.product-card-resource-selector {
    display: flex;
    gap: 8px;
    margin-bottom: 12px;
}

.product-card-resource-selector input {
    flex: 1;
}

.product-card-resource-current {
    margin-bottom: 12px;
    padding: 8px;
    background: #f5f5f5;
    border-radius: 4px;
}

.product-card-resource-results {
    max-height: 300px;
    overflow-y: auto;
}

.product-card-resource-list {
    display: flex;
    flex-direction: column;
    gap: 8px;
}

.product-card-resource-item {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 12px;
    border: 1px solid #e0e0e0;
    border-radius: 4px;
    background: #fff;
}

.product-card-resource-info {
    display: flex;
    align-items: center;
    gap: 8px;
}

.product-card-resource-info strong {
    font-weight: 600;
}
```

## 数据库迁移

在实现前端功能之前，需要先执行数据库迁移脚本：

```bash
php database/add_card_resource_binding.php
```

这将为 `products` 表添加 `card_resource_id` 字段。

## 测试步骤

1. 执行数据库迁移脚本
2. 修改前端代码（按照上述步骤）
3. 添加 CSS 样式
4. 刷新管理后台页面
5. 打开商品添加/编辑页面
6. 测试以下功能：
   - 搜索卡密资源
   - 选择卡密资源进行绑定
   - 解除卡密资源绑定
   - 保存商品时 card_resource_id 正确提交

## 注意事项

1. 卡密资源绑定是可选的，可以不绑定
2. 搜索支持按标题关键词搜索
3. 只显示状态为启用的卡密资源
4. 解除绑定后 card_resource_id 会设置为 0
5. 编辑商品时会显示当前绑定的卡密资源名称

## 后端代码参考

相关后端代码位置：
- 控制器：`app/admin/controller/Product.php`
- 路由：`app/admin/route/route.php`
- 数据库迁移：`database/add_card_resource_binding.php`
