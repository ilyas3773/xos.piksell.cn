// 这个脚本会在 openProductModal 函数中添加卡密绑定功能
const fs = require('fs');

const file = 'public/static/admin/js/card-admin.js';
console.log('正在读取文件...');
let content = fs.readFileSync(file, 'utf8');

// 1. 在 galleryState 之后添加 cardResourceState
const galleryStatePattern = /const galleryState = \{\s+items: normalizeProductGalleryImages\(isEdit \? product\.gallery_images : \[\]\),\s+\};/;
const cardResourceStateCode = `const galleryState = {
            items: normalizeProductGalleryImages(isEdit ? product.gallery_images : []),
        };
        const cardResourceState = {
            id: isEdit ? Number(product.card_resource_id || 0) : 0,
            name: isEdit ? String(product.card_resource_name || '') : '',
            moduleType: isEdit ? String(product.card_resource_module_type || '') : '',
        };`;

if (content.match(galleryStatePattern)) {
    content = content.replace(galleryStatePattern, cardResourceStateCode);
    console.log('✅ 已添加 cardResourceState');
} else {
    console.log('❌ 未找到 galleryState 定义');
}

// 2. 在商品介绍之后添加卡密绑定 HTML
const descriptionPattern = /(\+ '<div class="product-form-section">'\s+\+ '<div class="product-form-section-title">商品介绍<\/div>'\s+\+ '<div class="form-row"><label>介绍内容<\/label><textarea id="productDescription"[^<]+<\/textarea><\/div>'\s+\+ '<\/div>',)/;

const cardBindingHTML = `+ '<div class="product-form-section">'
            + '<div class="product-form-section-title">商品介绍</div>'
            + '<div class="form-row"><label>介绍内容</label><textarea id="productDescription" placeholder="填写商品介绍、使用说明、兑换规则、注意事项等">' + escapeHtml(isEdit ? (product.description || '') : '') + '</textarea></div>'
            + '</div>'
            + '<div class="product-form-section">'
            + '<div class="product-form-section-title">绑定卡密</div>'
            + '<div class="product-form-grid one">'
            + '<div class="form-row full">'
            + '<label>绑定卡密资源（可选）</label>'
            + '<input id="productCardResourceId" type="hidden" value="' + escapeHtmlAttr(String(cardResourceState.id)) + '">'
            + '<div class="product-card-resource-type-selector">'
            + '<label>卡密分类：</label>'
            + '<select id="productCardResourceType">'
            + '<option value="">请选择卡密分类</option>'
            + '<option value="account"' + (cardResourceState.moduleType === 'account' ? ' selected' : '') + '>账号密码类</option>'
            + '<option value="download"' + (cardResourceState.moduleType === 'download' ? ' selected' : '') + '>下载连接类</option>'
            + '<option value="tutorial"' + (cardResourceState.moduleType === 'tutorial' ? ' selected' : '') + '>教程类</option>'
            + '</select>'
            + '</div>'
            + '<div class="product-card-resource-selector">'
            + '<input id="productCardResourceSearch" placeholder="搜索卡密标题或内容..." value=""' + (cardResourceState.moduleType === '' ? ' disabled' : '') + '>'
            + '<button type="button" class="btn btn-small" id="searchCardResourceBtn"' + (cardResourceState.moduleType === '' ? ' disabled' : '') + '>搜索</button>'
            + '</div>'
            + '<div id="productCardResourceCurrent" class="product-card-resource-current">'
            + (cardResourceState.id > 0
                ? '<div class="tag tag-green">已绑定：' + escapeHtml(cardResourceState.name) + ' <span class="tag tag-blue">' + escapeHtml(getModuleTypeName(cardResourceState.moduleType)) + '</span> (ID: ' + cardResourceState.id + ')</div><button type="button" class="btn btn-danger btn-small" id="unbindCardResourceBtn">解除绑定</button>'
                : '<div class="muted">未绑定卡密资源</div>')
            + '</div>'
            + '<div id="productCardResourceResults" class="product-card-resource-results"></div>'
            + '</div>'
            + '</div>'
            + '</div>',`;

// 简化的替换：直接在商品介绍后面添加
const simplePattern = /(\+ '<div class="form-row"><label>介绍内容<\/label><textarea id="productDescription" placeholder="填写商品介绍、使用说明、兑换规则、注意事项等">' \+ escapeHtml\(isEdit \? \(product\.description \|\| ''\) : ''\) \+ '<\/textarea><\/div>'\s+\+ '<\/div>',)/;

const replacement = `+ '<div class="form-row"><label>介绍内容</label><textarea id="productDescription" placeholder="填写商品介绍、使用说明、兑换规则、注意事项等">' + escapeHtml(isEdit ? (product.description || '') : '') + '</textarea></div>'
            + '</div>'
            + '<div class="product-form-section">'
            + '<div class="product-form-section-title">绑定卡密</div>'
            + '<div class="product-form-grid one">'
            + '<div class="form-row full">'
            + '<label>绑定卡密资源（可选）</label>'
            + '<input id="productCardResourceId" type="hidden" value="0">'
            + '<div style="margin-bottom:12px"><label style="margin-right:8px">卡密分类：</label><select id="productCardResourceType" style="padding:6px"><option value="">请选择</option><option value="account">账号密码类</option><option value="download">下载连接类</option><option value="tutorial">教程类</option></select></div>'
            + '<div style="margin-bottom:12px"><input id="productCardResourceSearch" placeholder="搜索卡密..." style="width:70%;margin-right:8px" disabled><button type="button" class="btn btn-small" id="searchCardResourceBtn" disabled>搜索</button></div>'
            + '<div id="productCardResourceCurrent" style="padding:8px;background:#f5f5f5;border-radius:4px;margin-bottom:12px"><div class="muted">未绑定卡密资源</div></div>'
            + '<div id="productCardResourceResults"></div>'
            + '</div>'
            + '</div>'
            + '</div>',`;

if (content.match(simplePattern)) {
    content = content.replace(simplePattern, replacement);
    console.log('✅ 已添加卡密绑定 HTML');
} else {
    console.log('❌ 未找到商品介绍部分');
}

// 3. 在 payload 中添加 card_resource_id
const payloadPattern = /(description: valueById\('productDescription'\),)\s+\};/;
const payloadReplacement = `$1
                            card_resource_id: valueById('productCardResourceId'),
                        };`;

if (content.match(payloadPattern)) {
    content = content.replace(payloadPattern, payloadReplacement);
    console.log('✅ 已在 payload 中添加 card_resource_id');
} else {
    console.log('❌ 未找到 payload 定义');
}

console.log('正在保存文件...');
fs.writeFileSync(file, content, 'utf8');
console.log('✅ 文件已保存！');
console.log('\n请刷新浏览器测试（Ctrl+F5）');
