(function () {
    const token = localStorage.getItem('admin_token');
    if (!token) {
        window.location.href = '/login.html';
        return;
    }

    const bodyData = document.body && document.body.dataset ? document.body.dataset : {};
    const categoryGroupMetaMap = {
        type: {
            key: 'type',
            label: '类型分类',
            emptyText: '暂无类型分类，先创建一级类型分类吧',
            rootText: '新增一级类型分类',
            batchText: '批量添加类型分类',
        },
        kind: {
            key: 'kind',
            label: '类别分类',
            emptyText: '暂无类别分类，先创建一级类别分类吧',
            rootText: '新增一级类别分类',
            batchText: '批量添加类别分类',
        },
    };
    const cardModuleMetaMap = {
        account: {
            key: 'account',
            title: '账号密码类',
            addText: '新增账号密码',
            emptyText: '暂无账号密码资源',
        },
        download: {
            key: 'download',
            title: '下载连接类',
            addText: '新增下载连接',
            emptyText: '暂无下载连接资源',
        },
        tutorial: {
            key: 'tutorial',
            title: '教程类',
            addText: '新增教程',
            emptyText: '暂无教程资源',
        },
    };
    const configGroupMetaMap = {
        website: {
            key: 'website',
            label: '网站配置',
            navKey: 'configs-website',
            url: '/admin-config-website.html',
        },
        miniapp: {
            key: 'miniapp',
            label: '小程序配置',
            navKey: 'configs-miniapp',
            url: '/admin-config-miniapp.html',
        },
        official_account: {
            key: 'official_account',
            label: '公众号配置',
            navKey: 'configs-official-account',
            url: '/admin-config-official-account.html',
        },
        wechat_pay: {
            key: 'wechat_pay',
            label: '微信支付配置',
            navKey: 'configs-wechat-pay',
            url: '/admin-config-wechat-pay.html',
        },
        alipay: {
            key: 'alipay',
            label: '支付宝支付配置',
            navKey: 'configs-alipay',
            url: '/admin-config-alipay.html',
        },
        epay: {
            key: 'epay',
            label: '易支付配置',
            navKey: 'configs-epay',
            url: '/admin-config-epay.html',
        },
        service: {
            key: 'service',
            label: '客服配置',
            navKey: 'configs-service',
            url: '/admin-config-service.html',
        },
    };
    const configImageFieldKeys = new Set([
        'site_logo',
        'site_icon',
        'default_share_image',
        'home_banner_image',
        'customer_qr_image',
    ]);
    const configLayoutMetaMap = {
        website: {
            heroTitle: '网站配置中心',
            heroDescription: '统一管理网站品牌图片、介绍文案、页脚信息和搜索展示内容。',
            sections: [
                {
                    key: 'brand',
                    title: '品牌与视觉素材',
                    description: 'Logo、图标、横幅、分享图和客服二维码等站点视觉资源。',
                    items: ['site_name', 'site_logo', 'site_icon', 'home_banner_image', 'default_share_image', 'customer_qr_image'],
                },
                {
                    key: 'content',
                    title: '首页介绍与公告',
                    description: '首页短介绍、详细介绍和全局公告提示。',
                    items: ['site_tagline', 'site_intro', 'service_notice'],
                },
                {
                    key: 'contact',
                    title: '联系与页脚信息',
                    description: '底部备案、版权和联系方式等基础站务内容。',
                    items: ['record_number', 'copyright_text', 'contact_email'],
                },
                {
                    key: 'seo',
                    title: '搜索与分享优化',
                    description: '搜索关键词、描述和对外分享时常用的内容配置。',
                    items: ['seo_keywords', 'seo_description'],
                },
            ],
        },
    };
    const sidebarMenuSchema = [
        { type: 'link', label: '仪表盘', view: 'dashboard', navKey: 'dashboard', url: '/admin.html' },
        {
            type: 'group',
            key: 'categories',
            label: '分类管理',
            children: [
                { label: '类型分类', view: 'categories', navKey: 'categories-type', url: '/admin-categories.html' },
                { label: '类别分类', view: 'categories', navKey: 'categories-kind', url: '/admin-category-kinds.html' },
            ],
        },
        { type: 'link', label: '商品管理', view: 'products', navKey: 'products', url: '/admin-products.html' },
        { type: 'link', label: '精选管理', view: 'featured', navKey: 'featured', url: '/admin-featured.html' },
        { type: 'link', label: '许愿管理', view: 'wishes', navKey: 'wishes', url: '/admin-wishes.html' },
        {
            type: 'group',
            key: 'cards',
            label: '卡密管理',
            children: [
                { label: '账号密码类', view: 'cards', navKey: 'cards-account', url: '/admin-cards.html' },
                { label: '下载连接类', view: 'cards', navKey: 'cards-download', url: '/admin-card-downloads.html' },
                { label: '教程类', view: 'cards', navKey: 'cards-tutorial', url: '/admin-card-tutorials.html' },
            ],
        },
        { type: 'link', label: '订单管理', view: 'orders', navKey: 'orders', url: '/admin-orders.html' },
        { type: 'link', label: '\u6570\u636e\u7edf\u8ba1', view: 'data-statistics', navKey: 'data-statistics', url: '/admin-data-statistics.html' },
        { type: 'link', label: '\u641c\u7d22\u6570\u636e\u7ba1\u7406', view: 'product-search-logs', navKey: 'product-search-logs', url: '/admin-product-search-logs.html' },
        {
            type: 'group',
            key: 'energy',
            label: '能量管理',
            children: [
                { label: '能量管理', view: 'energy', navKey: 'energy-balance', url: '/admin-energy.html' },
                { label: '能量获取', view: 'energy-sources', navKey: 'energy-sources', url: '/admin-energy-sources.html' },
                { label: '\u80fd\u91cf\u5957\u9910', view: 'energy-packages', navKey: 'energy-packages', url: '/admin-energy-packages.html' },
            ],
        },
        {
            type: 'group',
            key: 'configs',
            label: '系统配置',
            children: [
                { label: '小程序配置', view: 'configs', navKey: 'configs-miniapp', url: '/admin-config-miniapp.html' },
                { label: '公众号配置', view: 'configs', navKey: 'configs-official-account', url: '/admin-config-official-account.html' },
                { label: '微信支付配置', view: 'configs', navKey: 'configs-wechat-pay', url: '/admin-config-wechat-pay.html' },
                { label: '支付宝支付配置', view: 'configs', navKey: 'configs-alipay', url: '/admin-config-alipay.html' },
                { label: '易支付配置', view: 'configs', navKey: 'configs-epay', url: '/admin-config-epay.html' },
                { label: '客服配置', view: 'configs', navKey: 'configs-service', url: '/admin-config-service.html' },
            ],
        },
        { type: 'link', label: '免责声明', view: 'disclaimer', navKey: 'disclaimer', url: '/admin-disclaimer.html' },
        { type: 'link', label: '常见问题', view: 'faqs', navKey: 'faqs', url: '/admin-faqs.html' },
        { type: 'link', label: '用户管理', view: 'users', navKey: 'users', url: '/admin-users.html' },
    ];
    sidebarMenuSchema.splice(sidebarMenuSchema.length - 1, 0, {
        type: 'link',
        label: '公告管理',
        view: 'announcements',
        navKey: 'announcements',
        url: '/admin-announcements.html',
    });
    const configMenuGroup = sidebarMenuSchema.find((item) => item.type === 'group' && item.key === 'configs');
    if (configMenuGroup && Array.isArray(configMenuGroup.children)) {
        const hasWebsiteConfig = configMenuGroup.children.some((item) => item.navKey === 'configs-website');
        if (!hasWebsiteConfig) {
            configMenuGroup.children.unshift({
                label: '网站配置',
                view: 'configs',
                navKey: 'configs-website',
                url: '/admin-config-website.html',
            });
        }
    }
    const state = {
        view: 'dashboard',
        categoryGroup: normalizeCategoryGroup(bodyData.categoryGroup || 'type'),
        cardModule: normalizeCardModule(bodyData.cardModule || 'account'),
        configGroup: normalizeConfigGroup(bodyData.configGroup || 'miniapp'),
        dashboard: null,
        categoryTree: [],
        categoryFlat: [],
        categoryDataMap: {
            type: { tree: [], flat: [], maxLevel: 4 },
            kind: { tree: [], flat: [], maxLevel: 4 },
        },
        selectedCategoryIds: [],
        maxCategoryLevel: 4,
        productList: [],
        productFilters: { keyword: '', categoryId: '', status: '', featured: '' },
        selectedProductIds: [],
        cardProducts: [],
        cardResourceList: [],
        cardResourceFilters: {
            account: { keyword: '', productId: '', status: '', scopeType: '' },
            download: { keyword: '', productId: '', status: '', scopeType: '' },
            tutorial: { keyword: '', productId: '', status: '', scopeType: '' },
        },
        orderProducts: [],
        userFilters: { keyword: '', status: '' },
        faqFilters: { keyword: '', status: '' },
        energySourceFilters: { keyword: '', status: '' },
        energyPackageFilters: { keyword: '', status: '' },
        dataStatisticFilters: { keyword: '', period: 'day', limit: '20', tab: 'click' },
        productSearchLogFilters: { keyword: '', userKeyword: '', visitorId: '', startDate: '', endDate: '', period: 'day' },
    };
    state.announcementFilters = { keyword: '', status: '' };
    const viewPageMap = {
        dashboard: '/admin.html',
        categories: '/admin-categories.html',
        products: '/admin-products.html',
        featured: '/admin-featured.html',
        wishes: '/admin-wishes.html',
        cards: '/admin-cards.html',
        orders: '/admin-orders.html',
        'data-statistics': '/admin-data-statistics.html',
        'product-search-logs': '/admin-product-search-logs.html',
        energy: '/admin-energy.html',
        'energy-sources': '/admin-energy-sources.html',
        'energy-packages': '/admin-energy-packages.html',
        configs: '/admin-config-miniapp.html',
        disclaimer: '/admin-disclaimer.html',
        faqs: '/admin-faqs.html',
        users: '/admin-users.html',
    };
    viewPageMap.announcements = '/admin-announcements.html';
    const pageView = viewPageMap[bodyData.view || '']
        ? bodyData.view
        : 'dashboard';
    const pageNavKey = bodyData.navKey || pageView;

    const app = document.getElementById('app');
    const adminNameEl = document.getElementById('adminName');
    const modalMask = document.getElementById('modalMask');
    const modalTitle = document.getElementById('modalTitle');
    const modalBody = document.getElementById('modalBody');
    const modalFoot = document.getElementById('modalFoot');
    const categoryPopover = createCategoryPopover();
    let activeCategoryPopoverId = 0;
    let activeCategoryTrigger = null;
    let activeRichEditorRange = null;

    const adminUser = safeJsonParse(localStorage.getItem('admin_user')) || {};
    adminNameEl.textContent = adminUser.nickname || adminUser.username || 'admin';
    renderSidebar();

    document.querySelectorAll('.menu-link').forEach((btn) => {
        btn.addEventListener('click', function () {
            navigateToView(this.dataset.view, this.dataset.navUrl || '');
        });
    });
    document.querySelectorAll('[data-menu-toggle]').forEach((btn) => {
        btn.addEventListener('click', function () {
            toggleMenuGroup(this.dataset.menuToggle);
        });
    });
    document.getElementById('logoutBtn').addEventListener('click', logout);
    document.getElementById('refreshStatsBtn').addEventListener('click', function () {
        switchView(state.view || pageView);
    });
    document.getElementById('modalCloseBtn').addEventListener('click', closeModal);
    categoryPopover.addEventListener('click', function (event) {
        event.stopPropagation();
        handleCategoryPopoverClick(event);
    });
    document.addEventListener('click', function (event) {
        if (!categoryPopover.classList.contains('show')) {
            return;
        }
        if (categoryPopover.contains(event.target)) {
            return;
        }
        if (activeCategoryTrigger && activeCategoryTrigger.contains(event.target)) {
            return;
        }
        closeCategoryPopover();
    });
    window.addEventListener('resize', closeCategoryPopover);
    window.addEventListener('scroll', closeCategoryPopover, true);

    switchView(pageView);

    function getViewUrl(view) {
        return viewPageMap[view] || viewPageMap.dashboard;
    }

    function navigateToView(view, customUrl) {
        const targetView = viewPageMap[view] ? view : 'dashboard';
        const targetUrl = customUrl || getViewUrl(targetView);
        const currentPath = window.location.pathname.replace(/\\/g, '/');
        if (state.view === targetView && currentPath === targetUrl) {
            return;
        }
        window.location.href = targetUrl;
    }

    function switchView(view) {
        closeCategoryPopover();
        state.view = view;
        updateSidebarActiveState(view);

        if (view === 'dashboard') return renderDashboard();
        if (view === 'categories') return renderCategories();
        if (view === 'products') return renderProducts();
        if (view === 'featured') return renderFeatured();
        if (view === 'wishes') return renderWishes();
        if (view === 'cards') return renderCards();
        if (view === 'orders') return renderOrders();
        if (view === 'data-statistics') return renderDataStatistics();
        if (view === 'product-search-logs') return renderProductSearchLogs();
        if (view === 'energy') return renderEnergyManagement();
        if (view === 'energy-sources') return renderEnergySources();
        if (view === 'energy-packages') return renderEnergyPackages();
        if (view === 'configs') return renderConfigs();
        if (view === 'disclaimer') return renderDisclaimer();
        if (view === 'faqs') return renderFaqs();
        if (view === 'announcements') return renderAnnouncements();
        if (view === 'users') return renderUsers();
    }

    function toggleMenuGroup(groupKey) {
        const groupEl = document.querySelector('[data-menu-group="' + groupKey + '"]');
        if (!groupEl) {
            return;
        }

        groupEl.classList.toggle('open');
    }

    function updateSidebarActiveState(view) {
        document.querySelectorAll('.menu-link').forEach((btn) => {
            const currentKey = btn.dataset.navKey || btn.dataset.view;
            const isActive = currentKey === pageNavKey
                || (!btn.dataset.navKey && pageNavKey === view && btn.dataset.view === view);
            btn.classList.toggle('active', isActive);
        });

        document.querySelectorAll('.menu-group').forEach((groupEl) => {
            const hasActiveChild = !!groupEl.querySelector('.menu-link.active');
            const toggleBtn = groupEl.querySelector('[data-menu-toggle]');
            if (toggleBtn) {
                toggleBtn.classList.toggle('active', hasActiveChild);
            }
            if (hasActiveChild) {
                groupEl.classList.add('open');
            }
        });
    }

    async function api(path, options) {
        const config = options || {};
        const method = config.method || 'GET';
        let url = path;

        if (config.query) {
            const query = new URLSearchParams();
            Object.entries(config.query).forEach(([key, value]) => {
                if (value !== '' && value !== undefined && value !== null) {
                    query.append(key, String(value));
                }
            });
            const queryString = query.toString();
            if (queryString) {
                url += (url.indexOf('?') >= 0 ? '&' : '?') + queryString;
            }
        }

        const headers = {
            'Accept': 'application/json',
            'Authorization': 'Bearer ' + token,
        };
        const fetchOptions = { method, headers };

        if (config.data) {
            const form = new URLSearchParams();
            appendForm(form, config.data);
            fetchOptions.body = form.toString();
            headers['Content-Type'] = 'application/x-www-form-urlencoded;charset=UTF-8';
        }

        const response = await fetch(url, fetchOptions);
        if (response.status === 401) {
            logout();
            throw new Error('登录状态已失效');
        }

        let payload;
        try {
            payload = await response.json();
        } catch (error) {
            throw new Error('接口返回格式错误');
        }

        if (!response.ok || payload.code !== 0) {
            throw new Error(payload.msg || '请求失败');
        }

        return payload.data || {};
    }

    function appendForm(form, data, prefix) {
        const parentKey = prefix || '';
        Object.entries(data).forEach(([key, value]) => {
            const field = parentKey ? parentKey + '[' + key + ']' : key;
            if (Array.isArray(value)) {
                value.forEach((item, index) => {
                    if (item && typeof item === 'object') {
                        appendForm(form, item, field + '[' + index + ']');
                    } else {
                        form.append(field + '[' + index + ']', item == null ? '' : String(item));
                    }
                });
                return;
            }
            if (value && typeof value === 'object') {
                appendForm(form, value, field);
                return;
            }
            form.append(field, value == null ? '' : String(value));
        });
    }

    function renderError(error, title) {
        app.innerHTML = '<div class="panel"><div class="panel-title">' + escapeHtml(title || '加载失败') + '</div><div class="empty">' + escapeHtml(error.message || '系统异常') + '</div></div>';
    }

    async function loadCategories(groupKey) {
        const targetGroup = normalizeCategoryGroup(groupKey || getCurrentCategoryGroup().key);
        const data = await api('/admin/categories', {
            query: {
                group: targetGroup,
            }
        });
        const normalized = {
            tree: Array.isArray(data.tree) ? data.tree : [],
            flat: Array.isArray(data.flat) ? data.flat : [],
            maxLevel: Number(data.max_level || 4) || 4,
        };

        state.categoryDataMap[targetGroup] = normalized;

        if (targetGroup === getCurrentCategoryGroup().key) {
            state.categoryTree = normalized.tree;
            state.categoryFlat = normalized.flat;
            state.maxCategoryLevel = normalized.maxLevel;
        }

        data.tree = normalized.tree;
        data.flat = normalized.flat;
        data.max_level = normalized.maxLevel;
        return data;
    }

    function getCategoryFlatByGroup(groupKey) {
        const targetGroup = normalizeCategoryGroup(groupKey);
        const data = state.categoryDataMap[targetGroup];
        return data && Array.isArray(data.flat) ? data.flat : [];
    }

    async function renderDashboard() {
        app.innerHTML = '<div class="panel"><div class="panel-title">仪表盘</div><div class="empty">加载中...</div></div>';
        try {
            const data = await api('/admin/dashboard/stats');
            state.dashboard = data;
            app.innerHTML = '<div class="panel"><div class="panel-title">发卡系统实时统计</div><div class="stats-grid">'
                + statCard('分类总数', data.category_total || 0)
                + statCard('商品总数', data.product_total || 0)
                + statCard('卡密总数', data.card_total || 0)
                + statCard('可用卡密', data.card_unused || 0)
                + statCard('已售卡密', data.card_sold || 0)
                + statCard('待支付订单', data.order_pending || 0)
                + statCard('已支付订单', data.order_paid || 0)
                + statCard('已发货订单', data.order_delivered || 0)
                + statCard('累计兑换能量', energy(data.total_revenue || 0) + ' 能量')
                + statCard('今日兑换能量', energy(data.today_revenue || 0) + ' 能量')
                + '</div></div>';
        } catch (error) {
            renderError(error, '仪表盘');
        }
    }

    async function renderCategories() {
        closeCategoryPopover();
        const categoryGroup = getCurrentCategoryGroup();
        app.innerHTML = '<div class="panel"><div class="panel-title">' + escapeHtml(categoryGroup.label) + '</div><div class="empty">加载中...</div></div>';
        try {
            const data = await loadCategories();
            syncSelectedCategoryIds();

            app.innerHTML = '<div class="panel"><div class="panel-title">' + escapeHtml(categoryGroup.label) + '</div><div class="toolbar">'
                + '<button class="btn btn-main" id="addRootCategoryBtn">' + escapeHtml(categoryGroup.rootText) + '</button>'
                + '<button class="btn btn-small" id="batchAddCategoryBtn">' + escapeHtml(categoryGroup.batchText) + '</button>'
                + '<button class="btn btn-danger" id="batchDeleteCategoryBtn" disabled>批量删除</button>'
                + '<button class="btn btn-warning" id="clearCategorySelectionBtn">清空选择</button>'
                + '<span class="muted" id="categorySelectionText">已选 0 项</span>'
                + '<span class="muted">当前分组：' + escapeHtml(categoryGroup.label) + '</span>'
                + '<span class="muted">当前共 ' + escapeHtml(String(state.categoryFlat.length)) + ' 个分类，支持最多 ' + escapeHtml(String(data.max_level || 4)) + ' 级</span>'
                + '</div>'
                + (state.categoryTree.length ? renderCategoryStream(state.categoryTree) : '<div class="empty">' + escapeHtml(categoryGroup.emptyText) + '</div>')
                + '</div>';

            document.getElementById('addRootCategoryBtn').addEventListener('click', function () {
                openCategoryModal(null, 0);
            });
            document.getElementById('batchAddCategoryBtn').addEventListener('click', function () {
                openCategoryBatchModal(0);
            });
            document.getElementById('batchDeleteCategoryBtn').addEventListener('click', async function () {
                if (!state.selectedCategoryIds.length) {
                    alert('请先勾选要删除的分类');
                    return;
                }

                if (!confirm('确定批量删除已选的 ' + state.selectedCategoryIds.length + ' 个分类吗？')) {
                    return;
                }

                try {
                    const result = await api('/admin/categories/delete-batch', {
                        method: 'POST',
                        data: {
                            ids: state.selectedCategoryIds,
                        },
                    });
                    state.selectedCategoryIds = [];
                    await renderCategories();
                    alert('已删除 ' + String(result.count || 0) + ' 个分类');
                } catch (error) {
                    alert(error.message);
                }
            });
            document.getElementById('clearCategorySelectionBtn').addEventListener('click', function () {
                state.selectedCategoryIds = [];
                updateCategorySelectionUI();
            });

            bindCategoryEvents();
            updateCategorySelectionUI();
        } catch (error) {
            renderError(error, categoryGroup.label);
        }
    }

    function renderCategoryStream(tree) {
        return '<div class="category-stream">' + tree.map((node) => renderCategoryGroup(node)).join('') + '</div>';
    }

    function renderCategoryGroup(rootNode) {
        let html = '<div class="category-group">';
        html += renderCategoryLine(rootNode, rootNode.children || []);

        (rootNode.children || []).forEach((child) => {
            html += renderCategoryDescendantLines(child);
        });

        html += '</div>';
        return html;
    }

    function renderCategoryDescendantLines(node) {
        if (!node.children || !node.children.length) {
            return '';
        }

        let html = renderCategoryLine(node, node.children || []);
        (node.children || []).forEach((child) => {
            html += renderCategoryDescendantLines(child);
        });
        return html;
    }

    function renderCategoryLine(parent, children) {
        const lineClass = ' level-' + String(parent.level || 1);
        return '<div class="category-line' + lineClass + '">'
            + '<div class="category-line-label">'
            + '<span class="category-line-prefix">' + escapeHtml(getCategoryLevelLabel(Number(parent.level || 1))) + '</span>'
            + renderCategoryChip(parent, true)
            + '</div>'
            + '<div class="category-line-items">' + renderCategoryLineItems(children, parent) + '</div>'
            + '</div>';
    }

    function renderCategoryLineItems(children, parent) {
        if (!children || !children.length) {
            return '<span class="category-line-empty">当前 ' + escapeHtml(parent.name || '分类') + ' 下暂无下级分类</span>';
        }

        return children.map((child) => renderCategoryChip(child, false)).join('');
    }

    function renderCategoryChip(node, isParentChip) {
        const selectedClass = isCategorySelected(node.id) ? ' selected' : '';
        const parentWrapClass = isParentChip ? ' parent-chip-wrap' : '';
        const parentChipClass = isParentChip ? ' parent-chip' : '';

        return '<span class="category-chip-wrap' + parentWrapClass + selectedClass + '" data-category-item="' + node.id + '">'
            + '<label class="category-select"><input class="category-checkbox" type="checkbox" data-category-select="' + node.id + '"' + (isCategorySelected(node.id) ? ' checked' : '') + '></label>'
            + '<button type="button" class="category-chip' + parentChipClass + '" data-category-trigger="' + node.id + '">' + escapeHtml(node.name) + '</button>'
            + '</span>';
    }

    function renderCategoryPopoverContent(node) {
        let actionHtml = '<button type="button" class="btn btn-small" data-popover-action="edit" data-category-id="' + node.id + '">编辑</button>';

        if (Number(node.level) < Number(state.maxCategoryLevel)) {
            actionHtml += '<button type="button" class="btn btn-main" data-popover-action="add" data-category-id="' + node.id + '">添加子级</button>';
            actionHtml += '<button type="button" class="btn btn-warning" data-popover-action="batch" data-category-id="' + node.id + '">批量子级</button>';
        }

        actionHtml += '<button type="button" class="btn btn-danger" data-popover-action="delete" data-category-id="' + node.id + '">删除</button>';

        return '<div class="category-popover-card">'
            + '<div class="category-popover-head">'
            + '<div>'
            + '<div class="category-popover-title">'
            + '<strong class="category-popover-name">' + escapeHtml(node.name) + '</strong>'
            + '<span class="tag tag-blue">L' + escapeHtml(String(node.level || 1)) + '</span>'
            + renderCategoryStatusTag(node.status)
            + '</div>'
            + '<div class="category-popover-meta">'
            + '<span class="tag tag-gray">排序 ' + escapeHtml(String(node.sort || 0)) + '</span>'
            + '<span class="tag tag-gray">商品 ' + escapeHtml(String(node.products_count || 0)) + '</span>'
            + '<span class="tag tag-gray">ID ' + escapeHtml(String(node.id)) + '</span>'
            + '</div>'
            + (node.description ? '<div class="category-popover-desc">' + escapeHtml(node.description) + '</div>' : '')
            + '</div>'
            + '<button type="button" class="link-btn" data-popover-action="close">关闭</button>'
            + '</div>'
            + '<div class="category-popover-actions">' + actionHtml + '</div>'
            + '</div>';
    }

    function getCategoryLevelLabel(level) {
        if (level === 1) return '一级分类:';
        if (level === 2) return '二级分类:';
        if (level === 3) return '三级分类:';
        return '四级分类:';
    }

    function bindCategoryEvents() {
        document.querySelectorAll('[data-category-trigger]').forEach((btn) => {
            btn.addEventListener('click', function (event) {
                event.preventDefault();
                event.stopPropagation();
                toggleCategoryPopover(Number(this.dataset.categoryTrigger), this);
            });
        });
        document.querySelectorAll('[data-category-select]').forEach((input) => {
            input.addEventListener('click', function (event) {
                event.stopPropagation();
            });
            input.addEventListener('change', function () {
                toggleCategorySelection(Number(this.dataset.categorySelect), this.checked);
            });
        });
    }

    function createCategoryPopover() {
        const el = document.createElement('div');
        el.className = 'category-action-popover';
        document.body.appendChild(el);
        return el;
    }

    function toggleCategoryPopover(categoryId, triggerEl) {
        if (activeCategoryPopoverId === Number(categoryId) && categoryPopover.classList.contains('show')) {
            closeCategoryPopover();
            return;
        }

        openCategoryPopover(categoryId, triggerEl);
    }

    function openCategoryPopover(categoryId, triggerEl) {
        const node = findCategoryById(categoryId, state.categoryTree);
        if (!node) {
            closeCategoryPopover();
            return;
        }

        activeCategoryPopoverId = Number(categoryId);
        activeCategoryTrigger = triggerEl;
        categoryPopover.dataset.categoryId = String(categoryId);
        categoryPopover.innerHTML = renderCategoryPopoverContent(node);
        categoryPopover.classList.add('show');
        positionCategoryPopover(triggerEl);
    }

    function positionCategoryPopover(triggerEl) {
        if (!triggerEl) {
            return;
        }

        const gap = 12;
        const viewportPadding = 12;
        const rect = triggerEl.getBoundingClientRect();
        const popoverRect = categoryPopover.getBoundingClientRect();
        const maxLeft = Math.max(viewportPadding, window.innerWidth - popoverRect.width - viewportPadding);
        let left = rect.left;
        let top = rect.bottom + gap;

        if (left > maxLeft) {
            left = maxLeft;
        }
        if (left < viewportPadding) {
            left = viewportPadding;
        }
        if (top + popoverRect.height > window.innerHeight - viewportPadding) {
            top = rect.top - popoverRect.height - gap;
        }
        if (top < viewportPadding) {
            top = viewportPadding;
        }

        categoryPopover.style.left = Math.round(left) + 'px';
        categoryPopover.style.top = Math.round(top) + 'px';
    }

    async function handleCategoryPopoverClick(event) {
        const actionEl = event.target.closest('[data-popover-action]');
        if (!actionEl) {
            return;
        }

        const action = actionEl.dataset.popoverAction;
        if (action === 'close') {
            closeCategoryPopover();
            return;
        }

        const categoryId = Number(actionEl.dataset.categoryId || categoryPopover.dataset.categoryId || 0);
        const category = findCategoryById(categoryId, state.categoryTree);
        if (!category) {
            closeCategoryPopover();
            return;
        }

        if (action === 'edit') {
            closeCategoryPopover();
            openCategoryModal(category, Number(category.parent_id || 0));
            return;
        }

        if (action === 'add') {
            closeCategoryPopover();
            openCategoryModal(null, categoryId);
            return;
        }

        if (action === 'batch') {
            closeCategoryPopover();
            openCategoryBatchModal(categoryId);
            return;
        }

        if (action === 'delete') {
            if (!confirm('确定删除这个分类吗？')) {
                return;
            }

            try {
                await api('/admin/categories/' + categoryId, { method: 'DELETE' });
                state.selectedCategoryIds = state.selectedCategoryIds.filter((item) => Number(item) !== categoryId);
                closeCategoryPopover();
                await renderCategories();
            } catch (error) {
                alert(error.message);
            }
        }
    }

    function closeCategoryPopover() {
        activeCategoryPopoverId = 0;
        activeCategoryTrigger = null;
        categoryPopover.classList.remove('show');
        categoryPopover.removeAttribute('data-category-id');
        categoryPopover.style.left = '-9999px';
        categoryPopover.style.top = '-9999px';
        categoryPopover.innerHTML = '';
    }

    function openCategoryModal(category, presetParentId) {
        closeCategoryPopover();
        const isEdit = !!category;
        const currentId = isEdit ? Number(category.id) : 0;
        const selectedParentId = isEdit ? Number(category.parent_id || 0) : Number(presetParentId || 0);
        const invalidIds = currentId > 0 ? collectDescendantIds(currentId, state.categoryTree).concat([currentId]) : [];
        const categoryGroup = getCurrentCategoryGroup();

        openModal(
            isEdit ? '编辑' + categoryGroup.label : '新增' + categoryGroup.label,
            '<div class="form-row"><label>父级分类</label><select id="categoryParentId">' + buildCategoryOptions(selectedParentId, invalidIds) + '</select></div>'
            + '<div class="form-row"><label>分类名称</label><input id="categoryName" value="' + escapeHtmlAttr(isEdit ? category.name : '') + '"></div>'
            + '<div class="form-row"><label>排序值</label><input id="categorySort" type="number" value="' + escapeHtmlAttr(String(isEdit ? category.sort : 0)) + '"></div>'
            + '<div class="form-row"><label>状态</label><select id="categoryStatus"><option value="1"' + (isEdit && Number(category.status) === 0 ? '' : ' selected') + '>启用</option><option value="0"' + (isEdit && Number(category.status) === 0 ? ' selected' : '') + '>停用</option></select></div>'
            + '<div class="form-row"><label>描述</label><textarea id="categoryDescription">' + escapeHtml(isEdit ? (category.description || '') : '') + '</textarea></div>',
            [
                { text: '取消', className: 'btn btn-warning', onClick: closeModal },
                {
                    text: isEdit ? '保存' : '创建',
                    className: 'btn btn-main',
                    onClick: async function () {
                        const payload = {
                            parent_id: valueById('categoryParentId'),
                            name: valueById('categoryName'),
                            sort: valueById('categorySort'),
                            status: valueById('categoryStatus'),
                            description: valueById('categoryDescription'),
                            group_key: categoryGroup.key,
                        };
                        try {
                            if (isEdit) {
                                await api('/admin/categories/' + currentId, { method: 'PUT', data: payload });
                            } else {
                                await api('/admin/categories', { method: 'POST', data: payload });
                            }
                            closeModal();
                            renderCategories();
                        } catch (error) {
                            alert(error.message);
                        }
                    }
                }
            ]
        );
    }

    function openCategoryBatchModal(presetParentId) {
        closeCategoryPopover();
        const selectedParentId = Number(presetParentId || 0);
        const categoryGroup = getCurrentCategoryGroup();

        openModal(
            categoryGroup.batchText,
            '<div class="form-row"><label>父级分类</label><select id="categoryBatchParentId">' + buildCategoryOptions(selectedParentId, []) + '</select></div>'
            + '<div class="form-row"><label>起始排序</label><input id="categoryBatchSortStart" type="number" value="0"></div>'
            + '<div class="form-row"><label>排序步长</label><input id="categoryBatchSortStep" type="number" value="10"></div>'
            + '<div class="form-row"><label>状态</label><select id="categoryBatchStatus"><option value="1" selected>启用</option><option value="0">停用</option></select></div>'
            + '<div class="form-row"><label>公共描述</label><textarea id="categoryBatchDescription" placeholder="可选，创建的分类都会带上这段描述"></textarea></div>'
            + '<div class="form-row"><label>分类名称</label><textarea id="categoryBatchNames" placeholder="每行一个分类名称&#10;例如：&#10;手机专区&#10;电脑专区&#10;配件专区"></textarea></div>'
            + '<div class="muted">说明：每行一个分类名称，系统会自动去掉空行和重复项，并按起始排序加步长递增。</div>',
            [
                { text: '取消', className: 'btn btn-warning', onClick: closeModal },
                {
                    text: '批量创建',
                    className: 'btn btn-main',
                    onClick: async function () {
                        const names = valueById('categoryBatchNames')
                            .split(/\r?\n/)
                            .map((name) => name.trim())
                            .filter(Boolean);

                        if (!names.length) {
                            alert('请至少填写一个分类名称');
                            return;
                        }

                        try {
                            const result = await api('/admin/categories/batch', {
                                method: 'POST',
                                data: {
                                    parent_id: valueById('categoryBatchParentId'),
                                    names: names,
                                    sort_start: valueById('categoryBatchSortStart') || '0',
                                    sort_step: valueById('categoryBatchSortStep') || '10',
                                    status: valueById('categoryBatchStatus'),
                                    description: valueById('categoryBatchDescription'),
                                    group_key: categoryGroup.key,
                                },
                            });
                            closeModal();
                            await renderCategories();
                            alert('已批量创建 ' + String(result.count || names.length) + ' 个分类');
                        } catch (error) {
                            alert(error.message);
                        }
                    }
                }
            ]
        );
    }

    function buildCategoryOptions(selectedParentId, invalidIds) {
        let html = '<option value="0"' + (selectedParentId === 0 ? ' selected' : '') + '>作为一级分类</option>';
        state.categoryFlat.forEach((item) => {
            const id = Number(item.id);
            const level = Number(item.level || 1);
            const disabled = invalidIds.indexOf(id) >= 0 || (level >= state.maxCategoryLevel && id !== selectedParentId);
            html += '<option value="' + id + '"' + (id === selectedParentId ? ' selected' : '') + (disabled ? ' disabled' : '') + '>' + escapeHtml(item.title || item.name) + '</option>';
        });
        return html;
    }

    function renderCategoryStatusTag(status) {
        return Number(status) === 1
            ? '<span class="tag tag-green">启用</span>'
            : '<span class="tag tag-gray">停用</span>';
    }

    function toggleCategorySelection(id, checked) {
        const currentId = Number(id);
        const current = new Set(state.selectedCategoryIds.map((item) => Number(item)));

        if (checked) {
            current.add(currentId);
        } else {
            current.delete(currentId);
        }

        state.selectedCategoryIds = Array.from(current);
        updateCategorySelectionUI();
    }

    function updateCategorySelectionUI() {
        const selectedSet = new Set(state.selectedCategoryIds.map((item) => Number(item)));
        const textEl = document.getElementById('categorySelectionText');
        const deleteBtn = document.getElementById('batchDeleteCategoryBtn');

        if (textEl) {
            textEl.textContent = '已选 ' + state.selectedCategoryIds.length + ' 项';
        }
        if (deleteBtn) {
            deleteBtn.disabled = state.selectedCategoryIds.length === 0;
        }

        document.querySelectorAll('[data-category-item]').forEach((item) => {
            const id = Number(item.dataset.categoryItem);
            item.classList.toggle('selected', selectedSet.has(id));
        });
        document.querySelectorAll('[data-category-select]').forEach((input) => {
            input.checked = selectedSet.has(Number(input.dataset.categorySelect));
        });
    }

    function syncSelectedCategoryIds() {
        const validIds = new Set(state.categoryFlat.map((item) => Number(item.id)));
        state.selectedCategoryIds = state.selectedCategoryIds.filter((id) => validIds.has(Number(id)));
    }

    function isCategorySelected(id) {
        return state.selectedCategoryIds.map((item) => Number(item)).indexOf(Number(id)) >= 0;
    }

    function syncProductFilters() {
        if (!state.productFilters || typeof state.productFilters !== 'object') {
            state.productFilters = { keyword: '', categoryId: '', status: '' };
            return;
        }

        state.productFilters.keyword = String(state.productFilters.keyword || '');
        state.productFilters.categoryId = String(state.productFilters.categoryId || '');
        state.productFilters.status = state.productFilters.status === '0' || state.productFilters.status === '1'
            ? state.productFilters.status
            : '';

        if (state.productFilters.categoryId !== '') {
            const exists = getCategoryFlatByGroup('type').some((item) => Number(item.id) === Number(state.productFilters.categoryId));
            if (!exists) {
                state.productFilters.categoryId = '';
            }
        }
    }

    function getCategoryChildren(parentId, flatList) {
        const currentParentId = Number(parentId || 0);
        const list = Array.isArray(flatList) ? flatList : state.categoryFlat;
        return list.filter((item) => Number(item.parent_id || 0) === currentParentId);
    }

    function getCategoryPathNodes(categoryId, flatList) {
        const currentId = Number(categoryId || 0);
        if (currentId <= 0) {
            return [];
        }

        const list = Array.isArray(flatList) ? flatList : state.categoryFlat;
        const map = new Map(list.map((item) => [Number(item.id), item]));
        const path = [];
        let cursor = map.get(currentId) || null;
        let guard = 0;

        while (cursor && guard < 10) {
            path.unshift(cursor);
            const parentId = Number(cursor.parent_id || 0);
            cursor = parentId > 0 ? (map.get(parentId) || null) : null;
            guard += 1;
        }

        return path;
    }

    function getCategoryPathText(categoryId, fallbackText, flatList) {
        const path = getCategoryPathNodes(categoryId, flatList);
        if (!path.length) {
            return fallbackText || '未分类';
        }

        return path.map((item) => item.name).join(' / ');
    }

    function getCategoryLevelNumber(categoryId, flatList) {
        const path = getCategoryPathNodes(categoryId, flatList);
        return path.length || 0;
    }

    function getProductCategoryLevelMap(categoryId, flatList) {
        const path = getCategoryPathNodes(categoryId, flatList);

        return {
            level1: path[0] ? path[0].name : '',
            level2: path[1] ? path[1].name : '',
            level3: path[2] ? path[2].name : '',
            level4: path[3] ? path[3].name : '',
        };
    }

    function normalizeProductGalleryImages(value) {
        if (Array.isArray(value)) {
            return value.map((item) => String(item || '').trim()).filter(Boolean);
        }

        if (typeof value === 'string' && value.trim() !== '') {
            try {
                const parsed = JSON.parse(value);
                if (Array.isArray(parsed)) {
                    return parsed.map((item) => String(item || '').trim()).filter(Boolean);
                }
            } catch (error) {
                return [];
            }
        }

        return [];
    }

    async function uploadImageFile(file, dir, emptyMessage, failMessage) {
        if (!file) {
            throw new Error(emptyMessage || '请选择图片文件');
        }

        const formData = new FormData();
        formData.append('file', file);
        formData.append('dir', String(dir || 'products'));

        const response = await fetch('/admin/upload/image', {
            method: 'POST',
            headers: {
                'Authorization': 'Bearer ' + token,
            },
            body: formData,
        });

        if (response.status === 401) {
            logout();
            throw new Error('登录状态已失效');
        }

        const payload = await response.json();
        if (!response.ok || payload.code !== 0 || !payload.data || !payload.data.url) {
            throw new Error(payload.msg || failMessage || '图片上传失败');
        }

        return payload.data;
    }

    async function saveImageByUrl(imageUrl, dir, label, failMessage) {
        const url = normalizeHttpUrl(imageUrl, label || '图片链接', { autoPrependScheme: true });

        const result = await api('/admin/upload/image', {
            method: 'POST',
            data: {
                dir: String(dir || 'products'),
                image_url: url,
            },
        });

        if (!result || !result.url) {
            throw new Error(failMessage || '图片保存失败');
        }

        return result;
    }

    function getConfigUploadDirectory(groupKey) {
        const normalizedGroup = String(groupKey || '').trim();
        if (normalizedGroup === 'website') {
            return 'website';
        }

        return normalizedGroup ? ('configs/' + normalizedGroup) : 'configs';
    }

    async function uploadProductImageFile(file) {
        return uploadImageFile(file, 'products', '请选择图片文件', '图片上传失败');
    }

    async function saveProductImageByUrl(imageUrl) {
        return saveImageByUrl(imageUrl, 'products', '图片链接', '图片保存失败');
    }

    async function uploadTutorialImageFile(file) {
        if (!file) {
            throw new Error('请选择图片文件');
        }

        const formData = new FormData();
        formData.append('file', file);
        formData.append('dir', 'tutorials');

        const response = await fetch('/admin/upload/image', {
            method: 'POST',
            headers: {
                'Authorization': 'Bearer ' + token,
            },
            body: formData,
        });

        if (response.status === 401) {
            logout();
            throw new Error('登录状态已失效');
        }

        const payload = await response.json();
        if (!response.ok || payload.code !== 0 || !payload.data || !payload.data.url) {
            throw new Error(payload.msg || '教程图片上传失败');
        }

        return payload.data;
    }

    async function saveTutorialImageByUrl(imageUrl) {
        const url = String(imageUrl || '').trim();
        if (!url) {
            throw new Error('请先输入图片链接');
        }

        const result = await api('/admin/upload/image', {
            method: 'POST',
            data: {
                dir: 'tutorials',
                image_url: url,
            },
        });

        if (!result || !result.url) {
            throw new Error('教程图片保存失败');
        }

        return result;
    }

    function getCardResourceRichEditor() {
        return document.getElementById('cardResourceRichEditor');
    }

    function saveRichEditorSelection() {
        const editor = getCardResourceRichEditor();
        const selection = window.getSelection ? window.getSelection() : null;
        if (!editor || !selection || selection.rangeCount <= 0) {
            return;
        }

        const range = selection.getRangeAt(0);
        if (!editor.contains(range.commonAncestorContainer)) {
            return;
        }

        activeRichEditorRange = range.cloneRange();
    }

    function restoreRichEditorSelection() {
        const editor = getCardResourceRichEditor();
        const selection = window.getSelection ? window.getSelection() : null;
        if (!editor || !selection) {
            return false;
        }

        editor.focus();
        selection.removeAllRanges();

        if (activeRichEditorRange) {
            selection.addRange(activeRichEditorRange);
            return true;
        }

        const range = document.createRange();
        range.selectNodeContents(editor);
        range.collapse(false);
        selection.addRange(range);
        activeRichEditorRange = range.cloneRange();
        return true;
    }

    function insertRichEditorHtml(html) {
        const editor = getCardResourceRichEditor();
        if (!editor || !html) {
            return;
        }

        restoreRichEditorSelection();

        if (document.queryCommandSupported && document.queryCommandSupported('insertHTML')) {
            document.execCommand('insertHTML', false, html);
            saveRichEditorSelection();
            return;
        }

        const selection = window.getSelection ? window.getSelection() : null;
        if (!selection || selection.rangeCount <= 0) {
            return;
        }

        const range = selection.getRangeAt(0);
        range.deleteContents();

        const container = document.createElement('div');
        container.innerHTML = html;
        const fragment = document.createDocumentFragment();
        let lastNode = null;
        while (container.firstChild) {
            lastNode = fragment.appendChild(container.firstChild);
        }
        range.insertNode(fragment);

        if (lastNode) {
            range.setStartAfter(lastNode);
            range.collapse(true);
            selection.removeAllRanges();
            selection.addRange(range);
            activeRichEditorRange = range.cloneRange();
        }
    }

    function normalizeHttpUrl(url, label) {
        const text = String(url || '').trim();
        if (!text) {
            throw new Error((label || '链接') + '不能为空');
        }

        let parsed;
        try {
            parsed = new URL(text);
        } catch (error) {
            throw new Error((label || '链接') + '格式不正确');
        }

        if (!/^https?:$/i.test(parsed.protocol)) {
            throw new Error((label || '链接') + '仅支持 http 或 https');
        }

        return parsed.toString();
    }

    function buildRichImageHtml(imageUrl) {
        const safeUrl = normalizeHttpUrl(imageUrl, '图片链接');
        return '<figure class="rich-media-card"><img src="' + escapeHtmlAttr(safeUrl) + '" alt="教程图片" loading="lazy"></figure><p><br></p>';
    }

    function buildRichVideoHtml(videoUrl) {
        const safeUrl = normalizeHttpUrl(videoUrl, '视频链接');
        const isDirectVideo = /\.(mp4|webm|ogg|mov)([?#].*)?$/i.test(safeUrl);

        if (isDirectVideo) {
            return '<div class="rich-video-card">'
                + '<div class="rich-video-card-title">视频教程</div>'
                + '<video controls preload="metadata" src="' + escapeHtmlAttr(safeUrl) + '"></video>'
                + '<a class="resource-link" href="' + escapeHtmlAttr(safeUrl) + '" target="_blank" rel="noreferrer">打开视频链接</a>'
                + '</div><p><br></p>';
        }

        return '<div class="rich-video-card">'
            + '<div class="rich-video-card-title">视频教程链接</div>'
            + '<a class="resource-link" href="' + escapeHtmlAttr(safeUrl) + '" target="_blank" rel="noreferrer">' + escapeHtml(safeUrl) + '</a>'
            + '</div><p><br></p>';
    }

    async function saveTutorialImageByUrl(imageUrl) {
        const url = normalizeHttpUrl(imageUrl, '图片链接', { autoPrependScheme: true });

        const result = await api('/admin/upload/image', {
            method: 'POST',
            data: {
                dir: 'tutorials',
                image_url: url,
            },
        });

        if (!result || !result.url) {
            throw new Error('教程图片保存失败');
        }

        return result;
    }

    function normalizeHttpUrl(url, label, options) {
        const config = options || {};
        let text = String(url || '').trim().replace(/^['"]+|['"]+$/g, '');
        if (!text) {
            throw new Error((label || '链接') + '不能为空');
        }

        if (/^\/\//.test(text)) {
            text = 'https:' + text;
        }
        if (config.autoPrependScheme && !/^[a-zA-Z][a-zA-Z\d+\-.]*:/.test(text) && /^[^\/\s]+\.[^\/\s]+/.test(text)) {
            text = 'https://' + text;
        }

        let parsed;
        try {
            parsed = config.allowRelative ? new URL(text, window.location.origin) : new URL(text);
        } catch (error) {
            throw new Error((label || '链接') + '格式不正确');
        }

        if (!/^https?:$/i.test(parsed.protocol)) {
            throw new Error((label || '链接') + '仅支持 http 或 https');
        }

        if (config.allowRelative && /^\/(?!\/)/.test(text)) {
            return parsed.pathname + parsed.search + parsed.hash;
        }

        return parsed.toString();
    }

    function buildRichImageHtml(imageUrl) {
        const safeUrl = normalizeHttpUrl(imageUrl, '图片链接', { allowRelative: true });
        return '<figure class="rich-media-card"><img src="' + escapeHtmlAttr(safeUrl) + '" alt="教程图片" loading="lazy"></figure><p><br></p>';
    }

    function buildRichVideoHtml(videoUrl) {
        const safeUrl = normalizeHttpUrl(videoUrl, '视频链接', { allowRelative: true, autoPrependScheme: true });
        const isDirectVideo = /\.(mp4|webm|ogg|mov)([?#].*)?$/i.test(safeUrl);

        if (isDirectVideo) {
            return '<div class="rich-video-card">'
                + '<div class="rich-video-card-title">视频教程</div>'
                + '<video controls preload="metadata" src="' + escapeHtmlAttr(safeUrl) + '"></video>'
                + '<a class="resource-link" href="' + escapeHtmlAttr(safeUrl) + '" target="_blank" rel="noreferrer">打开视频链接</a>'
                + '</div><p><br></p>';
        }

        return '<div class="rich-video-card">'
            + '<div class="rich-video-card-title">视频教程链接</div>'
            + '<a class="resource-link" href="' + escapeHtmlAttr(safeUrl) + '" target="_blank" rel="noreferrer">' + escapeHtml(safeUrl) + '</a>'
            + '</div><p><br></p>';
    }

    function renderProductCategoryFilterChip(value, label, isActive, extraClass) {
        const className = 'product-filter-chip'
            + (extraClass ? ' ' + extraClass : '')
            + (isActive ? ' active' : '');

        return '<button type="button" class="' + className + '" data-product-category-filter="' + escapeHtmlAttr(value == null ? '' : String(value)) + '">'
            + escapeHtml(label)
            + '</button>';
    }

    function renderProductCategoryRow(label, nodes, activeId, parentId) {
        if (!nodes.length) {
            return '';
        }

        return '<div class="product-category-row">'
            + '<div class="product-category-row-label">' + escapeHtml(label) + '</div>'
            + '<div class="product-category-row-items">'
            + renderProductCategoryFilterChip(parentId, '全部', activeId === 0, 'ghost')
            + nodes.map((node) => renderProductCategoryFilterChip(node.id, node.name, Number(activeId) === Number(node.id), '')).join('')
            + '</div>'
            + '</div>';
    }

    function renderProductCategoryBrowser() {
        const typeFlat = getCategoryFlatByGroup('type');
        if (!typeFlat.length) {
            return '<div class="product-category-browser empty">暂无分类，可先去分类模块创建分类结构</div>';
        }

        const currentId = Number(state.productFilters.categoryId || 0);
        const path = getCategoryPathNodes(currentId, typeFlat);
        const rootNodes = getCategoryChildren(0, typeFlat);
        const rootId = path[0] ? Number(path[0].id) : 0;
        const secondId = path[1] ? Number(path[1].id) : 0;
        const thirdId = path[2] ? Number(path[2].id) : 0;
        let html = '<div class="product-category-browser">';

        html += '<div class="product-category-row">'
            + '<div class="product-category-row-label">分类筛选</div>'
            + '<div class="product-category-row-items">'
            + renderProductCategoryFilterChip('', '全部分类', currentId === 0, 'all')
            + rootNodes.map((node) => renderProductCategoryFilterChip(node.id, node.name, rootId === Number(node.id), 'root')).join('')
            + '</div>'
            + '</div>';

        if (rootId > 0) {
            html += renderProductCategoryRow('二级分类', getCategoryChildren(rootId, typeFlat), secondId, rootId);
        }
        if (secondId > 0) {
            html += renderProductCategoryRow('三级分类', getCategoryChildren(secondId, typeFlat), thirdId, secondId);
        }
        if (thirdId > 0) {
            html += renderProductCategoryRow('四级分类', getCategoryChildren(thirdId, typeFlat), path[3] ? Number(path[3].id) : 0, thirdId);
        }

        html += '</div>';
        return html;
    }

    function renderProductSummaryCard(label, value, subtext) {
        return '<div class="product-summary-card">'
            + '<div class="product-summary-label">' + escapeHtml(label) + '</div>'
            + '<div class="product-summary-value">' + escapeHtml(String(value)) + '</div>'
            + '<div class="product-summary-subtext">' + escapeHtml(subtext) + '</div>'
            + '</div>';
    }

    function renderProductSummary(list) {
        const total = list.length;
        const activeCount = list.filter((item) => Number(item.status) === 1).length;
        const inactiveCount = list.filter((item) => Number(item.status) !== 1).length;
        const typeFlat = getCategoryFlatByGroup('type');
        const filterCategoryText = state.productFilters.categoryId
            ? getCategoryPathText(state.productFilters.categoryId, '当前分类', typeFlat)
            : '全部分类';
        const filterKeywordText = state.productFilters.keyword || '未输入关键词';
        const filterStatusText = state.productFilters.status === '1'
            ? '只看上架'
            : (state.productFilters.status === '0' ? '只看下架' : '全部状态');

        return '<div class="product-summary-grid">'
            + renderProductSummaryCard('商品总数', total, '当前筛选结果')
            + renderProductSummaryCard('上架商品', activeCount, '可售中的商品')
            + renderProductSummaryCard('下架商品', inactiveCount, '暂不销售')
            + '</div>'
            + '<div class="product-filter-note">当前分类：' + escapeHtml(filterCategoryText) + '，状态：' + escapeHtml(filterStatusText) + '，关键词：' + escapeHtml(filterKeywordText) + '</div>';
    }

    async function renderProducts() {
        app.innerHTML = '<div class="panel"><div class="panel-title">商品管理</div><div class="empty">加载中...</div></div>';
        try {
            await Promise.all([loadCategories('type'), loadCategories('kind')]);
            syncProductFilters();

            const data = await api('/admin/products', {
                query: {
                    page: 1,
                    limit: 500,
                    keyword: state.productFilters.keyword,
                    category_id: state.productFilters.categoryId,
                    status: state.productFilters.status,
                    is_featured: state.productFilters.featured,
                }
            });
            const list = data.list || [];
            const total = Number(data.pagination && data.pagination.total ? data.pagination.total : list.length);
            state.productList = list;

            app.innerHTML = '<div class="panel"><div class="panel-title">商品管理</div>'
                + '<div class="toolbar product-toolbar">'
                + '<input id="productKeywordFilter" placeholder="搜索商品中文名或英文名" value="' + escapeHtmlAttr(state.productFilters.keyword) + '">'
                + '<select id="productStatusFilter"><option value="">全部状态</option><option value="1">只看上架</option><option value="0">只看下架</option></select>'
                + '<select id="productFeaturedFilter"><option value="">全部精选</option><option value="1">只看精选</option><option value="0">非精选</option></select>'
                + '<button class="btn btn-small" id="searchProductsBtn">筛选</button>'
                + '<button class="btn btn-warning" id="resetProductsBtn">重置</button>'
                + '<button class="btn btn-danger" id="batchDeleteProductsBtn" style="display:none">批量删除</button>'
                + '<button class="btn btn-info" id="batchFeaturedProductsBtn" style="display:none">批量精选</button>'
                + '<button class="btn btn-main" id="addProductBtn">新增商品</button>'
                + '<span class="muted">当前共 ' + escapeHtml(String(total)) + ' 个商品，已展示 ' + escapeHtml(String(list.length)) + ' 个</span>'
                + '</div>'
                + renderProductCategoryBrowser()
                + renderProductSummary(list)
                + renderProductTable(list)
                + '</div>';

            document.getElementById('productStatusFilter').value = state.productFilters.status;
            const featuredFilterEl = document.getElementById('productFeaturedFilter');
            if (featuredFilterEl) {
                featuredFilterEl.value = state.productFilters.featured || '';
            }
            document.getElementById('searchProductsBtn').addEventListener('click', function () {
                state.productFilters.keyword = valueById('productKeywordFilter');
                state.productFilters.status = valueById('productStatusFilter');
                state.productFilters.featured = valueById('productFeaturedFilter');
                renderProducts();
            });
            document.getElementById('productKeywordFilter').addEventListener('keydown', function (event) {
                if (event.key !== 'Enter') {
                    return;
                }

                state.productFilters.keyword = valueById('productKeywordFilter');
                state.productFilters.status = valueById('productStatusFilter');
                state.productFilters.featured = valueById('productFeaturedFilter');
                renderProducts();
            });
            document.querySelectorAll('[data-product-category-filter]').forEach((btn) => {
                btn.addEventListener('click', function () {
                    state.productFilters.categoryId = this.dataset.productCategoryFilter || '';
                    renderProducts();
                });
            });
            document.getElementById('resetProductsBtn').addEventListener('click', function () {
                state.productFilters.categoryId = '';
                state.productFilters.keyword = '';
                state.productFilters.status = '';
                state.productFilters.featured = '';
                renderProducts();
            });
            document.getElementById('addProductBtn').addEventListener('click', function () {
                openProductModal(null);
            });
            document.getElementById('batchDeleteProductsBtn').addEventListener('click', async function () {
                const selectedIds = state.selectedProductIds || [];
                if (selectedIds.length === 0) {
                    alert('请先选择要删除的商品');
                    return;
                }
                
                if (!confirm(`确定要删除选中的 ${selectedIds.length} 个商品吗？\n\n删除后会自动解除所有绑定的卡密和卡密资源。`)) {
                    return;
                }
                
                try {
                    const result = await api('/admin/products/delete-batch', {
                        method: 'POST',
                        data: { ids: selectedIds }
                    });
                    alert(result.message || '批量删除成功');
                    state.selectedProductIds = [];
                    await renderProducts();
                } catch (error) {
                    alert('批量删除失败：' + error.message);
                }
            });

            const batchFeaturedBtn = document.getElementById('batchFeaturedProductsBtn');
            if (batchFeaturedBtn) {
                batchFeaturedBtn.addEventListener('click', async function () {
                    const selectedIds = state.selectedProductIds || [];
                    if (selectedIds.length === 0) {
                        alert('请先选择商品');
                        return;
                    }
                    let isFeatured = 1;
                    const choice = prompt('设置精选状态：\n  1 = 设为精选\n  0 = 取消精选\n\n请输入 1 或 0：', '1');
                    if (choice === null) return;
                    isFeatured = String(choice).trim() === '0' ? 0 : 1;
                    try {
                        const result = await api('/admin/products/toggle-featured-batch', {
                            method: 'POST',
                            data: { ids: selectedIds, is_featured: isFeatured },
                        });
                        showAdminToast(result.message || '操作成功');
                        state.selectedProductIds = [];
                        await renderProducts();
                    } catch (error) {
                        alert('批量操作失败：' + error.message);
                    }
                });
            }
            bindProductTableEvents();
        } catch (error) {
            renderError(error, '商品管理');
        }
    }

    function renderProductStatusTag(status) {
        return Number(status) === 1
            ? '<span class="tag tag-green">上架</span>'
            : '<span class="tag tag-gray">下架</span>';
    }

    // ============ 精选管理页面 ============
    if (!state.featuredState) {
        state.featuredState = {
            list: [],
            keyword: '',
            filter: 'all',
            categoryId: '',
            viewMode: 'grid',
        };
    }
    var featuredState = state.featuredState;

    async function renderFeatured() {
        if (!state.featuredState) {
            state.featuredState = {
                list: [],
                keyword: '',
                filter: 'all',
                categoryId: '',
                viewMode: 'grid',
            };
        }
        featuredState = state.featuredState;
        app.innerHTML = '<div class="panel"><div class="panel-title">精选管理</div><div class="empty">加载中...</div></div>';
        try {
            await Promise.all([loadCategories('type'), loadCategories('kind')]);

            const data = await api('/admin/products', {
                query: {
                    page: 1,
                    limit: 500,
                    keyword: featuredState.keyword,
                    category_id: featuredState.categoryId,
                    status: 1,
                }
            });
            featuredState.list = data.list || [];

            const total = featuredState.list.length;
            const featuredCount = featuredState.list.filter(p => Number(p.is_featured) === 1).length;
            const unfeaturedCount = total - featuredCount;

            const filteredList = featuredState.list.filter((p) => {
                if (featuredState.filter === 'featured') return Number(p.is_featured) === 1;
                if (featuredState.filter === 'unfeatured') return Number(p.is_featured) !== 1;
                return true;
            });

            const isGrid = featuredState.viewMode === 'grid';
            const contentHtml = filteredList.length === 0
                ? '<div class="featured-empty" style="text-align:center;padding:60px 0;color:#94a3b8;">没有匹配的商品</div>'
                : (isGrid
                    ? '<div class="featured-grid">' + filteredList.map(renderFeaturedCard).join('') + '</div>'
                    : '<div class="featured-list">' + filteredList.map(renderFeaturedRow).join('') + '</div>');

            app.innerHTML = '<div class="panel"><div class="panel-title">精选管理</div>'
                + '<div class="muted" style="margin-bottom:14px;">设置游戏 / 应用的精选状态。客户端轮播图等模块会优先展示精选商品。</div>'
                + '<div class="featured-summary">'
                + '<div class="featured-summary-card"><div class="featured-summary-label">商品总数</div><div class="featured-summary-value">' + total + '</div></div>'
                + '<div class="featured-summary-card" style="border-color:#f5a623;"><div class="featured-summary-label">已精选</div><div class="featured-summary-value" style="color:#f5a623;">' + featuredCount + '</div></div>'
                + '<div class="featured-summary-card" style="border-color:#cbd5e1;background:#ffffff;"><div class="featured-summary-label">未精选</div><div class="featured-summary-value" style="color:#64748b;">' + unfeaturedCount + '</div></div>'
                + '</div>'
                + '<div class="featured-toolbar">'
                + '<input id="featuredKeyword" placeholder="搜索商品中文名 / 英文名" value="' + escapeHtmlAttr(featuredState.keyword) + '" style="padding:8px 12px;border:1px solid #e2e8f0;border-radius:8px;min-width:220px;">'
                + '<select id="featuredFilter" style="padding:8px 12px;border:1px solid #e2e8f0;border-radius:8px;">'
                + '<option value="all"' + (featuredState.filter === 'all' ? ' selected' : '') + '>全部</option>'
                + '<option value="featured"' + (featuredState.filter === 'featured' ? ' selected' : '') + '>仅精选</option>'
                + '<option value="unfeatured"' + (featuredState.filter === 'unfeatured' ? ' selected' : '') + '>仅未精选</option>'
                + '</select>'
                + '<button class="btn btn-small" id="featuredSearchBtn">查询</button>'
                + '<button class="btn btn-warning" id="featuredResetBtn">重置</button>'
                + '<button class="btn btn-info" id="featuredSelectAllBtn">全部设为精选</button>'
                + '<button class="btn btn-light" id="featuredClearAllBtn">全部取消精选</button>'
                + '<div class="featured-view-switch" style="margin-left:auto;">'
                + '<button class="featured-view-btn ' + (isGrid ? 'active' : '') + '" id="featuredViewGridBtn">▦ 宫格</button>'
                + '<button class="featured-view-btn ' + (!isGrid ? 'active' : '') + '" id="featuredViewListBtn">☰ 列表</button>'
                + '</div>'
                + '</div>'
                + contentHtml
                + '</div>';

            // 绑定事件
            document.getElementById('featuredSearchBtn').addEventListener('click', () => {
                featuredState.keyword = valueById('featuredKeyword');
                featuredState.filter = valueById('featuredFilter');
                renderFeatured();
            });
            document.getElementById('featuredResetBtn').addEventListener('click', () => {
                featuredState.keyword = '';
                featuredState.filter = 'all';
                featuredState.categoryId = '';
                renderFeatured();
            });
            document.getElementById('featuredKeyword').addEventListener('keydown', (e) => {
                if (e.key === 'Enter') {
                    featuredState.keyword = valueById('featuredKeyword');
                    renderFeatured();
                }
            });
            document.getElementById('featuredSelectAllBtn').addEventListener('click', () => bulkToggleFeatured(1));
            document.getElementById('featuredClearAllBtn').addEventListener('click', () => bulkToggleFeatured(0));

            document.getElementById('featuredViewGridBtn').addEventListener('click', () => {
                featuredState.viewMode = 'grid';
                renderFeatured();
            });
            document.getElementById('featuredViewListBtn').addEventListener('click', () => {
                featuredState.viewMode = 'list';
                renderFeatured();
            });

            document.querySelectorAll('[data-featured-toggle]').forEach((btn) => {
                btn.addEventListener('click', async (e) => {
                    e.stopPropagation();
                    const id = Number(btn.dataset.featuredToggle);
                    const product = featuredState.list.find((p) => Number(p.id) === id);
                    if (!product) return;
                    const next = Number(product.is_featured) === 1 ? 0 : 1;
                    try {
                        await api('/admin/products/' + id + '/featured', {
                            method: 'PUT',
                            data: { is_featured: next },
                        });
                        product.is_featured = next;
                        showAdminToast(next === 1 ? '已设为精选' : '已取消精选');
                        renderFeatured();
                    } catch (err) {
                        alert(err.message || '操作失败');
                    }
                });
            });
        } catch (error) {
            renderError(error, '精选管理');
        }
    }

    function renderFeaturedCard(product) {
        const isFeatured = Number(product.is_featured) === 1;
        const cover = product.cover_image
            ? '<img class="featured-card-cover" src="' + escapeHtmlAttr(product.cover_image) + '" alt="">'
            : '<div class="featured-card-cover-empty">暂无图片</div>';
        const catName = (product.category && product.category.name) ? product.category.name : '未分类';
        return '<div class="featured-card' + (isFeatured ? ' is-featured' : '') + '">'
            + (isFeatured ? '<span class="featured-badge">精选</span>' : '')
            + cover
            + '<div class="featured-card-name">' + escapeHtml(product.name || '') + '</div>'
            + '<div class="featured-card-en">' + escapeHtml(product.name_en || '—') + '</div>'
            + '<div class="featured-card-meta">'
            + '<span class="featured-card-energy">⚡ ' + escapeHtml(String(product.exchange_energy || 0)) + '</span>'
            + '<span class="featured-card-cat">' + escapeHtml(catName) + '</span>'
            + '</div>'
            + '<button class="featured-card-toggle ' + (isFeatured ? 'remove' : 'add') + '" data-featured-toggle="' + product.id + '">'
            + (isFeatured ? '取消精选' : '设为精选')
            + '</button>'
            + '</div>';
    }

    function renderFeaturedRow(product) {
        const isFeatured = Number(product.is_featured) === 1;
        const cover = product.cover_image
            ? '<img class="featured-row-cover" src="' + escapeHtmlAttr(product.cover_image) + '" alt="">'
            : '<div class="featured-row-cover" style="display:flex;align-items:center;justify-content:center;color:#94a3b8;font-size:11px;">无图</div>';
        const catName = (product.category && product.category.name) ? product.category.name : '未分类';
        return '<div class="featured-row' + (isFeatured ? ' is-featured' : '') + '">'
            + cover
            + '<div class="featured-row-info">'
            + '<div class="featured-row-name">' + escapeHtml(product.name || '') + '</div>'
            + '<div class="featured-row-en">' + escapeHtml(product.name_en || '—') + '</div>'
            + '</div>'
            + '<div class="featured-row-meta">'
            + '<span class="featured-row-energy">⚡ ' + escapeHtml(String(product.exchange_energy || 0)) + ' 能量</span>'
            + '<span class="featured-row-cat">' + escapeHtml(catName) + '</span>'
            + '</div>'
            + '<span class="featured-row-status ' + (isFeatured ? 'on' : 'off') + '">' + (isFeatured ? '已精选' : '未精选') + '</span>'
            + '<button class="featured-row-action ' + (isFeatured ? 'remove' : 'add') + '" data-featured-toggle="' + product.id + '">'
            + (isFeatured ? '取消精选' : '设为精选')
            + '</button>'
            + '</div>';
    }

    async function bulkToggleFeatured(isFeatured) {
        const text = isFeatured === 1 ? '设为精选' : '取消精选';
        if (!confirm('确认将当前列表中的所有商品全部' + text + '吗？')) return;
        const ids = featuredState.list.map((p) => Number(p.id));
        if (ids.length === 0) return;
        try {
            const res = await api('/admin/products/toggle-featured-batch', {
                method: 'POST',
                data: { ids: ids, is_featured: isFeatured },
            });
            showAdminToast(res.message || ('已批量' + text));
            renderFeatured();
        } catch (err) {
            alert(err.message || '批量操作失败');
        }
    }

    // ============ 许愿管理页面 ============
    if (!state.wishesState) {
        state.wishesState = {
            list: [],
            keyword: '',
            status: '',
            stats: { pending: 0, processing: 0, completed: 0, rejected: 0 },
        };
    }
    var wishesState = state.wishesState;

    var wishStatusMap = {
        0: { label: '待处理', color: '#f5a623', bg: '#fff8eb' },
        1: { label: '处理中', color: '#1f6bff', bg: '#eff5ff' },
        2: { label: '已上架', color: '#16a37a', bg: '#e9f8f1' },
        3: { label: '已拒绝', color: '#94a3b8', bg: '#f1f5f9' },
    };

    async function renderWishes() {
        if (!state.wishesState) {
            state.wishesState = {
                list: [],
                keyword: '',
                status: '',
                stats: { pending: 0, processing: 0, completed: 0, rejected: 0 },
            };
        }
        wishesState = state.wishesState;
        app.innerHTML = '<div class="panel"><div class="panel-title">许愿管理</div><div class="empty">加载中...</div></div>';
        try {
            const data = await api('/admin/wishes', {
                query: {
                    page: 1,
                    limit: 200,
                    keyword: wishesState.keyword,
                    status: wishesState.status,
                },
            });
            wishesState.list = data.list || [];
            wishesState.stats = data.stats || wishesState.stats;

            const html = '<div class="panel"><div class="panel-title">许愿管理</div>'
                + '<div class="muted" style="margin-bottom:14px;">用户提交的"想要的游戏 / 应用"许愿，按"待处理 → 处理中 → 已上架 / 已拒绝"流转。</div>'
                + renderWishStats(wishesState.stats)
                + renderWishToolbar()
                + renderWishTable(wishesState.list)
                + '</div>';
            app.innerHTML = html;
            bindWishEvents();
        } catch (error) {
            renderError(error, '许愿管理');
        }
    }

    function renderWishStats(stats) {
        const card = (label, value, color) => '<div style="flex:1;padding:18px 22px;border-radius:12px;background:#ffffff;border:2px solid #eef2f7;">'
            + '<div style="font-size:13px;color:#94a3b8;">' + label + '</div>'
            + '<div style="margin-top:6px;font-size:26px;font-weight:700;color:' + color + ';">' + value + '</div>'
            + '</div>';
        return '<div style="display:flex;gap:14px;margin-bottom:16px;">'
            + card('待处理', stats.pending || 0, '#f5a623')
            + card('处理中', stats.processing || 0, '#1f6bff')
            + card('已上架', stats.completed || 0, '#16a37a')
            + card('已拒绝', stats.rejected || 0, '#94a3b8')
            + '</div>';
    }

    function renderWishToolbar() {
        return '<div class="toolbar" style="margin-bottom:14px;">'
            + '<input id="wishKeyword" placeholder="搜索名称 / 描述 / 联系方式" value="' + escapeHtmlAttr(wishesState.keyword) + '" style="padding:8px 12px;border:1px solid #e2e8f0;border-radius:8px;min-width:240px;">'
            + '<select id="wishStatus" style="padding:8px 12px;border:1px solid #e2e8f0;border-radius:8px;">'
            + '<option value=""' + (wishesState.status === '' ? ' selected' : '') + '>全部状态</option>'
            + '<option value="0"' + (wishesState.status === '0' ? ' selected' : '') + '>待处理</option>'
            + '<option value="1"' + (wishesState.status === '1' ? ' selected' : '') + '>处理中</option>'
            + '<option value="2"' + (wishesState.status === '2' ? ' selected' : '') + '>已上架</option>'
            + '<option value="3"' + (wishesState.status === '3' ? ' selected' : '') + '>已拒绝</option>'
            + '</select>'
            + '<button class="btn btn-small" id="wishSearchBtn">查询</button>'
            + '<button class="btn btn-warning" id="wishResetBtn">重置</button>'
            + '</div>';
    }

    function renderWishTable(list) {
        if (!list.length) {
            return '<div class="empty">暂无许愿数据</div>';
        }
        const rows = list.map((item) => {
            const status = Number(item.status || 0);
            const meta = wishStatusMap[status] || wishStatusMap[0];
            const userText = item.user_id && Number(item.user_id) > 0
                ? escapeHtml((item.user_nickname || item.user_username || '用户#' + item.user_id))
                : '<span class="muted">未登录</span>';
            return '<tr>'
                + '<td>' + escapeHtml(String(item.id)) + '</td>'
                + '<td><strong>' + escapeHtml(item.name || '') + '</strong></td>'
                + '<td>' + (item.description ? escapeHtml(item.description) : '<span class="muted">-</span>') + '</td>'
                + '<td>' + (item.contact ? escapeHtml(item.contact) : '<span class="muted">-</span>') + '</td>'
                + '<td>' + userText + '</td>'
                + '<td><span class="tag" style="background:' + meta.bg + ';color:' + meta.color + ';">' + meta.label + '</span></td>'
                + '<td>' + (item.admin_remark ? escapeHtml(item.admin_remark) : '<span class="muted">-</span>') + '</td>'
                + '<td>' + escapeHtml(item.created_at || '-') + '</td>'
                + '<td class="actions">'
                + '<button class="link-btn" data-wish-update="' + item.id + '">处理</button>'
                + '<button class="link-btn danger" data-wish-delete="' + item.id + '">删除</button>'
                + '</td>'
                + '</tr>';
        }).join('');
        return '<table class="product-table"><thead><tr>'
            + '<th>ID</th><th>名称</th><th>描述</th><th>联系方式</th><th>用户</th><th>状态</th><th>管理员备注</th><th>提交时间</th><th>操作</th>'
            + '</tr></thead><tbody>' + rows + '</tbody></table>';
    }

    function bindWishEvents() {
        document.getElementById('wishSearchBtn').addEventListener('click', () => {
            wishesState.keyword = valueById('wishKeyword');
            wishesState.status = valueById('wishStatus');
            renderWishes();
        });
        document.getElementById('wishResetBtn').addEventListener('click', () => {
            wishesState.keyword = '';
            wishesState.status = '';
            renderWishes();
        });
        document.getElementById('wishKeyword').addEventListener('keydown', (e) => {
            if (e.key === 'Enter') {
                wishesState.keyword = valueById('wishKeyword');
                renderWishes();
            }
        });
        document.querySelectorAll('[data-wish-update]').forEach((btn) => {
            btn.addEventListener('click', () => {
                const id = Number(btn.dataset.wishUpdate);
                const wish = wishesState.list.find((w) => Number(w.id) === id);
                if (wish) openWishUpdateModal(wish);
            });
        });
        document.querySelectorAll('[data-wish-delete]').forEach((btn) => {
            btn.addEventListener('click', async () => {
                const id = Number(btn.dataset.wishDelete);
                if (!confirm('确认删除该许愿吗？')) return;
                try {
                    await api('/admin/wishes/' + id, { method: 'DELETE' });
                    showAdminToast('已删除');
                    renderWishes();
                } catch (err) {
                    alert(err.message || '删除失败');
                }
            });
        });
    }

    function openWishUpdateModal(wish) {
        const body = '<div class="form-row"><label>名称</label><div>' + escapeHtml(wish.name || '') + '</div></div>'
            + (wish.description ? '<div class="form-row"><label>描述</label><div>' + escapeHtml(wish.description) + '</div></div>' : '')
            + (wish.contact ? '<div class="form-row"><label>联系方式</label><div>' + escapeHtml(wish.contact) + '</div></div>' : '')
            + '<div class="form-row"><label>状态</label><select id="wishUpdateStatus">'
            + '<option value="0"' + (Number(wish.status) === 0 ? ' selected' : '') + '>待处理</option>'
            + '<option value="1"' + (Number(wish.status) === 1 ? ' selected' : '') + '>处理中</option>'
            + '<option value="2"' + (Number(wish.status) === 2 ? ' selected' : '') + '>已上架</option>'
            + '<option value="3"' + (Number(wish.status) === 3 ? ' selected' : '') + '>已拒绝</option>'
            + '</select></div>'
            + '<div class="form-row"><label>管理员备注</label><textarea id="wishUpdateRemark" placeholder="选填，如：将于本周内上架">' + escapeHtml(wish.admin_remark || '') + '</textarea></div>';

        openModal('处理许愿', body, async () => {
            const status = Number(valueById('wishUpdateStatus') || 0);
            const remark = valueById('wishUpdateRemark') || '';
            try {
                await api('/admin/wishes/' + wish.id, {
                    method: 'PUT',
                    data: { status: status, admin_remark: remark },
                });
                showAdminToast('已更新');
                closeModal();
                renderWishes();
            } catch (err) {
                alert(err.message || '更新失败');
            }
        });
    }

    function renderProductTable(list) {
        const typeFlat = getCategoryFlatByGroup('type');
        const kindFlat = getCategoryFlatByGroup('kind');
        if (!list.length) {
            return '<div class="empty">当前筛选条件下暂无商品，可以切换分类或直接新增商品</div>';
        }

        const selectedIds = state.selectedProductIds || [];
        const allSelected = list.length > 0 && list.every(item => selectedIds.includes(Number(item.id)));

        const rows = list.map((item) => {
            const categoryId = Number(item.category_id || 0);
            const kindCategoryId = Number(item.kind_category_id || 0);
            const categoryLevels = getProductCategoryLevelMap(categoryId, typeFlat);
            const typePathText = getCategoryPathText(categoryId, '未分类', typeFlat);
            const kindPathText = getCategoryPathText(kindCategoryId, '未分类', kindFlat);
            const hasDescription = item.description && item.description.trim() !== '';
            const descriptionHtml = hasDescription
                ? escapeHtml(item.description)
                : '<span class="muted">暂无描述</span>';
            const galleryImages = normalizeProductGalleryImages(item.gallery_images);
            const categoryTags = [];
            const mediaTags = [];
            const isSelected = selectedIds.includes(Number(item.id));

            if (Number(item.exchange_energy || 0) > 0) {
                categoryTags.push('<span class="tag tag-yellow">兑换能量 ' + escapeHtml(String(item.exchange_energy)) + '</span>');
            }
            mediaTags.push('<span class="tag tag-gray">轮播图 ' + escapeHtml(String(galleryImages.length)) + ' 张</span>');
            if (item.category && Number(item.category.status) === 0) {
                mediaTags.push('<span class="tag tag-yellow">分类停用</span>');
            }

            if (item.kind_category && Number(item.kind_category.status) === 0) {
                mediaTags.push('<span class="tag tag-yellow">类别分类停用</span>');
            }

            return '<tr>'
                + '<td><input type="checkbox" class="product-checkbox" data-product-id="' + item.id + '"' + (isSelected ? ' checked' : '') + '></td>'
                + '<td>' + escapeHtml(String(item.id)) + '</td>'
                + '<td>'
                + '<div class="product-info-block">'
                + (item.cover_image
                    ? '<div class="product-cover-thumb"><img src="' + escapeHtmlAttr(item.cover_image) + '" alt="' + escapeHtmlAttr(item.name || '') + '"></div>'
                    : '<div class="product-cover-thumb empty"><span>无图</span></div>')
                + '<div class="product-info-main">'
                + '<div class="product-cell-title">' + escapeHtml(item.name || '') + '</div>'
                + (item.name_en ? '<div class="product-name-sub">' + escapeHtml(item.name_en) + '</div>' : '<div class="product-name-sub muted">未填写英文名称</div>')
                + '<div class="product-cell-desc' + (hasDescription ? ' collapsible' : '') + '" data-product-id="' + item.id + '">'
                + descriptionHtml
                + (hasDescription ? '<span class="desc-toggle" data-product-id="' + item.id + '">展开</span>' : '')
                + '</div>'
                + (categoryTags.length ? '<div class="product-inline-tags">' + categoryTags.join('') + '</div>' : '')
                + '</div>'
                + '</div>'
                + '</td>'
                + '<td>'
                + '<div class="product-category-line"><strong>一级分类：</strong>' + escapeHtml(categoryLevels.level1 || '未分类') + '</div>'
                + '<div class="product-category-line"><strong>二级分类：</strong>' + escapeHtml(categoryLevels.level2 || '未设置') + '</div>'
                + (categoryLevels.level3 ? '<div class="product-category-line"><strong>三级分类：</strong>' + escapeHtml(categoryLevels.level3) + '</div>' : '')
                + (categoryLevels.level4 ? '<div class="product-category-line"><strong>四级分类：</strong>' + escapeHtml(categoryLevels.level4) + '</div>' : '')
                + '<div class="product-inline-tags">' + mediaTags.join('') + '</div>'
                + '</td>'
                + '<td>'
                + '<div class="product-price-main">' + escapeHtml(energy(item.exchange_energy)) + ' 能量</div>'
                + '</td>'
                + '<td>' + renderProductStatusTag(item.status) + '</td>'
                + '<td>' + renderProductFeaturedTag(item.is_featured) + '</td>'
                + '<td>' + escapeHtml(item.updated_at || item.created_at || '-') + '</td>'
                + '<td class="actions"><button class="link-btn" data-product-edit="' + item.id + '">编辑</button><button class="link-btn" data-product-toggle-featured="' + item.id + '">' + (Number(item.is_featured) === 1 ? '取消精选' : '设为精选') + '</button><button class="link-btn danger" data-product-delete="' + item.id + '">删除</button></td>'
                + '</tr>';
        }).join('');

        return '<table class="product-table"><thead><tr><th><input type="checkbox" id="selectAllProducts"' + (allSelected ? ' checked' : '') + '></th><th>ID</th><th>商品信息</th><th>分类层级</th><th>兑换能量</th><th>状态</th><th>精选</th><th>更新时间</th><th>操作</th></tr></thead><tbody>' + rows + '</tbody></table>';
    }

    function renderProductFeaturedTag(isFeatured) {
        return Number(isFeatured) === 1
            ? '<span class="tag tag-yellow">已精选</span>'
            : '<span class="tag tag-gray">未精选</span>';
    }

    function bindProductTableEvents() {
        // 全选/取消全选
        const selectAllCheckbox = document.getElementById('selectAllProducts');
        if (selectAllCheckbox) {
            selectAllCheckbox.addEventListener('change', function() {
                const checked = this.checked;
                state.selectedProductIds = checked ? state.productList.map(item => Number(item.id)) : [];
                document.querySelectorAll('.product-checkbox').forEach(cb => {
                    cb.checked = checked;
                });
                updateBatchDeleteButton();
            });
        }

        // 单个复选框
        document.querySelectorAll('.product-checkbox').forEach((checkbox) => {
            checkbox.addEventListener('change', function() {
                const productId = Number(this.dataset.productId);
                if (!state.selectedProductIds) {
                    state.selectedProductIds = [];
                }
                
                if (this.checked) {
                    if (!state.selectedProductIds.includes(productId)) {
                        state.selectedProductIds.push(productId);
                    }
                } else {
                    state.selectedProductIds = state.selectedProductIds.filter(id => id !== productId);
                }
                
                // 更新全选框状态
                if (selectAllCheckbox) {
                    selectAllCheckbox.checked = state.productList.length > 0 && 
                        state.productList.every(item => state.selectedProductIds.includes(Number(item.id)));
                }
                
                updateBatchDeleteButton();
            });
        });

        document.querySelectorAll('[data-product-edit]').forEach((btn) => {
            btn.addEventListener('click', function () {
                const id = Number(this.dataset.productEdit);
                const product = state.productList.find((item) => Number(item.id) === id);
                if (product) openProductModal(product);
            });
        });
        document.querySelectorAll('[data-product-delete]').forEach((btn) => {
            btn.addEventListener('click', async function () {
                const id = Number(this.dataset.productDelete);
                if (!confirm('确定删除这个商品吗？\n\n删除后会自动解除所有绑定的卡密和卡密资源。')) return;
                try {
                    await api('/admin/products/' + id, { method: 'DELETE' });
                    renderProducts();
                } catch (error) {
                    alert(error.message);
                }
            });
        });

        document.querySelectorAll('[data-product-toggle-featured]').forEach((btn) => {
            btn.addEventListener('click', async function () {
                const id = Number(this.dataset.productToggleFeatured);
                const product = state.productList.find((item) => Number(item.id) === id);
                const currentFeatured = product ? Number(product.is_featured || 0) : 0;
                const nextFeatured = currentFeatured === 1 ? 0 : 1;
                try {
                    const result = await api('/admin/products/' + id + '/featured', {
                        method: 'PUT',
                        data: { is_featured: nextFeatured },
                    });
                    if (product) {
                        product.is_featured = nextFeatured;
                    }
                    renderProducts();
                    if (result && result.message) {
                        // 简单 toast 提示
                        const msg = result.message;
                        showAdminToast(msg);
                    }
                } catch (error) {
                    alert(error.message || '操作失败');
                }
            });
        });

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
    }

    function updateBatchDeleteButton() {
        const btn = document.getElementById('batchDeleteProductsBtn');
        const featuredBtn = document.getElementById('batchFeaturedProductsBtn');
        const count = (state.selectedProductIds || []).length;
        if (btn) {
            if (count > 0) {
                btn.style.display = 'inline-block';
                btn.textContent = `批量删除 (${count})`;
            } else {
                btn.style.display = 'none';
            }
        }
        if (featuredBtn) {
            if (count > 0) {
                featuredBtn.style.display = 'inline-block';
                featuredBtn.textContent = `批量精选 (${count})`;
            } else {
                featuredBtn.style.display = 'none';
            }
        }
    }

    function showAdminToast(message) {
        try {
            let el = document.getElementById('adminToast');
            if (!el) {
                el = document.createElement('div');
                el.id = 'adminToast';
                el.style.cssText = 'position:fixed;bottom:48px;left:50%;transform:translateX(-50%);background:rgba(0,0,0,0.78);color:#fff;padding:10px 22px;border-radius:8px;font-size:14px;z-index:9999;transition:opacity .3s;pointer-events:none;';
                document.body.appendChild(el);
            }
            el.textContent = String(message || '');
            el.style.opacity = '1';
            clearTimeout(el._t);
            el._t = setTimeout(() => { el.style.opacity = '0'; }, 1800);
        } catch (e) { /* ignore */ }
    }

    function renderProductCategoryCascader() {
        return '<div class="form-row">'
            + '<label>所属分类</label>'
            + '<div class="product-category-cascader">'
            + '<div class="product-category-level-grid">'
            + '<div class="product-category-level" id="productCategoryLevel1Wrap"><span class="product-category-level-label">一级分类</span><select id="productCategoryLevel1"></select></div>'
            + '<div class="product-category-level" id="productCategoryLevel2Wrap"><span class="product-category-level-label">二级分类</span><select id="productCategoryLevel2"></select></div>'
            + '<div class="product-category-level" id="productCategoryLevel3Wrap"><span class="product-category-level-label">三级分类</span><select id="productCategoryLevel3"></select></div>'
            + '<div class="product-category-level" id="productCategoryLevel4Wrap"><span class="product-category-level-label">四级分类</span><select id="productCategoryLevel4"></select></div>'
            + '</div>'
            + '<input id="productCategoryId" type="hidden" value="0">'
            + '<div class="product-path-preview" id="productCategoryPathText">当前选择：未分类</div>'
            + '<div class="muted">如果当前分类已经是最终分类，可以不用继续选择下一级。</div>'
            + '</div>'
            + '</div>';
    }

    function initProductCategoryCascader(initialCategoryId) {
        const selectedIds = [0, 0, 0, 0];
        const path = getCategoryPathNodes(initialCategoryId);
        const levelConfigs = [
            { wrap: document.getElementById('productCategoryLevel1Wrap'), select: document.getElementById('productCategoryLevel1'), emptyText: '未分类' },
            { wrap: document.getElementById('productCategoryLevel2Wrap'), select: document.getElementById('productCategoryLevel2'), emptyText: '不继续细分' },
            { wrap: document.getElementById('productCategoryLevel3Wrap'), select: document.getElementById('productCategoryLevel3'), emptyText: '不继续细分' },
            { wrap: document.getElementById('productCategoryLevel4Wrap'), select: document.getElementById('productCategoryLevel4'), emptyText: '不继续细分' },
        ];

        path.forEach((item, index) => {
            if (index < selectedIds.length) {
                selectedIds[index] = Number(item.id);
            }
        });

        function updatePathPreview() {
            let finalCategoryId = 0;
            selectedIds.forEach((id) => {
                if (Number(id) > 0) {
                    finalCategoryId = Number(id);
                }
            });

            document.getElementById('productCategoryId').value = String(finalCategoryId);
            document.getElementById('productCategoryPathText').textContent = '当前选择：' + getCategoryPathText(finalCategoryId, '未分类');
        }

        function fillLevel(levelIndex) {
            const config = levelConfigs[levelIndex];
            const parentId = levelIndex === 0 ? 0 : Number(selectedIds[levelIndex - 1] || 0);
            const nodes = levelIndex === 0 ? getCategoryChildren(0) : (parentId > 0 ? getCategoryChildren(parentId) : []);
            const shouldShow = levelIndex === 0 || parentId > 0;

            if (!shouldShow || (levelIndex > 0 && !nodes.length)) {
                config.wrap.style.display = 'none';
                config.select.innerHTML = '';
                selectedIds[levelIndex] = 0;
                return;
            }

            config.wrap.style.display = 'flex';
            config.select.innerHTML = '<option value="0">' + config.emptyText + '</option>'
                + nodes.map((node) => '<option value="' + node.id + '">' + escapeHtml(node.name) + '</option>').join('');

            const currentSelectedId = Number(selectedIds[levelIndex] || 0);
            if (currentSelectedId > 0 && nodes.some((node) => Number(node.id) === currentSelectedId)) {
                config.select.value = String(currentSelectedId);
            } else {
                config.select.value = '0';
                selectedIds[levelIndex] = 0;
            }
        }

        function refreshLevels() {
            levelConfigs.forEach((_, levelIndex) => {
                fillLevel(levelIndex);
            });
            updatePathPreview();
        }

        levelConfigs.forEach((config, levelIndex) => {
            config.select.addEventListener('change', function () {
                selectedIds[levelIndex] = Number(this.value || 0);
                for (let index = levelIndex + 1; index < selectedIds.length; index += 1) {
                    selectedIds[index] = 0;
                }
                refreshLevels();
            });
        });

        refreshLevels();
    }

    function renderProductCategoryPicker(groupKey, title, hiddenId, pathId, browserId, note) {
        return '<div class="product-category-picker">'
            + '<div class="product-category-picker-title">' + escapeHtml(title) + '</div>'
            + '<input id="' + escapeHtmlAttr(hiddenId) + '" type="hidden" value="0">'
            + '<div class="product-path-preview" id="' + escapeHtmlAttr(pathId) + '">当前选择：未分类</div>'
            + '<div id="' + escapeHtmlAttr(browserId) + '"></div>'
            + '<div class="product-category-picker-note">' + escapeHtml(note) + '</div>'
            + '</div>';
    }

    function renderProductCategorySelectionSection() {
        return '<div class="product-form-section">'
            + '<div class="product-form-section-title">分类选择</div>'
            + '<div class="product-category-select-stack">'
            + renderProductCategoryPicker('type', '类型分类', 'productTypeCategoryId', 'productTypeCategoryPathText', 'productTypeCategoryBrowser', '先点一级分类，下方会继续横向展开二级、三级、四级分类。')
            + renderProductCategoryPicker('kind', '类别分类', 'productKindCategoryId', 'productKindCategoryPathText', 'productKindCategoryBrowser', '类别分类和类型分类分开选择，选法完全一样。')
            + '</div>'
            + '</div>';
    }

    function renderProductCategoryPickerChip(groupKey, levelIndex, categoryId, label, isActive, extraClass) {
        const className = 'product-filter-chip'
            + (extraClass ? ' ' + extraClass : '')
            + (isActive ? ' active' : '');

        return '<button type="button" class="' + className + '" data-product-picker-group="' + escapeHtmlAttr(groupKey) + '" data-product-picker-level="' + escapeHtmlAttr(String(levelIndex)) + '" data-product-picker-id="' + escapeHtmlAttr(String(categoryId)) + '">'
            + escapeHtml(label)
            + '</button>';
    }

    function renderProductCategoryPickerRow(groupKey, levelIndex, label, nodes, activeId, clearLabel) {
        return '<div class="product-category-row">'
            + '<div class="product-category-row-label">' + escapeHtml(label) + '</div>'
            + '<div class="product-category-row-items">'
            + renderProductCategoryPickerChip(groupKey, levelIndex, 0, clearLabel, Number(activeId) === 0, 'ghost')
            + nodes.map((node) => renderProductCategoryPickerChip(groupKey, levelIndex, node.id, node.name, Number(activeId) === Number(node.id), '')).join('')
            + '</div>'
            + '</div>';
    }

    function initProductCategoryPicker(options) {
        const flatList = getCategoryFlatByGroup(options.groupKey);
        const browserEl = document.getElementById(options.browserId);
        const hiddenInput = document.getElementById(options.hiddenId);
        const pathTextEl = document.getElementById(options.pathId);

        if (!browserEl || !hiddenInput || !pathTextEl) {
            return;
        }

        if (!flatList.length) {
            hiddenInput.value = '0';
            pathTextEl.textContent = '当前选择：' + options.emptyText;
            browserEl.innerHTML = '<div class="product-category-browser empty">暂无' + escapeHtml(options.title) + '，可先去分类管理创建</div>';
            return;
        }

        const selectedIds = [0, 0, 0, 0];
        const path = getCategoryPathNodes(options.initialCategoryId, flatList);
        path.forEach((item, index) => {
            if (index < selectedIds.length) {
                selectedIds[index] = Number(item.id);
            }
        });

        function getFinalCategoryId() {
            let finalId = 0;
            selectedIds.forEach((itemId) => {
                if (Number(itemId) > 0) {
                    finalId = Number(itemId);
                }
            });
            return finalId;
        }

        function renderRows() {
            const levelLabels = ['一级分类', '二级分类', '三级分类', '四级分类'];
            const rows = [];
            let parentId = 0;

            for (let levelIndex = 0; levelIndex < levelLabels.length; levelIndex += 1) {
                const nodes = levelIndex === 0
                    ? getCategoryChildren(0, flatList)
                    : (parentId > 0 ? getCategoryChildren(parentId, flatList) : []);

                if (levelIndex > 0 && (!parentId || !nodes.length)) {
                    break;
                }

                rows.push(renderProductCategoryPickerRow(
                    options.groupKey,
                    levelIndex,
                    levelLabels[levelIndex],
                    nodes,
                    selectedIds[levelIndex],
                    levelIndex === 0 ? '暂不选择' : '停在当前级'
                ));

                parentId = Number(selectedIds[levelIndex] || 0);
            }

            browserEl.innerHTML = rows.join('');
            hiddenInput.value = String(getFinalCategoryId());
            pathTextEl.textContent = '当前选择：' + getCategoryPathText(getFinalCategoryId(), options.emptyText, flatList);

            browserEl.querySelectorAll('[data-product-picker-group]').forEach((btn) => {
                btn.addEventListener('click', function () {
                    if (this.dataset.productPickerGroup !== options.groupKey) {
                        return;
                    }

                    const levelIndex = Number(this.dataset.productPickerLevel || 0);
                    const categoryId = Number(this.dataset.productPickerId || 0);
                    selectedIds[levelIndex] = categoryId;
                    for (let index = levelIndex + 1; index < selectedIds.length; index += 1) {
                        selectedIds[index] = 0;
                    }
                    renderRows();
                });
            });
        }

        renderRows();
    }

    async function openProductModal(product) {
        try {
            await Promise.all([loadCategories('type'), loadCategories('kind')]);
        } catch (error) {
            alert(error.message);
            return;
        }

        const isEdit = !!product;
        const typeCategoryId = isEdit
            ? Number(product.category_id || 0)
            : Number(state.productFilters.categoryId || 0);
        const kindCategoryId = isEdit
            ? Number(product.kind_category_id || 0)
            : 0;
        const coverState = {
            url: isEdit ? String(product.cover_image || '').trim() : '',
        };
        const galleryState = {
            items: normalizeProductGalleryImages(isEdit ? product.gallery_images : []),
        };
        const cardResourceState = {
            id: isEdit ? Number(product.card_resource_id || 0) : 0,
            name: isEdit ? String(product.card_resource_name || '') : '',
            moduleType: isEdit ? String(product.card_resource_module_type || '') : '',
        };

        openModal(
            isEdit ? '编辑商品' : '新增商品',
            renderProductCategorySelectionSection()
            + '<div class="product-form-section">'
            + '<div class="product-form-section-title">基础信息</div>'
            + '<div class="product-form-grid two">'
            + '<div class="form-row"><label>商品中文名称</label><input id="productName" value="' + escapeHtmlAttr(isEdit ? product.name : '') + '" placeholder="例如：Roblox 1000 Robux"></div>'
            + '<div class="form-row"><label>商品英文名称</label><input id="productNameEn" value="' + escapeHtmlAttr(isEdit ? (product.name_en || '') : '') + '" placeholder="例如：Roblox 1000 Robux Global"></div>'
            + '<div class="form-row"><label>兑换所需能量</label><input id="productExchangeEnergy" type="number" min="0" step="1" value="' + escapeHtmlAttr(String(isEdit ? Number(product.exchange_energy || 0) : 0)) + '"></div>'
            + '<div class="form-row"><label>状态</label><select id="productStatus"><option value="1"' + (isEdit && Number(product.status) === 0 ? '' : ' selected') + '>上架</option><option value="0"' + (isEdit && Number(product.status) === 0 ? ' selected' : '') + '>下架</option></select></div>'
            + '</div>'
            + '</div>'
            + '<div class="product-form-section">'
            + '<div class="product-form-section-title">图片资源</div>'
            + '<div class="product-media-grid">'
            + '<div class="product-media-panel">'
            + '<div class="product-media-label">商品头像</div>'
            + '<input id="productCoverImage" type="hidden" value="' + escapeHtmlAttr(coverState.url) + '">'
            + '<div class="product-media-toolbar">'
            + '<input id="productCoverUrlInput" placeholder="输入图片链接后保存到本地" value="">'
            + '<button type="button" class="btn btn-small" id="saveProductCoverUrlBtn">链接保存</button>'
            + '<label class="btn btn-warning product-upload-label">上传图片<input id="productCoverFileInput" type="file" accept="image/*" hidden></label>'
            + '</div>'
            + '<div class="product-media-tip">头像会保存到本站本地存储，建议上传清晰方图。</div>'
            + '<div class="product-media-preview single" id="productCoverPreview"></div>'
            + '</div>'
            + '<div class="product-media-panel">'
            + '<div class="product-media-label">页面轮播图</div>'
            + '<input id="productGalleryImages" type="hidden" value="">'
            + '<div class="product-media-toolbar">'
            + '<input id="productGalleryUrlInput" placeholder="输入轮播图链接后添加到本地">'
            + '<button type="button" class="btn btn-small" id="addProductGalleryUrlBtn">添加链接图</button>'
            + '<label class="btn btn-warning product-upload-label">上传多图<input id="productGalleryFileInput" type="file" accept="image/*" multiple hidden></label>'
            + '</div>'
            + '<div class="product-media-tip">支持多张轮播图，建议最多 8 张。图片链接也会先保存到本地再使用。</div>'
            + '<div class="product-media-preview gallery" id="productGalleryPreview"></div>'
            + '</div>'
            + '</div>'
            + '</div>'
            + '<div class="product-form-section">'
            + '<div class="product-form-section-title">商品规格</div>'
            + '<div class="product-form-grid two">'
            + '<div class="form-row"><label>游戏大小</label><input id="productGameSize" value="' + escapeHtmlAttr(isEdit ? (product.game_size || '') : '') + '" placeholder="例如：2.4GB"></div>'
            + '<div class="form-row"><label>支持语言</label><input id="productSupportedLanguages" value="' + escapeHtmlAttr(isEdit ? (product.supported_languages || '') : '') + '" placeholder="例如：中文 / English / 日本語"></div>'
            + '<div class="form-row full"><label>兼容性</label><input id="productCompatibility" value="' + escapeHtmlAttr(isEdit ? (product.compatibility || '') : '') + '" placeholder="例如：iOS 14+ / Android 10+ / Windows 10"></div>'
            + '</div>'
            + '</div>'
            + '<div class="product-form-section">'
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
            + '<div style="margin-bottom:12px">'
            + '<label style="margin-right:8px;font-weight:600">资源范围：</label>'
            + '<select id="productCardResourceScope" style="padding:8px;min-width:150px">'
            + '<option value="">请选择</option>'
            + '<option value="common">通用</option>'
            + '<option value="specific">指定商品</option>'
            + '</select>'
            + '</div>'
            + '<div style="margin-bottom:12px;display:flex;gap:8px">'
            + '<input id="productCardResourceSearch" placeholder="搜索卡密标题、用户名、URL、内容..." style="flex:1;padding:8px" disabled>'
            + '<button type="button" class="btn btn-small" id="searchCardResourceBtn" disabled>搜索</button>'
            + '</div>'
            + '<div id="productCardResourceCurrent" style="padding:12px;background:#f5f5f5;border-radius:4px;margin-bottom:12px">'
            + (cardResourceState.id > 0
                ? '<div style="display:flex;align-items:center;justify-content:space-between"><div><strong>已绑定：</strong>' + escapeHtml(cardResourceState.name) + ' <span class="tag tag-blue">' + escapeHtml(cardResourceState.moduleType === 'common' ? '通用资源' : cardResourceState.moduleType === 'account' ? '账号密码类' : cardResourceState.moduleType === 'download' ? '下载连接类' : '教程类') + '</span> <span class="muted">(ID: ' + cardResourceState.id + ')</span></div><button type="button" class="btn btn-danger btn-small" id="unbindCardResourceBtn">解除绑定</button></div>'
                : '<div class="muted">未绑定卡密资源</div>')
            + '</div>'
            + '<div id="productCardResourceResults" style="max-height:300px;overflow-y:auto"></div>'
            + '</div>'
            + '</div>'
            + '</div>',
            [
                { text: '取消', className: 'btn btn-warning', onClick: closeModal },
                {
                    text: isEdit ? '保存' : '创建',
                    className: 'btn btn-main',
                    onClick: async function () {
                        const payload = {
                            category_id: valueById('productTypeCategoryId'),
                            kind_category_id: valueById('productKindCategoryId'),
                            name: valueById('productName'),
                            name_en: valueById('productNameEn'),
                            exchange_energy: valueById('productExchangeEnergy'),
                            status: valueById('productStatus'),
                            cover_image: valueById('productCoverImage'),
                            gallery_images: valueById('productGalleryImages'),
                            game_size: valueById('productGameSize'),
                            supported_languages: valueById('productSupportedLanguages'),
                            compatibility: valueById('productCompatibility'),
                            description: valueById('productDescription'),
                            card_resource_id: valueById('productCardResourceId'),
                        };
                        try {
                            if (isEdit) {
                                await api('/admin/products/' + product.id, { method: 'PUT', data: payload });
                            } else {
                                await api('/admin/products', { method: 'POST', data: payload });
                            }
                            closeModal();
                            renderProducts();
                        } catch (error) {
                            alert(error.message);
                        }
                    }
                }
            ]
        );

        initProductCategoryPicker({
            groupKey: 'type',
            title: '类型分类',
            browserId: 'productTypeCategoryBrowser',
            hiddenId: 'productTypeCategoryId',
            pathId: 'productTypeCategoryPathText',
            initialCategoryId: typeCategoryId,
            emptyText: '未选择类型分类',
        });
        initProductCategoryPicker({
            groupKey: 'kind',
            title: '类别分类',
            browserId: 'productKindCategoryBrowser',
            hiddenId: 'productKindCategoryId',
            pathId: 'productKindCategoryPathText',
            initialCategoryId: kindCategoryId,
            emptyText: '未选择类别分类',
        });

        // 卡密绑定事件处理
        const cardResourceScopeSelect = document.getElementById('productCardResourceScope');
        const cardResourceTypeSelect = document.getElementById('productCardResourceType');
        const cardResourceSearchInput = document.getElementById('productCardResourceSearch');
        const cardResourceSearchBtn = document.getElementById('searchCardResourceBtn');
        const cardResourceCurrentDiv = document.getElementById('productCardResourceCurrent');
        const cardResourceResultsDiv = document.getElementById('productCardResourceResults');
        const cardResourceIdInput = document.getElementById('productCardResourceId');

        // 检查是否可以启用搜索，并自动加载卡密列表
        function updateSearchState() {
            const scope = cardResourceScopeSelect.value;
            const type = cardResourceTypeSelect.value;
            
            // 必须同时选择卡密分类和资源范围才能搜索
            const canSearch = type !== '' && scope !== '';
            
            cardResourceSearchInput.disabled = !canSearch;
            cardResourceSearchBtn.disabled = !canSearch;
            
            if (!canSearch) {
                cardResourceResultsDiv.innerHTML = '';
                cardResourceSearchInput.value = '';
            } else {
                // 两个条件都选择后，自动加载卡密列表
                searchCardResources();
            }
        }

        // 选择资源范围后更新搜索状态并自动加载
        cardResourceScopeSelect.addEventListener('change', updateSearchState);

        // 选择卡密分类后更新搜索状态并自动加载
        cardResourceTypeSelect.addEventListener('change', updateSearchState);

        // 搜索卡密资源
        async function searchCardResources() {
            const scope = cardResourceScopeSelect.value;
            const type = cardResourceTypeSelect.value;
            const keyword = cardResourceSearchInput.value.trim();
            
            // 验证两个筛选条件都已选择
            if (!type) {
                alert('请先选择卡密分类');
                return;
            }
            if (!scope) {
                alert('请先选择资源范围');
                return;
            }

            try {
                cardResourceSearchBtn.disabled = true;
                cardResourceSearchBtn.textContent = '搜索中...';
                
                // 传递两个参数：module_type 和 scope
                const params = new URLSearchParams({ 
                    module_type: type,
                    scope: scope
                });
                if (keyword) {
                    params.append('keyword', keyword);
                }
                
                console.log('搜索卡密资源，参数：', { type, scope, keyword });
                const response = await api('/admin/products/card-resources?' + params.toString());
                console.log('API响应（已经是data部分）：', response);
                
                // api()函数已经返回了payload.data，所以response就是数组
                const resources = Array.isArray(response) ? response : [];
                console.log('卡密资源列表：', resources);
                
                if (resources.length === 0) {
                    cardResourceResultsDiv.innerHTML = '<div style="padding:12px;text-align:center;color:#999">未找到匹配的卡密资源<br><small style="color:#666">分类=' + escapeHtml(type) + ', 范围=' + escapeHtml(scope) + (keyword ? ', 关键词=' + escapeHtml(keyword) : '') + '</small></div>';
                } else {
                    cardResourceResultsDiv.innerHTML = resources.map(resource => {
                        const typeName = resource.module_type === 'account' ? '账号密码类' : resource.module_type === 'download' ? '下载连接类' : resource.module_type === 'tutorial' ? '教程类' : '通用资源';
                        const displayName = resource.title || resource.username || resource.url || 'ID:' + resource.id;
                        return '<div style="padding:10px;border:1px solid #e0e0e0;border-radius:4px;margin-bottom:8px;cursor:pointer;transition:background 0.2s;hover:background:#f5f5f5" data-card-resource-id="' + resource.id + '" data-card-resource-name="' + escapeHtmlAttr(displayName) + '" data-card-resource-type="' + escapeHtmlAttr(resource.module_type) + '" class="card-resource-item">'
                            + '<div style="font-weight:600;margin-bottom:4px">' + escapeHtml(displayName) + '</div>'
                            + '<div style="font-size:12px;color:#666">'
                            + '<span class="tag tag-blue">' + typeName + '</span> '
                            + '<span class="muted">ID: ' + resource.id + '</span> '
                            + (resource.is_common ? '<span class="tag tag-green">通用</span>' : '<span class="tag tag-gray">指定</span>')
                            + '</div>'
                            + '</div>';
                    }).join('');
                    
                    // 绑定点击事件
                    cardResourceResultsDiv.querySelectorAll('.card-resource-item').forEach(item => {
                        item.addEventListener('click', function() {
                            const resourceId = this.dataset.cardResourceId;
                            const resourceName = this.dataset.cardResourceName;
                            const resourceType = this.dataset.cardResourceType;
                            
                            cardResourceIdInput.value = resourceId;
                            const typeName = resourceType === 'account' ? '账号密码类' : resourceType === 'download' ? '下载连接类' : resourceType === 'tutorial' ? '教程类' : '通用资源';
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
                console.error('搜索卡密资源失败：', error);
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

        const coverUrlInput = document.getElementById('productCoverUrlInput');
        const coverPreview = document.getElementById('productCoverPreview');
        const coverHiddenInput = document.getElementById('productCoverImage');
        const galleryUrlInput = document.getElementById('productGalleryUrlInput');
        const galleryPreview = document.getElementById('productGalleryPreview');
        const galleryHiddenInput = document.getElementById('productGalleryImages');

        function renderCoverPreview() {
            coverHiddenInput.value = coverState.url;
            if (!coverState.url) {
                coverPreview.innerHTML = '<div class="product-media-empty">暂无商品头像</div>';
                return;
            }

            coverPreview.innerHTML = '<div class="product-media-card single">'
                + '<img src="' + escapeHtmlAttr(coverState.url) + '" alt="商品头像">'
                + '<button type="button" class="product-media-remove" data-cover-remove="1">移除</button>'
                + '</div>';
        }

        function renderGalleryPreview() {
            galleryHiddenInput.value = JSON.stringify(galleryState.items);
            if (!galleryState.items.length) {
                galleryPreview.innerHTML = '<div class="product-media-empty">暂无轮播图</div>';
                return;
            }

            galleryPreview.innerHTML = galleryState.items.map((url, index) => '<div class="product-media-card">'
                + '<img src="' + escapeHtmlAttr(url) + '" alt="轮播图 ' + String(index + 1) + '">'
                + '<button type="button" class="product-media-remove" data-gallery-remove="' + index + '">移除</button>'
                + '</div>').join('');
        }

        function addGalleryImage(url) {
            const normalizedUrl = String(url || '').trim();
            if (!normalizedUrl) {
                return;
            }
            if (galleryState.items.indexOf(normalizedUrl) >= 0) {
                return;
            }
            if (galleryState.items.length >= 8) {
                alert('轮播图最多保留 8 张');
                return;
            }

            galleryState.items.push(normalizedUrl);
            renderGalleryPreview();
        }

        coverPreview.addEventListener('click', function (event) {
            const removeBtn = event.target.closest('[data-cover-remove]');
            if (!removeBtn) {
                return;
            }

            coverState.url = '';
            coverUrlInput.value = '';
            renderCoverPreview();
        });
        galleryPreview.addEventListener('click', function (event) {
            const removeBtn = event.target.closest('[data-gallery-remove]');
            if (!removeBtn) {
                return;
            }

            const index = Number(removeBtn.dataset.galleryRemove);
            galleryState.items = galleryState.items.filter((_, itemIndex) => itemIndex !== index);
            renderGalleryPreview();
        });
        document.getElementById('saveProductCoverUrlBtn').addEventListener('click', async function () {
            const button = this;
            const imageUrl = coverUrlInput.value.trim();
            if (!imageUrl) {
                alert('请先输入头像图片链接');
                return;
            }

            button.disabled = true;
            try {
                const result = await saveProductImageByUrl(imageUrl);
                coverState.url = result.url;
                coverUrlInput.value = '';
                renderCoverPreview();
            } catch (error) {
                alert(error.message);
            } finally {
                button.disabled = false;
            }
        });
        document.getElementById('productCoverFileInput').addEventListener('change', async function () {
            const file = this.files && this.files[0];
            if (!file) {
                return;
            }

            try {
                const result = await uploadProductImageFile(file);
                coverState.url = result.url;
                coverUrlInput.value = '';
                renderCoverPreview();
            } catch (error) {
                alert(error.message);
            } finally {
                this.value = '';
            }
        });
        document.getElementById('addProductGalleryUrlBtn').addEventListener('click', async function () {
            const button = this;
            const imageUrl = galleryUrlInput.value.trim();
            if (!imageUrl) {
                alert('请先输入轮播图链接');
                return;
            }

            button.disabled = true;
            try {
                const result = await saveProductImageByUrl(imageUrl);
                addGalleryImage(result.url);
                galleryUrlInput.value = '';
            } catch (error) {
                alert(error.message);
            } finally {
                button.disabled = false;
            }
        });
        document.getElementById('productGalleryFileInput').addEventListener('change', async function () {
            const files = Array.from(this.files || []);
            if (!files.length) {
                return;
            }

            try {
                for (let index = 0; index < files.length; index += 1) {
                    const result = await uploadProductImageFile(files[index]);
                    addGalleryImage(result.url);
                }
            } catch (error) {
                alert(error.message);
            } finally {
                this.value = '';
            }
        });

        renderCoverPreview();
        renderGalleryPreview();
    }

    function renderCardTable(cards) {
        if (!cards.length) return '<div class="empty">暂无卡密，请先导入卡密</div>';

        const rows = cards.map((item) => '<tr>'
            + '<td>' + escapeHtml(String(item.id)) + '</td>'
            + '<td>' + escapeHtml(item.product && item.product.name ? item.product.name : '') + '</td>'
            + '<td>' + escapeHtml(item.card_no || '') + '</td>'
            + '<td>' + cardStatusTag(item.status) + '</td>'
            + '<td>' + escapeHtml(String(item.order_id || '-')) + '</td>'
            + '<td>' + escapeHtml(item.sold_at || '-') + '</td>'
            + '<td class="actions"><button class="link-btn" data-card-status="' + item.id + '" data-new-status="unused">设可用</button><button class="link-btn" data-card-status="' + item.id + '" data-new-status="locked">锁定</button><button class="link-btn" data-card-status="' + item.id + '" data-new-status="invalid">作废</button><button class="link-btn danger" data-card-delete="' + item.id + '">删除</button></td>'
            + '</tr>').join('');

        return '<table><thead><tr><th>ID</th><th>商品</th><th>卡号</th><th>状态</th><th>订单ID</th><th>售出时间</th><th>操作</th></tr></thead><tbody>' + rows + '</tbody></table>';
    }

    function bindCardTableEvents() {
        document.querySelectorAll('[data-card-status]').forEach((btn) => {
            btn.addEventListener('click', async function () {
                try {
                    await api('/admin/cards/' + Number(this.dataset.cardStatus) + '/status', { method: 'PUT', data: { status: this.dataset.newStatus } });
                    renderCards();
                } catch (error) {
                    alert(error.message);
                }
            });
        });
        document.querySelectorAll('[data-card-delete]').forEach((btn) => {
            btn.addEventListener('click', async function () {
                if (!confirm('确定删除这条卡密吗？')) return;
                try {
                    await api('/admin/cards/' + Number(this.dataset.cardDelete), { method: 'DELETE' });
                    renderCards();
                } catch (error) {
                    alert(error.message);
                }
            });
        });
    }

    function openImportCardsModal(products, presetProductId, onSuccess) {
        if (!products.length) {
            alert('请先创建商品');
            return;
        }

        const productOptions = products.map((item) => '<option value="' + item.id + '">' + escapeHtml(item.name) + '</option>').join('');
        openModal(
            '批量导入卡密',
            '<div class="form-row"><label>所属商品</label><select id="importProductId">' + productOptions + '</select></div>'
            + '<div class="form-row"><label>卡密内容（每行一条，格式：卡号----卡密）</label><textarea id="importLines" placeholder="VIP001----PASS001&#10;VIP002----PASS002"></textarea></div>',
            [
                { text: '取消', className: 'btn btn-warning', onClick: closeModal },
                {
                    text: '导入',
                    className: 'btn btn-main',
                    onClick: async function () {
                        const lines = valueById('importLines').split(/\r?\n/).map((line) => line.trim()).filter(Boolean);
                        if (!lines.length) {
                            alert('请先填写卡密内容');
                            return;
                        }
                        const cards = [];
                        for (let i = 0; i < lines.length; i += 1) {
                            const parts = lines[i].split('----');
                            if (parts.length < 2) {
                                alert('第 ' + (i + 1) + ' 行格式不正确');
                                return;
                            }
                            cards.push({ card_no: parts[0].trim(), card_secret: parts.slice(1).join('----').trim() });
                        }
                        try {
                            await api('/admin/cards/batch', { method: 'POST', data: { product_id: valueById('importProductId'), cards: cards } });
                            closeModal();
                            if (typeof onSuccess === 'function') {
                                await onSuccess();
                            } else if (state.view === 'products') {
                                await renderProducts();
                            } else {
                                await renderCards();
                            }
                        } catch (error) {
                            alert(error.message);
                        }
                    }
                }
            ]
        );

        if (presetProductId) {
            document.getElementById('importProductId').value = String(presetProductId);
        }
    }

    function getCurrentCardModule() {
        return cardModuleMetaMap[state.cardModule] || cardModuleMetaMap.account;
    }

    function syncCardResourceFilters(moduleKey) {
        const currentModule = normalizeCardModule(moduleKey);
        const current = state.cardResourceFilters[currentModule];
        if (!current || typeof current !== 'object') {
            state.cardResourceFilters[currentModule] = { keyword: '', productId: '', status: '', scopeType: '' };
            return;
        }

        current.keyword = String(current.keyword || '');
        current.productId = String(current.productId || '');
        current.status = current.status === '0' || current.status === '1' ? current.status : '';
        current.scopeType = current.scopeType === 'common' || current.scopeType === 'product' ? current.scopeType : '';
    }

    async function ensureCardProductsLoaded() {
        const data = await api('/admin/products', { query: { page: 1, limit: 500 } });
        state.cardProducts = data.list || [];
        return state.cardProducts;
    }

    function renderCardResourceScope(item) {
        if (Number(item.is_common || 0) === 1) {
            return '<span class="tag tag-blue">通用</span>';
        }

        if (item.product && item.product.name) {
            return '<span class="tag tag-gray">' + escapeHtml(item.product.name) + '</span>';
        }

        return '<span class="tag tag-red">未绑定商品</span>';
    }

    function getCardResourceProductOptions(selectedProductId) {
        const currentId = Number(selectedProductId || 0);
        return '<option value="0">请选择商品</option>'
            + state.cardProducts.map((item) => '<option value="' + item.id + '"' + (currentId === Number(item.id) ? ' selected' : '') + '>' + escapeHtml(item.name || '') + '</option>').join('');
    }

    function maskCardResourceValue(value, hiddenLength) {
        const text = String(value || '').trim();
        const maskSize = Math.max(1, Number(hiddenLength || 4));
        if (!text) {
            return '-';
        }
        if (text.length <= maskSize) {
            return '*'.repeat(text.length);
        }
        return text.slice(0, text.length - maskSize) + '*'.repeat(maskSize);
    }

    /*
    function renderMaskedCardResourceValue(value, titleText) {
        const text = String(value || '').trim();
        if (!text) {
            return '-';
        }
        return '<span class="masked-secret-text" title="' + escapeHtmlAttr(titleText || '已隐藏后4位') + '">' + escapeHtml(maskCardResourceValue(text, 4)) + '</span>';
    }

    */

    function renderMaskedCardResourceValue(value, titleText) {
        const text = String(value || '').trim();
        if (!text) {
            return '-';
        }
        return '<span class="masked-secret-text" title="' + escapeHtmlAttr(titleText || '已隐藏后4位') + '">' + escapeHtml(maskCardResourceValue(text, 4)) + '</span>';
    }

    function renderCardResourcePasswordCell(item) {
        const password = String(item.password || '').trim();
        if (!password) {
            return '<span class="masked-secret-text">-</span>';
        }
        return '<div class="masked-secret-cell">'
            + '<span class="masked-secret-text" title="双击可以直接编辑密码">' + escapeHtml(maskCardResourceValue(password, 4)) + '</span>'
            + '<span class="masked-secret-hint">双击编辑</span>'
            + '</div>';
    }

    function buildCardResourcePayload(moduleKey, values) {
        const currentModule = normalizeCardModule(moduleKey);
        const tutorialMode = currentModule === 'tutorial'
            ? (String(values.tutorial_mode || 'url').trim() || 'url')
            : 'url';

        return {
            module_type: currentModule,
            is_common: Number(values.is_common || 0) === 1 ? 1 : 0,
            product_id: Number(values.product_id || 0),
            title: String(values.title || '').trim(),
            username: currentModule === 'account' ? String(values.username || '').trim() : '',
            password: currentModule === 'account' ? String(values.password || '').trim() : '',
            url: currentModule === 'account' || tutorialMode === 'richtext' ? '' : String(values.url || '').trim(),
            tutorial_mode: tutorialMode,
            content: currentModule === 'tutorial' && tutorialMode === 'richtext'
                ? String(values.content || '').trim()
                : '',
            sort: Number(values.sort || 0),
            status: String(values.status == null ? '1' : values.status).trim() === '0' ? 0 : 1,
            remark: String(values.remark || '').trim(),
        };
    }

    function renderCardResourceTable(moduleKey, list) {
        if (!list.length) {
            return '<div class="empty">' + escapeHtml(getCurrentCardModule().emptyText) + '</div>';
        }

        if (moduleKey === 'account') {
            const rows = list.map((item) => '<tr>'
                + '<td>' + escapeHtml(String(item.id)) + '</td>'
                + '<td>' + renderCardResourceScope(item) + '</td>'
                + '<td>' + escapeHtml(item.title || '-') + '</td>'
                + '<td>' + renderMaskedCardResourceValue(item.username || '', '账号已隐藏后4位') + '</td>'
                + '<td class="masked-secret-column" data-card-resource-password-cell="' + item.id + '">' + renderCardResourcePasswordCell(item) + '</td>'
                + '<td>' + (Number(item.status) === 1 ? '<span class="tag tag-green">启用</span>' : '<span class="tag tag-gray">停用</span>') + '</td>'
                + '<td>' + escapeHtml(String(item.sort || 0)) + '</td>'
                + '<td>' + escapeHtml(item.remark || '-') + '</td>'
                + '<td>' + escapeHtml(item.updated_at || item.created_at || '-') + '</td>'
                + '<td class="actions"><button class="link-btn" data-card-resource-edit="' + item.id + '">编辑</button><button class="link-btn danger" data-card-resource-delete="' + item.id + '">删除</button></td>'
                + '</tr>').join('');

            return '<table><thead><tr><th>ID</th><th>范围</th><th>标题</th><th>账号</th><th>密码</th><th>状态</th><th>排序</th><th>备注</th><th>更新时间</th><th>操作</th></tr></thead><tbody>' + rows + '</tbody></table>';
        }

        if (moduleKey === 'download') {
            const rows = list.map((item) => '<tr>'
                + '<td>' + escapeHtml(String(item.id)) + '</td>'
                + '<td>' + renderCardResourceScope(item) + '</td>'
                + '<td>' + escapeHtml(item.title || '-') + '</td>'
                + '<td><a class="resource-link" href="' + escapeHtmlAttr(item.url || '#') + '" target="_blank" rel="noreferrer">' + escapeHtml(item.url || '-') + '</a></td>'
                + '<td>' + (Number(item.status) === 1 ? '<span class="tag tag-green">启用</span>' : '<span class="tag tag-gray">停用</span>') + '</td>'
                + '<td>' + escapeHtml(String(item.sort || 0)) + '</td>'
                + '<td>' + escapeHtml(item.remark || '-') + '</td>'
                + '<td>' + escapeHtml(item.updated_at || item.created_at || '-') + '</td>'
                + '<td class="actions"><button class="link-btn" data-card-resource-edit="' + item.id + '">编辑</button><button class="link-btn danger" data-card-resource-delete="' + item.id + '">删除</button></td>'
                + '</tr>').join('');

            return '<table><thead><tr><th>ID</th><th>范围</th><th>标题</th><th>下载地址</th><th>状态</th><th>排序</th><th>备注</th><th>更新时间</th><th>操作</th></tr></thead><tbody>' + rows + '</tbody></table>';
        }

        const rows = list.map((item) => {
            const previewText = item.tutorial_mode === 'richtext'
                ? getTextPreview(item.content || '', 80)
                : (item.url || '-');
            return '<tr>'
                + '<td>' + escapeHtml(String(item.id)) + '</td>'
                + '<td>' + renderCardResourceScope(item) + '</td>'
                + '<td>' + escapeHtml(item.title || '-') + '</td>'
                + '<td>' + (item.tutorial_mode === 'richtext' ? '<span class="tag tag-blue">富文本</span>' : '<span class="tag tag-gray">网址</span>') + '</td>'
                + '<td class="resource-preview">' + escapeHtml(previewText || '-') + '</td>'
                + '<td>' + (Number(item.status) === 1 ? '<span class="tag tag-green">启用</span>' : '<span class="tag tag-gray">停用</span>') + '</td>'
                + '<td>' + escapeHtml(String(item.sort || 0)) + '</td>'
                + '<td>' + escapeHtml(item.updated_at || item.created_at || '-') + '</td>'
                + '<td class="actions"><button class="link-btn" data-card-resource-preview="' + item.id + '">查看</button><button class="link-btn" data-card-resource-edit="' + item.id + '">编辑</button><button class="link-btn danger" data-card-resource-delete="' + item.id + '">删除</button></td>'
                + '</tr>';
        }).join('');

        return '<table><thead><tr><th>ID</th><th>范围</th><th>标题</th><th>模式</th><th>内容预览</th><th>状态</th><th>排序</th><th>更新时间</th><th>操作</th></tr></thead><tbody>' + rows + '</tbody></table>';
    }

    async function renderCards() {
        const module = getCurrentCardModule();
        syncCardResourceFilters(module.key);
        const filters = state.cardResourceFilters[module.key];

        app.innerHTML = '<div class="panel"><div class="panel-title">' + escapeHtml(module.title) + '</div><div class="empty">加载中...</div></div>';
        try {
            const [products, data] = await Promise.all([
                ensureCardProductsLoaded(),
                api('/admin/card-resources', {
                    query: {
                        page: 1,
                        limit: 500,
                        module_type: module.key,
                        keyword: filters.keyword,
                        product_id: filters.productId,
                        status: filters.status,
                        scope_type: filters.scopeType,
                    }
                })
            ]);
            const list = data.list || [];
            state.cardProducts = products;
            state.cardResourceList = list;

            app.innerHTML = '<div class="panel"><div class="panel-title">' + escapeHtml(module.title) + '</div>'
                + '<div class="toolbar">'
                + '<input id="cardResourceKeyword" placeholder="搜索标题、网址、账号或备注" value="' + escapeHtmlAttr(filters.keyword) + '">'
                + '<select id="cardResourceScopeFilter"><option value="">全部范围</option><option value="common">只看通用</option><option value="product">只看商品专用</option></select>'
                + '<select id="cardResourceProductFilter"><option value="">全部商品</option>' + products.map((item) => '<option value="' + item.id + '">' + escapeHtml(item.name || '') + '</option>').join('') + '</select>'
                + '<select id="cardResourceStatusFilter"><option value="">全部状态</option><option value="1">只看启用</option><option value="0">只看停用</option></select>'
                + '<button class="btn btn-small" id="searchCardResourceBtn">筛选</button>'
                + '<button class="btn btn-warning" id="resetCardResourceBtn">重置</button>'
                + '<button class="btn btn-main" id="addCardResourceBtn">' + escapeHtml(module.addText) + '</button>'
                + '<span class="muted">共 ' + escapeHtml(String(list.length)) + ' 条资源</span>'
                + '</div>'
                + renderCardResourceTable(module.key, list)
                + '</div>';

            document.getElementById('cardResourceScopeFilter').value = filters.scopeType;
            document.getElementById('cardResourceProductFilter').value = filters.productId;
            document.getElementById('cardResourceStatusFilter').value = filters.status;
            document.getElementById('searchCardResourceBtn').addEventListener('click', function () {
                filters.keyword = valueById('cardResourceKeyword');
                filters.scopeType = valueById('cardResourceScopeFilter');
                filters.productId = valueById('cardResourceProductFilter');
                filters.status = valueById('cardResourceStatusFilter');
                renderCards();
            });
            document.getElementById('cardResourceKeyword').addEventListener('keydown', function (event) {
                if (event.key !== 'Enter') {
                    return;
                }
                filters.keyword = valueById('cardResourceKeyword');
                filters.scopeType = valueById('cardResourceScopeFilter');
                filters.productId = valueById('cardResourceProductFilter');
                filters.status = valueById('cardResourceStatusFilter');
                renderCards();
            });
            document.getElementById('resetCardResourceBtn').addEventListener('click', function () {
                state.cardResourceFilters[module.key] = { keyword: '', productId: '', status: '', scopeType: '' };
                renderCards();
            });
            document.getElementById('addCardResourceBtn').addEventListener('click', function () {
                openCardResourceModal(module.key, null);
            });
            bindCardResourceEvents(module.key);
        } catch (error) {
            renderError(error, module.title);
        }
    }

    function bindCardResourceEvents(moduleKey) {
        document.querySelectorAll('[data-card-resource-edit]').forEach((btn) => {
            btn.addEventListener('click', function () {
                const id = Number(this.dataset.cardResourceEdit);
                const item = state.cardResourceList.find((row) => Number(row.id) === id);
                if (item) {
                    openCardResourceModal(moduleKey, item);
                }
            });
        });
        document.querySelectorAll('[data-card-resource-delete]').forEach((btn) => {
            btn.addEventListener('click', async function () {
                const id = Number(this.dataset.cardResourceDelete);
                if (!confirm('确定删除这条资源吗？')) {
                    return;
                }
                try {
                    await api('/admin/card-resources/' + id, { method: 'DELETE' });
                    renderCards();
                } catch (error) {
                    alert(error.message);
                }
            });
        });
        document.querySelectorAll('[data-card-resource-preview]').forEach((btn) => {
            btn.addEventListener('click', function () {
                const id = Number(this.dataset.cardResourcePreview);
                const item = state.cardResourceList.find((row) => Number(row.id) === id);
                if (!item) {
                    return;
                }

                const bodyHtml = item.tutorial_mode === 'richtext'
                    ? '<div class="resource-preview-modal">' + (item.content || '<div class="muted">暂无教程内容</div>') + '</div>'
                    : '<div class="form-row"><label>教程网址</label><a class="resource-link" href="' + escapeHtmlAttr(item.url || '#') + '" target="_blank" rel="noreferrer">' + escapeHtml(item.url || '-') + '</a></div>';
                openModal('查看教程', bodyHtml, [
                    { text: '关闭', className: 'btn btn-warning', onClick: closeModal }
                ]);
            });
        });
        if (moduleKey === 'account') {
            document.querySelectorAll('[data-card-resource-password-cell]').forEach((cell) => {
                cell.addEventListener('dblclick', function (event) {
                    if (event.target.closest('button,input')) {
                        return;
                    }

                    const id = Number(this.dataset.cardResourcePasswordCell);
                    const item = state.cardResourceList.find((row) => Number(row.id) === id);
                    if (!item) {
                        return;
                    }

                    openCardResourcePasswordEditor(this, item);
                });
            });
        }
    }

    function openCardResourcePasswordEditor(cell, item) {
        if (!cell || !item || cell.dataset.editing === '1') {
            return;
        }

        cell.dataset.editing = '1';
        cell.innerHTML = '<div class="masked-secret-editor">'
            + '<input type="text" data-card-resource-password-input value="' + escapeHtmlAttr(item.password || '') + '" placeholder="请输入新密码">'
            + '<div class="masked-secret-editor-actions">'
            + '<button type="button" class="link-btn" data-card-resource-password-save>保存</button>'
            + '<button type="button" class="link-btn danger" data-card-resource-password-cancel>取消</button>'
            + '</div>'
            + '</div>';

        const input = cell.querySelector('[data-card-resource-password-input]');
        const saveBtn = cell.querySelector('[data-card-resource-password-save]');
        const cancelBtn = cell.querySelector('[data-card-resource-password-cancel]');
        const restoreCell = function () {
            delete cell.dataset.editing;
            cell.innerHTML = renderCardResourcePasswordCell(item);
        };

        if (cancelBtn) {
            cancelBtn.addEventListener('click', restoreCell);
        }
        if (input) {
            input.focus();
            input.select();
            input.addEventListener('keydown', function (event) {
                if (event.key === 'Enter') {
                    event.preventDefault();
                    if (saveBtn) {
                        saveBtn.click();
                    }
                    return;
                }
                if (event.key === 'Escape') {
                    event.preventDefault();
                    restoreCell();
                }
            });
        }
        if (saveBtn) {
            saveBtn.addEventListener('click', async function () {
                const nextPassword = String((input && input.value) || '').trim();
                if (!nextPassword) {
                    alert('请填写登录密码');
                    return;
                }

                if (input) {
                    input.disabled = true;
                }
                saveBtn.disabled = true;
                if (cancelBtn) {
                    cancelBtn.disabled = true;
                }

                try {
                    const payload = buildCardResourcePayload('account', {
                        is_common: item.is_common,
                        product_id: item.product_id,
                        title: item.title,
                        username: item.username,
                        password: nextPassword,
                        sort: item.sort,
                        status: item.status,
                        remark: item.remark,
                    });
                    await api('/admin/card-resources/' + Number(item.id), { method: 'PUT', data: payload });
                    item.password = nextPassword;
                    renderCards();
                } catch (error) {
                    if (input) {
                        input.disabled = false;
                        input.focus();
                        input.select();
                    }
                    saveBtn.disabled = false;
                    if (cancelBtn) {
                        cancelBtn.disabled = false;
                    }
                    alert(error.message);
                }
            });
        }
    }

    function openCardResourceModal(moduleKey, item) {
        const isEdit = !!item;
        const module = cardModuleMetaMap[moduleKey] || cardModuleMetaMap.account;
        const isCommon = isEdit ? Number(item.is_common || 0) === 1 : false;
        const selectedProductId = isEdit ? Number(item.product_id || 0) : 0;
        const selectedStatus = isEdit ? Number(item.status || 0) : 1;
        const selectedSort = isEdit ? Number(item.sort || 0) : 0;
        const tutorialMode = isEdit ? String(item.tutorial_mode || 'url') : 'url';
        const richHtml = isEdit ? String(item.content || '') : '';

        let moduleFields = '';
        if (moduleKey === 'account') {
            moduleFields = '<div class="form-row"><label>账号标题</label><input id="cardResourceTitle" value="' + escapeHtmlAttr(isEdit ? (item.title || '') : '') + '" placeholder="例如：美区共享账号"></div>'
                + '<div class="form-row"><label>登录账号</label><input id="cardResourceUsername" value="' + escapeHtmlAttr(isEdit ? (item.username || '') : '') + '" placeholder="填写账号"></div>'
                + '<div class="form-row"><label>登录密码</label><input id="cardResourcePassword" value="' + escapeHtmlAttr(isEdit ? (item.password || '') : '') + '" placeholder="填写密码"></div>';
        } else if (moduleKey === 'download') {
            moduleFields = '<div class="form-row"><label>下载项标题</label><input id="cardResourceTitle" value="' + escapeHtmlAttr(isEdit ? (item.title || '') : '') + '" placeholder="例如：iOS 下载地址"></div>'
                + '<div class="form-row"><label>下载网址</label><input id="cardResourceUrl" value="' + escapeHtmlAttr(isEdit ? (item.url || '') : '') + '" placeholder="https://"></div>';
        } else {
            moduleFields = '<div class="form-row"><label>教程标题</label><input id="cardResourceTitle" value="' + escapeHtmlAttr(isEdit ? (item.title || '') : '') + '" placeholder="例如：安装教程"></div>'
                + '<div class="form-row"><label>教程模式</label><select id="cardResourceTutorialMode"><option value="url"' + (tutorialMode === 'url' ? ' selected' : '') + '>教程网址</option><option value="richtext"' + (tutorialMode === 'richtext' ? ' selected' : '') + '>富文本内容</option></select></div>'
                + '<div class="form-row" id="cardResourceUrlWrap"><label>教程网址</label><input id="cardResourceUrl" value="' + escapeHtmlAttr(isEdit ? (item.url || '') : '') + '" placeholder="https://"></div>'
                + '<div class="form-row" id="cardResourceRichWrap">'
                + '<label>富文本内容</label>'
                + '<div class="rich-editor-toolbar">'
                + '<button type="button" class="btn btn-small" data-rich-command="bold">加粗</button>'
                + '<button type="button" class="btn btn-small" data-rich-command="italic">斜体</button>'
                + '<button type="button" class="btn btn-small" data-rich-command="insertUnorderedList">列表</button>'
                + '<button type="button" class="btn btn-small" data-rich-command="formatBlock" data-rich-value="h3">标题</button>'
                + '<button type="button" class="btn btn-warning" data-rich-command="createLink">插入链接</button>'
                + '<button type="button" class="btn btn-warning" data-rich-command="removeFormat">清除格式</button>'
                + '<button type="button" class="btn btn-warning" data-rich-action="insertImageByUrl">图片链接</button>'
                + '<button type="button" class="btn btn-warning" data-rich-action="uploadImage">上传图片</button>'
                + '<button type="button" class="btn btn-warning" data-rich-action="insertVideoLink">视频链接</button>'
                + '</div>'
                + '<input id="cardResourceRichImageFile" type="file" accept="image/*" hidden>'
                + '<div id="cardResourceRichEditor" class="rich-editor" contenteditable="true">' + richHtml + '</div>'
                + '<div class="product-category-picker-note">这里可以直接编辑富文本教程内容，支持标题、列表、链接、加粗等基础格式。</div>'
                + '</div>';
        }

        openModal(
            (isEdit ? '编辑' : '新增') + module.title,
            '<div class="form-row"><label>资源范围</label><select id="cardResourceCommon"><option value="0"' + (isCommon ? '' : ' selected') + '>指定商品</option><option value="1"' + (isCommon ? ' selected' : '') + '>通用资源</option></select></div>'
            + '<div class="form-row" id="cardResourceProductWrap"><label>对应商品</label><select id="cardResourceProductId">' + getCardResourceProductOptions(selectedProductId) + '</select></div>'
            + moduleFields
            + '<div class="form-row"><label>排序值</label><input id="cardResourceSort" type="number" value="' + escapeHtmlAttr(String(selectedSort)) + '"></div>'
            + '<div class="form-row"><label>状态</label><select id="cardResourceStatus"><option value="1"' + (selectedStatus === 1 ? ' selected' : '') + '>启用</option><option value="0"' + (selectedStatus === 0 ? ' selected' : '') + '>停用</option></select></div>'
            + '<div class="form-row"><label>备注</label><textarea id="cardResourceRemark" placeholder="可选，填写额外说明">' + escapeHtml(isEdit ? (item.remark || '') : '') + '</textarea></div>',
            [
                { text: '取消', className: 'btn btn-warning', onClick: closeModal },
                {
                    text: isEdit ? '保存' : '创建',
                    className: 'btn btn-main',
                    onClick: async function () {
                        const payload = buildCardResourcePayload(moduleKey, {
                            is_common: valueById('cardResourceCommon'),
                            product_id: valueById('cardResourceProductId'),
                            title: valueById('cardResourceTitle'),
                            username: moduleKey === 'account' ? valueById('cardResourceUsername') : '',
                            password: moduleKey === 'account' ? valueById('cardResourcePassword') : '',
                            url: moduleKey === 'account' ? '' : valueById('cardResourceUrl'),
                            tutorial_mode: moduleKey === 'tutorial' ? valueById('cardResourceTutorialMode') : 'url',
                            content: moduleKey === 'tutorial'
                                ? String((document.getElementById('cardResourceRichEditor') && document.getElementById('cardResourceRichEditor').innerHTML) || '').trim()
                                : '',
                            sort: valueById('cardResourceSort'),
                            status: valueById('cardResourceStatus'),
                            remark: valueById('cardResourceRemark'),
                        });

                        try {
                            if (isEdit) {
                                await api('/admin/card-resources/' + item.id, { method: 'PUT', data: payload });
                            } else {
                                await api('/admin/card-resources', { method: 'POST', data: payload });
                            }
                            closeModal();
                            renderCards();
                        } catch (error) {
                            alert(error.message);
                        }
                    }
                }
            ]
        );

        function syncScopeWrap() {
            const isCommonMode = valueById('cardResourceCommon') === '1';
            const productWrap = document.getElementById('cardResourceProductWrap');
            if (productWrap) {
                productWrap.style.display = isCommonMode ? 'none' : 'block';
            }
        }

        function syncTutorialModeWrap() {
            if (moduleKey !== 'tutorial') {
                return;
            }

            const currentMode = valueById('cardResourceTutorialMode');
            const urlWrap = document.getElementById('cardResourceUrlWrap');
            const richWrap = document.getElementById('cardResourceRichWrap');
            if (urlWrap) {
                urlWrap.style.display = currentMode === 'url' ? 'block' : 'none';
            }
            if (richWrap) {
                richWrap.style.display = currentMode === 'richtext' ? 'block' : 'none';
            }
        }

        document.getElementById('cardResourceCommon').addEventListener('change', syncScopeWrap);
        syncScopeWrap();

        if (moduleKey === 'tutorial') {
            document.getElementById('cardResourceTutorialMode').addEventListener('change', syncTutorialModeWrap);
            document.querySelectorAll('[data-rich-command]').forEach((btn) => {
                btn.addEventListener('click', function () {
                    const command = this.dataset.richCommand;
                    const value = this.dataset.richValue || null;
                    const editor = document.getElementById('cardResourceRichEditor');
                    if (!editor) {
                        return;
                    }
                    restoreRichEditorSelection();
                    if (command === 'createLink') {
                        const link = window.prompt('请输入链接地址', 'https://');
                        if (!link) {
                            return;
                        }
                        document.execCommand('createLink', false, normalizeHttpUrl(link, '链接'));
                        saveRichEditorSelection();
                        return;
                    }
                    document.execCommand(command, false, value);
                    saveRichEditorSelection();
                });
            });
            const richEditor = document.getElementById('cardResourceRichEditor');
            const richImageFileInput = document.getElementById('cardResourceRichImageFile');

            if (richEditor) {
                ['mouseup', 'keyup', 'focus', 'input'].forEach((eventName) => {
                    richEditor.addEventListener(eventName, saveRichEditorSelection);
                });
            }
            document.querySelectorAll('[data-rich-command], [data-rich-action]').forEach((btn) => {
                btn.addEventListener('mousedown', function () {
                    saveRichEditorSelection();
                });
            });
            document.querySelectorAll('[data-rich-action]').forEach((btn) => {
                btn.addEventListener('click', async function () {
                    const action = this.dataset.richAction;
                    if (action === 'uploadImage') {
                        if (richImageFileInput) {
                            richImageFileInput.click();
                        }
                        return;
                    }

                    try {
                        if (action === 'insertImageByUrl') {
                            const imageUrl = window.prompt('请输入图片链接', 'https://');
                            if (!imageUrl) {
                                return;
                            }
                            this.disabled = true;
                            const result = await saveTutorialImageByUrl(imageUrl);
                            insertRichEditorHtml(buildRichImageHtml(result.url));
                            return;
                        }

                        if (action === 'insertVideoLink') {
                            const videoUrl = window.prompt('请输入视频链接', 'https://');
                            if (!videoUrl) {
                                return;
                            }
                            insertRichEditorHtml(buildRichVideoHtml(videoUrl));
                        }
                    } catch (error) {
                        alert(error.message);
                    } finally {
                        this.disabled = false;
                        if (richEditor) {
                            richEditor.focus();
                        }
                    }
                });
            });
            if (richImageFileInput) {
                richImageFileInput.addEventListener('change', async function () {
                    const file = this.files && this.files[0];
                    if (!file) {
                        return;
                    }

                    try {
                        const result = await uploadTutorialImageFile(file);
                        insertRichEditorHtml(buildRichImageHtml(result.url));
                    } catch (error) {
                        alert(error.message);
                    } finally {
                        this.value = '';
                        if (richEditor) {
                            richEditor.focus();
                        }
                    }
                });
            }
            syncTutorialModeWrap();
        }
    }

    async function renderOrders() {
        app.innerHTML = '<div class="panel"><div class="panel-title">' + escapeHtml(adminText('order.title')) + '</div><div class="empty">' + escapeHtml(adminText('order.loading')) + '</div></div>';
        try {
            const [orderData, productData] = await Promise.all([
                api('/admin/orders', { query: { page: 1, limit: 300 } }),
                api('/admin/products', { query: { page: 1, limit: 300 } }),
            ]);
            const orders = orderData.list || [];
            state.orderProducts = productData.list || [];

            app.innerHTML = '<div class="panel"><div class="panel-title">' + escapeHtml(adminText('order.title')) + '</div><div class="toolbar">'
                + '<button class="btn btn-main" id="createOrderBtn">' + escapeHtml(adminText('order.createOrder')) + '</button>'
                + '<span class="muted">' + escapeHtml(adminText('order.totalOrders', { count: orders.length })) + '</span>'
                + '</div>' + renderOrderTable(orders) + '</div>';

            document.getElementById('createOrderBtn').addEventListener('click', function () {
                openOrderModal();
            });
            bindOrderTableEvents();
        } catch (error) {
            renderError(error, adminText('order.title'));
        }
    }

    function renderOrderTable(orders) {
        if (!orders.length) return '<div class="empty">' + escapeHtml(adminText('order.empty')) + '</div>';

        const rows = orders.map((item) => '<tr>'
            + '<td>' + escapeHtml(String(item.id)) + '</td>'
            + '<td>' + escapeHtml(item.order_no || '') + '</td>'
            + '<td>' + escapeHtml(item.product && item.product.name ? item.product.name : '') + '</td>'
            + '<td>' + escapeHtml(String(item.quantity || 0)) + '</td>'
            + '<td>' + escapeHtml(energy(item.total_amount)) + ' ' + escapeHtml(adminText('order.energy')) + '</td>'
            + '<td>' + orderStatusTag(item.status) + '</td>'
            + '<td>' + escapeHtml(getOrderBuyerText(item)) + '</td>'
            + '<td>' + escapeHtml(formatOrderExpireText(item)) + '</td>'
            + '<td class="actions">'
            + '<button class="link-btn" data-order-detail="' + item.id + '">' + escapeHtml(adminText('order.detail')) + '</button>'
            + '<button class="link-btn" data-order-edit="' + item.id + '">' + escapeHtml(adminText('order.edit')) + '</button>'
            + '<button class="link-btn" data-order-status="' + item.id + '" data-new-status="paid">' + escapeHtml(adminText('order.setPaid')) + '</button>'
            + '<button class="link-btn" data-order-status="' + item.id + '" data-new-status="expired">' + escapeHtml(adminText('order.setExpired')) + '</button>'
            + '<button class="link-btn" data-order-delete="' + item.id + '">' + escapeHtml(adminText('order.delete')) + '</button>'
            + '</td>'
            + '</tr>').join('');

        return '<table><thead><tr><th>' + escapeHtml(adminText('order.id')) + '</th><th>' + escapeHtml(adminText('order.orderNo')) + '</th><th>' + escapeHtml(adminText('order.product')) + '</th><th>' + escapeHtml(adminText('order.quantity')) + '</th><th>' + escapeHtml(adminText('order.energy')) + '</th><th>' + escapeHtml(adminText('order.status')) + '</th><th>' + escapeHtml(adminText('order.buyer')) + '</th><th>' + escapeHtml(adminText('order.expiresAt')) + '</th><th>' + escapeHtml(adminText('order.actions')) + '</th></tr></thead><tbody>' + rows + '</tbody></table>';
    }

    function bindOrderTableEvents() {
        document.querySelectorAll('[data-order-status]').forEach((btn) => {
            btn.addEventListener('click', async function () {
                try {
                    await api('/admin/orders/' + Number(this.dataset.orderStatus) + '/status', { method: 'PUT', data: { status: this.dataset.newStatus } });
                    renderOrders();
                } catch (error) {
                    alert(error.message);
                }
            });
        });
        document.querySelectorAll('[data-order-detail]').forEach((btn) => {
            btn.addEventListener('click', async function () {
                try {
                    const detail = await api('/admin/orders/' + Number(this.dataset.orderDetail));
                    openOrderDetailModal(detail);
                } catch (error) {
                    alert(error.message);
                }
            });
        });
        document.querySelectorAll('[data-order-edit]').forEach((btn) => {
            btn.addEventListener('click', async function () {
                try {
                    const detail = await api('/admin/orders/' + Number(this.dataset.orderEdit));
                    openOrderModal(detail);
                } catch (error) {
                    alert(error.message);
                }
            });
        });
        document.querySelectorAll('[data-order-delete]').forEach((btn) => {
            btn.addEventListener('click', async function () {
                if (!confirm(adminText('order.confirmDelete'))) return;
                try {
                    await api('/admin/orders/' + Number(this.dataset.orderDelete), { method: 'DELETE' });
                    renderOrders();
                } catch (error) {
                    alert(error.message);
                }
            });
        });
    }

    function openOrderDetailModal(order) {
        const currentOrder = order || {};
        openModal(
            adminText('order.detailTitle'),
            '<div class="form-row"><label>' + escapeHtml(adminText('order.orderNo')) + '</label><div>' + escapeHtml(currentOrder.order_no || '-') + '</div></div>'
            + '<div class="form-row"><label>' + escapeHtml(adminText('order.buyerInfo')) + '</label><div>' + escapeHtml(getOrderBuyerText(currentOrder)) + '</div></div>'
            + '<div class="form-row"><label>' + escapeHtml(adminText('order.product')) + '</label><div>' + escapeHtml(currentOrder.product && currentOrder.product.name ? currentOrder.product.name : '-') + '</div></div>'
            + '<div class="form-row"><label>' + escapeHtml(adminText('order.quantity')) + '</label><div>' + escapeHtml(String(currentOrder.quantity || 0)) + '</div></div>'
            + '<div class="form-row"><label>' + escapeHtml(adminText('order.energy')) + '</label><div>' + escapeHtml(energy(currentOrder.total_amount)) + '</div></div>'
            + '<div class="form-row"><label>' + escapeHtml(adminText('order.status')) + '</label><div>' + orderStatusTag(currentOrder.status) + '</div></div>'
            + '<div class="form-row"><label>' + escapeHtml(adminText('order.createdAt')) + '</label><div>' + escapeHtml(currentOrder.created_at || '-') + '</div></div>'
            + '<div class="form-row"><label>' + escapeHtml(adminText('order.payTime')) + '</label><div>' + escapeHtml(currentOrder.pay_time || '-') + '</div></div>'
            + '<div class="form-row"><label>' + escapeHtml(adminText('order.deliverTime')) + '</label><div>' + escapeHtml(currentOrder.deliver_time || '-') + '</div></div>'
            + '<div class="form-row"><label>' + escapeHtml(adminText('order.expiresAt')) + '</label><div>' + escapeHtml(formatOrderExpireText(currentOrder)) + '</div></div>'
            + '<div class="form-row"><label>' + escapeHtml(adminText('order.deliveryContent')) + '</label><div>' + escapeHtml(currentOrder.deliver_content || adminText('order.noContent')) + '</div></div>'
            + '<div class="form-row"><label>' + escapeHtml(adminText('order.remark')) + '</label><div>' + escapeHtml(currentOrder.remark || '-') + '</div></div>',
            [
                { text: adminText('order.close'), className: 'btn btn-warning', onClick: closeModal },
                {
                    text: adminText('order.editOrder'),
                    className: 'btn btn-main',
                    onClick: function () {
                        openOrderModal(currentOrder);
                    }
                }
            ]
        );
    }

    function openOrderModal(order) {
        if (!state.orderProducts.length) {
            alert(adminText('order.productRequired'));
            return;
        }

        const currentOrder = order || {};
        const selectedProductId = String(currentOrder.product_id || (state.orderProducts[0] ? state.orderProducts[0].id : ''));
        const productOptions = state.orderProducts.map((item) => '<option value="' + item.id + '"' + (String(item.id) === selectedProductId ? ' selected' : '') + '>' + escapeHtml(item.name) + ' / ' + escapeHtml(energy(item.exchange_energy)) + ' ' + escapeHtml(adminText('order.energy')) + '</option>').join('');
        const expiresAt = currentOrder.expires_at || buildDefaultOrderExpiresAt(currentOrder.created_at);
        const isEdit = !!currentOrder.id;
        openModal(
            isEdit ? adminText('order.modalEdit') : adminText('order.modalCreate'),
            '<div class="form-row"><label>' + escapeHtml(adminText('order.product')) + '</label><select id="orderProductId">' + productOptions + '</select></div>'
            + '<div class="form-row"><label>' + escapeHtml(adminText('order.quantity')) + '</label><input id="orderQuantity" type="number" min="1" max="100" value="' + escapeHtml(String(currentOrder.quantity || 1)) + '"></div>'
            + '<div class="form-row"><label>' + escapeHtml(adminText('order.expiresAt')) + '</label><input id="orderExpiresAt" value="' + escapeHtml(expiresAt) + '" placeholder="YYYY-MM-DD HH:mm:ss"></div>'
            + '<div class="form-row"><label>' + escapeHtml(adminText('order.buyerEmail')) + '</label><input id="orderEmail" value="' + escapeHtml(currentOrder.buyer_email || '') + '" placeholder="' + escapeHtml(adminText('order.optional')) + '"></div>'
            + '<div class="form-row"><label>' + escapeHtml(adminText('order.contact')) + '</label><input id="orderContact" value="' + escapeHtml(currentOrder.buyer_contact || '') + '" placeholder="' + escapeHtml(adminText('order.optional')) + '"></div>'
            + '<div class="form-row"><label>' + escapeHtml(adminText('order.remark')) + '</label><input id="orderRemark" value="' + escapeHtml(currentOrder.remark || '') + '" placeholder="' + escapeHtml(adminText('order.optional')) + '"></div>',
            [
                { text: adminText('order.cancel'), className: 'btn btn-warning', onClick: closeModal },
                {
                    text: isEdit ? adminText('order.save') : adminText('order.create'),
                    className: 'btn btn-main',
                    onClick: async function () {
                        try {
                            await api('/admin/orders' + (isEdit ? '/' + Number(currentOrder.id) : ''), {
                                method: isEdit ? 'PUT' : 'POST',
                                data: {
                                    product_id: valueById('orderProductId'),
                                    quantity: valueById('orderQuantity'),
                                    expires_at: valueById('orderExpiresAt'),
                                    buyer_email: valueById('orderEmail'),
                                    buyer_contact: valueById('orderContact'),
                                    remark: valueById('orderRemark'),
                                },
                            });
                            closeModal();
                            renderOrders();
                        } catch (error) {
                            alert(error.message);
                        }
                    }
                }
            ]
        );
    }

    function getOrderBuyerText(order) {
        const currentOrder = order || {};
        const user = currentOrder.user || {};
        const id = Number(currentOrder.user_id || user.id || 0);
        const name = user.nickname || user.username || '';
        const contact = user.phone || user.email || currentOrder.buyer_contact || currentOrder.buyer_email || '';

        if (name && id > 0) {
            return name + ' (ID:' + id + ')';
        }
        if (name) {
            return name;
        }
        if (contact && id > 0) {
            return contact + ' (ID:' + id + ')';
        }
        if (contact) {
            return contact;
        }
        if (id > 0) {
            return 'ID:' + id;
        }

        return adminText('order.noUser');
    }

    function buildDefaultOrderExpiresAt(createdAt) {
        const base = createdAt ? new Date(String(createdAt).replace(/-/g, '/')) : new Date();
        if (Number.isNaN(base.getTime())) {
            return '';
        }
        base.setHours(base.getHours() + 24);
        return formatDateTime(base);
    }

    function formatOrderExpireText(order) {
        const expiresAt = order.expires_at || buildDefaultOrderExpiresAt(order.created_at);
        if (!expiresAt) {
            return '-';
        }
        const expireDate = new Date(String(expiresAt).replace(/-/g, '/'));
        if (Number.isNaN(expireDate.getTime())) {
            return expiresAt;
        }
        if (expireDate.getTime() <= Date.now() || String(order.status || '') === 'expired') {
            return expiresAt + ' (' + adminText('order.expiredSuffix') + ')';
        }
        const minutes = Math.ceil((expireDate.getTime() - Date.now()) / 60000);
        const hours = Math.floor(minutes / 60);
        const restMinutes = minutes % 60;
        return expiresAt + ' (' + adminText('order.remaining', { hours: hours, minutes: restMinutes }) + ')';
    }

    function formatDateTime(date) {
        const pad = (value) => String(value).padStart(2, '0');
        return date.getFullYear() + '-'
            + pad(date.getMonth() + 1) + '-'
            + pad(date.getDate()) + ' '
            + pad(date.getHours()) + ':'
            + pad(date.getMinutes()) + ':'
            + pad(date.getSeconds());
    }

    async function renderUsers() {
        app.innerHTML = '<div class="panel"><div class="panel-title">用户管理</div><div class="empty">加载中...</div></div>';
        try {
            const data = await api('/admin/users', {
                query: {
                    page: 1,
                    limit: 300,
                    keyword: state.userFilters.keyword,
                    status: state.userFilters.status,
                },
            });
            const list = data.list || [];
            const summary = data.summary || {};

            app.innerHTML = '<div class="panel"><div class="panel-title">用户管理</div>'
                + '<div class="stats-grid">'
                + statCard('用户总数', summary.user_total || 0)
                + statCard('启用用户', summary.enabled_total || 0)
                + statCard('停用用户', summary.disabled_total || 0)
                + statCard('总能量池', summary.energy_total || 0)
                + '</div></div>'
                + '<div class="panel"><div class="panel-title">用户列表</div><div class="toolbar">'
                + '<button class="btn btn-main" id="addUserBtn">新增用户</button>'
                + '<input id="userKeyword" placeholder="搜索用户名/昵称/手机/邮箱/OpenID" value="' + escapeHtmlAttr(state.userFilters.keyword || '') + '">'
                + '<select id="userStatus"><option value="">全部状态</option><option value="1"' + (state.userFilters.status === '1' ? ' selected' : '') + '>启用</option><option value="0"' + (state.userFilters.status === '0' ? ' selected' : '') + '>停用</option></select>'
                + '<button class="btn btn-small" id="searchUserBtn">查询</button>'
                + '<button class="btn btn-warning" id="resetUserBtn">重置</button>'
                + '<span class="muted">共 ' + escapeHtml(String(list.length)) + ' 位用户</span>'
                + '</div>' + renderUserTable(list) + '</div>';

            document.getElementById('addUserBtn').addEventListener('click', function () {
                openUserModal();
            });
            document.getElementById('searchUserBtn').addEventListener('click', function () {
                state.userFilters.keyword = valueById('userKeyword');
                state.userFilters.status = valueById('userStatus');
                renderUsers();
            });
            document.getElementById('resetUserBtn').addEventListener('click', function () {
                state.userFilters = { keyword: '', status: '' };
                renderUsers();
            });
            bindUserTableEvents(list);
        } catch (error) {
            renderError(error, '用户管理');
        }
    }

    function renderUserTable(list) {
        if (!list.length) {
            return '<div class="empty">暂无用户数据</div>';
        }

        const rows = list.map((item) => '<tr>'
            + '<td>' + escapeHtml(String(item.id)) + '</td>'
            + '<td><strong>' + escapeHtml(item.username || '') + '</strong><div class="muted">' + escapeHtml(item.nickname || '-') + '</div></td>'
            + '<td>' + escapeHtml(item.phone || '-') + '</td>'
            + '<td>' + escapeHtml(item.email || '-') + '</td>'
            + '<td><span class="muted">' + escapeHtml(item.wx_openid || '-') + '</span></td>'
            + '<td><span class="tag tag-yellow">能量 ' + escapeHtml(String(item.energy || 0)) + '</span></td>'
            + '<td>' + renderEnabledStatusTag(item.status) + '</td>'
            + '<td>' + escapeHtml(item.updated_at || item.created_at || '-') + '</td>'
            + '<td class="actions"><button class="link-btn" data-user-edit="' + item.id + '">编辑</button><button class="link-btn" data-user-energy="' + item.id + '">调整能量</button><button class="link-btn" data-user-delete="' + item.id + '">删除</button></td>'
            + '</tr>').join('');

        return '<table><thead><tr><th>ID</th><th>用户信息</th><th>手机号</th><th>邮箱</th><th>微信 OpenID</th><th>能量</th><th>状态</th><th>更新时间</th><th>操作</th></tr></thead><tbody>' + rows + '</tbody></table>';
    }

    function bindUserTableEvents(list) {
        document.querySelectorAll('[data-user-edit]').forEach((btn) => {
            btn.addEventListener('click', function () {
                const item = list.find((row) => Number(row.id) === Number(this.dataset.userEdit));
                openUserModal(item || null);
            });
        });
        document.querySelectorAll('[data-user-energy]').forEach((btn) => {
            btn.addEventListener('click', function () {
                const item = list.find((row) => Number(row.id) === Number(this.dataset.userEnergy));
                if (!item) {
                    return;
                }
                openEnergyAdjustModal(item.id, item.username || item.nickname || ('#' + item.id));
            });
        });
        document.querySelectorAll('[data-user-delete]').forEach((btn) => {
            btn.addEventListener('click', async function () {
                if (!confirm('确定删除这个用户吗？')) {
                    return;
                }
                try {
                    await api('/admin/users/' + Number(this.dataset.userDelete), { method: 'DELETE' });
                    renderUsers();
                } catch (error) {
                    alert(error.message);
                }
            });
        });
    }

    function openUserModal(user) {
        const isEdit = !!user;
        openModal(
            isEdit ? '编辑用户' : '新增用户',
            '<div class="form-row"><label>用户名</label><input id="userUsername" value="' + escapeHtmlAttr(isEdit ? (user.username || '') : '') + '" placeholder="请输入用户名"></div>'
            + '<div class="form-row"><label>昵称</label><input id="userNickname" value="' + escapeHtmlAttr(isEdit ? (user.nickname || '') : '') + '" placeholder="请输入昵称"></div>'
            + '<div class="form-row"><label>手机号</label><input id="userPhone" value="' + escapeHtmlAttr(isEdit ? (user.phone || '') : '') + '" placeholder="请输入手机号"></div>'
            + '<div class="form-row"><label>邮箱</label><input id="userEmail" value="' + escapeHtmlAttr(isEdit ? (user.email || '') : '') + '" placeholder="请输入邮箱"></div>'
            + '<div class="form-row"><label>头像地址</label><input id="userAvatar" value="' + escapeHtmlAttr(isEdit ? (user.avatar || '') : '') + '" placeholder="可选"></div>'
            + '<div class="form-row"><label>微信 OpenID</label><input id="userWxOpenid" value="' + escapeHtmlAttr(isEdit ? (user.wx_openid || '') : '') + '" placeholder="微信登录后自动记录，也可手动填写"></div>'
            + '<div class="form-row"><label>初始能量</label><input id="userEnergy" type="number" min="0" value="' + escapeHtmlAttr(String(isEdit ? Number(user.energy || 0) : 0)) + '"></div>'
            + '<div class="form-row"><label>状态</label><select id="userStatusModal"><option value="1"' + (!isEdit || Number(user.status) === 1 ? ' selected' : '') + '>启用</option><option value="0"' + (isEdit && Number(user.status) === 0 ? ' selected' : '') + '>停用</option></select></div>'
            + '<div class="form-row"><label>备注</label><textarea id="userRemark">' + escapeHtml(isEdit ? (user.remark || '') : '') + '</textarea></div>',
            [
                { text: '取消', className: 'btn btn-warning', onClick: closeModal },
                {
                    text: isEdit ? '保存' : '创建',
                    className: 'btn btn-main',
                    onClick: async function () {
                        const payload = {
                            username: valueById('userUsername'),
                            nickname: valueById('userNickname'),
                            phone: valueById('userPhone'),
                            email: valueById('userEmail'),
                            avatar: valueById('userAvatar'),
                            wx_openid: valueById('userWxOpenid'),
                            energy: valueById('userEnergy'),
                            status: valueById('userStatusModal'),
                            remark: valueById('userRemark'),
                        };
                        try {
                            if (isEdit) {
                                await api('/admin/users/' + Number(user.id), { method: 'PUT', data: payload });
                            } else {
                                await api('/admin/users', { method: 'POST', data: payload });
                            }
                            closeModal();
                            renderUsers();
                        } catch (error) {
                            alert(error.message);
                        }
                    },
                },
            ]
        );
    }

    async function renderEnergyManagement() {
        app.innerHTML = '<div class="panel"><div class="panel-title">能量管理</div><div class="empty">加载中...</div></div>';
        try {
            const [userData, logData] = await Promise.all([
                api('/admin/users', { query: { page: 1, limit: 300 } }),
                api('/admin/energy-logs', { query: { page: 1, limit: 50 } }),
            ]);
            const users = userData.list || [];
            const logs = logData.list || [];
            const summary = userData.summary || {};

            app.innerHTML = '<div class="panel"><div class="panel-title">能量管理</div>'
                + '<div class="stats-grid">'
                + statCard('用户总数', summary.user_total || 0)
                + statCard('总能量池', summary.energy_total || 0)
                + statCard('近 50 条记录', logs.length)
                + statCard('启用用户', summary.enabled_total || 0)
                + '</div></div>'
                + '<div class="panel"><div class="panel-title">用户能量列表</div><div class="toolbar">'
                + '<button class="btn btn-warning" id="refreshEnergyBtn">刷新数据</button>'
                + '<span class="muted">点击“调整能量”可直接手动加减</span>'
                + '</div>' + renderEnergyUserTable(users) + '</div>'
                + '<div class="panel"><div class="panel-title">最近能量记录</div>' + renderEnergyLogTable(logs) + '</div>';

            document.getElementById('refreshEnergyBtn').addEventListener('click', function () {
                renderEnergyManagement();
            });
            document.querySelectorAll('[data-energy-adjust]').forEach((btn) => {
                btn.addEventListener('click', function () {
                    const user = users.find((item) => Number(item.id) === Number(this.dataset.energyAdjust));
                    if (!user) {
                        return;
                    }
                    openEnergyAdjustModal(user.id, user.username || user.nickname || ('#' + user.id));
                });
            });
        } catch (error) {
            renderError(error, '能量管理');
        }
    }

    function renderEnergyUserTable(users) {
        if (!users.length) {
            return '<div class="empty">暂无用户，先去用户管理里创建用户吧</div>';
        }

        const rows = users.map((item) => '<tr>'
            + '<td>' + escapeHtml(String(item.id)) + '</td>'
            + '<td>' + escapeHtml(item.username || '') + '<div class="muted">' + escapeHtml(item.nickname || '-') + '</div></td>'
            + '<td><span class="tag tag-yellow">当前 ' + escapeHtml(String(item.energy || 0)) + '</span></td>'
            + '<td>' + renderEnabledStatusTag(item.status) + '</td>'
            + '<td>' + escapeHtml(item.updated_at || item.created_at || '-') + '</td>'
            + '<td class="actions"><button class="link-btn" data-energy-adjust="' + item.id + '">调整能量</button></td>'
            + '</tr>').join('');

        return '<table><thead><tr><th>ID</th><th>用户</th><th>能量余额</th><th>状态</th><th>更新时间</th><th>操作</th></tr></thead><tbody>' + rows + '</tbody></table>';
    }

    function renderEnergyLogTable(logs) {
        if (!logs.length) {
            return '<div class="empty">暂无能量变动记录</div>';
        }

        const rows = logs.map((item) => '<tr>'
            + '<td>' + escapeHtml(String(item.id)) + '</td>'
            + '<td>' + escapeHtml(item.user && (item.user.nickname || item.user.username) ? (item.user.nickname || item.user.username) : '-') + '</td>'
            + '<td>' + renderEnergyChangeTypeTag(item.change_type) + '</td>'
            + '<td>' + escapeHtml((Number(item.change_amount || 0) > 0 ? '+' : '') + String(item.change_amount || 0)) + '</td>'
            + '<td>' + escapeHtml(String(item.balance_before || 0)) + ' -> ' + escapeHtml(String(item.balance_after || 0)) + '</td>'
            + '<td>' + escapeHtml(item.remark || item.source || '-') + '</td>'
            + '<td>' + escapeHtml(item.operator && (item.operator.nickname || item.operator.username) ? (item.operator.nickname || item.operator.username) : '-') + '</td>'
            + '<td>' + escapeHtml(item.created_at || '-') + '</td>'
            + '</tr>').join('');

        return '<table><thead><tr><th>ID</th><th>用户</th><th>类型</th><th>变动</th><th>余额变化</th><th>说明</th><th>操作人</th><th>时间</th></tr></thead><tbody>' + rows + '</tbody></table>';
    }

    function openEnergyAdjustModal(userId, userLabel) {
        openModal(
            '调整能量 - ' + userLabel,
            '<div class="form-row"><label>调整数量</label><input id="energyChangeAmount" type="number" placeholder="正数增加，负数减少"></div>'
            + '<div class="form-row"><label>备注</label><textarea id="energyChangeRemark" placeholder="例如：后台补偿、人工扣减、活动赠送"></textarea></div>',
            [
                { text: '取消', className: 'btn btn-warning', onClick: closeModal },
                {
                    text: '确认调整',
                    className: 'btn btn-main',
                    onClick: async function () {
                        try {
                            await api('/admin/users/' + Number(userId) + '/adjust-energy', {
                                method: 'POST',
                                data: {
                                    change_amount: valueById('energyChangeAmount'),
                                    remark: valueById('energyChangeRemark'),
                                },
                            });
                            closeModal();
                            if (state.view === 'users') {
                                renderUsers();
                            } else {
                                renderEnergyManagement();
                            }
                        } catch (error) {
                            alert(error.message);
                        }
                    },
                },
            ]
        );
    }

    async function renderEnergySources() {
        app.innerHTML = '<div class="panel"><div class="panel-title">能量获取</div><div class="empty">加载中...</div></div>';
        try {
            const data = await api('/admin/energy-sources', {
                query: {
                    keyword: state.energySourceFilters.keyword,
                    status: state.energySourceFilters.status,
                },
            });
            const list = data.list || [];

            app.innerHTML = '<div class="panel"><div class="panel-title">能量获取</div><div class="toolbar">'
                + '<button class="btn btn-main" id="addEnergySourceBtn">新增获取方式</button>'
                + '<input id="energySourceKeyword" placeholder="搜索名称/标识/描述" value="' + escapeHtmlAttr(state.energySourceFilters.keyword || '') + '">'
                + '<select id="energySourceStatus"><option value="">全部状态</option><option value="1"' + (state.energySourceFilters.status === '1' ? ' selected' : '') + '>启用</option><option value="0"' + (state.energySourceFilters.status === '0' ? ' selected' : '') + '>停用</option></select>'
                + '<button class="btn btn-small" id="searchEnergySourceBtn">查询</button>'
                + '<button class="btn btn-warning" id="resetEnergySourceBtn">重置</button>'
                + '<span class="muted">共 ' + escapeHtml(String(list.length)) + ' 条获取方式</span>'
                + '</div>' + renderEnergySourceTable(list) + '</div>';

            document.getElementById('addEnergySourceBtn').addEventListener('click', function () {
                openEnergySourceModal();
            });
            document.getElementById('searchEnergySourceBtn').addEventListener('click', function () {
                state.energySourceFilters.keyword = valueById('energySourceKeyword');
                state.energySourceFilters.status = valueById('energySourceStatus');
                renderEnergySources();
            });
            document.getElementById('resetEnergySourceBtn').addEventListener('click', function () {
                state.energySourceFilters = { keyword: '', status: '' };
                renderEnergySources();
            });

            document.querySelectorAll('[data-energy-source-edit]').forEach((btn) => {
                btn.addEventListener('click', function () {
                    const item = list.find((row) => Number(row.id) === Number(this.dataset.energySourceEdit));
                    openEnergySourceModal(item || null);
                });
            });
            document.querySelectorAll('[data-energy-source-delete]').forEach((btn) => {
                btn.addEventListener('click', async function () {
                    if (!confirm('确定删除这个能量获取方式吗？')) {
                        return;
                    }
                    try {
                        await api('/admin/energy-sources/' + Number(this.dataset.energySourceDelete), { method: 'DELETE' });
                        renderEnergySources();
                    } catch (error) {
                        alert(error.message);
                    }
                });
            });
        } catch (error) {
            renderError(error, '能量获取');
        }
    }

    function renderEnergySourceTable(list) {
        if (!list.length) {
            return '<div class="empty">暂无能量获取方式</div>';
        }

        const rows = list.map((item) => '<tr>'
            + '<td>' + escapeHtml(String(item.id)) + '</td>'
            + '<td><strong>' + escapeHtml(item.name || '') + '</strong><div class="muted">' + escapeHtml(item.source_key || '') + '</div></td>'
            + '<td>' + escapeHtml(String(item.energy_value || 0)) + '</td>'
            + '<td>' + escapeHtml(String(item.daily_limit || 0)) + '</td>'
            + '<td>' + escapeHtml(String(item.sort || 0)) + '</td>'
            + '<td>' + renderEnabledStatusTag(item.status) + '</td>'
            + '<td>' + escapeHtml(item.description || '-') + '</td>'
            + '<td class="actions"><button class="link-btn" data-energy-source-edit="' + item.id + '">编辑</button><button class="link-btn" data-energy-source-delete="' + item.id + '">删除</button></td>'
            + '</tr>').join('');

        return '<table><thead><tr><th>ID</th><th>方式</th><th>奖励能量</th><th>每日上限</th><th>排序</th><th>状态</th><th>描述</th><th>操作</th></tr></thead><tbody>' + rows + '</tbody></table>';
    }

    function openEnergySourceModal(item) {
        const isEdit = !!item;
        openModal(
            isEdit ? '编辑能量获取方式' : '新增能量获取方式',
            '<div class="form-row"><label>方式名称</label><input id="energySourceName" value="' + escapeHtmlAttr(isEdit ? (item.name || '') : '') + '"></div>'
            + '<div class="form-row"><label>方式标识</label><input id="energySourceKey" value="' + escapeHtmlAttr(isEdit ? (item.source_key || '') : '') + '" placeholder="例如 daily_checkin"></div>'
            + '<div class="form-row"><label>奖励能量</label><input id="energySourceValue" type="number" min="0" value="' + escapeHtmlAttr(String(isEdit ? Number(item.energy_value || 0) : 0)) + '"></div>'
            + '<div class="form-row"><label>每日上限</label><input id="energySourceLimit" type="number" min="0" value="' + escapeHtmlAttr(String(isEdit ? Number(item.daily_limit || 0) : 0)) + '"></div>'
            + '<div class="form-row"><label>排序</label><input id="energySourceSort" type="number" value="' + escapeHtmlAttr(String(isEdit ? Number(item.sort || 0) : 0)) + '"></div>'
            + '<div class="form-row"><label>状态</label><select id="energySourceStatusModal"><option value="1"' + (!isEdit || Number(item.status) === 1 ? ' selected' : '') + '>启用</option><option value="0"' + (isEdit && Number(item.status) === 0 ? ' selected' : '') + '>停用</option></select></div>'
            + '<div class="form-row"><label>描述</label><textarea id="energySourceDesc">' + escapeHtml(isEdit ? (item.description || '') : '') + '</textarea></div>',
            [
                { text: '取消', className: 'btn btn-warning', onClick: closeModal },
                {
                    text: isEdit ? '保存' : '创建',
                    className: 'btn btn-main',
                    onClick: async function () {
                        const payload = {
                            name: valueById('energySourceName'),
                            source_key: valueById('energySourceKey'),
                            energy_value: valueById('energySourceValue'),
                            daily_limit: valueById('energySourceLimit'),
                            sort: valueById('energySourceSort'),
                            status: valueById('energySourceStatusModal'),
                            description: valueById('energySourceDesc'),
                        };
                        try {
                            if (isEdit) {
                                await api('/admin/energy-sources/' + Number(item.id), { method: 'PUT', data: payload });
                            } else {
                                await api('/admin/energy-sources', { method: 'POST', data: payload });
                            }
                            closeModal();
                            renderEnergySources();
                        } catch (error) {
                            alert(error.message);
                        }
                    },
                },
            ]
        );
    }


    async function renderEnergyPackages() {
        app.innerHTML = '<div class="panel"><div class="panel-title">\u80fd\u91cf\u5957\u9910</div><div class="empty">\u52a0\u8f7d\u4e2d...</div></div>';
        try {
            const data = await api('/admin/energy-packages', {
                query: {
                    keyword: state.energyPackageFilters.keyword,
                    status: state.energyPackageFilters.status,
                },
            });
            const list = data.list || [];

            app.innerHTML = '<div class="panel"><div class="panel-title">\u80fd\u91cf\u5957\u9910</div><div class="toolbar">'
                + '<button class="btn btn-main" id="addEnergyPackageBtn">\u65b0\u589e\u5957\u9910</button>'
                + '<input id="energyPackageKeyword" placeholder="\u641c\u7d22\u5957\u9910\u540d\u79f0/\u63cf\u8ff0" value="' + escapeHtmlAttr(state.energyPackageFilters.keyword || '') + '">'
                + '<select id="energyPackageStatus"><option value="">\u5168\u90e8\u72b6\u6001</option><option value="1"' + (state.energyPackageFilters.status === '1' ? ' selected' : '') + '>\u542f\u7528</option><option value="0"' + (state.energyPackageFilters.status === '0' ? ' selected' : '') + '>\u505c\u7528</option></select>'
                + '<button class="btn btn-small" id="searchEnergyPackageBtn">\u67e5\u8be2</button>'
                + '<button class="btn btn-warning" id="resetEnergyPackageBtn">\u91cd\u7f6e</button>'
                + '<span class="muted">\u5171 ' + escapeHtml(String(list.length)) + ' \u4e2a\u5957\u9910\uff0c\u524d\u7aef\u83b7\u53d6\u80fd\u91cf\u9875\u5b9e\u65f6\u8bfb\u53d6\u8fd9\u91cc\u7684\u542f\u7528\u5957\u9910</span>'
                + '</div>' + renderEnergyPackageTable(list) + '</div>';

            document.getElementById('addEnergyPackageBtn').addEventListener('click', function () {
                openEnergyPackageModal();
            });
            document.getElementById('searchEnergyPackageBtn').addEventListener('click', function () {
                state.energyPackageFilters.keyword = valueById('energyPackageKeyword');
                state.energyPackageFilters.status = valueById('energyPackageStatus');
                renderEnergyPackages();
            });
            document.getElementById('resetEnergyPackageBtn').addEventListener('click', function () {
                state.energyPackageFilters = { keyword: '', status: '' };
                renderEnergyPackages();
            });

            document.querySelectorAll('[data-energy-package-edit]').forEach((btn) => {
                btn.addEventListener('click', function () {
                    const item = list.find((row) => Number(row.id) === Number(this.dataset.energyPackageEdit));
                    openEnergyPackageModal(item || null);
                });
            });
            document.querySelectorAll('[data-energy-package-delete]').forEach((btn) => {
                btn.addEventListener('click', async function () {
                    if (!confirm('\u786e\u5b9a\u5220\u9664\u8fd9\u4e2a\u80fd\u91cf\u5957\u9910\u5417\uff1f')) {
                        return;
                    }
                    try {
                        await api('/admin/energy-packages/' + Number(this.dataset.energyPackageDelete), { method: 'DELETE' });
                        renderEnergyPackages();
                    } catch (error) {
                        alert(error.message);
                    }
                });
            });
        } catch (error) {
            renderError(error, '\u80fd\u91cf\u5957\u9910');
        }
    }

    function renderEnergyPackageTable(list) {
        if (!list.length) {
            return '<div class="empty">\u6682\u65e0\u80fd\u91cf\u5957\u9910</div>';
        }

        const rows = list.map((item) => {
            const energyValue = Number(item.energy_value || 0);
            const bonusEnergy = Number(item.bonus_energy || 0);
            const totalEnergy = energyValue + bonusEnergy;
            const amount = Number(item.amount || 0).toFixed(2);
            return '<tr>'
                + '<td>' + escapeHtml(String(item.id)) + '</td>'
                + '<td><strong>' + escapeHtml(item.name || '') + '</strong><div class="muted">' + escapeHtml(item.description || '-') + '</div></td>'
                + '<td>' + escapeHtml(String(energyValue)) + '</td>'
                + '<td>' + escapeHtml(String(bonusEnergy)) + '</td>'
                + '<td><strong>' + escapeHtml(String(totalEnergy)) + '</strong></td>'
                + '<td>\u00a5' + escapeHtml(amount) + '</td>'
                + '<td>' + escapeHtml(String(item.sort || 0)) + '</td>'
                + '<td>' + renderEnabledStatusTag(item.status) + '</td>'
                + '<td class="actions"><button class="link-btn" data-energy-package-edit="' + item.id + '">\u7f16\u8f91</button><button class="link-btn" data-energy-package-delete="' + item.id + '">\u5220\u9664</button></td>'
                + '</tr>';
        }).join('');

        return '<table><thead><tr><th>ID</th><th>\u5957\u9910</th><th>\u57fa\u7840\u80fd\u91cf</th><th>\u8d60\u9001\u80fd\u91cf</th><th>\u5230\u8d26\u80fd\u91cf</th><th>\u91d1\u989d</th><th>\u6392\u5e8f</th><th>\u72b6\u6001</th><th>\u64cd\u4f5c</th></tr></thead><tbody>' + rows + '</tbody></table>';
    }

    function openEnergyPackageModal(item) {
        const isEdit = !!item;
        openModal(
            isEdit ? '\u7f16\u8f91\u80fd\u91cf\u5957\u9910' : '\u65b0\u589e\u80fd\u91cf\u5957\u9910',
            '<div class="form-row"><label>\u5957\u9910\u540d\u79f0</label><input id="energyPackageName" value="' + escapeHtmlAttr(isEdit ? (item.name || '') : '') + '"></div>'
            + '<div class="form-row"><label>\u57fa\u7840\u80fd\u91cf</label><input id="energyPackageValue" type="number" min="1" value="' + escapeHtmlAttr(String(isEdit ? Number(item.energy_value || 0) : 100)) + '"></div>'
            + '<div class="form-row"><label>\u8d60\u9001\u80fd\u91cf</label><input id="energyPackageBonus" type="number" min="0" value="' + escapeHtmlAttr(String(isEdit ? Number(item.bonus_energy || 0) : 0)) + '"></div>'
            + '<div class="form-row"><label>\u5957\u9910\u91d1\u989d</label><input id="energyPackageAmount" type="number" min="0.01" step="0.01" value="' + escapeHtmlAttr(String(isEdit ? Number(item.amount || 0) : 9.9)) + '"></div>'
            + '<div class="form-row"><label>\u6392\u5e8f</label><input id="energyPackageSort" type="number" value="' + escapeHtmlAttr(String(isEdit ? Number(item.sort || 0) : 0)) + '"></div>'
            + '<div class="form-row"><label>\u72b6\u6001</label><select id="energyPackageStatusModal"><option value="1"' + (!isEdit || Number(item.status) === 1 ? ' selected' : '') + '>\u542f\u7528</option><option value="0"' + (isEdit && Number(item.status) === 0 ? ' selected' : '') + '>\u505c\u7528</option></select></div>'
            + '<div class="form-row"><label>\u63cf\u8ff0</label><textarea id="energyPackageDesc">' + escapeHtml(isEdit ? (item.description || '') : '') + '</textarea></div>',
            [
                { text: '\u53d6\u6d88', className: 'btn btn-warning', onClick: closeModal },
                {
                    text: isEdit ? '\u4fdd\u5b58' : '\u521b\u5efa',
                    className: 'btn btn-main',
                    onClick: async function () {
                        const payload = {
                            name: valueById('energyPackageName'),
                            energy_value: valueById('energyPackageValue'),
                            bonus_energy: valueById('energyPackageBonus'),
                            amount: valueById('energyPackageAmount'),
                            sort: valueById('energyPackageSort'),
                            status: valueById('energyPackageStatusModal'),
                            description: valueById('energyPackageDesc'),
                        };
                        try {
                            if (isEdit) {
                                await api('/admin/energy-packages/' + Number(item.id), { method: 'PUT', data: payload });
                            } else {
                                await api('/admin/energy-packages', { method: 'POST', data: payload });
                            }
                            closeModal();
                            renderEnergyPackages();
                        } catch (error) {
                            alert(error.message);
                        }
                    },
                },
            ]
        );
    }

    async function renderConfigs() {
        const currentGroup = getCurrentConfigGroup();
        app.innerHTML = '<div class="panel"><div class="panel-title">' + escapeHtml(currentGroup.label) + '</div><div class="empty">加载中...</div></div>';
        try {
            const data = await api('/admin/configs', {
                query: {
                    group: currentGroup.key,
                },
            });
            const items = data.items || [];

            app.innerHTML = '<div class="panel"><div class="panel-title">' + escapeHtml(currentGroup.label) + '</div><div class="toolbar">'
                + Object.values(configGroupMetaMap).map((item) => '<button class="btn ' + (item.key === currentGroup.key ? 'btn-main' : 'btn-small') + '" data-config-nav="' + escapeHtmlAttr(item.url) + '">' + escapeHtml(item.label) + '</button>').join('')
                + '<button class="btn btn-warning" id="reloadConfigBtn">刷新</button>'
                + '</div>'
                + '<div class="config-grid">' + items.map((item) => renderConfigField(item)).join('') + '</div>'
                + '<div class="toolbar"><button class="btn btn-main" id="saveConfigBtn">保存配置</button><span class="muted">当前分组：' + escapeHtml(currentGroup.label) + '</span></div>'
                + '</div>';

            document.querySelectorAll('[data-config-nav]').forEach((btn) => {
                btn.addEventListener('click', function () {
                    window.location.href = this.dataset.configNav;
                });
            });
            document.getElementById('reloadConfigBtn').addEventListener('click', function () {
                renderConfigs();
            });
            bindConfigImageFieldEvents(currentGroup.key);
            document.getElementById('saveConfigBtn').addEventListener('click', async function () {
                const payload = {};
                document.querySelectorAll('[data-config-field]').forEach((el) => {
                    payload[el.dataset.configField] = el.value;
                });
                try {
                    await api('/admin/configs/group/' + currentGroup.key, {
                        method: 'POST',
                        data: payload,
                    });
                    alert('系统配置保存成功');
                    renderConfigs();
                } catch (error) {
                    alert(error.message);
                }
            });
        } catch (error) {
            renderError(error, currentGroup.label);
        }
    }

    function renderConfigField(item) {
        const inputType = String(item.input_type || 'text');
        let fieldHtml = '';
        let fieldCardClass = '';

        if (isConfigImageField(item)) {
            fieldCardClass = ' config-card-image';
            fieldHtml = renderConfigImageField(item);
        } else if (inputType === 'textarea') {
            fieldCardClass = ' config-card-textarea';
            fieldHtml = '<textarea class="config-textarea" data-config-field="' + escapeHtmlAttr(item.config_key || '') + '" placeholder="' + escapeHtmlAttr(item.placeholder || '') + '">' + escapeHtml(item.config_value || '') + '</textarea>';
        } else {
            fieldHtml = '<input type="' + (inputType === 'password' ? 'password' : 'text') + '" data-config-field="' + escapeHtmlAttr(item.config_key || '') + '" value="' + escapeHtmlAttr(item.config_value || '') + '" placeholder="' + escapeHtmlAttr(item.placeholder || '') + '">';
        }

        return '<div class="config-card' + fieldCardClass + '">'
            + '<div class="form-row"><label>' + escapeHtml(item.config_name || '') + '</label>' + fieldHtml + '</div>'
            + '<div class="muted config-hint">' + escapeHtml(item.remark || '') + '</div>'
            + '</div>';
    }

    function isConfigImageField(item) {
        const inputType = String(item && item.input_type ? item.input_type : 'text').toLowerCase();
        const configKey = String(item && item.config_key ? item.config_key : '');
        return inputType === 'image' || configImageFieldKeys.has(configKey);
    }

    function renderConfigImageField(item) {
        const configKey = String(item && item.config_key ? item.config_key : '');
        const configName = String(item && item.config_name ? item.config_name : '');
        const configValue = String(item && item.config_value ? item.config_value : '').trim();
        const placeholder = String(item && item.placeholder ? item.placeholder : '');

        return '<div class="config-image-field">'
            + '<input type="text" data-config-field="' + escapeHtmlAttr(configKey) + '" data-config-image-input="1" data-config-image-label="' + escapeHtmlAttr(configName || '图片') + '" value="' + escapeHtmlAttr(configValue) + '" placeholder="' + escapeHtmlAttr(placeholder) + '">'
            + '<div class="config-image-tools">'
            + '<button type="button" class="btn btn-small" data-config-image-save-url="' + escapeHtmlAttr(configKey) + '">链接保存</button>'
            + '<label class="btn btn-warning product-upload-label">选择图片<input type="file" accept="image/*" data-config-image-upload="' + escapeHtmlAttr(configKey) + '" hidden></label>'
            + '</div>'
            + '<div class="config-image-preview" data-config-image-preview="' + escapeHtmlAttr(configKey) + '">' + renderConfigImagePreviewHtml(configValue, configName) + '</div>'
            + '<div class="config-image-tip">上传或链接抓取成功后，会自动回填本地图片地址。</div>'
            + '</div>';
    }

    function renderConfigImagePreviewHtml(url, label) {
        const imageUrl = String(url || '').trim();
        if (!imageUrl) {
            return '<div class="product-media-empty">暂无图片</div>';
        }

        return '<div class="product-media-card single">'
            + '<img src="' + escapeHtmlAttr(imageUrl) + '" alt="' + escapeHtmlAttr(label || '图片') + '">'
            + '<button type="button" class="product-media-remove" data-config-image-clear="1">移除</button>'
            + '</div>';
    }

    function getConfigFieldInput(configKey) {
        return document.querySelector('[data-config-field="' + String(configKey || '') + '"]');
    }

    function getConfigImagePreviewNode(configKey) {
        return document.querySelector('[data-config-image-preview="' + String(configKey || '') + '"]');
    }

    function updateConfigImageField(configKey, value) {
        const input = getConfigFieldInput(configKey);
        const nextValue = String(value || '').trim();
        if (input) {
            input.value = nextValue;
        }
        renderConfigImagePreview(configKey, nextValue);
    }

    function renderConfigImagePreview(configKey, value) {
        const previewNode = getConfigImagePreviewNode(configKey);
        const input = getConfigFieldInput(configKey);
        if (!previewNode) {
            return;
        }

        previewNode.innerHTML = renderConfigImagePreviewHtml(
            value,
            input ? input.dataset.configImageLabel : ''
        );
    }

    function bindConfigImageFieldEvents(groupKey) {
        const uploadDir = getConfigUploadDirectory(groupKey);

        document.querySelectorAll('[data-config-image-input]').forEach((input) => {
            input.addEventListener('input', function () {
                renderConfigImagePreview(this.dataset.configField, this.value);
            });
        });

        document.querySelectorAll('[data-config-image-preview]').forEach((previewNode) => {
            previewNode.addEventListener('click', function (event) {
                const clearBtn = event.target.closest('[data-config-image-clear]');
                if (!clearBtn) {
                    return;
                }

                updateConfigImageField(this.dataset.configImagePreview, '');
            });
        });

        document.querySelectorAll('[data-config-image-save-url]').forEach((button) => {
            button.addEventListener('click', async function () {
                const configKey = this.dataset.configImageSaveUrl;
                const input = getConfigFieldInput(configKey);
                if (!input) {
                    return;
                }

                const imageUrl = String(input.value || '').trim();
                if (!imageUrl) {
                    alert('请先输入图片链接');
                    input.focus();
                    return;
                }

                this.disabled = true;
                try {
                    const result = await saveImageByUrl(imageUrl, uploadDir, '图片链接', '图片保存失败');
                    updateConfigImageField(configKey, result.url);
                } catch (error) {
                    alert(error.message);
                } finally {
                    this.disabled = false;
                }
            });
        });

        document.querySelectorAll('[data-config-image-upload]').forEach((input) => {
            input.addEventListener('change', async function () {
                const file = this.files && this.files[0];
                if (!file) {
                    return;
                }

                try {
                    const result = await uploadImageFile(file, uploadDir, '请选择图片文件', '图片上传失败');
                    updateConfigImageField(this.dataset.configImageUpload, result.url);
                } catch (error) {
                    alert(error.message);
                } finally {
                    this.value = '';
                }
            });
        });
    }

    async function renderDisclaimer() {
        app.innerHTML = '<div class="panel"><div class="panel-title">免责声明</div><div class="empty">加载中...</div></div>';
        try {
            const data = await api('/admin/site-contents/disclaimer');
            app.innerHTML = '<div class="panel"><div class="panel-title">免责声明</div>'
                + '<div class="toolbar"><button class="btn btn-main" id="saveDisclaimerBtn">保存内容</button><button class="btn btn-warning" id="reloadDisclaimerBtn">刷新</button></div>'
                + '<div class="form-row"><label>标题</label><input id="disclaimerTitle" value="' + escapeHtmlAttr(data.title || '') + '"></div>'
                + '<div class="form-row"><label>摘要</label><textarea id="disclaimerSummary">' + escapeHtml(data.summary || '') + '</textarea></div>'
                + '<div class="form-row"><label>状态</label><select id="disclaimerStatus"><option value="1"' + (Number(data.status || 1) === 1 ? ' selected' : '') + '>启用</option><option value="0"' + (Number(data.status || 1) === 0 ? ' selected' : '') + '>停用</option></select></div>'
                + '<div class="form-row"><label>正文内容</label><textarea class="content-editor" id="disclaimerContent">' + escapeHtml(data.content || '') + '</textarea></div>'
                + '</div>';

            document.getElementById('reloadDisclaimerBtn').addEventListener('click', function () {
                renderDisclaimer();
            });
            document.getElementById('saveDisclaimerBtn').addEventListener('click', async function () {
                try {
                    await api('/admin/site-contents/disclaimer', {
                        method: 'PUT',
                        data: {
                            title: valueById('disclaimerTitle'),
                            summary: valueById('disclaimerSummary'),
                            status: valueById('disclaimerStatus'),
                            content: valueById('disclaimerContent'),
                        },
                    });
                    alert('免责声明保存成功');
                    renderDisclaimer();
                } catch (error) {
                    alert(error.message);
                }
            });
        } catch (error) {
            renderError(error, '免责声明');
        }
    }

    async function renderFaqs() {
        app.innerHTML = '<div class="panel"><div class="panel-title">常见问题</div><div class="empty">加载中...</div></div>';
        try {
            const data = await api('/admin/faqs', {
                query: {
                    keyword: state.faqFilters.keyword,
                    status: state.faqFilters.status,
                },
            });
            const list = data.list || [];

            app.innerHTML = '<div class="panel"><div class="panel-title">常见问题</div><div class="toolbar">'
                + '<button class="btn btn-main" id="addFaqBtn">新增问题</button>'
                + '<input id="faqKeyword" placeholder="搜索问题或答案" value="' + escapeHtmlAttr(state.faqFilters.keyword || '') + '">'
                + '<select id="faqStatus"><option value="">全部状态</option><option value="1"' + (state.faqFilters.status === '1' ? ' selected' : '') + '>启用</option><option value="0"' + (state.faqFilters.status === '0' ? ' selected' : '') + '>停用</option></select>'
                + '<button class="btn btn-small" id="searchFaqBtn">查询</button>'
                + '<button class="btn btn-warning" id="resetFaqBtn">重置</button>'
                + '<span class="muted">共 ' + escapeHtml(String(list.length)) + ' 条问题</span>'
                + '</div>' + renderFaqTable(list) + '</div>';

            document.getElementById('addFaqBtn').addEventListener('click', function () {
                openFaqModal();
            });
            document.getElementById('searchFaqBtn').addEventListener('click', function () {
                state.faqFilters.keyword = valueById('faqKeyword');
                state.faqFilters.status = valueById('faqStatus');
                renderFaqs();
            });
            document.getElementById('resetFaqBtn').addEventListener('click', function () {
                state.faqFilters = { keyword: '', status: '' };
                renderFaqs();
            });
            document.querySelectorAll('[data-faq-edit]').forEach((btn) => {
                btn.addEventListener('click', function () {
                    const item = list.find((row) => Number(row.id) === Number(this.dataset.faqEdit));
                    openFaqModal(item || null);
                });
            });
            document.querySelectorAll('[data-faq-delete]').forEach((btn) => {
                btn.addEventListener('click', async function () {
                    if (!confirm('确定删除这个问题吗？')) {
                        return;
                    }
                    try {
                        await api('/admin/faqs/' + Number(this.dataset.faqDelete), { method: 'DELETE' });
                        renderFaqs();
                    } catch (error) {
                        alert(error.message);
                    }
                });
            });
        } catch (error) {
            renderError(error, '常见问题');
        }
    }

    function renderFaqTable(list) {
        if (!list.length) {
            return '<div class="empty">\u6682\u65e0\u5e38\u89c1\u95ee\u9898</div>';
        }

        const rows = list.map((item) => '<tr>'
            + '<td>' + escapeHtml(String(item.id)) + '</td>'
            + '<td><strong>' + escapeHtml(item.question || '') + '</strong></td>'
            + '<td>' + escapeHtml(getTextPreview(item.answer || '', 120) || '-') + '</td>'
            + '<td>' + (item.image ? '<img src="' + escapeHtmlAttr(item.image) + '" style="width:56px;height:56px;object-fit:cover;border-radius:8px" alt="FAQ\u56fe\u7247">' : '-') + '</td>'
            + '<td>' + escapeHtml(String(item.sort || 0)) + '</td>'
            + '<td>' + renderEnabledStatusTag(item.status) + '</td>'
            + '<td>' + escapeHtml(item.updated_at || item.created_at || '-') + '</td>'
            + '<td class="actions"><button class="link-btn" data-faq-edit="' + item.id + '">\u7f16\u8f91</button><button class="link-btn" data-faq-delete="' + item.id + '">\u5220\u9664</button></td>'
            + '</tr>').join('');

        return '<table><thead><tr><th>ID</th><th>\u95ee\u9898</th><th>\u7b54\u6848\u9884\u89c8</th><th>\u56fe\u7247</th><th>\u6392\u5e8f</th><th>\u72b6\u6001</th><th>\u66f4\u65b0\u65f6\u95f4</th><th>\u64cd\u4f5c</th></tr></thead><tbody>' + rows + '</tbody></table>';
    }

    function renderFaqImagePreview(url) {
        const value = String(url || '').trim();
        return value ? '<div class="product-media-card single"><img src="' + escapeHtmlAttr(value) + '" alt="\u5e38\u89c1\u95ee\u9898\u56fe\u7247"><button type="button" class="product-media-remove" id="removeFaqImageBtn">\u79fb\u9664</button></div>' : '<div class="product-media-empty">\u6682\u65e0\u56fe\u7247</div>';
    }

    function openFaqModal(item) {
        const isEdit = !!item;
        const imageState = { url: isEdit ? String(item.image || '').trim() : '' };
        openModal(
            isEdit ? '\u7f16\u8f91\u5e38\u89c1\u95ee\u9898' : '\u65b0\u589e\u5e38\u89c1\u95ee\u9898',
            '<div class="form-row"><label>\u95ee\u9898</label><input id="faqQuestion" value="' + escapeHtmlAttr(isEdit ? (item.question || '') : '') + '" placeholder="\u8bf7\u8f93\u5165\u95ee\u9898"></div>'
            + '<div class="form-row"><label>\u7b54\u6848</label><textarea id="faqAnswer">' + escapeHtml(isEdit ? (item.answer || '') : '') + '</textarea></div>'
            + '<div class="form-row"><label>\u56fe\u7247</label><input id="faqImage" type="hidden" value="' + escapeHtmlAttr(imageState.url) + '"><div class="product-media-toolbar"><input id="faqImageUrlInput" placeholder="\u8f93\u5165\u56fe\u7247\u94fe\u63a5\u540e\u4fdd\u5b58\u5230\u672c\u5730" value=""><button type="button" class="btn btn-small" id="saveFaqImageUrlBtn">\u94fe\u63a5\u4fdd\u5b58</button><label class="btn btn-warning product-upload-label">\u4e0a\u4f20\u56fe\u7247<input id="faqImageFileInput" type="file" accept="image/*" hidden></label></div><div class="product-media-tip">\u53ef\u9009\uff0c\u4e0a\u4f20\u540e\u4f1a\u5728\u5c0f\u7a0b\u5e8f\u5e38\u89c1\u95ee\u9898\u9875\u9762\u663e\u793a\u3002</div><div class="product-media-preview single" id="faqImagePreview">' + renderFaqImagePreview(imageState.url) + '</div></div>'
            + '<div class="form-row"><label>\u6392\u5e8f</label><input id="faqSort" type="number" value="' + escapeHtmlAttr(String(isEdit ? Number(item.sort || 0) : 0)) + '"></div>'
            + '<div class="form-row"><label>\u72b6\u6001</label><select id="faqStatusModal"><option value="1"' + (!isEdit || Number(item.status) === 1 ? ' selected' : '') + '>\u542f\u7528</option><option value="0"' + (isEdit && Number(item.status) === 0 ? ' selected' : '') + '>\u505c\u7528</option></select></div>',
            [
                { text: '\u53d6\u6d88', className: 'btn btn-warning', onClick: closeModal },
                {
                    text: isEdit ? '\u4fdd\u5b58' : '\u521b\u5efa',
                    className: 'btn btn-main',
                    onClick: async function () {
                        const payload = {
                            question: valueById('faqQuestion'),
                            answer: valueById('faqAnswer'),
                            image: valueById('faqImage'),
                            sort: valueById('faqSort'),
                            status: valueById('faqStatusModal'),
                        };
                        try {
                            if (isEdit) {
                                await api('/admin/faqs/' + Number(item.id), { method: 'PUT', data: payload });
                            } else {
                                await api('/admin/faqs', { method: 'POST', data: payload });
                            }
                            closeModal();
                            renderFaqs();
                        } catch (error) {
                            alert(error.message);
                        }
                    },
                },
            ]
        );

        function updateFaqImage(url) {
            imageState.url = String(url || '').trim();
            document.getElementById('faqImage').value = imageState.url;
            document.getElementById('faqImagePreview').innerHTML = renderFaqImagePreview(imageState.url);
            const removeBtn = document.getElementById('removeFaqImageBtn');
            if (removeBtn) {
                removeBtn.addEventListener('click', function () {
                    updateFaqImage('');
                });
            }
        }

        updateFaqImage(imageState.url);
        document.getElementById('saveFaqImageUrlBtn').addEventListener('click', async function () {
            try {
                const result = await saveImageByUrl(valueById('faqImageUrlInput'), 'faqs', '\u56fe\u7247\u94fe\u63a5', '\u56fe\u7247\u4fdd\u5b58\u5931\u8d25');
                updateFaqImage(result.url);
            } catch (error) {
                alert(error.message);
            }
        });
        document.getElementById('faqImageFileInput').addEventListener('change', async function () {
            const file = this.files && this.files[0];
            if (!file) {
                return;
            }
            try {
                const result = await uploadImageFile(file, 'faqs', '\u8bf7\u9009\u62e9\u56fe\u7247\u6587\u4ef6', '\u56fe\u7247\u4e0a\u4f20\u5931\u8d25');
                updateFaqImage(result.url);
            } catch (error) {
                alert(error.message);
            } finally {
                this.value = '';
            }
        });
    }

    async function renderProductSearchLogs() {
        const filters = state.productSearchLogFilters;
        app.innerHTML = '<div class="panel"><div class="panel-title">\u641c\u7d22\u6570\u636e\u7ba1\u7406</div><div class="empty">\u52a0\u8f7d\u4e2d...</div></div>';
        try {
            const data = await api('/admin/product-search-logs', {
                query: {
                    page: 1,
                    limit: 200,
                    keyword: filters.keyword,
                    user_keyword: filters.userKeyword,
                    visitor_id: filters.visitorId,
                    start_date: filters.startDate,
                    end_date: filters.endDate,
                    period: filters.period,
                },
            });
            const list = data.list || [];
            const summary = data.summary || {};
            const pagination = data.pagination || {};
            const analysis = data.analysis || {};
            app.innerHTML = '<div class="panel"><div class="panel-title">\u641c\u7d22\u6570\u636e\u7ba1\u7406</div>'
                + '<div class="stats-grid">'
                + statCard('\u641c\u7d22\u603b\u6b21\u6570', summary.total || 0)
                + statCard('\u4eca\u65e5\u641c\u7d22', summary.today_total || 0)
                + statCard('\u767b\u5f55\u7528\u6237\u641c\u7d22', summary.user_total || 0)
                + statCard('\u6e38\u5ba2\u641c\u7d22', summary.guest_total || 0)
                + '</div></div>'
                + renderProductSearchAnalysis(analysis, filters.period)
                + '<div class="panel"><div class="panel-title">\u641c\u7d22\u8bcd\u8bb0\u5f55</div><div class="toolbar">'
                + '<input id="searchLogKeyword" placeholder="\u641c\u7d22\u8bcd" value="' + escapeHtmlAttr(filters.keyword || '') + '">'
                + '<input id="searchLogUserKeyword" placeholder="\u7528\u6237\u540d/\u6635\u79f0" value="' + escapeHtmlAttr(filters.userKeyword || '') + '">'
                + '<input id="searchLogVisitorId" placeholder="\u6e38\u5ba2ID" value="' + escapeHtmlAttr(filters.visitorId || '') + '">'
                + '<input id="searchLogStartDate" type="date" value="' + escapeHtmlAttr(filters.startDate || '') + '">'
                + '<input id="searchLogEndDate" type="date" value="' + escapeHtmlAttr(filters.endDate || '') + '">'
                + '<button class="btn btn-small" id="searchLogSearchBtn">\u67e5\u8be2</button>'
                + '<button class="btn btn-warning" id="searchLogResetBtn">\u91cd\u7f6e</button>'
                + '<span class="muted">\u5171 ' + escapeHtml(String(pagination.total || list.length)) + ' \u6761</span>'
                + '</div>' + renderProductSearchLogTable(list) + '</div>';

            document.querySelectorAll('[data-search-analysis-period]').forEach((btn) => {
                btn.addEventListener('click', function () {
                    state.productSearchLogFilters.period = this.dataset.searchAnalysisPeriod || 'day';
                    renderProductSearchLogs();
                });
            });
            document.getElementById('searchLogSearchBtn').addEventListener('click', function () {
                state.productSearchLogFilters.keyword = valueById('searchLogKeyword');
                state.productSearchLogFilters.userKeyword = valueById('searchLogUserKeyword');
                state.productSearchLogFilters.visitorId = valueById('searchLogVisitorId');
                state.productSearchLogFilters.startDate = valueById('searchLogStartDate');
                state.productSearchLogFilters.endDate = valueById('searchLogEndDate');
                renderProductSearchLogs();
            });
            document.getElementById('searchLogResetBtn').addEventListener('click', function () {
                state.productSearchLogFilters = { keyword: '', userKeyword: '', visitorId: '', startDate: '', endDate: '', period: filters.period || 'day' };
                renderProductSearchLogs();
            });
        } catch (error) {
            renderError(error, '\u641c\u7d22\u6570\u636e\u7ba1\u7406');
        }
    }

    function renderProductSearchAnalysis(analysis, activePeriod) {
        const period = activePeriod || analysis.period || 'day';
        return '<div class="panel"><div class="panel-title">\u641c\u7d22\u7edf\u8ba1\u5206\u6790</div>'
            + '<div class="toolbar">'
            + searchAnalysisPeriodButton(period, 'day', '\u6309\u5929')
            + searchAnalysisPeriodButton(period, 'week', '\u6309\u661f\u671f')
            + searchAnalysisPeriodButton(period, 'month', '\u6309\u6708')
            + searchAnalysisPeriodButton(period, 'quarter', '\u6309\u5b63\u5ea6')
            + searchAnalysisPeriodButton(period, 'year', '\u6309\u5e74')
            + searchAnalysisPeriodButton(period, 'all', '\u6240\u6709\u65f6\u95f4')
            + '<span class="muted">' + escapeHtml((analysis.start_date || '-') + ' ~ ' + (analysis.end_date || '-')) + '</span>'
            + '</div>'
            + '<div class="search-analysis-grid">'
            + renderHotKeywordTable('\u6700\u591a\u641c\u7d22\u8bcd', analysis.hot_keywords || [], false)
            + renderHotKeywordTable('\u65e0\u7ed3\u679c\u9ad8\u9891\u8bcd', analysis.zero_result_keywords || [], true)
            + renderHotProductTable('\u641c\u7d22\u547d\u4e2d\u5546\u54c1\u6392\u884c', analysis.hot_products || [])
            + '</div></div>';
    }

    function searchAnalysisPeriodButton(active, value, label) {
        return '<button class="btn ' + (active === value ? 'btn-main' : 'btn-small') + '" data-search-analysis-period="' + escapeHtmlAttr(value) + '">' + label + '</button>';
    }

    function renderHotKeywordTable(title, list, zeroOnly) {
        if (!list.length) {
            return '<div class="mini-panel"><h3>' + title + '</h3><div class="empty">' + (zeroOnly ? '\u6682\u65e0\u65e0\u7ed3\u679c\u641c\u7d22' : '\u6682\u65e0\u641c\u7d22\u8bcd\u6570\u636e') + '</div></div>';
        }
        const rows = list.map((item, index) => '<tr>'
            + '<td>#' + escapeHtml(String(index + 1)) + '</td>'
            + '<td><strong>' + escapeHtml(item.keyword || '-') + '</strong></td>'
            + '<td>' + escapeHtml(String(item.search_count || 0)) + '</td>'
            + '<td>' + escapeHtml(String(item.result_total || 0)) + '</td>'
            + '<td>' + escapeHtml(String(item.avg_result_count || 0)) + '</td>'
            + '</tr>').join('');
        return '<div class="mini-panel"><h3>' + title + '</h3><table class="compact-table"><thead><tr><th>\u6392\u540d</th><th>\u641c\u7d22\u8bcd</th><th>\u641c\u7d22\u6b21\u6570</th><th>\u603b\u547d\u4e2d</th><th>\u5e73\u5747\u547d\u4e2d</th></tr></thead><tbody>' + rows + '</tbody></table></div>';
    }

    function renderHotProductTable(title, list) {
        if (!list.length) {
            return '<div class="mini-panel"><h3>' + title + '</h3><div class="empty">\u6682\u65e0\u547d\u4e2d\u5546\u54c1\u6570\u636e</div></div>';
        }
        const rows = list.map((item, index) => '<tr>'
            + '<td>#' + escapeHtml(String(index + 1)) + '</td>'
            + '<td>' + escapeHtml(String(item.product_id || 0)) + '</td>'
            + '<td><strong>' + escapeHtml(item.product_name || '-') + '</strong><div class="muted">' + escapeHtml(item.product_name_en || '-') + '</div></td>'
            + '<td>' + escapeHtml(String(item.hit_count || 0)) + '</td>'
            + '<td>' + escapeHtml(String(item.search_count || 0)) + '</td>'
            + '</tr>').join('');
        return '<div class="mini-panel"><h3>' + title + '</h3><table class="compact-table"><thead><tr><th>\u6392\u540d</th><th>ID</th><th>\u5546\u54c1</th><th>\u547d\u4e2d\u6b21\u6570</th><th>\u641c\u7d22\u6b21\u6570</th></tr></thead><tbody>' + rows + '</tbody></table></div>';
    }


    function formatDeviceType(type) {
        const map = {
            desktop: '\u7535\u8111',
            mobile: '\u79fb\u52a8\u8bbe\u5907',
            tablet: '\u5e73\u677f',
            miniapp: '\u5c0f\u7a0b\u5e8f',
            miniapp_mobile: '\u5c0f\u7a0b\u5e8f/\u79fb\u52a8\u8bbe\u5907',
            unknown: '\u672a\u77e5',
        };
        return map[type] || map.unknown;
    }

    function renderProductSearchLogTable(list) {
        if (!list.length) {
            return '<div class="empty">\u6682\u65e0\u641c\u7d22\u8bb0\u5f55</div>';
        }
        const rows = list.map((item) => '<tr>'
            + '<td>' + escapeHtml(String(item.id || 0)) + '</td>'
            + '<td><strong>' + escapeHtml(item.keyword || '-') + '</strong></td>'
            + '<td>' + escapeHtml(item.user_id && Number(item.user_id) > 0 ? String(item.user_id) : '\u6e38\u5ba2') + '</td>'
            + '<td>' + escapeHtml(item.username || item.nickname || '-') + '</td>'
            + '<td><span class="muted">' + escapeHtml(item.visitor_id || '-') + '</span></td>'
            + '<td>' + escapeHtml(String(item.result_count || 0)) + '</td>'
            + '<td>' + escapeHtml(item.ip || '-') + '</td>'
            + '<td><span class="tag tag-blue">' + escapeHtml(formatDeviceType(item.device_type || 'unknown')) + '</span></td>'
            + '<td><span class="muted">' + escapeHtml(item.user_agent || '-') + '</span></td>'
            + '<td>' + escapeHtml(item.created_at || '-') + '</td>'
            + '</tr>').join('');
        return '<div class="table-scroll search-log-table-wrap"><table class="search-log-table"><thead><tr><th>ID</th><th>\u641c\u7d22\u8bcd</th><th>\u7528\u6237ID</th><th>\u7528\u6237</th><th>\u6e38\u5ba2ID</th><th>\u547d\u4e2d\u6570</th><th>IP</th><th>\u8bbe\u5907\u7c7b\u578b</th><th>\u8bbe\u5907\u73af\u5883</th><th>\u641c\u7d22\u65f6\u95f4</th></tr></thead><tbody>' + rows + '</tbody></table></div>';
    }

    async function renderDataStatistics() {
        const filters = state.dataStatisticFilters;
        app.innerHTML = '<div class="panel"><div class="panel-title">\u6570\u636e\u7edf\u8ba1</div><div class="empty">\u52a0\u8f7d\u4e2d...</div></div>';
        try {
            const data = await api('/admin/data-statistics/rankings', {
                query: {
                    keyword: filters.keyword,
                    period: filters.period,
                    limit: filters.limit,
                },
            });
            const summary = data.summary || {};
            const activeTab = ['click', 'exchange', 'search'].indexOf(filters.tab) >= 0 ? filters.tab : 'click';
            const rankingMap = {
                click: data.click_ranking || {},
                exchange: data.exchange_ranking || {},
                search: data.search_ranking || {},
            };
            const activeRanking = rankingMap[activeTab] || {};

            app.innerHTML = '<div class="panel"><div class="panel-title">\u6570\u636e\u7edf\u8ba1</div>'
                + '<div class="toolbar">'
                + '<button class="btn ' + (activeTab === 'click' ? 'btn-main' : 'btn-small') + '" data-stat-tab="click">\u70b9\u51fb\u6570\u636e</button>'
                + '<button class="btn ' + (activeTab === 'exchange' ? 'btn-main' : 'btn-small') + '" data-stat-tab="exchange">\u5151\u6362\u6570\u636e</button>'
                + '<button class="btn ' + (activeTab === 'search' ? 'btn-main' : 'btn-small') + '" data-stat-tab="search">\u641c\u7d22\u6570\u636e</button>'
                + '<button class="btn ' + (filters.period === 'day' ? 'btn-main' : 'btn-small') + '" data-stat-period="day">\u6309\u5929</button>'
                + '<button class="btn ' + (filters.period === 'week' ? 'btn-main' : 'btn-small') + '" data-stat-period="week">\u6309\u661f\u671f</button>'
                + '<button class="btn ' + (filters.period === 'month' ? 'btn-main' : 'btn-small') + '" data-stat-period="month">\u6309\u6708</button>'
                + '<button class="btn ' + (filters.period === 'quarter' ? 'btn-main' : 'btn-small') + '" data-stat-period="quarter">\u6309\u5b63\u5ea6</button>'
                + '<button class="btn ' + (filters.period === 'year' ? 'btn-main' : 'btn-small') + '" data-stat-period="year">\u6309\u5e74</button>'
                + '<button class="btn ' + (filters.period === 'all' ? 'btn-main' : 'btn-small') + '" data-stat-period="all">\u6240\u6709\u65f6\u95f4</button>'
                + '<input id="statKeyword" placeholder="\u641c\u7d22\u5546\u54c1\u4e2d\u6587\u540d\u6216\u82f1\u6587\u540d" value="' + escapeHtmlAttr(filters.keyword || '') + '">'
                + '<select id="statLimit"><option value="10"' + (String(filters.limit) === '10' ? ' selected' : '') + '>Top 10</option><option value="20"' + (String(filters.limit) === '20' ? ' selected' : '') + '>Top 20</option><option value="50"' + (String(filters.limit) === '50' ? ' selected' : '') + '>Top 50</option><option value="100"' + (String(filters.limit) === '100' ? ' selected' : '') + '>Top 100</option></select>'
                + '<button class="btn btn-small" id="searchStatisticsBtn">\u67e5\u8be2</button>'
                + '<button class="btn btn-warning" id="resetStatisticsBtn">\u91cd\u7f6e</button>'
                + '</div>'
                + '<div class="stats-grid">'
                + statCard('\u7edf\u8ba1\u5546\u54c1', summary.product_count || 0)
                + statCard('\u70b9\u51fb\u5408\u8ba1', summary.total_click_count || 0)
                + statCard('\u5151\u6362\u5408\u8ba1', summary.total_exchange_count || 0)
                + statCard('\u641c\u7d22\u5408\u8ba1', summary.total_search_count || 0)
                + '</div>'
                + '<div class="muted" style="margin:10px 0 14px">' + escapeHtml((activeRanking.start_date || '-') + ' ~ ' + (activeRanking.end_date || '-')) + '</div>'
                + renderStatisticRankingTable(activeRanking, activeTab)
                + '</div>';

            document.querySelectorAll('[data-stat-tab]').forEach((btn) => {
                btn.addEventListener('click', function () {
                    state.dataStatisticFilters.tab = this.dataset.statTab || 'click';
                    renderDataStatistics();
                });
            });
            document.querySelectorAll('[data-stat-period]').forEach((btn) => {
                btn.addEventListener('click', function () {
                    state.dataStatisticFilters.period = this.dataset.statPeriod || 'day';
                    renderDataStatistics();
                });
            });
            document.getElementById('searchStatisticsBtn').addEventListener('click', function () {
                state.dataStatisticFilters.keyword = valueById('statKeyword');
                state.dataStatisticFilters.limit = valueById('statLimit') || '20';
                renderDataStatistics();
            });
            document.getElementById('resetStatisticsBtn').addEventListener('click', function () {
                state.dataStatisticFilters = { keyword: '', period: 'day', limit: '20', tab: activeTab };
                renderDataStatistics();
            });
        } catch (error) {
            app.innerHTML = '<div class="panel"><div class="panel-title">\u6570\u636e\u7edf\u8ba1</div><div class="empty">' + escapeHtml(error.message) + '</div></div>';
        }
    }

    function renderStatisticRankingTable(ranking, type) {
        if (!ranking.enabled) {
            return '<div class="empty">\u8bf7\u5148\u6267\u884c database/product_metric_daily_init.sql \u521b\u5efa\u6bcf\u65e5\u7edf\u8ba1\u8868</div>';
        }
        const list = ranking.list || [];
        if (!list.length) {
            return '<div class="empty">\u6682\u65e0\u6392\u884c\u6570\u636e</div>';
        }
        const valueTitleMap = {
            exchange: '\u5151\u6362\u6b21\u6570',
            search: '\u641c\u7d22\u547d\u4e2d\u6b21\u6570',
            click: '\u70b9\u51fb\u6570',
        };
        const valueTitle = valueTitleMap[type] || valueTitleMap.click;
        const rows = list.map((item) => '<tr>'
            + '<td><strong>#' + escapeHtml(String(item.rank || 0)) + '</strong></td>'
            + '<td>' + escapeHtml(String(item.product_id || 0)) + '</td>'
            + '<td><strong>' + escapeHtml(item.product_name || '-') + '</strong></td>'
            + '<td>' + escapeHtml(item.product_name_en || '-') + '</td>'
            + '<td><strong>' + escapeHtml(String(item.value || 0)) + '</strong></td>'
            + '<td>' + escapeHtml(String(item.click_count || 0)) + '</td>'
            + '<td>' + escapeHtml(String(item.exchange_count || 0)) + '</td>'
            + '<td>' + escapeHtml(String(item.search_count || 0)) + '</td>'
            + '<td>' + escapeHtml(item.updated_at || '-') + '</td>'
            + '</tr>').join('');
        const productTitle = type === 'exchange' ? '\u5546\u54c1\u540d\u79f0' : '\u4e2d\u6587\u540d';
        return '<table><thead><tr><th>\u6392\u540d</th><th>\u5546\u54c1ID</th><th>' + productTitle + '</th><th>\u82f1\u6587\u540d</th><th>' + valueTitle + '</th><th>\u70b9\u51fb\u6570</th><th>\u5151\u6362\u6b21\u6570</th><th>\u641c\u7d22\u6b21\u6570</th><th>\u66f4\u65b0\u65f6\u95f4</th></tr></thead><tbody>' + rows + '</tbody></table>';
    }

    async function renderAnnouncements() {
        app.innerHTML = '<div class="panel"><div class="panel-title">公告管理</div><div class="empty">加载中...</div></div>';
        try {
            const data = await api('/admin/announcements', {
                query: {
                    keyword: state.announcementFilters.keyword,
                    status: state.announcementFilters.status,
                },
            });
            const list = data.list || [];

            app.innerHTML = '<div class="panel"><div class="panel-title">公告管理</div><div class="toolbar">'
                + '<button class="btn btn-main" id="addAnnouncementBtn">新增公告</button>'
                + '<input id="announcementKeyword" placeholder="搜索公告标题或内容" value="' + escapeHtmlAttr(state.announcementFilters.keyword || '') + '">'
                + '<select id="announcementStatus"><option value="">全部状态</option><option value="1"' + (state.announcementFilters.status === '1' ? ' selected' : '') + '>启用</option><option value="0"' + (state.announcementFilters.status === '0' ? ' selected' : '') + '>停用</option></select>'
                + '<button class="btn btn-small" id="searchAnnouncementBtn">查询</button>'
                + '<button class="btn btn-warning" id="resetAnnouncementBtn">重置</button>'
                + '<span class="muted">共 ' + escapeHtml(String(list.length)) + ' 条公告</span>'
                + '</div>' + renderAnnouncementTable(list) + '</div>';

            document.getElementById('addAnnouncementBtn').addEventListener('click', function () {
                openAnnouncementModal();
            });
            document.getElementById('searchAnnouncementBtn').addEventListener('click', function () {
                state.announcementFilters.keyword = valueById('announcementKeyword');
                state.announcementFilters.status = valueById('announcementStatus');
                renderAnnouncements();
            });
            document.getElementById('resetAnnouncementBtn').addEventListener('click', function () {
                state.announcementFilters = { keyword: '', status: '' };
                renderAnnouncements();
            });
            document.querySelectorAll('[data-announcement-edit]').forEach((btn) => {
                btn.addEventListener('click', function () {
                    const item = list.find((row) => Number(row.id) === Number(this.dataset.announcementEdit));
                    openAnnouncementModal(item || null);
                });
            });
            document.querySelectorAll('[data-announcement-delete]').forEach((btn) => {
                btn.addEventListener('click', async function () {
                    if (!confirm('确定删除这条公告吗？')) {
                        return;
                    }
                    try {
                        await api('/admin/announcements/' + Number(this.dataset.announcementDelete), { method: 'DELETE' });
                        renderAnnouncements();
                    } catch (error) {
                        alert(error.message);
                    }
                });
            });
        } catch (error) {
            renderError(error, '公告管理');
        }
    }

    function renderAnnouncementTable(list) {
        if (!list.length) {
            return '<div class="empty">暂无公告数据</div>';
        }

        const rows = list.map((item) => '<tr>'
            + '<td>' + escapeHtml(String(item.id)) + '</td>'
            + '<td><strong>' + escapeHtml(item.title || '') + '</strong></td>'
            + '<td>' + escapeHtml(getTextPreview(item.summary || item.content || '', 80) || '-') + '</td>'
            + '<td>' + escapeHtml(String(item.sort || 0)) + '</td>'
            + '<td>' + renderEnabledStatusTag(item.status) + '</td>'
            + '<td>' + escapeHtml(item.updated_at || item.created_at || '-') + '</td>'
            + '<td class="actions"><button class="link-btn" data-announcement-edit="' + item.id + '">编辑</button><button class="link-btn" data-announcement-delete="' + item.id + '">删除</button></td>'
            + '</tr>').join('');

        return '<table><thead><tr><th>ID</th><th>公告标题</th><th>摘要预览</th><th>排序</th><th>状态</th><th>更新时间</th><th>操作</th></tr></thead><tbody>' + rows + '</tbody></table>';
    }

    function openAnnouncementModal(item) {
        const isEdit = !!item;
        openModal(
            isEdit ? '编辑公告' : '新增公告',
            '<div class="form-row"><label>公告标题</label><input id="announcementTitle" value="' + escapeHtmlAttr(isEdit ? (item.title || '') : '') + '" placeholder="请输入公告标题"></div>'
            + '<div class="form-row"><label>公告摘要</label><textarea id="announcementSummary">' + escapeHtml(isEdit ? (item.summary || '') : '') + '</textarea></div>'
            + '<div class="form-row"><label>公告内容</label><textarea class="content-editor" id="announcementContent">' + escapeHtml(isEdit ? (item.content || '') : '') + '</textarea></div>'
            + '<div class="form-row"><label>排序</label><input id="announcementSort" type="number" value="' + escapeHtmlAttr(String(isEdit ? Number(item.sort || 0) : 0)) + '"></div>'
            + '<div class="form-row"><label>状态</label><select id="announcementStatusModal"><option value="1"' + (!isEdit || Number(item.status) === 1 ? ' selected' : '') + '>启用</option><option value="0"' + (isEdit && Number(item.status) === 0 ? ' selected' : '') + '>停用</option></select></div>',
            [
                { text: '取消', className: 'btn btn-warning', onClick: closeModal },
                {
                    text: isEdit ? '保存' : '创建',
                    className: 'btn btn-main',
                    onClick: async function () {
                        const payload = {
                            title: valueById('announcementTitle'),
                            summary: valueById('announcementSummary'),
                            content: valueById('announcementContent'),
                            sort: valueById('announcementSort'),
                            status: valueById('announcementStatusModal'),
                        };
                        try {
                            if (isEdit) {
                                await api('/admin/announcements/' + Number(item.id), { method: 'PUT', data: payload });
                            } else {
                                await api('/admin/announcements', { method: 'POST', data: payload });
                            }
                            closeModal();
                            renderAnnouncements();
                        } catch (error) {
                            alert(error.message);
                        }
                    },
                },
            ]
        );
    }

    function openModal(title, bodyHtml, buttons) {
        closeCategoryPopover();
        modalTitle.textContent = title;
        modalBody.innerHTML = bodyHtml;
        modalFoot.innerHTML = '';
        buttons.forEach((buttonConfig) => {
            const btn = document.createElement('button');
            btn.className = buttonConfig.className || 'btn btn-small';
            btn.textContent = buttonConfig.text;
            btn.addEventListener('click', buttonConfig.onClick);
            modalFoot.appendChild(btn);
        });
        modalMask.classList.add('show');
    }

    function closeModal() {
        closeCategoryPopover();
        modalMask.classList.remove('show');
        modalBody.innerHTML = '';
        modalFoot.innerHTML = '';
    }

    function logout() {
        localStorage.removeItem('admin_token');
        localStorage.removeItem('admin_user');
        window.location.href = '/login.html';
    }

    function valueById(id) {
        const el = document.getElementById(id);
        return el ? el.value.trim() : '';
    }

    function statCard(label, value) {
        return '<div class="stat-card"><div class="stat-label">' + escapeHtml(label) + '</div><div class="stat-value">' + escapeHtml(String(value)) + '</div></div>';
    }

    function renderSidebar() {
        const sidebar = document.querySelector('.sidebar');
        if (!sidebar) {
            return;
        }

        sidebar.innerHTML = sidebarMenuSchema.map((item) => {
            if (item.type === 'group') {
                return '<div class="menu-group" data-menu-group="' + escapeHtmlAttr(item.key) + '">'
                    + '<button class="menu-item menu-parent" type="button" data-menu-toggle="' + escapeHtmlAttr(item.key) + '">'
                    + '<span>' + escapeHtml(item.label) + '</span>'
                    + '<span class="menu-arrow"></span>'
                    + '</button>'
                    + '<div class="menu-submenu">'
                    + item.children.map((child) => '<button class="menu-subitem menu-link" type="button" data-view="' + escapeHtmlAttr(child.view) + '" data-nav-key="' + escapeHtmlAttr(child.navKey) + '" data-nav-url="' + escapeHtmlAttr(child.url) + '">' + escapeHtml(child.label) + '</button>').join('')
                    + '</div>'
                    + '</div>';
            }

            return '<button class="menu-item menu-link" type="button" data-view="' + escapeHtmlAttr(item.view) + '" data-nav-key="' + escapeHtmlAttr(item.navKey) + '" data-nav-url="' + escapeHtmlAttr(item.url) + '">' + escapeHtml(item.label) + '</button>';
        }).join('');
    }

    function energy(value) {
        return String(Math.max(0, Math.round(Number(value || 0))));
    }

    function normalizeCategoryGroup(groupKey) {
        return String(groupKey || '').toLowerCase() === 'kind' ? 'kind' : 'type';
    }

    function normalizeCardModule(moduleKey) {
        const current = String(moduleKey || '').toLowerCase();
        if (current === 'download' || current === 'tutorial') {
            return current;
        }
        return 'account';
    }

    function normalizeConfigGroup(groupKey) {
        const current = String(groupKey || '').toLowerCase();
        if (configGroupMetaMap[current]) {
            return current;
        }
        return 'miniapp';
    }

    function getCurrentCategoryGroup() {
        return categoryGroupMetaMap[state.categoryGroup] || categoryGroupMetaMap.type;
    }

    function getCurrentConfigGroup() {
        return configGroupMetaMap[state.configGroup] || configGroupMetaMap.miniapp;
    }

    function getTextPreview(html, limit) {
        const container = document.createElement('div');
        container.innerHTML = String(html || '');
        const text = (container.textContent || '').replace(/\s+/g, ' ').trim();
        const size = Number(limit || 60);
        if (text.length <= size) {
            return text;
        }
        return text.slice(0, size) + '...';
    }

    function safeJsonParse(text) {
        try { return JSON.parse(text); } catch (error) { return null; }
    }

    function escapeHtml(str) {
        return String(str).replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;').replace(/'/g, '&#39;');
    }

    function escapeHtmlAttr(str) {
        return escapeHtml(str).replace(/`/g, '&#96;');
    }

    function findCategoryById(id, tree) {
        for (let index = 0; index < tree.length; index += 1) {
            const node = tree[index];
            if (Number(node.id) === Number(id)) {
                return node;
            }

            const found = findCategoryById(id, node.children || []);
            if (found) {
                return found;
            }
        }

        return null;
    }

    function collectDescendantIds(id, tree) {
        const root = findCategoryById(id, tree);
        if (!root) {
            return [];
        }

        const result = [];
        const queue = [...(root.children || [])];

        while (queue.length > 0) {
            const current = queue.shift();
            result.push(Number(current.id));
            (current.children || []).forEach((child) => queue.push(child));
        }

        return result;
    }

    function renderEnabledStatusTag(status) {
        return Number(status) === 1
            ? '<span class="tag tag-green">启用</span>'
            : '<span class="tag tag-red">停用</span>';
    }

    function renderEnergyChangeTypeTag(changeType) {
        const current = String(changeType || '');
        if (current === 'manual_add') {
            return '<span class="tag tag-green">手动增加</span>';
        }
        if (current === 'manual_subtract') {
            return '<span class="tag tag-red">手动扣减</span>';
        }
        if (current === 'acquire') {
            return '<span class="tag tag-blue">获取奖励</span>';
        }
        if (current === 'consume') {
            return '<span class="tag tag-yellow">消费扣除</span>';
        }
        if (current === 'refund') {
            return '<span class="tag tag-gray">退回返还</span>';
        }
        return '<span class="tag tag-gray">' + escapeHtml(current || '-') + '</span>';
    }

    function cardStatusTag(status) {
        const current = String(status || '');

        if (current === 'unused') {
            return '<span class="tag tag-green">可用</span>';
        }
        if (current === 'locked') {
            return '<span class="tag tag-yellow">锁定</span>';
        }
        if (current === 'sold') {
            return '<span class="tag tag-blue">已售</span>';
        }

        return '<span class="tag tag-red">作废</span>';
    }

    function adminText(path, replacements) {
        if (window.AdminLang && typeof window.AdminLang.t === 'function') {
            return window.AdminLang.t(path, replacements || {});
        }
        const fallback = {
            'order.title': 'Order Management',
            'order.loading': 'Loading...',
            'order.createOrder': 'Create Order',
            'order.totalOrders': '{count} orders',
            'order.empty': 'No orders',
            'order.id': 'ID',
            'order.orderNo': 'Order No.',
            'order.product': 'Product',
            'order.quantity': 'Quantity',
            'order.energy': 'Energy',
            'order.status': 'Status',
            'order.buyer': 'Buyer',
            'order.buyerInfo': 'Buyer Info',
            'order.userId': 'User ID',
            'order.noUser': 'No user info',
            'order.expiresAt': 'Expires At',
            'order.createdAt': 'Created At',
            'order.payTime': 'Pay Time',
            'order.deliverTime': 'Deliver Time',
            'order.deliveryContent': 'Delivery Content',
            'order.noContent': 'No content',
            'order.actions': 'Actions',
            'order.detail': 'Detail',
            'order.detailTitle': 'Order Detail',
            'order.edit': 'Edit',
            'order.editOrder': 'Edit Order',
            'order.setPaid': 'Set Paid',
            'order.setExpired': 'Set Expired',
            'order.deliver': 'Deliver',
            'order.delete': 'Delete',
            'order.close': 'Close',
            'order.confirmDeliver': 'Deliver this order?',
            'order.confirmDelete': 'Delete this order?',
            'order.productRequired': 'Please create a product first',
            'order.buyerEmail': 'Buyer Email',
            'order.contact': 'Contact',
            'order.remark': 'Remark',
            'order.optional': 'Optional',
            'order.cancel': 'Cancel',
            'order.save': 'Save',
            'order.create': 'Create',
            'order.modalCreate': 'Create Order',
            'order.modalEdit': 'Edit Order',
            'order.remaining': '{hours}h {minutes}m left',
            'order.expiredSuffix': 'Expired',
            'order.statusPending': 'Pending',
            'order.statusPaid': 'Paid',
            'order.statusDelivered': 'Delivered',
            'order.statusRefunded': 'Refunded',
            'order.statusExpired': 'Expired',
            'order.statusCancelled': 'Cancelled'
        };
        const template = fallback[path] || path;
        return String(template).replace(/\{([a-zA-Z0-9_]+)\}/g, function (_, key) {
            return replacements && replacements[key] !== undefined ? String(replacements[key]) : '';
        });
    }

    function orderStatusTag(status) {
        const current = String(status || '');

        if (current === 'pending') {
            return '<span class="tag tag-yellow">' + escapeHtml(adminText('order.statusPending')) + '</span>';
        }
        if (current === 'paid') {
            return '<span class="tag tag-blue">' + escapeHtml(adminText('order.statusPaid')) + '</span>';
        }
        if (current === 'delivered') {
            return '<span class="tag tag-green">' + escapeHtml(adminText('order.statusDelivered')) + '</span>';
        }
        if (current === 'refunded') {
            return '<span class="tag tag-gray">' + escapeHtml(adminText('order.statusRefunded')) + '</span>';
        }
        if (current === 'expired') {
            return '<span class="tag tag-red">' + escapeHtml(adminText('order.statusExpired')) + '</span>';
        }

        return '<span class="tag tag-red">' + escapeHtml(adminText('order.statusCancelled')) + '</span>';
    }
})();
