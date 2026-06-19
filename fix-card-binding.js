// 修复卡密绑定功能 - 删除重复的 openProductModal 函数并在正确的函数中添加卡密绑定
const fs = require('fs');

const file = 'public/static/admin/js/card-admin.js';
console.log('正在读取文件...');
let content = fs.readFileSync(file, 'utf8');

// 步骤 1: 删除第一个 openProductModal 函数 (从 line 1697 开始到 line 2171 结束)
console.log('\n步骤 1: 删除第一个 openProductModal 函数...');
const firstFunctionStart = '    function openProductModal(product) {\n        const isEdit = !!product;\n        const categoryId = isEdit';
const firstFunctionEnd = '        renderRows();\n    }\n\n    async function openProductModal(product) {';

if (content.includes(firstFunctionStart) && content.includes(firstFunctionEnd)) {
    const startIndex = content.indexOf(firstFunctionStart);
    const endIndex = content.indexOf(firstFunctionEnd);
    
    if (startIndex !== -1 && endIndex !== -1 && startIndex < endIndex) {
        // 删除第一个函数，保留 async function 的开始
        content = content.substring(0, startIndex) + '    async function openProductModal(product) {' + content.substring(endIndex + firstFunctionEnd.length);
        console.log('✅ 已删除第一个 openProductModal 函数');
    } else {
        console.log('❌ 无法定位第一个函数的位置');
    }
} else {
    console.log('⚠️  未找到重复的函数，可能已经删除');
}

// 步骤 2: 在第二个 async 函数中添加 cardResourceState
console.log('\n步骤 2: 添加 cardResourceState...');
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
    console.log('⚠️  未找到 galleryState 或已经添加过');
}

// 步骤 3: 在商品介绍后添加卡密绑定 HTML
console.log('\n步骤 3: 添加卡密绑定 HTML...');
const descriptionSectionPattern = /(\+ '<div class="product-form-section">'\s+\+ '<div class="product-form-section-title">商品介绍<\/div>'\s+\+ '<div class="form-row"><label>介绍内容<\/label><textarea id="productDescription" placeholder="填写商品介绍、使用说明、兑换规则、注意事项等">' \+ escapeHtml\(isEdit \? \(product\.description \|\| ''\) : ''\) \+ '<\/textarea><\/div>'\s+\+ '<\/div>',)/;

const cardBindingSection = `+ '<div class="product-form-section">'
            + '<div class="product-form-section-title">商品介绍</div>'
            + '<div class="form-row"><label>介绍内容</label><textarea id="productDescription" placeholder="填写商品介绍、使用说明、兑换规则、注意事项等">' + escapeHtml(isEdit ? (product.description || '') : '') + '</textarea></div>'
            + '</div>'
            + '<div class="product-form-section">'
            + '<div class="product-form-section-title">绑定卡密资源</div>'
            + '<div class="product-form-grid one">'
            + '<div class="form-row full">'
            + '<label>绑定卡密资源（可选）</label>'
            + '<input id="productCardResourceId" type="hidden" value="' + escapeHtmlAttr(String(cardResourceState.id)) + '">'
            + '<div style="margin-bottom:12px">'
            + '<label style="margin-right:8px;font-weight:600">卡密分类：</label>'
            + '<select id="productCardResourceType" style="padding:8px;min-width:150px">'
            + '<option value="">请选择卡密分类</option>'
            + '<option value="account"' + (cardResourceState.moduleType === 'account' ? ' selected' : '') + '>账号密码类</option>'
            + '<option value="download"' + (cardResourceState.moduleType === 'download' ? ' selected' : '') + '>下载连接类</option>'
            + '<option value="tutorial"' + (cardResourceState.moduleType === 'tutorial' ? ' selected' : '') + '>教程类</option>'
            + '</select>'
            + '</div>'
            + '<div style="margin-bottom:12px;display:flex;gap:8px">'
            + '<input id="productCardResourceSearch" placeholder="搜索卡密标题、用户名、URL、内容..." style="flex:1;padding:8px"' + (cardResourceState.moduleType ? '' : ' disabled') + '>'
            + '<button type="button" class="btn btn-small" id="searchCardResourceBtn"' + (cardResourceState.moduleType ? '' : ' disabled') + '>搜索</button>'
            + '</div>'
            + '<div id="productCardResourceCurrent" style="padding:12px;background:#f5f5f5;border-radius:4px;margin-bottom:12px">'
            + (cardResourceState.id > 0
                ? '<div style="display:flex;align-items:center;justify-content:space-between"><div><strong>已绑定：</strong>' + escapeHtml(cardResourceState.name) + ' <span class="tag tag-blue">' + escapeHtml(cardResourceState.moduleType === 'account' ? '账号密码类' : cardResourceState.moduleType === 'download' ? '下载连接类' : '教程类') + '</span> <span class="muted">(ID: ' + cardResourceState.id + ')</span></div><button type="button" class="btn btn-danger btn-small" id="unbindCardResourceBtn">解除绑定</button></div>'
                : '<div class="muted">未绑定卡密资源</div>')
            + '</div>'
            + '<div id="productCardResourceResults" style="max-height:300px;overflow-y:auto"></div>'
            + '</div>'
            + '</div>'
            + '</div>',`;

