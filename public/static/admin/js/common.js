/**
 * 凯力在线学习系统 - 管理后台公共模块
 * 包含：API请求、弹窗、分页、工具函数
 */

const API_BASE = '/admin';
const token = localStorage.getItem('admin_token');
const adminUser = JSON.parse(localStorage.getItem('admin_user') || '{}');

// 未登录跳转
if (!token) { window.location.href = '/login.html'; }

// 显示管理员名称
document.getElementById('adminName').textContent = adminUser.real_name || adminUser.username || '';

/**
 * API 请求封装
 */
async function api(url, options = {}) {
    const headers = { 'Content-Type': 'application/json', 'Authorization': 'Bearer ' + token, ...options.headers };
    const res = await fetch(API_BASE + url, { ...options, headers });
    if (res.status === 401) { localStorage.clear(); window.location.href = '/login.html'; return; }
    return await res.json();
}

/**
 * 退出登录
 */
function logout() {
    localStorage.clear();
    window.location.href = '/login.html';
}

// ==================== 侧边栏 ====================

/**
 * 侧边栏菜单折叠切换
 */
function toggleMenu(titleEl) {
    const arrow = titleEl.querySelector('.arrow');
    const sub = titleEl.nextElementSibling;
    arrow.classList.toggle('open');
    sub.classList.toggle('open');
}

/**
 * 侧边栏子菜单点击事件
 */
document.querySelectorAll('.menu-item').forEach(el => {
    el.addEventListener('click', function () {
        const route = this.dataset.route;
        if (route) navigateTo(route);
    });
});

// ==================== 弹窗 ====================

/**
 * 打开弹窗
 * @param {string} title - 弹窗标题
 * @param {string} bodyHTML - 弹窗内容HTML
 * @param {string} footerHTML - 弹窗底部HTML
 * @param {boolean} wide - 是否宽屏模式
 */
function openModal(title, bodyHTML, footerHTML, wide) {
    document.getElementById('modalTitle').textContent = title;
    document.getElementById('modalBody').innerHTML = bodyHTML;
    document.getElementById('modalFooter').innerHTML = footerHTML || '';
    const dlg = document.getElementById('modalDialog');
    if (wide) { dlg.classList.add('modal-wide'); } else { dlg.classList.remove('modal-wide'); }
    document.getElementById('modalOverlay').classList.add('show');
}

/**
 * 关闭弹窗
 */
function closeModal() {
    document.getElementById('modalOverlay').classList.remove('show');
    document.getElementById('modalDialog').classList.remove('modal-wide');
}

// ==================== 分页 ====================

/**
 * 生成分页HTML
 */
function paginationHTML(total, page, limit, fn) {
    const pages = Math.ceil(total / limit) || 1;
    let html = `<div class="pagination"><span>共 ${total} 条</span><div class="page-btns">`;
    for (let i = 1; i <= Math.min(pages, 10); i++) {
        html += `<span class="page-btn ${i === page ? 'active' : ''}" onclick="${fn}(${i})">${i}</span>`;
    }
    html += '</div></div>';
    return html;
}

// ==================== 通用列表加载 ====================

/**
 * 通用列表加载函数
 */
async function loadGenericList(title, apiUrl, columns, page = 1) {
    const main = document.getElementById('mainContent');
    main.innerHTML = '<div class="loading">加载中...</div>';
    const data = await api(`${apiUrl}${apiUrl.includes('?') ? '&' : '?'}page=${page}&limit=15`);
    const list = data?.data || [];
    const total = data?.count || 0;

    main.innerHTML = `
        <div class="card">
            <div class="card-title">${title}</div>
            <table>
                <thead><tr>${columns.map(c => `<th>${c.title}</th>`).join('')}</tr></thead>
                <tbody>${list.length ? list.map((item, idx) => `
                    <tr>${columns.map(c => `<td>${c.render ? c.render(item, idx, page) : (item[c.key] || '--')}</td>`).join('')}</tr>
                `).join('') : `<tr><td colspan="${columns.length}" class="empty-data">暂无数据</td></tr>`}</tbody>
            </table>
            ${paginationHTML(total, page, 15, `loadPage_${title.replace(/[^a-zA-Z]/g, '')}`)}
        </div>
    `;
}

// ==================== 工具函数 ====================

/** HTML转义 */
function esc(str) {
    const d = document.createElement('div');
    d.textContent = str || '';
    return d.innerHTML;
}

