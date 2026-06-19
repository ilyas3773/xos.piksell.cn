# 商品介绍展开/收起功能文档

## 功能概述
在商品管理页面的商品列表中，商品介绍默认只显示一行，超出部分隐藏。用户可以点击"展开"按钮查看完整介绍，再次点击"收起"按钮恢复为一行显示。

## 实现细节

### 前端实现

#### HTML 结构
**文件**: `public/static/admin/js/card-admin.js`

商品介绍部分的 HTML 结构：
```html
<div class="product-cell-desc collapsible" data-product-id="123">
  这是商品的介绍内容...
  <span class="desc-toggle" data-product-id="123">展开</span>
</div>
```

**关键点**:
1. 有介绍内容的商品会添加 `collapsible` 类
2. 添加 `data-product-id` 属性用于关联
3. 在介绍内容后添加 `desc-toggle` 按钮
4. 无介绍内容的商品不添加展开按钮

#### JavaScript 逻辑
**文件**: `public/static/admin/js/card-admin.js` (约第1555-1570行)

**渲染逻辑**:
```javascript
const hasDescription = item.description && item.description.trim() !== '';
const descriptionHtml = hasDescription
    ? escapeHtml(item.description)
    : '<span class="muted">暂无描述</span>';

// 在渲染时添加 collapsible 类和展开按钮
'<div class="product-cell-desc' + (hasDescription ? ' collapsible' : '') + '" data-product-id="' + item.id + '">'
+ descriptionHtml
+ (hasDescription ? '<span class="desc-toggle" data-product-id="' + item.id + '">展开</span>' : '')
+ '</div>'
```

**事件处理** (约第1680-1700行):
```javascript
// 商品介绍展开/收起
document.querySelectorAll('.desc-toggle').forEach((toggle) => {
    toggle.addEventListener('click', function(e) {
        e.stopPropagation();
        const productId = this.dataset.productId;
        const descContainer = document.querySelector('.product-cell-desc[data-product-id="' + productId + '"]');
        
        if (descContainer) {
            const isExpanded = descContainer.classList.contains('expanded');
            
            if (isExpanded) {
                descContainer.classList.remove('expanded');
                this.textContent = '展开';
            } else {
                descContainer.classList.add('expanded');
                this.textContent = '收起';
            }
        }
    });
});
```

**功能说明**:
1. 阻止事件冒泡，避免触发其他点击事件
2. 根据 `product-id` 找到对应的介绍容器
3. 切换 `expanded` 类来控制展开/收起状态
4. 更新按钮文本（"展开" ↔ "收起"）

#### CSS 样式
**文件**: `public/static/admin/css/card-admin.css` (约第677-720行)

**基础样式**:
```css
.product-cell-desc {
    margin-top: 6px;
    max-width: 320px;
    color: #64748b;
    font-size: 13px;
    word-break: break-word;
    position: relative;
}
```

**收起状态（默认）**:
```css
.product-cell-desc.collapsible {
    display: -webkit-box;
    -webkit-line-clamp: 1;           /* 只显示1行 */
    -webkit-box-orient: vertical;
    overflow: hidden;
    text-overflow: ellipsis;         /* 超出显示省略号 */
    padding-right: 50px;             /* 为展开按钮留出空间 */
}
```

**展开状态**:
```css
.product-cell-desc.collapsible.expanded {
    display: block;
    -webkit-line-clamp: unset;       /* 取消行数限制 */
    overflow: visible;               /* 显示所有内容 */
}
```

**展开/收起按钮样式**:
```css
.desc-toggle {
    position: absolute;
    right: 0;
    bottom: 0;
    background: linear-gradient(90deg, transparent 0%, #ffffff 20%, #ffffff 100%);
    padding-left: 20px;              /* 渐变背景，避免文字被遮挡 */
    color: #0284c7;
    font-size: 12px;
    font-weight: 600;
    cursor: pointer;
    user-select: none;
    transition: color 0.2s;
}

.desc-toggle:hover {
    color: #0369a1;
    text-decoration: underline;
}
```

**展开状态下的按钮**:
```css
.product-cell-desc.expanded .desc-toggle {
    position: static;                /* 不再绝对定位 */
    display: inline-block;
    margin-left: 8px;                /* 跟在文字后面 */
    background: none;
    padding-left: 0;
}
```

## 用户交互流程

1. **默认状态**
   - 商品介绍只显示一行
   - 超出部分用省略号表示
   - 右侧显示蓝色"展开"按钮

2. **点击展开**
   - 介绍内容完整显示
   - 按钮文本变为"收起"
   - 按钮位置移到文字末尾

3. **点击收起**
   - 介绍内容恢复为一行显示
   - 按钮文本变为"展开"
   - 按钮位置回到右侧

4. **无介绍内容**
   - 显示灰色"暂无描述"文字
   - 不显示展开/收起按钮

## 视觉效果

### 收起状态
```
┌─────────────────────────────────────────┐
│ 小小梦魇                                 │
│ little nightmare                         │
│ 这是一款恐怖冒险游戏，玩家将扮演... 展开 │
└─────────────────────────────────────────┘
```

### 展开状态
```
┌─────────────────────────────────────────┐
│ 小小梦魇                                 │
│ little nightmare                         │
│ 这是一款恐怖冒险游戏，玩家将扮演一个    │
│ 穿着黄色雨衣的小女孩，在一艘巨大的船    │
│ 上探索，躲避各种怪物的追捕。游戏画面    │
│ 阴暗诡异，充满了压抑的氛围。 收起       │
└─────────────────────────────────────────┘
```

## 技术特点

1. **CSS 多行截断**: 使用 `-webkit-line-clamp` 实现单行显示
2. **渐变背景**: 展开按钮使用渐变背景，避免遮挡文字
3. **平滑过渡**: 按钮颜色变化有过渡效果
4. **响应式**: 自动适应不同长度的介绍内容
5. **无介绍处理**: 没有介绍内容时不显示展开按钮

## 浏览器兼容性

- **Chrome/Edge**: 完全支持
- **Firefox**: 支持（需要 Firefox 68+）
- **Safari**: 完全支持
- **IE**: 不支持（`-webkit-line-clamp` 不兼容）

## 相关文件

- `public/static/admin/js/card-admin.js` - JavaScript 逻辑
- `public/static/admin/css/card-admin.css` - CSS 样式

## 更新日期
2026-04-22
