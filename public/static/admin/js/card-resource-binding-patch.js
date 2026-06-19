/**
 * 卡密资源绑定功能补丁
 * 
 * 使用方法：
 * 1. 在 card-admin.js 中找到 openProductModal 函数
 * 2. 按照 docs/CARD_RESOURCE_BINDING_FRONTEND_GUIDE.md 中的说明进行修改
 * 3. 或者直接在 admin-products.html 中引入此文件作为补丁
 */

// 卡密资源选择器辅助函数
window.CardResourceBindingHelper = {
    /**
     * 初始化卡密资源绑定功能
     * @param {Object} product - 商品对象（编辑时传入）
     * @param {Function} api - API调用函数
     * @param {Function} escapeHtml - HTML转义函数
     * @param {Function} escapeHtmlAttr - HTML属性转义函数
     */
    init: function(product, api, escapeHtml, escapeHtmlAttr) {
        const isEdit = !!product;
        const cardResourceState = {
            id: isEdit ? Number(product.card_resource_id || 0) : 0,
            name: isEdit ? String(product.card_resource_name || '') : '',
        };

        // 返回表单HTML
        const formHtml = '<div class="product-form-section">'
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
            + '</div>';

        // 返回事件绑定函数
        const bindEvents = function() {
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
                                const unbindBtn = document.getElementById('unbindCardResourceBtn');
                                if (unbindBtn) {
                                    unbindBtn.style.display = 'inline-block';
                                } else {
                                    // 动态添加解除绑定按钮
                                    const selector = document.querySelector('.product-card-resource-selector');
                                    const newUnbindBtn = document.createElement('button');
                                    newUnbindBtn.type = 'button';
                                    newUnbindBtn.className = 'btn btn-danger';
                                    newUnbindBtn.id = 'unbindCardResourceBtn';
                                    newUnbindBtn.textContent = '解除绑定';
                                    selector.appendChild(newUnbindBtn);
                                    
                                    newUnbindBtn.addEventListener('click', function () {
                                        if (!confirm('确定要解除卡密资源绑定吗？')) {
                                            return;
                                        }
                                        
                                        cardResourceState.id = 0;
                                        cardResourceState.name = '';
                                        cardResourceIdInput.value = '0';
                                        cardResourceCurrentDiv.innerHTML = '<div class="muted">未绑定卡密资源</div>';
                                        cardResourceResultsDiv.innerHTML = '';
                                        this.remove();
                                    });
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
        };

        return {
            formHtml: formHtml,
            bindEvents: bindEvents,
            getCardResourceId: function() {
                return cardResourceState.id;
            }
        };
    }
};