/** 时间格式化（支持Unix时间戳、日期字符串） */
function ts(t) {
    if (!t || t == 0) return '--';
    let d;
    if (typeof t === 'string') {
        d = new Date(t.replace(/-/g, '/'));
    } else {
        d = new Date(t * 1000);
    }
    if (isNaN(d.getTime())) return '--';
    return d.getFullYear() + '-' + String(d.getMonth() + 1).padStart(2, '0') + '-' + String(d.getDate()).padStart(2, '0') + ' ' + String(d.getHours()).padStart(2, '0') + ':' + String(d.getMinutes()).padStart(2, '0');
}

/** 考试结果标签 */
function examResult(r) {
    return r == 1 ? '<span class="tag tag-success">合格</span>' : r == 2 ? '<span class="tag tag-danger">不合格</span>' : '<span class="tag tag-info">未考</span>';
}

/** Strip HTML标签 */
function stripHtml(html) {
    const d = document.createElement('div');
    d.innerHTML = html;
    return d.textContent || '';
}

// ==================== Hash 路由 ====================

/**
 * 路由表：hash路径 → 加载函数
 */
const ROUTES = {
    '/admin/dashboard':              () => loadDashboard(),
    '/admin/sys/notice':             () => loadNotice(),
    '/admin/news/news':              () => loadEnterpriseNotice(),
    '/admin/course/rating':          () => loadEvaluation(),
    '/admin/course/qa':              () => loadQa(),
    '/admin/stats/monthly':          () => loadMonthlyReport(),
    '/admin/stats/safety':           () => loadMonthlySafety(),
    '/admin/stats/supplementary':    () => loadSupplementary(),
    '/admin/stats/enterprise-safety':() => loadEnterpriseSafety(),
    '/admin/stats/mandarin':         () => loadMandarin(),
    '/admin/safety/inspection':      () => loadInspection(),
    '/admin/safety/rectification':   () => loadRectification(),
    '/admin/safety/report':          () => loadSafetyReport(),
    '/admin/safety/login-log':       () => loadLoginLog(),
    '/admin/user/user':              () => loadUser(),
    '/admin/user/bureau':            () => loadBureauUser(),
    '/admin/user/organization':      () => loadOrganization(),
};

/**
 * 导航到指定hash路径
 */
function navigateTo(path) {
    window.location.hash = path;
}

/**
 * 兼容旧的 loadPage 调用
 */
const PAGE_TO_ROUTE = {
    'dashboard': '/admin/dashboard',
    'notice': '/admin/sys/notice',
    'enterprise-notice': '/admin/news/news',
    'evaluation': '/admin/course/rating',
    'qa': '/admin/course/qa',
    'monthly-report': '/admin/stats/monthly',
    'monthly-safety': '/admin/stats/safety',
    'supplementary': '/admin/stats/supplementary',
    'enterprise-safety': '/admin/stats/enterprise-safety',
    'mandarin': '/admin/stats/mandarin',
    'inspection': '/admin/safety/inspection',
    'rectification': '/admin/safety/rectification',
    'safety-report': '/admin/safety/report',
    'login-log': '/admin/safety/login-log',
    'user': '/admin/user/user',
    'bureau-user': '/admin/user/bureau',
    'organization': '/admin/user/organization',
};

function loadPage(page) {
    const route = PAGE_TO_ROUTE[page];
    if (route) {
        navigateTo(route);
    } else {
        document.getElementById('mainContent').innerHTML = '<div class="card"><div class="empty-data">页面开发中...</div></div>';
    }
}

/**
 * 根据当前hash执行路由
 */
function handleRoute() {
    const hash = window.location.hash.replace(/^#/, '') || '/admin/dashboard';
    const handler = ROUTES[hash];
    // 更新侧边栏高亮
    document.querySelectorAll('.menu-item').forEach(el => {
        el.classList.toggle('active', el.dataset.route === hash);
        if (el.dataset.route === hash) {
            const sub = el.closest('.menu-sub');
            if (sub && !sub.classList.contains('open')) {
                sub.classList.add('open');
                sub.previousElementSibling.querySelector('.arrow').classList.add('open');
            }
        }
    });
    if (handler) {
        handler();
    } else {
        document.getElementById('mainContent').innerHTML = '<div class="card"><div class="empty-data">页面开发中...</div></div>';
    }
}

// 监听hash变化
window.addEventListener('hashchange', handleRoute);
