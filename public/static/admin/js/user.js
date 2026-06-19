/**
 * 凯力在线学习系统 - 用户管理模块
 * 包含：基础用户管理、局单位用户管理、组织架构管理
 */

// ==================== 基础用户管理 ====================
function loadUser(p = 1) {
    loadGenericList('基础用户管理', '/user', [
        { title: '序号', render: (_, i, pg) => (pg - 1) * 15 + i + 1 },
        { title: '车牌号', key: 'plate_number' },
        { title: '登录账号', key: 'username' },
        { title: '真实姓名', key: 'real_name' },
        { title: '角色', key: 'role' },
        { title: '积分', key: 'points' },
        { title: '状态', render: r => r.status == 1 ? '<span class="tag tag-success">正常</span>' : '<span class="tag tag-danger">禁用</span>' },
        { title: '在职', render: r => r.job_status == 1 ? '<span class="tag tag-info">在职</span>' : '<span class="tag tag-warning">离职</span>' },
    ], p);
}

// ==================== 局单位用户管理 ====================
function loadBureauUser(p = 1) {
    loadGenericList('局单位用户管理', '/bureau-user', [
        { title: '序号', render: (_, i, pg) => (pg - 1) * 15 + i + 1 },
        { title: '车牌号', key: 'plate_number' },
        { title: '登录账号', key: 'username' },
        { title: '真实姓名', key: 'real_name' },
        { title: '角色', key: 'role' },
        { title: '积分', key: 'points' },
        { title: '状态', render: r => r.status == 1 ? '<span class="tag tag-success">正常</span>' : '<span class="tag tag-danger">禁用</span>' },
        { title: '在职', render: r => r.job_status == 1 ? '<span class="tag tag-info">在职</span>' : '<span class="tag tag-warning">离职</span>' },
    ], p);
}

// ==================== 组织架构管理 ====================
async function loadOrganization() {
    const main = document.getElementById('mainContent');
    main.innerHTML = '<div class="loading">加载中...</div>';
    const data = await api('/organization/tree');
    const tree = data?.data || [];

    function renderTree(nodes, level = 0) {
        return nodes.map(n => `
            <tr>
                <td style="padding-left:${24 + level * 24}px">${'├─ '.repeat(level ? 1 : 0)}${esc(n.name)}</td>
                <td>${n.type == 1 ? '地区' : n.type == 2 ? '局单位' : '企业'}</td>
                <td>${n.status == 1 ? '<span class="tag tag-success">正常</span>' : '<span class="tag tag-danger">禁用</span>'}</td>
            </tr>
            ${n.children ? renderTree(n.children, level + 1) : ''}
        `).join('');
    }

    main.innerHTML = `
        <div class="card">
            <div class="card-title">组织架构管理</div>
            <table>
                <thead><tr><th>组织名称</th><th>类型</th><th>状态</th></tr></thead>
                <tbody>${tree.length ? renderTree(tree) : '<tr><td colspan="3" class="empty-data">暂无数据，请先添加组织架构</td></tr>'}</tbody>
            </table>
        </div>
    `;
}