if (content.match(descriptionSectionPattern)) {
    content = content.replace(descriptionSectionPattern, cardBindingSection);
    console.log('✅ 已添加卡密绑定 HTML');
} else {
    console.log('❌ 未找到商品介绍部分');
}

// 步骤 4: 在 payload 中添加 card_resource_id
console.log('\n步骤 4: 在 payload 中添加 card_resource_id...');
const payloadPattern = /(description: valueById\('productDescription'\),)\s+\};/;
const payloadReplacement = `$1
                            card_resource_id: valueById('productCardResourceId'),
                        };`;

if (content.match(payloadPattern)) {
    content = content.replace(payloadPattern, payloadReplacement);
    console.log('✅ 已在 payload 中添加 card_resource_id');
} else {
    console.log('⚠️  未找到 payload 或已经添加过');
}

// 步骤 5: 在事件处理器之前添加卡密绑定的事件处理代码
console.log('\n步骤 5: 添加卡密绑定事件处理代码...');
const eventHandlerInsertPoint = '        const coverUrlInput = document.getElementById(\'productCoverUrlInput\');';
const cardBindingEventHandlers = `        // 卡密绑定事件处理
        const cardResourceTypeSelect = document.getElementById('productCardResourceType');
        const cardResourceSearchInput = document.getElementById('productCardResourceSearch');
        const cardResourceSearchBtn = document.getElementById('searchCardResourceBtn');
        const cardResourceCurrentDiv = document.getElementById('productCardResourceCurrent');
        const cardResourceResultsDiv = document.getElementById('productCardResourceResults');
        const cardResourceIdInput = document.getElementById('productCardResourceId');

        // 选择卡密分类后启用搜索
        cardResourceTypeSelect.addEventListener('change', function() {
            const moduleType = this.value;
            const hasType = moduleType !== '';
            cardResourceSearchInput.disabled = !hasType;
            cardResourceSearchBtn.disabled = !hasType;
            cardResourceResultsDiv.innerHTML = '';
            if (!hasType) {
                cardResourceSearchInput.value = '';
            }
        });

        // 搜索卡密资源
        async function searchCardResources() {
            const moduleType = cardResourceTypeSelect.value;
            const keyword = cardResourceSearchInput.value.trim();
            
            if (!moduleType) {
                alert('请先选择卡密分类');
                return;
            }

            try {
                cardResourceSearchBtn.disabled = true;
                cardResourceSearchBtn.textContent = '搜索中...';
                
                const params = new URLSearchParams({ module_type: moduleType });
                if (keyword) {
                    params.append('keyword', keyword);
                }
                
                const response = await api('/admin/products/card-resources?' + params.toString());
                const resources = response.data || [];
                
                if (resources.length === 0) {
                    cardResourceResultsDiv.innerHTML = '<div style="padding:12px;text-align:center;color:#999">未找到匹配的卡密资源</div>';
                } else {
                    cardResourceResultsDiv.innerHTML = resources.map(resource => {
                        const typeName = resource.module_type === 'account' ? '账号密码类' : resource.module_type === 'download' ? '下载连接类' : '教程类';
                        return '<div style="padding:10px;border:1px solid #e0e0e0;border-radius:4px;margin-bottom:8px;cursor:pointer;transition:background 0.2s" data-card-resource-id="' + resource.id + '" data-card-resource-name="' + escapeHtmlAttr(resource.title || resource.username || resource.url || 'ID:' + resource.id) + '" data-card-resource-type="' + escapeHtmlAttr(resource.module_type) + '" class="card-resource-item">'
                            + '<div style="font-weight:600;margin-bottom:4px">' + escapeHtml(resource.title || resource.username || resource.url || 'ID:' + resource.id) + '</div>'
                            + '<div style="font-size:12px;color:#666"><span class="tag tag-blue">' + typeName + '</span> <span class="muted">ID: ' + resource.id + '</span></div>'
                            + '</div>';
                    }).join('');
                    
                    // 绑定点击事件
                    cardResourceResultsDiv.querySelectorAll('.card-resource-item').forEach(item => {
                        item.addEventListener('click', function() {
                            const resourceId = this.dataset.cardResourceId;
                            const resourceName = this.dataset.cardResourceName;
                            const resourceType = this.dataset.cardResourceType;
                            
                            cardResourceIdInput.value = resourceId;
                            const typeName = resourceType === 'account' ? '账号密码类' : resourceType === 'download' ? '下载连接类' : '教程类';
                            cardResourceCurrentDiv.innerHTML = '<div style="display:flex;align-items:center;justify-content:space-between"><div><strong>已选择：</strong>' + escapeHtml(resourceName) + ' <span class="tag tag-blue">' + typeName + '</span> <span class="muted">(ID: ' + resourceId + ')</span></div><button type="button" class="btn btn-danger btn-small" id="unbindCardResourceBtn">解除绑定</button></div>';
                            cardResourceResultsDiv.innerHTML = '';
                            
                            // 重新绑定解除按钮事件
                            const unbindBtn = document.getElementById('unbindCardResourceBtn');
                            if (unbindBtn) {
                                unbindBtn.addEventListener('click', unbindCardResource);
                            }
                        });
                    });
                }
            } catch (error) {
                alert('搜索失败：' + error.message);
                cardResourceResultsDiv.innerHTML = '<div style="padding:12px;text-align:center;color:#f44336">搜索失败：' + escapeHtml(error.message) + '</div>';
            } finally {
                cardResourceSearchBtn.disabled = false;
                cardResourceSearchBtn.textContent = '搜索';
            }
        }

        // 解除绑定
        function unbindCardResource() {
            cardResourceIdInput.value = '0';
            cardResourceCurrentDiv.innerHTML = '<div class="muted">未绑定卡密资源</div>';
            cardResourceResultsDiv.innerHTML = '';
        }

        cardResourceSearchBtn.addEventListener('click', searchCardResources);
        cardResourceSearchInput.addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                searchCardResources();
            }
        });

        // 如果是编辑模式且已绑定，添加解除绑定事件
        const unbindBtn = document.getElementById('unbindCardResourceBtn');
        if (unbindBtn) {
            unbindBtn.addEventListener('click', unbindCardResource);
        }

        const coverUrlInput = document.getElementById('productCoverUrlInput');`;

if (content.includes(eventHandlerInsertPoint)) {
    content = content.replace(eventHandlerInsertPoint, cardBindingEventHandlers);
    console.log('✅ 已添加卡密绑定事件处理代码');
} else {
    console.log('❌ 未找到事件处理器插入点');
}

console.log('\n正在保存文件...');
fs.writeFileSync(file, content, 'utf8');
console.log('✅ 文件已保存！');
console.log('\n✅ 修复完成！请刷新浏览器测试（Ctrl+F5 强制刷新）');
console.log('\n📝 下一步：');
console.log('1. 清除浏览器缓存并刷新页面');
console.log('2. 打开商品管理，点击"新增商品"或"编辑商品"');
console.log('3. 应该能看到"绑定卡密资源"部分');
console.log('4. 选择卡密分类后，搜索框会启用');
console.log('5. 输入关键词搜索，点击结果即可绑定');
