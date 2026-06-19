/**
 * 凯力在线学习系统 - 首页总览模块
 */

async function loadDashboard() {
    const main = document.getElementById('mainContent');
    const data = await api('/dashboard');
    const onlineCount = data?.data?.online_count || 0;
    document.getElementById('onlineCount').textContent = '在线: ' + onlineCount;

    main.innerHTML = `
        <div class="quick-entry">
            <div class="entry" onclick="navigateTo('/admin/user/user')"><div class="icon"><svg viewBox="0 0 24 24" fill="#1890ff"><path d="M12 12c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm0 2c-2.67 0-8 1.34-8 4v2h16v-2c0-2.66-5.33-4-8-4z"/></svg></div><div class="text">用户管理</div></div>
            <div class="entry" onclick="navigateTo('/admin/stats/monthly')"><div class="icon"><svg viewBox="0 0 24 24" fill="#52c41a"><path d="M19 3H5c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h14c1.1 0 2-.9 2-2V5c0-1.1-.9-2-2-2zM9 17H7v-7h2v7zm4 0h-2V7h2v10zm4 0h-2v-4h2v4z"/></svg></div><div class="text">月度报表</div></div>
            <div class="entry" onclick="navigateTo('/admin/stats/safety')"><div class="icon"><svg viewBox="0 0 24 24" fill="#fa8c16"><path d="M12 1L3 5v6c0 5.55 3.84 10.74 9 12 5.16-1.26 9-6.45 9-12V5l-9-4zm-2 16l-4-4 1.41-1.41L10 14.17l6.59-6.59L18 9l-8 8z"/></svg></div><div class="text">月安全学习</div></div>
            <div class="entry" onclick="navigateTo('/admin/sys/notice')"><div class="icon"><svg viewBox="0 0 24 24" fill="#722ed1"><path d="M12 22c1.1 0 2-.9 2-2h-4c0 1.1.89 2 2 2zm6-6v-5c0-3.07-1.64-5.64-4.5-6.32V4c0-.83-.67-1.5-1.5-1.5s-1.5.67-1.5 1.5v.68C7.63 5.36 6 7.92 6 11v5l-2 2v1h16v-1l-2-2z"/></svg></div><div class="text">公告管理</div></div>
        </div>
        <div class="stat-cards">
            <div class="stat-card blue"><div class="label">在线用户数</div><div class="value">${onlineCount}</div></div>
            <div class="stat-card green"><div class="label">学习统计</div><div class="value">--</div></div>
            <div class="stat-card orange"><div class="label">用户总览</div><div class="value">--</div></div>
            <div class="stat-card purple"><div class="label">考试总览</div><div class="value">--</div></div>
        </div>
        <div class="card">
            <div class="card-title">学习统计</div>
            <div class="empty-data">数据统计图表将在前端框架对接时实现</div>
        </div>
        <div class="card">
            <div class="card-title">考试总览</div>
            <div class="empty-data">数据统计图表将在前端框架对接时实现</div>
        </div>
    `;
}
